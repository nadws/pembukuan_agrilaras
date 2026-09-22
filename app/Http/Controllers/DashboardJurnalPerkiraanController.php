<?php

namespace App\Http\Controllers;

use App\Services\LabaRugiKandangService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DashboardJurnalPerkiraanController extends Controller
{
    private const WIDGET_DASHBOARD = ['pakan', 'pakan-rincian', 'telur', 'laba-rugi', 'piutang', 'stok'];

    private const WIDGET_SPAN_DEFAULT = ['pakan' => 8, 'pakan-rincian' => 4, 'telur' => 8, 'laba-rugi' => 4, 'piutang' => 12, 'stok' => 12];

    public function index(Request $request): View
    {
        $akhir = $this->parseDate($request->input('tgl2')) ?? now()->startOfDay();
        $mulai = $this->parseDate($request->input('tgl1')) ?? $akhir->copy()->startOfMonth();
        if ($mulai->gt($akhir)) {
        [$mulai, $akhir] = [$akhir->copy(), $mulai->copy()];
        }
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
            ->where(function ($query) {
            $query->where('s.pcs', '>', 0)->orWhere('s.kg', '>', 0);
            })
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

        $jumlahHari = max(1, min(31, $mulai->diffInDays($akhir) + 1));
        $hari = collect(range(0, $jumlahHari - 1))->map(fn ($offset) => $mulai->copy()->addDays($offset));
        $pakanHarian = $hari->map(fn ($date) => (float) ($pemakaianPakan->firstWhere('tanggal', $date->toDateString())->jumlah_kg ?? 0));
        $pakanKandangHarian = DB::table('stok_produk_perencanaan as s')
            ->join('tb_produk_perencanaan as p', 'p.id_produk', '=', 's.id_pakan')
            ->leftJoin('kandang as k', 'k.id_kandang', '=', 's.id_kandang')
            ->whereBetween('s.tgl', [$mulai->toDateString(), $akhir->toDateString()])->where('p.kategori', 'pakan')
            ->where('s.id_kandang', '>', 0)->where('s.pcs_kredit', '>', 0)
            ->selectRaw("DATE(s.tgl) as tanggal, s.id_kandang, COALESCE(k.nm_kandang, CONCAT('Kandang ', s.id_kandang)) as nm_kandang")
            ->selectRaw('SUM(COALESCE(s.pcs_kredit, 0)) / 1000 as jumlah_kg')
            ->groupBy('tanggal', 's.id_kandang', 'k.nm_kandang')->orderBy('tanggal')->get();
        $pakanSeries = $pakanKandangHarian->groupBy('id_kandang')->map(function ($rows, $id) use ($hari) {
            return ['name' => (string) ($rows->first()->nm_kandang ?: 'Kandang '.$id), 'data' => $hari->map(fn ($date) => (float) $rows->where('tanggal', $date->toDateString())->sum('jumlah_kg'))->values()];
        })->values();
        $telurHarian = $hari->map(fn ($date) => (float) $produksiTelur->where('tanggal', $date->toDateString())->sum('jumlah_kg'));
        $telurSeries = $produksiTelur->groupBy('id_kandang')->map(function ($rows, $id) use ($hari) {
            return ['name' => (string) ($rows->first()->nm_kandang ?: 'Kandang '.$id), 'data' => $hari->map(fn ($date) => (float) $rows->where('tanggal', $date->toDateString())->sum('jumlah_kg'))->values()];
        })->values();

        // Perolehan kemarin (H-1) per kandang untuk histogram bawah.
        $tglKemarin = now()->subDay()->toDateString();
        $produksiKemarin = DB::table('stok_telur as s')
            ->leftJoin('kandang as k', 'k.id_kandang', '=', 's.id_kandang')
            ->whereDate('s.tgl', $tglKemarin)
            ->where('s.id_kandang', '>', 0)->where('s.id_gudang', 1)
            ->where(function ($query) {
            $query->where('s.pcs', '>', 0)->orWhere('s.kg', '>', 0);
            })
            ->selectRaw("COALESCE(k.nm_kandang, CONCAT('Kandang ', s.id_kandang)) as nama")
            ->selectRaw('SUM(COALESCE(s.kg, 0) - (COALESCE(s.pcs, 0) / 180)) as kg')
            ->selectRaw('SUM(COALESCE(s.pcs, 0)) as pcs')
            ->groupBy('s.id_kandang', 'k.nm_kandang')->orderByDesc('kg')->get();
        $kemarinTotalKg = (float) $produksiKemarin->sum('kg');

        // Panel laba rugi per kandang pada dashboard sengaja memakai kalkulator
        // yang sama dengan laporan Laba rugi kandang agar angkanya selalu cocok.
        $labaRugi = app(LabaRugiKandangService::class)->hitung($mulai->toDateString(), $akhir->toDateString());
        $labaRugiPerKandang = collect($labaRugi['kandang'])->map(function ($k) use ($labaRugi) {
            $id = (int) $k->id_kandang;
            $nilai = $labaRugi['nilaiKandang'];
            $pendapatan = (float) ($nilai['jual_telur'][$id] ?? 0) + (float) ($nilai['jual_ayam'][$id] ?? 0);
            $biaya = (float) ($nilai['pakan'][$id] ?? 0)
                + (float) ($nilai['vitamin'][$id] ?? 0)
                + (float) ($nilai['vaksin'][$id] ?? 0)
                + (float) ($nilai['rak'][$id] ?? 0)
                + (float) ($nilai['operasional'][$id] ?? 0);

            return (object) ['nama' => (string) ($k->nm_kandang ?: 'Kandang '.$id), 'laba' => $pendapatan - $biaya];
        })->values();
        $labaRugiTotal = (float) $labaRugiPerKandang->sum('laba')
            + (float) ($labaRugi['totalPerKategori']['jual_umum'] ?? 0);

        $tataLetak = $this->tataLetak();

        // Piutang telur belum lunas berumur maks 10 hari (snapshot hari ini,
        // tidak mengikuti filter periode).
        $piutangBelumLunas = DB::table('invoice_telur as i')
            ->leftJoin('customer as c', 'c.id_customer', '=', 'i.id_customer')
            ->leftJoinSub(
                DB::table('bayar_telur')
                    ->groupBy('no_nota')
                    ->select('no_nota')
                    ->selectRaw('SUM(COALESCE(debit, 0) - COALESCE(kredit, 0)) as terbayar'),
                'b', 'b.no_nota', '=', 'i.no_nota'
            )
            ->where('i.status', 'unpaid')
            ->where('i.lokasi', '!=', 'opname')
            ->groupBy('i.no_nota')
            ->select('i.no_nota')
            ->selectRaw('MAX(i.tgl) as tgl')
            ->selectRaw("MAX(COALESCE(NULLIF(c.nm_customer, ''), i.customer)) as customer")
            ->selectRaw('SUM(i.total_rp) as total')
            ->selectRaw('COALESCE(MAX(b.terbayar), 0) as terbayar')
            ->orderByDesc('tgl')
            ->limit(100)
            ->get()
            ->map(function ($row) {
                $sisa = (float) $row->total - (float) $row->terbayar;
                $umur = (int) floor((now()->startOfDay()->timestamp - Carbon::parse($row->tgl)->startOfDay()->timestamp) / 86400);

                return (object) [
                    'no_nota' => (string) $row->no_nota,
                    'tgl' => (string) $row->tgl,
                    'customer' => trim((string) ($row->customer ?? '')) !== '' ? (string) $row->customer : '-',
                    'sisa' => $sisa,
                    'umur' => $umur,
                ];
            })
            ->filter(fn ($row) => $row->sisa > 0 && $row->umur >= 10)
            ->sortByDesc('umur')
            ->values();

        return view('dashboard', [
            'title' => 'Dashboard', 'tanggal' => $tanggal, 'tanggalMulai' => $mulai->toDateString(), 'tanggalAkhir' => $akhir->toDateString(),
            'pemakaianPakan' => $pemakaianPakan, 'produksiTelur' => $produksiTelur,
            'labelHari' => $hari->map(fn ($date) => $date->format('d/m'))->values(),
            'pakanHarian' => $pakanHarian->values(), 'pakanSeries' => $pakanSeries, 'telurHarian' => $telurHarian->values(),
            'telurSeries' => $telurSeries,
            'pakanKandang' => $pakanKandang,
            'labaRugiPerKandang' => $labaRugiPerKandang, 'labaRugiTotal' => $labaRugiTotal,
            'piutangBelumLunas' => $piutangBelumLunas, 'piutangTotal' => (float) $piutangBelumLunas->sum('sisa'),

            // Stok telur sistem per gudang: mutasi aktif (opname=T),
            // debit dikurangi kredit, sama seperti angka opname gudang.
            'stokTelur' => DB::table('stok_telur as s')
                ->leftJoin('telur_produk as p', 'p.id_produk_telur', '=', 's.id_telur')
                ->leftJoin('gudang_telur as g', 'g.id_gudang_telur', '=', 's.id_gudang')
                ->where('s.opname', 'T')
                ->groupBy('s.id_gudang', 's.id_telur')
                ->select('s.id_gudang')
                ->selectRaw('MAX(g.nm_gudang) as gudang, MAX(p.nm_telur) as produk')
                ->selectRaw('SUM(COALESCE(s.pcs, 0) - COALESCE(s.pcs_kredit, 0)) as pcs')
                ->selectRaw('SUM(COALESCE(s.kg, 0) - COALESCE(s.kg_kredit, 0)) as kg')
                ->orderBy('s.id_gudang')
                ->orderBy('produk')
                ->get()
                ->groupBy('id_gudang'),
            'tglKemarin' => $tglKemarin, 'produksiKemarin' => $produksiKemarin, 'kemarinTotalKg' => $kemarinTotalKg,
            'jumlahHari' => $jumlahHari,
            'totalPakanKg' => (float) $pakanHarian->sum(), 'totalTelurKg' => (float) $telurHarian->sum(),
            'fcrWeek' => $telurHarian->sum() > 0 ? (float) $pakanHarian->sum() / (float) $telurHarian->sum() : 0,
            'widgetOrder' => $tataLetak['orderMap'], 'widgetHidden' => $tataLetak['hidden'],
            'widgetSpan' => $tataLetak['span'],
            'bolehUbah' => (int) auth()->user()->posisi_id === 1,
        ]);
    }

    /**
     * Tata letak global: satu baris (user_id NULL) dipakai semua akun.
     * Baris per-user yang lama diabaikan.
     *
     * @return array{orderMap: array<string, int>, hidden: string[], span: array<string, int>}
     */
    private function tataLetak(): array
    {
        $simpan = DB::table('dashboard_layout')->whereNull('user_id')->value('tata_letak');
        $data = is_string($simpan) ? (array) json_decode($simpan, true) : [];
        $order = array_values(array_intersect((array) ($data['order'] ?? []), self::WIDGET_DASHBOARD));
        foreach (self::WIDGET_DASHBOARD as $widget) {
            if (! in_array($widget, $order, true)) {
                $order[] = $widget;
            }
        }
        $hidden = array_values(array_intersect((array) ($data['hidden'] ?? []), self::WIDGET_DASHBOARD));
        $spanSimpan = array_intersect_key((array) ($data['span'] ?? []), array_flip(self::WIDGET_DASHBOARD));
        $span = self::WIDGET_SPAN_DEFAULT;
        foreach ($spanSimpan as $widget => $lebar) {
            $lebar = (int) $lebar;
            if (in_array($lebar, [4, 6, 8, 12], true)) {
                $span[$widget] = $lebar;
            }
        }

        return ['orderMap' => array_flip($order), 'hidden' => $hidden, 'span' => $span];
    }

    public function updateLayout(Request $request): JsonResponse
    {
        // Hanya super admin (posisi_id 1) yang boleh mengubah tata letak global.
        if ((int) auth()->user()->posisi_id !== 1) {
            abort(403, 'Hanya super admin yang boleh mengubah tata letak dashboard.');
        }

        $valid = $request->validate([
            'order' => ['required', 'array', 'min:1'],
            'order.*' => ['string', Rule::in(self::WIDGET_DASHBOARD)],
            'hidden' => ['sometimes', 'array'],
            'hidden.*' => ['string', Rule::in(self::WIDGET_DASHBOARD)],
            'span' => ['sometimes', 'array'],
            'span.*' => ['integer', Rule::in([4, 6, 8, 12])],
        ]);

        $span = self::WIDGET_SPAN_DEFAULT;
        foreach (array_intersect_key((array) ($valid['span'] ?? []), array_flip(self::WIDGET_DASHBOARD)) as $widget => $lebar) {
            $span[$widget] = (int) $lebar;
        }
        $baris = [
            'tata_letak' => json_encode([
                'order' => array_values($valid['order']),
                'hidden' => array_values($valid['hidden'] ?? []),
                'span' => $span,
            ]),
            'updated_at' => now(),
        ];
        if (DB::table('dashboard_layout')->whereNull('user_id')->exists()) {
            DB::table('dashboard_layout')->whereNull('user_id')->update($baris);
        } else {
            $baris['user_id'] = null;
            $baris['created_at'] = now();
            DB::table('dashboard_layout')->insert($baris);
        }

        return response()->json(['ok' => true]);
    }

    private function parseDate(?string $value): ?Carbon
    {
        if (! $value) {
        return null;
        }
        try {
        return Carbon::parse($value)->startOfDay();
        } catch (\Throwable) {
        return null;
        }
    }
}
