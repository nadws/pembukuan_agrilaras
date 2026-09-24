<?php

namespace App\Services;

use App\Models\LaporanLayerModel;
use Illuminate\Support\Facades\DB;

/**
 * Kalkulator laporan laba rugi per kandang.
 *
 * Dipakai oleh halaman Laporan > Laba rugi kandang dan panel laba rugi
 * pada dashboard, sehingga keduanya selalu menghasilkan angka yang sama.
 */
class LabaRugiKandangService
{
    /**
     * @param  string|null  $tgl1  tanggal awal periode (Y-m-d)
     * @param  string|null  $tgl2  tanggal akhir periode (Y-m-d)
     * @return array<string, mixed> data view laporan laba rugi kandang
     */
    public function hitung($tgl1 = null, $tgl2 = null): array
    {
        $tgl1 = $tgl1 ?: date('Y-m-01');
        $tgl2 = $tgl2 ?: date('Y-m-t');

        /*
         * Tampilkan hanya kandang yang memiliki pemakaian produk pada
         * periode laporan. Kandang baru tanpa data periode tidak perlu
         * menjadi kolom kosong di laporan laba rugi.
         */
        $kandang = DB::table('kandang as k')
            ->whereExists(function ($query) use ($tgl1, $tgl2) {
                $query->select(DB::raw(1))
                    ->from('stok_produk_perencanaan as spp')
                    ->whereColumn('spp.id_kandang', 'k.id_kandang')
                    ->whereBetween('spp.tgl', [$tgl1, $tgl2]);
            })
            ->select('k.*')
            ->orderBy('k.nm_kandang', 'ASC')
            ->get();

        $totalTelur = DB::table('stok_telur')
            ->select(
                'id_kandang',
                DB::raw('COUNT(id_stok_telur) as count_bagi'),
                DB::raw('SUM(pcs) as kuml_pcs'),
                DB::raw('SUM(kg) as kuml_kg')
            )
            ->where('pcs', '!=', 0)
            ->whereBetween('tgl', [$tgl1, $tgl2])
            ->groupBy('id_kandang')
            ->get()
            ->keyBy('id_kandang');

        $populasi = DB::table('populasi as a')

            ->select(
                'a.id_kandang',
                DB::raw('SUM(a.mati) as mati'),
                DB::raw('SUM(a.jual) as jual'),
                DB::raw('SUM(a.afkir) as afkir')
            )
            ->whereBetween('a.tgl', [$tgl1, $tgl2])
            ->groupBy('a.id_kandang')
            ->get()
            ->keyBy('id_kandang');
        $populasiKumulatif = DB::table('populasi')
            ->whereDate('tgl', '<=', $tgl2)
            ->whereIn('id_kandang', $kandang->pluck('id_kandang'))
            ->groupBy('id_kandang')
            ->select('id_kandang')
            ->selectRaw('SUM(COALESCE(mati, 0) + COALESCE(jual, 0) + COALESCE(afkir, 0)) as keluar')
            ->pluck('keluar', 'id_kandang');
        $rata_rata_telur = LaporanLayerModel::rataRataTelurtgl($tgl1, $tgl2);
        $total_jual_telur_bulan = LaporanLayerModel::rataRataTelurtgl2($tgl1, $tgl2, '111');
        $total_jual_ayam_bulan = LaporanLayerModel::rataRataTelurtgl2($tgl1, $tgl2, '112');
        $total_beban_rak = LaporanLayerModel::rataRataTelurtgl2($tgl1, $tgl2, '122');

        $biaya_pakan = DB::table('jurnal_accurate')
            ->select(
                'nm_departemen',
                DB::raw('SUM(jurnal_accurate.debit) as ttl_rp')
            )
            ->where('jurnal_accurate.kode', '5101-04')
            ->whereBetween('jurnal_accurate.tgl', [$tgl1, $tgl2])
            ->groupBy('jurnal_accurate.nm_departemen')
            ->get()
            ->keyBy('nm_departemen');
        $biaya_vitamin = DB::table('jurnal_accurate')
            ->select(
                'nm_departemen',
                DB::raw('SUM(jurnal_accurate.debit) as ttl_rp')
            )
            ->where('jurnal_accurate.kode', '5101-03')
            ->whereBetween('jurnal_accurate.tgl', [$tgl1, $tgl2])
            ->groupBy('jurnal_accurate.nm_departemen')
            ->get()
            ->keyBy('nm_departemen');

        $biaya_ayam = DB::table('penjualan_barang_accurate as a')
            ->select(
                DB::raw('SUM(a.total_rp) as ttl_rp'),
                DB::raw('SUM(a.kuantitas) as qty'),
            )
            ->where('a.satuan', 'ekor')
            ->whereBetween('a.tanggal', [$tgl1, $tgl2])
            ->first();
        // Pemakaian vaksin terbaru dicatat sebagai produk pada
        // stok_produk_perencanaan (kategori vaksin), sehingga harus dihitung
        // dari sana agar periode September dan kandangnya ikut muncul.
        $vaksin = DB::table('stok_produk_perencanaan as s')
            ->join('tb_produk_perencanaan as p', 'p.id_produk', '=', 's.id_pakan')
            ->select(
                's.id_kandang',
                DB::raw('SUM(COALESCE(s.total_rp, 0) + COALESCE(s.biaya_dll, 0)) as ttl_rp')
            )
            ->whereRaw('LOWER(p.kategori) = ?', ['vaksin'])
            ->whereBetween('s.tgl', [$tgl1, $tgl2])
            ->whereIn('s.id_kandang', $kandang->pluck('id_kandang'))
            ->groupBy('s.id_kandang')
            ->get()->keyBy('id_kandang');

        $biaya_operasional = LaporanLayerModel::biayaOperasional2($tgl1, $tgl2);
        $total_populasi = DB::table('kandang')
            ->select(DB::raw('SUM(stok_awal) as stok_awal'))
            ->whereIn('id_kandang', $kandang->pluck('id_kandang'))
            ->first();

        /*
         * Kandang baru bisa belum memiliki transaksi pada periode laporan.
         * Lengkapi semua collection agar view selalu menerima nilai nol.
         */
        foreach ($kandang as $item) {
            $idKandang = $item->id_kandang;
            $namaKandang = $item->nm_kandang;

            if (! $totalTelur->has($idKandang)) {
                $totalTelur->put($idKandang, (object) [
                    'id_kandang' => $idKandang,
                    'count_bagi' => 0,
                    'kuml_pcs' => 0,
                    'kuml_kg' => 0,
                ]);
            }

            if (! $populasi->has($idKandang)) {
                $populasi->put($idKandang, (object) [
                    'id_kandang' => $idKandang,
                    'mati' => 0,
                    'jual' => 0,
                    'afkir' => 0,
                ]);
            }

            if (! $vaksin->has($idKandang)) {
                $vaksin->put($idKandang, (object) [
                    'id_kandang' => $idKandang,
                    'ttl_rp' => 0,
                ]);
            }

            if (! $biaya_pakan->has($namaKandang)) {
                $biaya_pakan->put($namaKandang, (object) [
                    'nm_departemen' => $namaKandang,
                    'ttl_rp' => 0,
                ]);
            }

            if (! $biaya_vitamin->has($namaKandang)) {
                $biaya_vitamin->put($namaKandang, (object) [
                    'nm_departemen' => $namaKandang,
                    'ttl_rp' => 0,
                ]);
            }
        }

        $kgJualTelur = (float) ($rata_rata_telur->kg_jual ?? 0);
        $hargaRataTelur = $kgJualTelur > 0
            ? (float) ($rata_rata_telur->ttl_rp ?? 0) / $kgJualTelur
            : 0;

        $qtyAyam = (float) ($biaya_ayam->qty ?? 0);
        $hargaRataAyam = $qtyAyam > 0
            ? (float) ($biaya_ayam->ttl_rp ?? 0) / $qtyAyam
            : 0;

        $stokAwalTotal = (float) ($total_populasi->stok_awal ?? 0);
        $biayaOperasionalTotal = (float) ($biaya_operasional->debit ?? 0);

        /*
         * Nilai rupiah laporan kandang harus berasal dari sumber yang sama dengan
         * laporan laba rugi. Jurnal belum menyimpan id_kandang, sehingga total
         * jurnal dibagikan ke kandang berdasarkan aktivitas operasionalnya.
         */
        $nilaiJurnal = DB::table('jurnal_perkiraan as j')
            ->join('impor_jurnal_perkiraan as i', 'i.id_impor_jurnal_perkiraan', '=', 'j.id_impor_jurnal_perkiraan')
            ->join('akun_perkiraan as a', 'a.id_akun_perkiraan', '=', 'j.id_akun_perkiraan')
            ->where('i.status', 'aktif')
            ->where('a.aktif', true)
            ->whereBetween('j.tanggal', [$tgl1, $tgl2])
            ->whereIn('a.tipe_akun', ['REVE', 'COGS', 'EXPS', 'OINC', 'OEXP'])
            ->groupBy('a.kode_perkiraan', 'a.tipe_akun')
            ->select('a.kode_perkiraan', 'a.tipe_akun')
            ->selectRaw('SUM(j.debit) AS debit, SUM(j.kredit) AS kredit')
            ->get();

        $nilaiKode = $nilaiJurnal->mapWithKeys(fn ($row) => [
            $row->kode_perkiraan => in_array($row->tipe_akun, ['REVE', 'OINC'], true)
                ? (float) $row->kredit - (float) $row->debit
                : (float) $row->debit - (float) $row->kredit,
        ]);
        $totalPendapatanJurnal = (float) $nilaiJurnal
            ->whereIn('tipe_akun', ['REVE', 'OINC'])
            ->sum(fn ($row) => (float) $row->kredit - (float) $row->debit);
        $totalPendapatanLainJurnal = (float) $nilaiJurnal
            ->where('tipe_akun', 'OINC')
            ->sum(fn ($row) => (float) $row->kredit - (float) $row->debit);
        $totalBiayaJurnal = (float) $nilaiJurnal
            ->whereIn('tipe_akun', ['COGS', 'EXPS', 'OEXP'])
            ->sum(fn ($row) => (float) $row->debit - (float) $row->kredit);

        $pemakaianProduk = DB::table('stok_produk_perencanaan as s')
            ->join('tb_produk_perencanaan as p', 'p.id_produk', '=', 's.id_pakan')
            ->whereBetween('s.tgl', [$tgl1, $tgl2])
            ->whereIn('s.id_kandang', $kandang->pluck('id_kandang'))
            ->groupBy('s.id_kandang', 'p.kategori')
            ->select('s.id_kandang', 'p.kategori')
            ->selectRaw('SUM(s.total_rp) AS total_rp')
            ->get();

        $idsKandang = $kandang->pluck('id_kandang')->map(fn ($id) => (int) $id)->all();
        $bagi = function (float $total, array $bobot) use ($idsKandang): array {
            $hasil = array_fill_keys($idsKandang, 0.0);
            $jumlahBobot = array_sum($bobot);
            $pembagi = $jumlahBobot != 0.0 ? $bobot : array_fill_keys($idsKandang, 1.0);
            $jumlahPembagi = array_sum($pembagi);
            $sisa = $total;

            foreach ($idsKandang as $index => $id) {
                $nilai = $index === array_key_last($idsKandang)
                    ? $sisa
                    : ($jumlahPembagi > 0 ? $total * (($pembagi[$id] ?? 0) / $jumlahPembagi) : 0);
                $hasil[$id] = $nilai;
                $sisa -= $nilai;
            }

            return $hasil;
        };

        $bobotTelur = $bobotAyam = $bobotPakan = $bobotVitamin = $bobotRak = $bobotUmum = [];
        foreach ($kandang as $item) {
            $id = (int) $item->id_kandang;
            $telur = (float) ($totalTelur[$id]->kuml_kg ?? 0) - ((float) ($totalTelur[$id]->kuml_pcs ?? 0) / 180);
            $bobotTelur[$id] = max(0, $telur);
            $bobotAyam[$id] = (float) ($populasi[$id]->jual ?? 0) + (float) ($populasi[$id]->afkir ?? 0);
            $bobotPakan[$id] = (float) optional($pemakaianProduk->first(fn ($row) => (int) $row->id_kandang === $id && strtolower((string) $row->kategori) === 'pakan'))->total_rp;
            $bobotVitamin[$id] = (float) $pemakaianProduk->filter(fn ($row) => (int) $row->id_kandang === $id && strtolower((string) $row->kategori) !== 'pakan')->sum('total_rp');
            $bobotRak[$id] = (float) ($totalTelur[$id]->kuml_pcs ?? 0);
            $bobotUmum[$id] = (float) $item->stok_awal;
        }

        $totalPerKategori = [
            'jual_telur' => (float) ($nilaiKode['400001'] ?? 0),
            'jual_ayam' => (float) ($nilaiKode['400002'] ?? 0),
            'jual_umum' => (float) ($nilaiKode['400003'] ?? 0),
            'pakan' => (float) ($nilaiKode['5101-04'] ?? 0),
            'vitamin' => (float) ($nilaiKode['5101-03'] ?? 0),
            // Biaya vaksin memiliki relasi kandang langsung pada
            // tb_vaksin_perencanaan, sehingga totalnya wajib mengikuti
            // rincian per kandang (bukan dibagi dari total jurnal umum).
            'vaksin' => (float) $vaksin->sum('ttl_rp'),
            'rak' => (float) ($nilaiKode['5101-01'] ?? 0),
        ];
        // Penjualan umum (REVE) bukan pendapatan kandang. Hanya pendapatan
        // di luar usaha (OINC) yang menjadi pengurang biaya operasional.
        $totalPerKategori['pendapatan_lain'] = $totalPendapatanLainJurnal;
        $totalPerKategori['operasional'] = $totalBiayaJurnal
            - $totalPerKategori['pakan']
            - $totalPerKategori['vitamin']
            - $totalPerKategori['vaksin']
            - $totalPerKategori['rak']
            - $totalPerKategori['pendapatan_lain'];
        $biayaOperasionalTotal = $totalPerKategori['operasional'];

        $nilaiKandang = [
            'jual_telur' => $bagi($totalPerKategori['jual_telur'], $bobotTelur),
            'jual_ayam' => $bagi($totalPerKategori['jual_ayam'], $bobotAyam),
            'pendapatan_lain' => $bagi($totalPerKategori['pendapatan_lain'], $bobotUmum),
            'pakan' => $bagi($totalPerKategori['pakan'], $bobotPakan),
            'vitamin' => $bagi($totalPerKategori['vitamin'], $bobotVitamin),
            'vaksin' => [],
            'rak' => $bagi($totalPerKategori['rak'], $bobotRak),
            'operasional' => $bagi($totalPerKategori['operasional'], $bobotUmum),
        ];

        // Gunakan total dari jurnal_perkiraan agar biaya pakan, vitamin
        // sama dengan laporan laba rugi. Nilainya dibagi ke kandang menurut
        // proporsi pemakaian produk pada periode yang dipilih.
        // VAKSIN: langsung pakai cost per kandang dari tb_vaksin_perencanaan (sudah per kandang)
        foreach ($kandang as $item) {
            $id = (int) $item->id_kandang;
            $biaya_pakan->put($item->nm_kandang, (object) [
                'nm_departemen' => $item->nm_kandang,
                'ttl_rp' => (float) ($nilaiKandang['pakan'][$id] ?? 0),
            ]);
            $biaya_vitamin->put($item->nm_kandang, (object) [
                'nm_departemen' => $item->nm_kandang,
                'ttl_rp' => (float) ($nilaiKandang['vitamin'][$id] ?? 0),
            ]);
            // Vaksin sudah per kandang di tb_vaksin_perencanaan, jangan dibagi rata
            $nilaiKandang['vaksin'][$id] = (float) ($vaksin[$id]->ttl_rp ?? 0);
        }

        return compact(
            'kandang',
            'totalTelur',
            'tgl1',
            'tgl2',
            'rata_rata_telur',
            'total_jual_telur_bulan',
            'total_jual_ayam_bulan',
            'total_beban_rak',
            'populasi',
            'populasiKumulatif',
            'biaya_pakan',
            'biaya_vitamin',
            'vaksin',
            'biaya_operasional',
            'total_populasi',
            'biaya_ayam',
            'hargaRataTelur',
            'hargaRataAyam',
            'stokAwalTotal',
            'biayaOperasionalTotal', 'nilaiKandang', 'totalPerKategori', 'totalPendapatanJurnal', 'totalBiayaJurnal'
        );
    }
}
