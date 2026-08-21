<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardJurnalPerkiraanController extends Controller
{
    public function index(Request $request)
    {
        $latestBatch = DB::table('impor_jurnal_perkiraan')
            ->where('status', 'aktif')
            ->latest('id_impor_jurnal_perkiraan')
            ->first();

        $range = DB::table('jurnal_perkiraan as j')
            ->join('impor_jurnal_perkiraan as i', 'i.id_impor_jurnal_perkiraan', '=', 'j.id_impor_jurnal_perkiraan')
            ->where('i.status', 'aktif')
            ->selectRaw('MIN(j.tanggal) as awal, MAX(j.tanggal) as akhir')
            ->first();

        $tgl1 = $request->tgl1 ?? optional($latestBatch)->periode_awal ?? optional($range)->awal;
        $tgl2 = $request->tgl2 ?? optional($latestBatch)->periode_akhir ?? optional($range)->akhir;

        $baseQuery = function () use ($tgl1, $tgl2) {
            $query = DB::table('jurnal_perkiraan as j')
                ->join('impor_jurnal_perkiraan as i', 'i.id_impor_jurnal_perkiraan', '=', 'j.id_impor_jurnal_perkiraan')
                ->leftJoin('akun_perkiraan as a', 'a.id_akun_perkiraan', '=', 'j.id_akun_perkiraan')
                ->where('i.status', 'aktif');

            if ($tgl1 && $tgl2) {
                $query->whereBetween('j.tanggal', [$tgl1, $tgl2]);
            }

            return $query;
        };

        $summary = $baseQuery()
            ->selectRaw('
                COALESCE(SUM(j.debit), 0) as total_debit,
                COALESCE(SUM(j.kredit), 0) as total_kredit,
                COUNT(j.id_jurnal_perkiraan) as jumlah_detail,
                COUNT(DISTINCT j.nomor_transaksi) as jumlah_transaksi,
                COUNT(DISTINCT j.id_akun_perkiraan) as jumlah_akun,
                COUNT(DISTINCT COALESCE(j.tipe_transaksi, "")) as jumlah_tipe
            ')
            ->first();

        $topAccounts = $baseQuery()
            ->selectRaw('
                a.kode_perkiraan,
                a.nama,
                COALESCE(SUM(j.debit), 0) as debit,
                COALESCE(SUM(j.kredit), 0) as kredit,
                COALESCE(SUM(j.debit + j.kredit), 0) as aktivitas
            ')
            ->groupBy('a.id_akun_perkiraan', 'a.kode_perkiraan', 'a.nama')
            ->orderByDesc('aktivitas')
            ->limit(8)
            ->get();

        $byType = $baseQuery()
            ->selectRaw('
                COALESCE(NULLIF(j.tipe_transaksi, ""), "Tanpa tipe") as tipe_transaksi,
                COALESCE(SUM(j.debit), 0) as debit,
                COALESCE(SUM(j.kredit), 0) as kredit,
                COUNT(DISTINCT j.nomor_transaksi) as jumlah_transaksi
            ')
            ->groupBy(DB::raw('COALESCE(NULLIF(j.tipe_transaksi, ""), "Tanpa tipe")'))
            ->orderByDesc(DB::raw('SUM(j.debit + j.kredit)'))
            ->limit(8)
            ->get();

        $monthlyTrend = $baseQuery()
            ->selectRaw('
                DATE_FORMAT(j.tanggal, "%Y-%m") as bulan,
                COALESCE(SUM(j.debit), 0) as debit,
                COALESCE(SUM(j.kredit), 0) as kredit
            ')
            ->groupBy(DB::raw('DATE_FORMAT(j.tanggal, "%Y-%m")'))
            ->orderBy('bulan')
            ->get();

        $recentJournals = $baseQuery()
            ->select([
                'j.tanggal',
                'j.nomor_transaksi',
                'j.tipe_transaksi',
                'j.deskripsi',
                'j.debit',
                'j.kredit',
                'a.kode_perkiraan',
                'a.nama as nama_akun',
            ])
            ->orderByDesc('j.tanggal')
            ->orderByDesc('j.id_jurnal_perkiraan')
            ->limit(10)
            ->get();

        $maxTrend = max(
            1,
            (float) $monthlyTrend->max('debit'),
            (float) $monthlyTrend->max('kredit')
        );

        $stockRows = DB::table('stok_telur as s')
            ->leftJoin('gudang_telur as g', 'g.id_gudang_telur', '=', 's.id_gudang')
            ->leftJoin('telur_produk as t', 't.id_produk_telur', '=', 's.id_telur')
            ->where('s.opname', 'T')
            ->selectRaw('
                s.id_gudang,
                COALESCE(g.nm_gudang, CONCAT("Gudang ", s.id_gudang)) as nm_gudang,
                s.id_telur,
                COALESCE(t.nm_telur, "Tanpa jenis") as nm_telur,
                COALESCE(SUM(s.pcs - s.pcs_kredit), 0) as pcs,
                COALESCE(SUM(s.kg - s.kg_kredit), 0) as kg
            ')
            ->groupBy('s.id_gudang', 'g.nm_gudang', 's.id_telur', 't.nm_telur')
            ->orderBy('g.nm_gudang')
            ->orderBy('t.nm_telur')
            ->get();

        $stockByWarehouse = $stockRows
            ->groupBy('id_gudang')
            ->map(function ($rows) {
                $first = $rows->first();

                return (object) [
                    'id_gudang' => $first->id_gudang,
                    'nm_gudang' => $first->nm_gudang,
                    'pcs' => $rows->sum('pcs'),
                    'kg' => $rows->sum('kg'),
                    'jenis_count' => $rows->where('pcs', '!=', 0)->count(),
                ];
            })
            ->values();

        $stockByEggType = $stockRows
            ->groupBy('id_telur')
            ->map(function ($rows) {
                $first = $rows->first();

                return (object) [
                    'id_telur' => $first->id_telur,
                    'nm_telur' => $first->nm_telur,
                    'pcs' => $rows->sum('pcs'),
                    'kg' => $rows->sum('kg'),
                    'warehouse_count' => $rows->where('pcs', '!=', 0)->count(),
                ];
            })
            ->sortByDesc('pcs')
            ->values();

        $stockTotal = (object) [
            'pcs' => $stockRows->sum('pcs'),
            'kg' => $stockRows->sum('kg'),
        ];

        $recognizedStockRows = DB::table('telur_produk as t')
            ->leftJoin(DB::raw('(
                SELECT
                    id_telur,
                    COALESCE(SUM(pcs - pcs_kredit), 0) as pcs,
                    COALESCE(SUM(kg - kg_kredit), 0) as kg
                FROM stok_telur
                WHERE id_gudang = 1
                    AND opname = "T"
                GROUP BY id_telur
            ) as s'), 's.id_telur', '=', 't.id_produk_telur')
            ->selectRaw('
                t.id_produk_telur,
                t.nm_telur,
                COALESCE(s.pcs, 0) as pcs,
                COALESCE(s.kg, 0) as kg
            ')
            ->orderBy('t.id_produk_telur')
            ->get();

        $recognizedStockTotal = (object) [
            'pcs' => $recognizedStockRows->sum('pcs'),
            'kg' => $recognizedStockRows->sum('kg'),
        ];

        return view('dashboard.jurnal_perkiraan', [
            'title' => 'Dashboard',
            'tgl1' => $tgl1,
            'tgl2' => $tgl2,
            'summary' => $summary,
            'topAccounts' => $topAccounts,
            'byType' => $byType,
            'monthlyTrend' => $monthlyTrend,
            'recentJournals' => $recentJournals,
            'maxTrend' => $maxTrend,
            'latestBatch' => $latestBatch,
            'stockByWarehouse' => $stockByWarehouse,
            'stockByEggType' => $stockByEggType,
            'stockRows' => $stockRows,
            'stockTotal' => $stockTotal,
            'recognizedStockRows' => $recognizedStockRows,
            'recognizedStockTotal' => $recognizedStockTotal,
        ]);
    }
}
