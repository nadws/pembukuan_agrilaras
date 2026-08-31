<?php

namespace App\Services;

use App\Models\AkunPerkiraan;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class LaporanArusKasPerkiraanService
{
    public function buat(Collection|array $akunKas, Carbon $awal, Carbon $akhir): array
    {
        $akunKasIds = collect($akunKas)->map(fn ($account) => $account instanceof AkunPerkiraan ? $account->getKey() : (int) $account)->filter()->values()->all();
        $periods = collect(CarbonPeriod::create(
            $awal->copy()->startOfMonth(),
            '1 month',
            $akhir->copy()->startOfMonth()
        ))->map(fn ($date) => Carbon::instance($date)->startOfMonth());
        $periodKeys = $periods->map->format('Y-m')->all();

        $movementQuery = DB::table('jurnal_perkiraan as kas')
            ->join('impor_jurnal_perkiraan as impor', 'impor.id_impor_jurnal_perkiraan', '=', 'kas.id_impor_jurnal_perkiraan')
            ->where('impor.status', 'aktif')
            ->whereIn('kas.id_akun_perkiraan', $akunKasIds)
            ->whereBetween('kas.tanggal', [$awal->toDateString(), $akhir->toDateString()])
            ->groupBy('kas.id_impor_jurnal_perkiraan', 'kas.tanggal', 'kas.nomor_transaksi')
            ->groupByRaw("COALESCE(kas.tipe_transaksi, '')")
            ->select([
                'kas.id_impor_jurnal_perkiraan',
                'kas.tanggal',
                'kas.nomor_transaksi',
            ])
            ->selectRaw("COALESCE(kas.tipe_transaksi, '') as tipe_key")
            ->selectRaw('SUM(kas.debit) as kas_debit, SUM(kas.kredit) as kas_kredit');

        $movements = (clone $movementQuery)->get()->keyBy(fn ($row) => $this->transactionKey($row));

        $counterparts = DB::table('jurnal_perkiraan as lawan')
            ->joinSub($movementQuery, 'mutasi', function ($join) {
                $join->on('mutasi.id_impor_jurnal_perkiraan', '=', 'lawan.id_impor_jurnal_perkiraan')
                    ->on('mutasi.tanggal', '=', 'lawan.tanggal')
                    ->on('mutasi.nomor_transaksi', '=', 'lawan.nomor_transaksi')
                    ->whereRaw("COALESCE(lawan.tipe_transaksi, '') = mutasi.tipe_key");
            })
            ->join('akun_perkiraan as akun', 'akun.id_akun_perkiraan', '=', 'lawan.id_akun_perkiraan')
            ->whereNotIn('lawan.id_akun_perkiraan', $akunKasIds)
            ->groupBy(
                'mutasi.id_impor_jurnal_perkiraan', 'mutasi.tanggal', 'mutasi.nomor_transaksi',
                'mutasi.tipe_key', 'mutasi.kas_debit', 'mutasi.kas_kredit',
                'akun.id_akun_perkiraan', 'akun.kode_perkiraan', 'akun.nama'
            )
            ->select([
                'mutasi.id_impor_jurnal_perkiraan', 'mutasi.tanggal', 'mutasi.nomor_transaksi',
                'mutasi.tipe_key', 'mutasi.kas_debit', 'mutasi.kas_kredit',
                'akun.id_akun_perkiraan', 'akun.kode_perkiraan', 'akun.nama',
            ])
            ->selectRaw('SUM(lawan.debit) as lawan_debit, SUM(lawan.kredit) as lawan_kredit')
            ->selectRaw("GROUP_CONCAT(DISTINCT COALESCE(lawan.deskripsi, '') SEPARATOR ' ') as deskripsi_transaksi")
            ->get()
            ->groupBy(fn ($row) => $this->transactionKey($row));

        $incoming = [];
        $outgoing = [];

        foreach ($movements as $key => $movement) {
            $net = round((float) $movement->kas_debit - (float) $movement->kas_kredit, 2);
            if (abs($net) < 0.005) {
                continue;
            }

            $direction = $net > 0 ? 'incoming' : 'outgoing';
            $amount = abs($net);
            $candidates = collect($counterparts->get($key, []))->map(function ($row) use ($direction) {
                $row->nilai_lawan = $direction === 'incoming'
                    ? max(0, (float) $row->lawan_kredit - (float) $row->lawan_debit)
                    : max(0, (float) $row->lawan_debit - (float) $row->lawan_kredit);

                if (preg_match('/utang usaha/i', (string) $row->nama)) {
                    $row->kategori_utang = $this->kategoriUtang($row->deskripsi_transaksi);
                    $row->nama = 'Utang Usaha - '.$row->kategori_utang;
                }

                return $row;
            })->filter(fn ($row) => $row->nilai_lawan > 0)->values();

            if ($candidates->isEmpty()) {
                $candidates = collect([(object) [
                    'id_akun_perkiraan' => 0,
                    'kode_perkiraan' => '-',
                    'nama' => 'Transaksi tanpa akun lawan',
                    'nilai_lawan' => $amount,
                ]]);
            }

            $counterTotal = (float) $candidates->sum('nilai_lawan');
            $allocated = 0.0;
            $period = substr((string) $movement->tanggal, 0, 7);

            foreach ($candidates as $index => $candidate) {
                $isLast = $index === $candidates->count() - 1;
                $portion = $isLast
                    ? round($amount - $allocated, 2)
                    : round($amount * ((float) $candidate->nilai_lawan / $counterTotal), 2);
                $allocated += $portion;
                if ($direction === 'incoming') {
                    $this->addAmount($incoming, $candidate, $period, $portion, $periodKeys);
                } else {
                    $this->addAmount($outgoing, $candidate, $period, $portion, $periodKeys);
                }
            }
        }

        $incomingRows = $this->rows($incoming);
        $outgoingRows = $this->rows($outgoing);
        $incomingTotals = $this->periodTotals($incomingRows, $periodKeys);
        $outgoingTotals = $this->periodTotals($outgoingRows, $periodKeys);
        $netTotals = collect($periodKeys)->mapWithKeys(fn ($period) => [
            $period => round($incomingTotals[$period] - $outgoingTotals[$period], 2),
        ])->all();

        $openingBalance = (float) DB::table('jurnal_perkiraan as j')
            ->join('impor_jurnal_perkiraan as i', 'i.id_impor_jurnal_perkiraan', '=', 'j.id_impor_jurnal_perkiraan')
            ->where('i.status', 'aktif')
            ->whereIn('j.id_akun_perkiraan', $akunKasIds)
            ->whereDate('j.tanggal', '<', $awal->toDateString())
            ->selectRaw('COALESCE(SUM(j.debit - j.kredit), 0) as saldo')
            ->value('saldo');
        $totalIncoming = round(array_sum($incomingTotals), 2);
        $totalOutgoing = round(array_sum($outgoingTotals), 2);
        $closingBalance = round($openingBalance + $totalIncoming - $totalOutgoing, 2);

        return compact(
            'periods', 'incomingRows', 'outgoingRows', 'incomingTotals', 'outgoingTotals',
            'netTotals', 'openingBalance', 'closingBalance', 'totalIncoming', 'totalOutgoing'
        );
    }

    private function transactionKey(object $row): string
    {
        return implode('|', [
            $row->id_impor_jurnal_perkiraan,
            $row->tanggal,
            $row->nomor_transaksi,
            $row->tipe_key,
        ]);
    }

    private function addAmount(array &$bucket, object $account, string $period, float $amount, array $periodKeys): void
    {
        $id = (int) $account->id_akun_perkiraan;
        $groupKey = $id.'|'.($account->kategori_utang ?? '');
        if (! isset($bucket[$groupKey])) {
            $bucket[$groupKey] = [
                'id' => $id,
                'kode' => $account->kode_perkiraan,
                'nama' => $account->nama,
                'kategori_utang' => $account->kategori_utang ?? null,
                'values' => array_fill_keys($periodKeys, 0.0),
            ];
        }
        $bucket[$groupKey]['values'][$period] = round(($bucket[$groupKey]['values'][$period] ?? 0) + $amount, 2);
    }

    private function rows(array $bucket): Collection
    {
        return collect($bucket)->map(function ($row) {
            $row['total'] = round(array_sum($row['values']), 2);

            return $row;
        })->sortBy(function ($row) {
            $name = $this->normalizeName($row['nama']);
            $order = $this->accountOrder();
            $position = $order[$name] ?? 999;

            return sprintf('%03d-%s', $position, $name);
        })->values();
    }

    private function periodTotals(Collection $rows, array $periodKeys): array
    {
        return collect($periodKeys)->mapWithKeys(fn ($period) => [
            $period => round((float) $rows->sum(fn ($row) => $row['values'][$period] ?? 0), 2),
        ])->all();
    }

    private function kategoriUtang(?string $deskripsi): string
    {
        $text = strtolower((string) $deskripsi);
        if (preg_match('/\bvaksin\b|\bvitamin\b|\bobat\b|virkon|flytox|hostazym|elitox/', $text)) {
            return 'Vitamin & Vaksin';
        }
        if (preg_match('/pakan|pokh?pan|newhope|al100|524a/', $text)) {
            return 'Pakan';
        }
        if (preg_match('/pullet|ayam/', $text)) {
            return 'Pullet / Ayam';
        }
        if (preg_match('/telur|rak telur/', $text)) {
            return 'Telur';
        }

        return 'Lainnya';
    }

    private function normalizeName(string $name): string
    {
        return trim((string) preg_replace('/\s+/', ' ', strtolower($name)));
    }

    private function accountOrder(): array
    {
        static $order;
        if ($order !== null) {
            return $order;
        }
        $names = [
            'Utang Usaha - Pakan', 'Utang Usaha - Vitamin & Vaksin', 'Utang Usaha - Pullet / Ayam',
            'Utang Usaha - Telur', 'Utang Usaha - Lainnya', 'Persediaan Pakan', 'Persediaan Vitamin/Obat', 'Persediaan Rak Telur',
            'Biaya Pokok Penjualan Telur (Hama) Tanpa Stok', 'Biaya Pokok Penjualan Telur (Tali Rafia)',
            'Biaya Pokok Penjualan Telur (Vaksin) Tanpa Stok', 'Biaya Pokok Penjualan Umum',
            'Biaya Adm Bank BCA Cost', 'Biaya ATK & Perlengkapan Kantor', 'Biaya Ekspedisi dan Ongkos Angkut',
            'Biaya Gaji, Upah & Honorer', 'Biaya Kendaraan (BBM, Parkir, SIM)', 'Biaya Lembur & Incentive',
            'Biaya THR, Bonus, Pesangon', 'Biaya Iuran & Sumbangan', 'Biaya Kebersihan Kantor',
            'Biaya Lain-Lain', 'Biaya Listrik', 'Biaya Material Perbaikan Bangunan',
            'Biaya Pajak PBB, Kendaraan, dll', 'Biaya Peralatan', 'Biaya Sparepart Kendaraan',
            'Biaya Telepon & Internet', 'PPh ps.25', 'Hutang Pajak PPh ps.29', 'Hutang Pajak PPh ps.23',
            'Biaya Pos & Kurir', 'Biaya BBM dan Gas Peralatan', 'Biaya BPJS dan Asuransi Karyawan',
            'Biaya Asuransi Bangunan, Kendaraan, dll', 'Biaya Perijinan', 'Biaya PDAM',
            'Inventaris Kantor', 'pallet kandang', 'pullet kandang',
        ];
        $order = [];
        foreach ($names as $index => $name) {
            $order[$this->normalizeName($name)] = $index + 1;
        }

        return $order;
    }
}
