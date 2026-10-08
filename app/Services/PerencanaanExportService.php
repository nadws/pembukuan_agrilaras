<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class PerencanaanExportService
{
    public function build(string $start, string $end, ?int $kandang): array
    {
        return DB::transaction(function () use ($start, $end, $kandang) {
            $houses = DB::table('kandang')->get()->keyBy('id_kandang');
            $products = DB::table('tb_produk_perencanaan')->get()->keyBy('id_produk');
            $units = DB::table('tb_satuan')->pluck('nm_satuan', 'id_satuan');
            $categories = ['pakan' => 'Pakan', 'obat_pakan' => 'Obat melalui pakan', 'obat_air' => 'Obat air minum', 'obat_ayam' => 'Obat per ekor', 'vitamin' => 'Vitamin', 'vaksin' => 'Vaksin'];
            $scope = fn ($table) => DB::table($table)->whereBetween('tgl', [$start, $end])
                ->when($kandang, fn ($q) => $q->where('id_kandang', $kandang))->orderBy('tgl')->orderBy('id_kandang');
            $feed = $scope('tb_pakan_perencanaan')->orderBy('id_pakan_perencanaan')->get();
            $medicine = $scope('tb_obat_perencanaan')->orderBy('id_obat_perencanaan')->get();
            $vaccines = $scope('tb_vaksin_perencanaan')->orderBy('id_vaksin')->get();
            $selectedStock = $scope('stok_produk_perencanaan')->orderBy('id_stok_telur')->get();
            $groups = [];
            foreach ($feed->concat($medicine)->concat($vaccines)->concat($selectedStock) as $row) {
                if (! $row->id_kandang) {
                continue;
                }
                $key = $row->tgl.'|'.$row->id_kandang;
                $groups[$key] ??= ['date' => $row->tgl, 'house' => (int) $row->id_kandang, 'grams' => 0, 'cost' => 0];
            }
            foreach ($feed as $row) {
            $groups[$row->tgl.'|'.$row->id_kandang]['grams'] += (float) $row->gr;
            }
            foreach ($selectedStock as $row) {
                if ($row->id_kandang && $row->pcs_kredit > 0 && $row->h_opname === 'T') {
                    $groups[$row->tgl.'|'.$row->id_kandang]['cost'] += (float) $row->total_rp;
                }
            }
            $journalRows = [];
            $journalTotals = [];
            $accounts = DB::table('akun_perkiraan')->pluck('nama', 'id_akun_perkiraan');
            $journals = DB::table('jurnal_perkiraan')->where('tipe_transaksi', 'Pemakaian Pakan Harian')
                ->whereRaw('SUBSTRING(nomor_transaksi, 5, 8) BETWEEN ? AND ?', [str_replace('-', '', $start), str_replace('-', '', $end)])
                ->orderBy('nomor_transaksi')->orderBy('id_jurnal_perkiraan')->get();
            foreach ($journals as $row) {
                if (! preg_match('/^PPH-(\d{4})(\d{2})(\d{2})-(\d+)$/', $row->nomor_transaksi, $match)) {
                continue;
                }
                $date = "$match[1]-$match[2]-$match[3]";
                $house = (int) $match[4];
                if ($date < $start || $date > $end || ($kandang && $house !== $kandang)) {
                continue;
                }
                $key = $date.'|'.$house;
                $groups[$key] ??= ['date' => $date, 'house' => $house, 'grams' => 0, 'cost' => 0];
                $journalTotals[$key] ??= [0, 0];
                $journalTotals[$key][0] += (float) $row->debit;
                $journalTotals[$key][1] += (float) $row->kredit;
                $journalRows[] = [$date, $houses[$house]->nm_kandang ?? 'Kandang tidak ditemukan', $row->nomor_transaksi,
                    $accounts[$row->id_akun_perkiraan] ?? 'Akun tidak ditemukan', (float) $row->debit, (float) $row->kredit, $row->deskripsi ?? ''];
            }
            ksort($groups);
            $losses = DB::table('populasi')->whereIn('id_kandang', array_column($groups, 'house'))->where('tgl', '<=', $end)
                ->select('id_kandang', 'tgl')->selectRaw('SUM(COALESCE(mati,0)+COALESCE(jual,0)+COALESCE(afkir,0)) AS lost')
                ->groupBy('id_kandang', 'tgl')->orderBy('tgl')->get()->groupBy('id_kandang');
            $populationState = [];
            $summary = [];
            foreach ($groups as $key => &$group) {
                $house = $group['house'];
                $populationState[$house] ??= ['index' => 0, 'lost' => 0];
                $history = $losses->get($house, collect());
                while (isset($history[$populationState[$house]['index']]) && $history[$populationState[$house]['index']]->tgl <= $group['date']) {
                    $populationState[$house]['lost'] += (float) $history[$populationState[$house]['index']]->lost;
                    $populationState[$house]['index']++;
                }
                $group['population'] = max(0, (float) ($houses[$house]->stok_awal ?? 0) - $populationState[$house]['lost']);
                [$debit, $credit] = $journalTotals[$key] ?? [0, 0];
                $summary[] = [$group['date'], $houses[$house]->nm_kandang ?? 'Kandang tidak ditemukan', $group['population'],
                    $group['population'] ? $group['grams'] / $group['population'] : null, $group['grams'] / 1000,
                    $group['cost'], $debit, $credit, round($debit - $credit, 2),
                    ! isset($journalTotals[$key]) ? 'Jurnal belum ada' : (abs($debit - $credit) > 0.01 ? 'Periksa: jurnal tidak seimbang' : 'Jurnal seimbang')];
            }
            unset($group);
            $feedRows = [];
            $expected = [];
            foreach ($feed as $row) {
                $product = $products->get($row->id_produk_pakan);
                $group = $groups[$row->tgl.'|'.$row->id_kandang];
                $key = $row->tgl.'|'.$row->id_kandang.'|'.$row->id_produk_pakan;
                $expected[$key] = ($expected[$key] ?? 0) + (float) $row->gr;
                $feedRows[] = [$row->tgl, $houses[$row->id_kandang]->nm_kandang ?? 'Kandang tidak ditemukan', $product->nm_produk ?? 'Produk tidak ditemukan',
                    (float) $row->persen, (float) $row->gr, (float) $row->gr / 1000, $group['population'],
                    $group['population'] ? $group['grams'] / $group['population'] : null, $row->admin ?? ''];
            }
            $medicineRows = [];
            foreach ($medicine as $row) {
                $product = $products->get($row->id_produk);
                $group = $groups[$row->tgl.'|'.$row->id_kandang];
                $quantity = match ($row->kategori) {
                    'obat_pakan' => $row->campuran > 0 ? $group['grams'] / 1000 / $row->campuran * $row->dosis : null,
                    'obat_ayam' => $group['population'] * $row->dosis,
                    default => (float) $row->dosis,
                };
                if (in_array($row->kategori, LaporanPerencanaanService::CATEGORIES, true)) {
                    $key = $row->tgl.'|'.$row->id_kandang.'|'.$row->id_produk;
                    $expected[$key] = $quantity === null || (array_key_exists($key, $expected) && $expected[$key] === null) ? null : ($expected[$key] ?? 0) + round($quantity, 6);
                }
                $medicineRows[] = [$row->tgl, $houses[$row->id_kandang]->nm_kandang ?? 'Kandang tidak ditemukan', $categories[$row->kategori] ?? $row->kategori,
                    $product->nm_produk ?? 'Produk tidak ditemukan', (float) $row->dosis, $units[$product->dosis_satuan ?? 0] ?? '',
                    $row->campuran === null ? null : (float) $row->campuran, $units[$product->campuran_satuan ?? 0] ?? '',
                    $row->waktu ? substr($row->waktu, 0, 5) : '', $row->cara_pemakaian ?? '', $row->ket ?? '',
                    $quantity === null ? null : round($quantity, 6), $row->admin ?? ''];
            }
            $vaccineRows = $vaccines->map(fn ($r) => [$r->tgl, $houses[$r->id_kandang]->nm_kandang ?? 'Kandang tidak ditemukan',
                $r->nm_vaksin, (float) $r->qty, (float) $r->ttl_rp, (float) $r->biaya_dll, $r->admin ?? ''])->all();
            $actual = [];
            foreach ($selectedStock as $row) {
                if ($row->id_kandang && $row->h_opname === 'T' && $row->pcs_kredit > 0 && in_array($products[$row->id_pakan]->kategori ?? '', LaporanPerencanaanService::CATEGORIES, true)) {
                    $key = $row->tgl.'|'.$row->id_kandang.'|'.$row->id_pakan;
                    $actual[$key] = ($actual[$key] ?? 0) + (float) $row->pcs_kredit;
                }
            }
            $checks = [];
            $checkKeys = array_unique(array_merge(array_keys($expected), array_keys($actual)));
            sort($checkKeys);
            foreach ($checkKeys as $key) {
                [$date, $house, $id] = explode('|', $key);
                $input = array_key_exists($key, $expected) ? $expected[$key] : 0;
                $stock = $actual[$key] ?? 0;
                $difference = $input === null ? null : round($stock - $input, 6);
                $checks[] = [$date, $houses[$house]->nm_kandang ?? 'Kandang tidak ditemukan', $products[$id]->nm_produk ?? 'Produk tidak ditemukan',
                    $units[$products[$id]->dosis_satuan ?? 0] ?? '', $input, $stock, $difference,
                    $difference === null ? 'Periksa: campuran input tidak valid' : (abs($difference) <= 0.01 ? 'Cocok' : 'Periksa: input dan stok berbeda')];
            }
            // Warehouse balance includes other houses' movements; hiding them would break reconciliation.
            $productIds = $selectedStock->pluck('id_pakan')->concat($feed->pluck('id_produk_pakan'))->concat($medicine->pluck('id_produk'))->unique();
            $opening = DB::table('stok_produk_perencanaan')->whereIn('id_pakan', $productIds)->where('tgl', '<', $start)
                ->select('id_pakan')->selectRaw('SUM(pcs-pcs_kredit) AS balance')->groupBy('id_pakan')->pluck('balance', 'id_pakan');
            $movements = DB::table('stok_produk_perencanaan')->whereIn('id_pakan', $productIds)->whereBetween('tgl', [$start, $end])
                ->orderBy('id_pakan')->orderBy('tgl')->orderBy('id_stok_telur')->get()->groupBy('id_pakan');
            $stockRows = [];
            $balanceRows = [];
            foreach ($productIds->sortBy(fn ($id) => $products[$id]->nm_produk ?? '')->values() as $id) {
                $product = $products->get($id);
                $name = $product->nm_produk ?? 'Produk tidak ditemukan';
                $unit = $units[$product->dosis_satuan ?? 0] ?? '';
                $balance = (float) ($opening[$id] ?? 0);
                $initial = $balance;
                $incoming = $outgoing = $selectedUsage = 0;
                $stockRows[] = [$start, $name, 'Gudang', 'Saldo awal periode', null, null, $balance, $unit, null, ''];
                foreach ($movements->get($id, collect()) as $row) {
                    $incoming += (float) $row->pcs;
                    $outgoing += (float) $row->pcs_kredit;
                    $balance += (float) $row->pcs - (float) $row->pcs_kredit;
                    if (! $kandang || (int) $row->id_kandang === $kandang) {
                    $selectedUsage += (float) $row->pcs_kredit;
                    }
                    $label = match (true) {
                        $row->opname === 'Y' || $row->h_opname === 'Y' => 'Opname / saldo stok',
                        $row->penyesuaian === 'Y' => 'Penyesuaian stok',
                        $row->pcs > 0 => 'Stok masuk',
                        default => 'Pemakaian / stok keluar',
                    };
                    $stockRows[] = [$row->tgl, $name, $row->id_kandang ? ($houses[$row->id_kandang]->nm_kandang ?? 'Kandang tidak ditemukan') : 'Gudang',
                        $label, (float) $row->pcs, (float) $row->pcs_kredit, $balance, $unit, (float) $row->total_rp, $row->admin ?? ''];
                }
                $balanceRows[] = [$name, $unit, $initial, $incoming, $outgoing, $balance, $selectedUsage];
            }
            $scopeText = 'Periode '.date('d/m/Y', strtotime($start)).' – '.date('d/m/Y', strtotime($end)).' | '.($kandang ? ($houses[$kandang]->nm_kandang ?? '') : 'Semua kandang');
            $sheet = fn ($title, $columns, $rows, $note) => compact('title', 'columns', 'rows', 'note') + ['scope' => $scopeText];

            return [
                $sheet('Ringkasan', ['Tanggal', 'Kandang', 'Populasi (ekor)', 'Pakan per ekor (gram)', 'Total pakan (kg)', 'Biaya pemakaian (Rp)', 'Debit PPH (Rp)', 'Kredit PPH (Rp)', 'Selisih jurnal (Rp)', 'Pemeriksaan jurnal'], $summary,
                    'Ringkasan harian per kandang. Biaya memakai nilai stok tersimpan; rincian input ada pada sheet Pakan, Obat Vitamin dan Vaksin.'),
                $sheet('Pakan', ['Tanggal', 'Kandang', 'Nama pakan', 'Komposisi (%)', 'Pemakaian (gram)', 'Pemakaian (kg)', 'Populasi (ekor)', 'Total pakan per ekor (gram)', 'Diinput oleh'], $feedRows,
                    'Komposisi dan gram mengikuti input tersimpan. Total pakan per ekor = seluruh pakan hari/kandang dibagi populasi pada tanggal tersebut.'),
                $sheet('Obat Vitamin', ['Tanggal', 'Kandang', 'Pemakaian melalui', 'Nama obat / vitamin', 'Dosis input', 'Satuan dosis', 'Campuran input', 'Satuan campuran', 'Waktu', 'Cara pemakaian', 'Keterangan', 'Total pemakaian', 'Diinput oleh'], $medicineRows,
                    'Dosis/campuran mengikuti input kandang. Total: obat pakan = kg pakan ÷ campuran × dosis; obat per ekor = populasi × dosis; obat air = dosis total. Satuan total mengikuti satuan dosis.'),
                $sheet('Vaksin', ['Tanggal', 'Kandang', 'Nama vaksin', 'Jumlah input', 'Nilai vaksin (Rp)', 'Biaya tambahan (Rp)', 'Diinput oleh'], $vaccineRows,
                    'Jumlah dan nilai mengikuti input vaksin tersimpan. Sumber vaksin tidak menyimpan satuan; satuan tidak ditebak.'),
                $sheet('Cocokkan Pemakaian', ['Tanggal', 'Kandang', 'Produk', 'Satuan', 'Pemakaian dari input', 'Stok keluar tercatat', 'Selisih stok − input', 'Hasil pemeriksaan'], $checks,
                    'Pakan dan obat pakan/air/per ekor dibandingkan per produk/tanggal/kandang. Cocok jika selisih ≤ 0,01. Vaksin tidak dicocokkan otomatis karena inputnya tidak menyimpan kaitan produk stok.'),
                $sheet('Mutasi Stok', ['Tanggal', 'Produk', 'Kandang / lokasi', 'Jenis mutasi', 'Debit / masuk', 'Kredit / keluar', 'Saldo berjalan', 'Satuan', 'Nilai mutasi (Rp)', 'Dicatat oleh'], $stockRows,
                    'Saldo gudang bersama untuk produk yang dipakai kandang pilihan. Semua mutasi produk ikut tampil, termasuk kandang lain, agar saldo awal + masuk − keluar = saldo akhir. Urutan dalam tanggal mengikuti pencatatan.'),
                $sheet('Saldo Produk', ['Produk', 'Satuan', 'Saldo awal', 'Debit / masuk', 'Kredit / keluar', 'Saldo akhir', 'Keluar untuk kandang pilihan'], $balanceRows,
                    'Saldo gudang mencakup semua kandang. Kolom terakhir hanya pemakaian sesuai pilihan kandang; jika semua kandang dipilih, nilainya sama dengan total keluar.'),
                $sheet('Jurnal PPH', ['Tanggal', 'Kandang', 'Nomor transaksi', 'Nama akun', 'Debit (Rp)', 'Kredit (Rp)', 'Keterangan'], $journalRows,
                    'Jurnal PPH tersimpan sesuai tanggal/kandang. Export membaca data tanpa mengubah stok atau membukukan ulang jurnal.'),
            ];
        });
    }
}
