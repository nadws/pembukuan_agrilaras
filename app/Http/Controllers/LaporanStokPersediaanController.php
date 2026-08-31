<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LaporanStokPersediaanController extends Controller
{
    public function index(Request $request)
    {
        $kategori = $request->input('kategori');
        $cari = trim((string) $request->input('cari'));
        $allowed = ['pakan', 'obat_pakan', 'obat_air', 'vitamin', 'vaksin'];

        $summary = DB::table('tb_produk_perencanaan as p')
            ->leftJoin('tb_satuan as u', 'u.id_satuan', '=', 'p.dosis_satuan')
            ->join('stok_produk_perencanaan as s', 's.id_pakan', '=', 'p.id_produk')
            ->whereIn('p.kategori', $allowed)
            ->when(in_array($kategori, $allowed, true), function ($q) use ($kategori) {
                $q->whereIn('p.kategori', $kategori === 'vitamin' ? ['vitamin', 'obat_pakan', 'obat_air'] : [$kategori]);
            })
            ->when($cari !== '', function ($q) use ($cari) {
                $needle = '%' . $cari . '%';
                $q->where(function ($sub) use ($needle) {
                    $sub->where('p.nm_produk', 'like', $needle)
                        ->orWhere('p.kode_accurate', 'like', $needle);
                });
            })
            ->groupBy('p.id_produk', 'p.nm_produk', 'p.kode_accurate', 'p.kategori', 'u.nm_satuan')
            ->select([
                'p.id_produk', 'p.nm_produk', 'p.kode_accurate', 'p.kategori', 'u.nm_satuan',
            ])
            ->selectRaw('SUM(CASE WHEN s.pcs > 0 THEN s.pcs ELSE 0 END) as total_masuk')
            ->selectRaw('SUM(CASE WHEN s.pcs_kredit > 0 THEN s.pcs_kredit ELSE 0 END) as total_pakai')
            ->selectRaw('SUM(s.pcs - s.pcs_kredit) as stok_akhir')
            ->selectRaw('SUM(CASE WHEN s.pcs > 0 THEN s.total_rp + s.biaya_dll ELSE 0 END) as nilai_masuk')
            ->havingRaw('SUM(s.pcs - s.pcs_kredit) <> 0')
            ->orderBy('p.kategori')->orderBy('p.nm_produk')
            ->paginate(20)->withQueryString();

        return view('laporan.stok_persediaan', compact('summary', 'kategori', 'cari'));
    }

    public function detail(Request $request, int $produk)
    {
        $item = DB::table('tb_produk_perencanaan as p')
            ->leftJoin('tb_satuan as u', 'u.id_satuan', '=', 'p.dosis_satuan')
            ->where('p.id_produk', $produk)
            ->whereIn('p.kategori', ['pakan', 'obat_pakan', 'obat_air', 'vitamin', 'vaksin'])
            ->first(['p.id_produk', 'p.nm_produk', 'p.kategori', 'u.nm_satuan']);
        abort_unless($item, 404);

        $tanggalPertama = DB::table('stok_produk_perencanaan')
            ->where('id_pakan', $item->id_produk)
            ->min('tgl');
        $tanggalTerakhir = DB::table('stok_produk_perencanaan')
            ->where('id_pakan', $item->id_produk)
            ->max('tgl');

        try {
            $tgl1 = Carbon::parse($request->input('tgl1', $tanggalPertama ?: date('Y-m-01')))->format('Y-m-d');
            $tgl2 = Carbon::parse($request->input('tgl2', $tanggalTerakhir ?: date('Y-m-d')))->format('Y-m-d');
        } catch (\Throwable) {
            $tgl1 = $tanggalPertama ?: date('Y-m-01');
            $tgl2 = $tanggalTerakhir ?: date('Y-m-d');
        }
        if ($tgl1 > $tgl2) {
            [$tgl1, $tgl2] = [$tgl2, $tgl1];
        }

        $saldoAwal = (float) DB::table('stok_produk_perencanaan')
            ->where('id_pakan', $item->id_produk)
            ->whereDate('tgl', '<', $tgl1)
            ->selectRaw('COALESCE(SUM(pcs - pcs_kredit), 0) as saldo')
            ->value('saldo');

        $mutasiPeriode = DB::table('stok_produk_perencanaan')
            ->where('id_pakan', $item->id_produk)
            ->whereBetween('tgl', [$tgl1, $tgl2])
            ->selectRaw('COALESCE(SUM(pcs), 0) as pembelian, COALESCE(SUM(pcs_kredit), 0) as pemakaian')
            ->first();
        $totalPembelian = (float) $mutasiPeriode->pembelian;
        $totalPemakaian = (float) $mutasiPeriode->pemakaian;
        $saldoAkhir = $saldoAwal + $totalPembelian - $totalPemakaian;

        $jenisSql = "CASE
            WHEN s.no_nota LIKE 'OPG-%' THEN 'penyesuaian'
            WHEN s.pcs_kredit > 0 THEN 'pemakaian'
            ELSE 'pembelian'
        END";

        $mutasiHarian = DB::table('stok_produk_perencanaan as s')
            ->leftJoin('kandang as k', 'k.id_kandang', '=', 's.id_kandang')
            ->where('s.id_pakan', $item->id_produk)
            ->whereBetween('s.tgl', [$tgl1, $tgl2])
            ->select('s.tgl')
            ->selectRaw($jenisSql.' as jenis')
            ->selectRaw('SUM(s.pcs) as pcs, SUM(s.pcs_kredit) as pcs_kredit')
            ->selectRaw('COUNT(*) as jumlah_transaksi')
            ->selectRaw("GROUP_CONCAT(DISTINCT NULLIF(s.no_nota, '') ORDER BY s.no_nota SEPARATOR ', ') as nomor_transaksi")
            ->selectRaw("GROUP_CONCAT(DISTINCT NULLIF(k.nm_kandang, '') ORDER BY k.nm_kandang SEPARATOR ', ') as nm_kandang")
            ->selectRaw("GROUP_CONCAT(DISTINCT NULLIF(s.id_kandang, 0) ORDER BY s.id_kandang SEPARATOR ',') as id_kandang_list")
            ->selectRaw("GROUP_CONCAT(DISTINCT NULLIF(s.admin, '') ORDER BY s.admin SEPARATOR ', ') as admin")
            ->groupBy('s.tgl')
            ->groupByRaw($jenisSql);

        $detail = DB::query()->fromSub($mutasiHarian, 'd')
            ->orderBy('d.tgl')
            ->orderByRaw("FIELD(d.jenis, 'pembelian', 'pemakaian', 'penyesuaian')")
            ->select('d.*')
            ->selectRaw("? + SUM(d.pcs - d.pcs_kredit) OVER (ORDER BY d.tgl, FIELD(d.jenis, 'pembelian', 'pemakaian', 'penyesuaian') ROWS UNBOUNDED PRECEDING) as saldo", [$saldoAwal])
            ->paginate(20)->withQueryString();

        $detail->getCollection()->transform(function ($row) {
            $ids = collect(explode(',', (string) $row->id_kandang_list))
                ->filter(fn ($id) => (int) $id > 0)
                ->map(fn ($id) => (int) $id)
                ->unique()->values();

            if ($ids->isEmpty()) {
                $row->ekor_ayam = null;
                return $row;
            }

            $pengurangan = DB::table('populasi')
                ->whereIn('id_kandang', $ids)
                ->whereDate('tgl', '<=', $row->tgl)
                ->select('id_kandang')
                ->selectRaw('SUM(COALESCE(mati,0) + COALESCE(jual,0) + COALESCE(afkir,0)) as berkurang')
                ->groupBy('id_kandang');

            $row->ekor_ayam = (float) DB::table('kandang as k')
                ->leftJoinSub($pengurangan, 'p', 'p.id_kandang', '=', 'k.id_kandang')
                ->whereIn('k.id_kandang', $ids)
                ->selectRaw('COALESCE(SUM(COALESCE(k.stok_awal,0) - COALESCE(p.berkurang,0)),0) as ekor')
                ->value('ekor');

            return $row;
        });

        return view('laporan.stok_persediaan_detail', compact(
            'item', 'detail', 'tgl1', 'tgl2', 'saldoAwal', 'totalPembelian', 'totalPemakaian', 'saldoAkhir'
        ))->with('produk', $item);
    }
}
