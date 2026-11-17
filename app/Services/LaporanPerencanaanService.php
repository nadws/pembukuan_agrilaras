<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LaporanPerencanaanService
{
    public const CATEGORIES = ['pakan', 'obat_pakan', 'obat_air', 'obat_ayam'];

    public const TABLES = ['tb_pakan_perencanaan', 'tb_obat_perencanaan', 'tb_karung_perencanaan', 'tb_vaksin_perencanaan'];

    public function exists(string $date, int $kandang): bool
    {
        foreach (array_merge(self::TABLES, ['stok_produk_perencanaan']) as $table) {
            if (DB::table($table)->where(['tgl' => $date, 'id_kandang' => $kandang])->exists()) {
                return true;
            }
        }

        return DB::table('jurnal_perkiraan')->where('nomor_transaksi', $this->number($date, $kandang))
            ->where('tipe_transaksi', 'Pemakaian Pakan Harian')->exists();
    }

    public function number(string $date, int $kandang): string
    {
        return 'PPH-'.str_replace('-', '', $date).'-'.$kandang;
    }

    public function population(string $date, int $kandang): float
    {
        $initial = DB::table('kandang')->where('id_kandang', $kandang)->value('stok_awal');
        $lost = DB::table('populasi')->where('id_kandang', $kandang)->where('tgl', '<=', $date)
            ->selectRaw('COALESCE(SUM(COALESCE(mati,0)+COALESCE(jual,0)+COALESCE(afkir,0)),0) as lost')->value('lost');

        return max(0, (float) $initial - (float) $lost);
    }

    public function snapshot(string $date, int $kandang): string
    {
        $state = [];
        foreach (array_merge(self::TABLES, ['stok_produk_perencanaan']) as $table) {
            $state[$table] = DB::table($table)->where(['tgl' => $date, 'id_kandang' => $kandang])->get()
                ->map(fn ($row) => json_encode($row))->sort()->values()->all();
        }
        $state['jurnal'] = DB::table('jurnal_perkiraan')->where('nomor_transaksi', $this->number($date, $kandang))
            ->where('tipe_transaksi', 'Pemakaian Pakan Harian')->orderBy('id_jurnal_perkiraan')->get()->all();
        $state['populasi'] = $this->population($date, $kandang);

        return hash('sha256', json_encode($state));
    }

    /** All reads and writes use the shared database, never a second connection. */
    public function save(array $input, string $date, int $kandang, bool $correction, string $admin): bool
    {
        $this->assertTransactionalTables();
        $lock = DB::selectOne('SELECT GET_LOCK(?, 10) as acquired', ['laporan-perencanaan:'.DB::connection()->getDatabaseName()]);
        if ((int) $lock->acquired !== 1) {
            $this->fail('Perencanaan sedang disimpan. Coba kembali.');
        }
        try {
            return DB::transaction(function () use ($input, $date, $kandang, $correction, $admin) {
                DB::table('kandang')->where('id_kandang', $kandang)->lockForUpdate()->first();
                foreach (array_merge(self::TABLES, ['stok_produk_perencanaan']) as $table) {
                    DB::table($table)->where(['tgl' => $date, 'id_kandang' => $kandang])->lockForUpdate()->get();
                }
                // Daily byproduct baseline covers every kandang. Lock its source rows too.
                DB::table('tb_pakan_perencanaan')->where('tgl', $date)->lockForUpdate()->get();
                DB::table('tb_karung_perencanaan')->where('tgl', $date)->lockForUpdate()->get();
                DB::table('jurnal_perkiraan')->where('nomor_transaksi', $this->number($date, $kandang))
                    ->where('tipe_transaksi', 'Pemakaian Pakan Harian')->lockForUpdate()->get();
                DB::table('populasi')->where('id_kandang', $kandang)->lockForUpdate()->get();
                $exists = $this->exists($date, $kandang);
                if (! $correction && $exists) {
                    return false; // Concurrent create goes to Koreksi without changing data.
                }
                if ($correction && (! $exists || ! hash_equals($this->snapshot($date, $kandang), $input['snapshot'] ?? ''))) {
                    $this->fail('Data berubah sejak form dibuka. Buka ulang Koreksi.');
                }
                $population = $this->population($date, $kandang);
                if ($population <= 0) {
                    $this->fail('Populasi pada tanggal tersebut harus lebih dari nol.');
                }
                $products = DB::table('tb_produk_perencanaan')->orderBy('id_produk')->lockForUpdate()->get()->keyBy('id_produk');
                [$feed, $medicine, $usage, $total] = $this->calculate($input, $population, $products);
                $scope = ['tgl' => $date, 'id_kandang' => $kandang];
                $oldFeed = DB::table('tb_pakan_perencanaan')->where($scope)->get();
                $oldMedicine = DB::table('tb_obat_perencanaan')->where($scope)->whereIn('kategori', array_slice(self::CATEGORIES, 1))->get();
                $oldBags = DB::table('tb_karung_perencanaan')->where($scope)->get();
                $notes = $oldFeed->merge($oldMedicine)->merge($oldBags)->pluck('no_nota')->unique()->all();
                if (in_array('', $notes, true)) {
                    $this->fail('Nota detail lama kosong. Rekonsiliasi diperlukan sebelum koreksi.');
                }
                $managedIds = $products->whereIn('kategori', self::CATEGORIES)->keys()->all();
                $oldStockQuery = DB::table('stok_produk_perencanaan')->where($scope)->whereIn('no_nota', $notes)
                    ->whereIn('id_pakan', $managedIds)->where('pcs', 0)->where('h_opname', 'T')
                    ->where('opname', 'T')->where('penyesuaian', 'T');
                $oldStock = (clone $oldStockQuery)->get();
                $this->assertOldUsage($oldFeed, $oldMedicine, $oldStock, $population);
                $oldTotal = (float) $oldFeed->sum('gr');
                $bags = (float) $input['kg_pakan_box'];
                $oldBagQty = (float) $oldBags->sum('karung');
                $this->adjustByproducts($date, $kandang, $total, $oldTotal, $bags, $oldBagQty, $admin);
                $oldIds = $oldStock->pluck('id_stok_telur')->all();
                $stockRows = [];
                $note = $notes[0] ?? ('LP-'.Str::upper(Str::random(12)));
                foreach ($usage as $id => $quantity) {
                    $this->assertAmount($quantity, 'Pemakaian stok');
                    $rows = DB::table('stok_produk_perencanaan')->where('id_pakan', $id)->orderBy('tgl')->orderBy('id_stok_telur')->lockForUpdate()->get();
                    $this->assertStock($rows, $oldIds, $date, $quantity, $products[$id]->nm_produk);
                    $purchases = $rows->filter(fn ($s) => $s->tgl >= '2023-01-01' && $s->tgl <= $date && $s->pcs > 0 && $s->admin !== 'import' && $s->h_opname === 'T');
                    $pcs = (float) $purchases->sum('pcs');
                    if ($pcs <= 0 || (float) $purchases->sum('total_rp') <= 0) {
                        $this->fail('Harga pembelian tidak tersedia: '.$products[$id]->nm_produk.'.');
                    }
                    $value = round($quantity * (float) $purchases->sum('total_rp') / $pcs, 2);
                    $this->assertAmount($value, 'Nilai stok');
                    $prior = $oldStock->where('id_pakan', $id);
                    $stockRows[] = $scope + ['id_pakan' => $id, 'pcs' => 0, 'pcs_kredit' => $quantity, 'total_rp' => $value,
                        'biaya_dll' => 0, 'no_nota' => $note, 'admin' => $admin, 'check' => $prior->contains('check', 'Y') ? 'Y' : 'T',
                        'cek_admin' => $prior->firstWhere('check', 'Y')->cek_admin ?? '', 'opname' => 'T', 'h_opname' => 'T', 'penyesuaian' => 'T'];
                }
                // Delete only stock proven to belong to editable details; preserve vaccines, receipts, adjustments and other notes.
                if ($oldIds) {
                    DB::table('stok_produk_perencanaan')->whereIn('id_stok_telur', $oldIds)->delete();
                }
                DB::table('tb_pakan_perencanaan')->where($scope)->delete();
                DB::table('tb_obat_perencanaan')->where($scope)->whereIn('kategori', array_slice(self::CATEGORIES, 1))->delete();
                DB::table('tb_karung_perencanaan')->where($scope)->delete();
                foreach ($feed as $row) {
                    DB::table('tb_pakan_perencanaan')->insert($scope + $row + ['no_nota' => $note, 'admin' => $admin]);
                }
                foreach ($medicine as $row) {
                    DB::table('tb_obat_perencanaan')->insert($scope + $row + ['no_nota' => $note, 'admin' => $admin]);
                }
                DB::table('tb_karung_perencanaan')->insert($scope + ['karung' => $bags, 'gr' => floor($total / ($bags * 1000)),
                    'gr2' => round(($total / ($bags * 1000) - floor($total / ($bags * 1000))) * 10, 2), 'no_nota' => $note, 'admin' => $admin]);
                DB::table('stok_produk_perencanaan')->insert($stockRows);
                $costs = [];
                $journalStock = DB::table('stok_produk_perencanaan as s')->join('tb_produk_perencanaan as p', 'p.id_produk', '=', 's.id_pakan')
                    ->where('s.tgl', $date)->where('s.id_kandang', $kandang)->where('s.pcs', 0)->where('s.pcs_kredit', '>', 0)
                    ->whereIn('p.kategori', self::CATEGORIES)->get(['p.kategori', 's.total_rp']);
                foreach ($journalStock as $row) {
                    $bucket = $row->kategori === 'pakan' ? 'pakan' : 'obat';
                    $costs[$bucket] = ($costs[$bucket] ?? 0) + $row->total_rp;
                }
                $this->journal($date, $kandang, $costs);

                return true;
            }, 3);
        } finally {
            DB::select('SELECT RELEASE_LOCK(?)', ['laporan-perencanaan:'.DB::connection()->getDatabaseName()]);
        }
    }

    private function calculate(array $input, float $population, $products): array
    {
        $feed = $medicine = $usage = [];
        $total = round($population * (float) $input['gr_pakan_ekor'], 6);
        $this->assertAmount($total, 'Total pakan');
        $percent = 0;
        $used = [];
        foreach ($input['pakan'] as $row) {
            $id = (int) $row['id_produk'];
            $this->product($products, $id, 'pakan');
            if (isset($used[$id])) {
                $this->fail('Pakan yang sama tidak boleh dipilih dua kali.');
            }
            $used[$id] = true;
            $percent += (float) $row['persen'];
            $quantity = round($total * (float) $row['persen'] / 100, 6);
            $feed[] = ['id_produk_pakan' => $id, 'persen' => $row['persen'], 'gr' => $quantity];
            $usage[$id] = $quantity;
        }
        if (abs($percent - 100) > 0.000001) {
            $this->fail('Total persentase pakan harus 100%.');
        }
        foreach (['obat_pakan', 'obat_air', 'obat_ayam'] as $category) {
            foreach ($input[$category] ?? [] as $row) {
                if (empty($row['id_produk'])) {
                    continue;
                }
                $id = (int) $row['id_produk'];
                $this->product($products, $id, $category);
                $dose = (float) ($row['dosis'] ?? 0);
                $mix = (float) ($row['campuran'] ?? 0);
                if ($dose <= 0 || ($category !== 'obat_ayam' && $mix <= 0)) {
                    $this->fail('Dosis dan campuran obat harus lebih dari nol.');
                }
                $quantity = match ($category) {
                    'obat_pakan' => ($total / 1000) / $mix * $dose,
                    'obat_ayam' => $population * $dose,
                    default => $dose, // Kandang: dosis air is total consumed, campuran is instruction.
                };
                $this->assertAmount($quantity, 'Pemakaian obat');
                $usage[$id] = ($usage[$id] ?? 0) + round($quantity, 6);
                $medicine[] = ['kategori' => $category, 'id_produk' => $id, 'dosis' => $dose, 'campuran' => $mix,
                    'waktu' => $row['waktu'] ?? null, 'cara_pemakaian' => $row['cara_pemakaian'] ?? null, 'ket' => $row['ket'] ?? null];
            }
        }

        return [$feed, $medicine, $usage, array_sum(array_column($feed, 'gr'))];
    }

    private function product($products, int $id, string $category): void
    {
        if (! isset($products[$id]) || $products[$id]->kategori !== $category) {
            $this->fail('Produk tidak cocok dengan kategori '.$category.'.');
        }
    }

    private function assertOldUsage($feed, $medicine, $stock, float $population): void
    {
        $expected = [];
        foreach ($feed as $row) {
            $expected[$row->id_produk_pakan] = ($expected[$row->id_produk_pakan] ?? 0) + $row->gr;
        }
        foreach ($medicine as $row) {
            if ($row->kategori === 'obat_pakan' && $row->campuran <= 0) {
                $this->fail('Campuran obat lama tidak valid. Rekonsiliasi diperlukan.');
            }
            $quantity = match ($row->kategori) {
                'obat_pakan' => $feed->sum('gr') / 1000 / $row->campuran * $row->dosis,
                'obat_ayam' => $population * $row->dosis,
                default => $row->dosis,
            };
            $expected[$row->id_produk] = ($expected[$row->id_produk] ?? 0) + $quantity;
        }
        $actual = $stock->groupBy('id_pakan')->map(fn ($rows) => $rows->sum('pcs_kredit'))->all();
        foreach (array_unique(array_merge(array_keys($actual), array_keys($expected))) as $id) {
            if (abs(($actual[$id] ?? 0) - ($expected[$id] ?? 0)) > 0.01) {
                $this->fail('Detail dan stok lama tidak cocok (produk '.$id.'). Rekonsiliasi diperlukan; stok lain tidak dihapus.');
            }
        }
    }

    private function assertStock($rows, array $oldIds, string $date, float $quantity, string $name): void
    {
        $before = $after = 0;
        $days = $rows->groupBy('tgl');
        $dates = $days->keys()->push($date)->unique()->sort();
        foreach ($dates as $day) {
            foreach ($days->get($day, collect()) as $row) {
                $before += $row->pcs - $row->pcs_kredit;
                if (! in_array($row->id_stok_telur, $oldIds)) {
                    $after += $row->pcs - $row->pcs_kredit;
                }
            }
            if ($day === $date) {
                $after -= $quantity;
            }
            if ($day >= $date && $after < min(0, $before) - 0.01) {
                $this->fail('Stok tidak cukup: '.$name.' pada '.$day.'.');
            }
        }
    }

    private function adjustByproducts(string $date, int $kandang, float $total, float $oldTotal, float $bags, float $oldBags, string $admin): void
    {
        $deltas = ['pupuk' => ($total - $oldTotal) / 1000 * 0.3, 'karung' => -($bags - $oldBags)];
        $rows = DB::table('stok_ayam')->where('id_gudang', 1)->whereIn('jenis', ['pupuk', 'karung'])->orderBy('tgl')->lockForUpdate()->get();
        foreach ($deltas as $type => $delta) {
            if (abs($delta) < 0.000001) {
                continue;
            }
            // Legacy stock has no kandang link. Verify the daily baseline before applying an additive delta.
            $expected = $type === 'pupuk'
                ? (float) DB::table('tb_pakan_perencanaan')->where('tgl', $date)->sum('gr') / 1000 * 0.3
                : -(float) DB::table('tb_karung_perencanaan')->where('tgl', $date)->sum('karung');
            $actual = $rows->filter(fn ($r) => $r->tgl === $date && $r->jenis === $type &&
                ($r->no_nota === '' || str_starts_with($r->no_nota, 'PPH-D-')))->sum(fn ($r) => $r->debit - $r->kredit);
            if (abs($expected - $actual) > 0.01) {
                $this->fail('Saldo '.$type.' lama tidak dapat direkonsiliasi tanpa kaitan nota/kandang. Koreksi dibatalkan; perlu rekonsiliasi atau migrasi terpisah.');
            }
            $balance = 0;
            foreach ($rows->where('jenis', $type)->groupBy('tgl') as $day => $daily) {
                $balance += $daily->sum(fn ($r) => $r->debit - $r->kredit);
                if ($day >= $date && $balance + $delta < min(0, $balance) - 0.01) {
                    $this->fail('Stok '.$type.' tidak cukup pada '.$day.'.');
                }
            }
            if ($delta < 0 && $rows->where('jenis', $type)->where('tgl', '<=', $date)->sum(fn ($r) => $r->debit - $r->kredit) + $delta < -0.01) {
                $this->fail('Stok '.$type.' tidak cukup pada '.$date.'.');
            }
            DB::table('stok_ayam')->insert(['tgl' => $date, 'no_nota' => 'PPH-D-'.str_replace('-', '', $date).'-'.$kandang.'-'.Str::upper(Str::random(10)),
                'debit' => max(0, $delta), 'kredit' => max(0, -$delta), 'id_gudang' => 1, 'admin' => $admin,
                'transfer' => 'T', 'cek' => 'T', 'jenis' => $type]);
        }
    }

    private function journal(string $date, int $kandang, array $costs): void
    {
        $number = $this->number($date, $kandang);
        $type = 'Pemakaian Pakan Harian';
        $details = [];
        $name = DB::table('kandang')->where('id_kandang', $kandang)->value('nm_kandang');
        foreach (['pakan' => ['Biaya Pokok Penjualan Telur (Pakan)', 'Persediaan Pakan'],
            'obat' => ['Biaya Pokok Penjualan Telur (Vitamin/Obat)', 'Persediaan Vitamin/Obat']] as $bucket => $names) {
            $amount = round($costs[$bucket] ?? 0, 2);
            $this->assertAmount($amount, 'Nilai jurnal');
            if ($amount <= 0) {
                continue;
            }
            foreach ($names as $index => $accountName) {
                $accounts = DB::table('akun_perkiraan')->where('nama', $accountName)->where('aktif', 1)->lockForUpdate()->get();
                if ($accounts->count() !== 1) {
                    $this->fail('Akun aktif harus tepat satu: '.$accountName.'.');
                }
                $details[] = ['id_akun_perkiraan' => $accounts->first()->id_akun_perkiraan, 'tanggal' => $date,
                    'nomor_transaksi' => $number, 'tipe_transaksi' => $type, 'urutan_detail' => count($details) + 1,
                    'deskripsi' => 'Pemakaian '.$bucket.' - Kandang '.$name.' - '.$date,
                    'debit' => $index === 0 ? $amount : 0, 'kredit' => $index === 1 ? $amount : 0];
            }
        }
        $this->assertAmount(array_sum(array_column($details, 'debit')), 'Total jurnal');
        $old = DB::table('jurnal_perkiraan')->where('nomor_transaksi', $number)->where('tipe_transaksi', $type);
        $batches = (clone $old)->lockForUpdate()->pluck('id_impor_jurnal_perkiraan')->unique();
        $old->delete();
        foreach ($batches as $id) {
            $remaining = DB::table('jurnal_perkiraan')->where('id_impor_jurnal_perkiraan', $id)->get();
            if ($remaining->isEmpty()) {
                DB::table('impor_jurnal_perkiraan')->where('id_impor_jurnal_perkiraan', $id)->delete();
            } else {
                DB::table('impor_jurnal_perkiraan')->where('id_impor_jurnal_perkiraan', $id)->update([
                    'jumlah_transaksi' => $remaining->unique(fn ($r) => $r->tipe_transaksi.'|'.$r->nomor_transaksi)->count(),
                    'jumlah_detail' => $remaining->count(), 'total_debit' => $remaining->sum('debit'), 'total_kredit' => $remaining->sum('kredit'),
                    'periode_awal' => $remaining->min('tanggal'), 'periode_akhir' => $remaining->max('tanggal'), 'updated_at' => now(),
                ]);
            }
        }
        $batch = DB::table('impor_jurnal_perkiraan')->insertGetId(['nama_file' => $type.' '.$number,
            'hash_file' => hash('sha256', strtolower($type).'|'.$number.'|'.Str::uuid()), 'periode_awal' => $date, 'periode_akhir' => $date,
            'jumlah_transaksi' => 1, 'jumlah_detail' => count($details), 'total_debit' => array_sum(array_column($details, 'debit')),
            'total_kredit' => array_sum(array_column($details, 'kredit')), 'status' => 'aktif', 'diimpor_oleh' => auth()->id(),
            'created_at' => now(), 'updated_at' => now()]);
        foreach ($details as $row) {
            DB::table('jurnal_perkiraan')->insert($row + ['id_impor_jurnal_perkiraan' => $batch, 'created_at' => now(), 'updated_at' => now()]);
        }
    }

    private function assertTransactionalTables(): void
    {
        $tables = array_merge(self::TABLES, ['stok_produk_perencanaan', 'stok_ayam', 'jurnal_perkiraan', 'impor_jurnal_perkiraan', 'kandang', 'populasi', 'tb_produk_perencanaan', 'akun_perkiraan']);
        $engines = DB::table('information_schema.tables')->where('table_schema', DB::connection()->getDatabaseName())->whereIn('table_name', $tables)->get(['table_name', 'engine']);
        if ($engines->count() !== count($tables) || $engines->contains(fn ($row) => strtoupper($row->engine ?? '') !== 'INNODB')) {
            $this->fail('Seluruh tabel perencanaan, stok, dan jurnal wajib InnoDB. Simpan tidak aman tanpa perubahan skema.');
        }
    }

    private function fail(string $message): never
    {
        throw ValidationException::withMessages(['perencanaan' => $message]);
    }

    private function assertAmount(float $amount, string $label): void
    {
        // MySQL strict mode is off; never let a decimal(24,12) silently clamp.
        if (! is_finite($amount) || $amount < 0 || $amount > 999999999999.99) {
            $this->fail($label.' di luar batas penyimpanan. Periksa dosis dan campuran.');
        }
    }
}
