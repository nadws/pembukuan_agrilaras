<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Carbon\Carbon;

class DashboardJurnalPerkiraanController extends Controller
{
    public function index(Request $request): View
    {
        $akhir = $this->parseDate($request->input('tgl2')) ?? now()->startOfDay();
        $mulai = $this->parseDate($request->input('tgl1')) ?? $akhir->copy()->subDays(6);
        if ($mulai->gt($akhir)) [$mulai, $akhir] = [$akhir->copy(), $mulai->copy()];
        $tanggal = $akhir->toDateString();

        $pemakaianPakan = DB::table('stok_produk_perencanaan as s')
            ->join('tb_produk_perencanaan as p', 'p.id_produk', '=', 's.id_pakan')
            ->whereBetween('s.tgl', [$mulai->toDateString(), $akhir->toDateString()])->where('p.kategori', 'pakan')
            ->where('s.id_kandang', '>', 0)->where('s.pcs_kredit', '>', 0)
            ->selectRaw('DATE(s.tgl) as tanggal, SUM(COALESCE(s.pcs_kredit, 0)) / 1000 as jumlah_kg')
            ->groupBy('tanggal')->orderBy('tanggal')->get();

        $produksiTelur = DB::table('stok_telur as s')
            ->leftJoin('telur_produk as p', 'p.id_produk_telur', '=', 's.id_telur')
            ->leftJoin('kandang as k', 'k.id_kandang', '=', 's.id_kandang')
            ->whereBetween('s.tgl', [$mulai->toDateString(), $akhir->toDateString()])
            ->where('s.id_kandang', '>', 0)->where('s.id_gudang', 1)
            ->where(function ($query) { $query->where('s.pcs', '>', 0)->orWhere('s.kg', '>', 0); })
            ->selectRaw("DATE(s.tgl) as tanggal, s.id_kandang, COALESCE(k.nm_kandang, CONCAT('Kandang ', s.id_kandang)) as nm_kandang, SUM(COALESCE(s.pcs, 0)) as jumlah_pcs, SUM(COALESCE(s.kg, 0) - (COALESCE(s.pcs, 0) / 180)) as jumlah_kg")
            ->groupBy('tanggal', 's.id_kandang', 'k.nm_kandang')->orderBy('tanggal')->get();

        $pakanKandang = DB::table('stok_produk_perencanaan as s')
            ->join('tb_produk_perencanaan as p', 'p.id_produk', '=', 's.id_pakan')
            ->leftJoin('kandang as k', 'k.id_kandang', '=', 's.id_kandang')
            ->whereBetween('s.tgl', [$mulai->toDateString(), $akhir->toDateString()])
            ->where('p.kategori', 'pakan')->where('s.id_kandang', '>', 0)->where('s.pcs_kredit', '>', 0)
            ->groupBy('s.id_kandang', 'k.nm_kandang', 'p.id_produk', 'p.nm_produk')
            ->select('s.id_kandang', 'k.nm_kandang', 'p.nm_produk')
            ->selectRaw('SUM(COALESCE(s.pcs_kredit, 0)) / 1000 as jumlah_kg')
            ->orderBy('k.nm_kandang')->orderByDesc('jumlah_kg')->get()
            ->groupBy('id_kandang');

        $hari = collect(range(0, 6))->map(fn ($offset) => $mulai->copy()->addDays($offset));
        $pakanHarian = $hari->map(fn ($date) => (float) ($pemakaianPakan->firstWhere('tanggal', $date->toDateString())->jumlah_kg ?? 0));
        $telurHarian = $hari->map(fn ($date) => (float) $produksiTelur->where('tanggal', $date->toDateString())->sum('jumlah_kg'));
        $telurSeries = $produksiTelur->groupBy('id_kandang')->map(function ($rows, $id) use ($hari) {
            return ['name' => (string) ($rows->first()->nm_kandang ?: 'Kandang '.$id), 'data' => $hari->map(fn ($date) => (float) $rows->where('tanggal', $date->toDateString())->sum('jumlah_kg'))->values()];
        })->values();

        return view('dashboard', [
            'title' => 'Dashboard', 'tanggal' => $tanggal, 'tanggalMulai' => $mulai->toDateString(), 'tanggalAkhir' => $akhir->toDateString(),
            'pemakaianPakan' => $pemakaianPakan, 'produksiTelur' => $produksiTelur,
            'labelHari' => $hari->map(fn ($date) => $date->format('d/m'))->values(),
            'pakanHarian' => $pakanHarian->values(), 'telurHarian' => $telurHarian->values(),
            'telurSeries' => $telurSeries,
            'pakanKandang' => $pakanKandang,
            'totalPakanKg' => (float) $pakanHarian->sum(), 'totalTelurKg' => (float) $telurHarian->sum(),
            'fcrWeek' => $telurHarian->sum() > 0 ? (float) $pakanHarian->sum() / (float) $telurHarian->sum() : 0,
        ]);
    }

    private function parseDate(?string $value): ?Carbon
    {
        if (! $value) return null;
        try { return Carbon::parse($value)->startOfDay(); } catch (\Throwable) { return null; }
    }
}
