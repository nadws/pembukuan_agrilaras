<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardJurnalPerkiraanController extends Controller
{
    public function index(Request $request): View
    {
        $tgl1 = $this->dateOrDefault($request->input('tgl1'), now()->startOfMonth());
        $tgl2 = $this->dateOrDefault($request->input('tgl2'), now());
        if ($tgl1->gt($tgl2)) {
            [$tgl1, $tgl2] = [$tgl2, $tgl1];
        }

        $profitRows = DB::table('jurnal_perkiraan as j')
            ->join('impor_jurnal_perkiraan as i', 'i.id_impor_jurnal_perkiraan', '=', 'j.id_impor_jurnal_perkiraan')
            ->join('akun_perkiraan as a', 'a.id_akun_perkiraan', '=', 'j.id_akun_perkiraan')
            ->where('i.status', 'aktif')->whereBetween('j.tanggal', [$tgl1->toDateString(), $tgl2->toDateString()])
            ->whereIn('a.tipe_akun', ['REVE', 'COGS', 'EXPS', 'OINC', 'OEXP'])
            ->select('a.tipe_akun')->selectRaw('SUM(j.debit) as debit, SUM(j.kredit) as kredit')
            ->groupBy('a.tipe_akun')->get()->keyBy('tipe_akun');

        $nilai = fn (string $tipe, bool $pendapatan = false): float => (float) ($pendapatan
            ? ($profitRows[$tipe]->kredit ?? 0) - ($profitRows[$tipe]->debit ?? 0)
            : ($profitRows[$tipe]->debit ?? 0) - ($profitRows[$tipe]->kredit ?? 0));
        $labaRugi = [
            'pendapatan' => $nilai('REVE'),
            'hpp' => $nilai('COGS'),
            'beban_operasional' => $nilai('EXPS'),
            'pendapatan_lain' => $nilai('OINC', true),
            'beban_lain' => $nilai('OEXP'),
        ];
        // Pendapatan normalnya bersaldo kredit.
        $labaRugi['pendapatan'] = $nilai('REVE', true);
        $labaRugi['laba_kotor'] = $labaRugi['pendapatan'] - $labaRugi['hpp'];
        $labaRugi['total_beban'] = $labaRugi['beban_operasional'] + $labaRugi['beban_lain'];
        $labaRugi['laba_bersih'] = $labaRugi['laba_kotor'] - $labaRugi['beban_operasional'] + $labaRugi['pendapatan_lain'] - $labaRugi['beban_lain'];

        $trend = DB::table('jurnal_perkiraan as j')
            ->join('impor_jurnal_perkiraan as i', 'i.id_impor_jurnal_perkiraan', '=', 'j.id_impor_jurnal_perkiraan')
            ->join('akun_perkiraan as a', 'a.id_akun_perkiraan', '=', 'j.id_akun_perkiraan')
            ->where('i.status', 'aktif')->whereBetween('j.tanggal', [$tgl1->toDateString(), $tgl2->toDateString()])
            ->whereIn('a.tipe_akun', ['REVE', 'COGS', 'EXPS', 'OINC', 'OEXP'])
            ->selectRaw("DATE_FORMAT(j.tanggal, '%Y-%m') as periode")
            ->selectRaw("SUM(CASE WHEN a.tipe_akun IN ('REVE','OINC') THEN j.kredit-j.debit ELSE 0 END) as pendapatan")
            ->selectRaw("SUM(CASE WHEN a.tipe_akun IN ('COGS','EXPS','OEXP') THEN j.debit-j.kredit ELSE 0 END) as beban")
            ->groupBy('periode')->orderBy('periode')->get()
            ->map(fn ($row) => (object) ['periode' => $row->periode, 'pendapatan' => (float) $row->pendapatan, 'beban' => (float) $row->beban, 'laba' => (float) $row->pendapatan - (float) $row->beban]);

        $topBeban = DB::table('jurnal_perkiraan as j')
            ->join('impor_jurnal_perkiraan as i', 'i.id_impor_jurnal_perkiraan', '=', 'j.id_impor_jurnal_perkiraan')
            ->join('akun_perkiraan as a', 'a.id_akun_perkiraan', '=', 'j.id_akun_perkiraan')
            ->where('i.status', 'aktif')->whereBetween('j.tanggal', [$tgl1->toDateString(), $tgl2->toDateString()])
            ->whereIn('a.tipe_akun', ['COGS', 'EXPS', 'OEXP'])
            ->groupBy('a.id_akun_perkiraan', 'a.kode_perkiraan', 'a.nama')
            ->select('a.kode_perkiraan', 'a.nama')->selectRaw('SUM(j.debit-j.kredit) as nilai')
            ->havingRaw('SUM(j.debit-j.kredit) != 0')->orderByDesc('nilai')->limit(8)->get();

        $eggBalance = DB::table('stok_telur')->where('opname', 'T')->select('id_gudang')
            ->selectRaw('SUM(COALESCE(pcs,0)-COALESCE(pcs_kredit,0)) as pcs')
            ->selectRaw('SUM(COALESCE(kg,0)-COALESCE(kg_kredit,0)) as kg')->groupBy('id_gudang');
        $stokTelur = DB::table('gudang_telur as g')->leftJoinSub($eggBalance, 's', 's.id_gudang', '=', 'g.id_gudang_telur')
            ->select('g.id_gudang_telur', 'g.nm_gudang')->selectRaw('COALESCE(s.pcs,0) as pcs, COALESCE(s.kg,0) as kg')
            ->orderBy('g.nm_gudang')->get();

        $planBalance = DB::table('stok_produk_perencanaan')->select('id_pakan')
            ->selectRaw('SUM(COALESCE(pcs,0)-COALESCE(pcs_kredit,0)) as stok')
            ->selectRaw('SUM(CASE WHEN pcs > 0 THEN total_rp+biaya_dll ELSE 0 END)-SUM(CASE WHEN pcs_kredit > 0 THEN total_rp ELSE 0 END) as nilai_stok')
            ->groupBy('id_pakan');
        $stokPerencanaan = DB::table('tb_produk_perencanaan as p')
            ->leftJoinSub($planBalance, 's', 's.id_pakan', '=', 'p.id_produk')
            ->leftJoin('tb_satuan as u', 'u.id_satuan', '=', 'p.dosis_satuan')
            ->whereIn('p.kategori', ['pakan', 'vitamin', 'obat_pakan', 'obat_air', 'obat_ayam', 'vaksin'])
            ->select('p.nm_produk', 'p.kategori', 'u.nm_satuan')->selectRaw('COALESCE(s.stok,0) as stok, COALESCE(s.nilai_stok,0) as nilai_stok')
            ->orderBy('p.kategori')->orderByDesc('stok')->get();

        $generalBalance = DB::table('pembukuan_baru_stok')->select('id_produk')
            ->selectRaw('SUM(qty) as stok, SUM(qty*harga_satuan) as nilai_stok')->groupBy('id_produk');
        $stokUmum = DB::table('tb_produk as p')->leftJoinSub($generalBalance, 's', 's.id_produk', '=', 'p.id_produk')
            ->leftJoin('tb_satuan as u', 'u.id_satuan', '=', 'p.satuan_id')->where('p.kategori_id', 1)
            ->select('p.nm_produk', 'p.kd_produk', 'u.nm_satuan')->selectRaw('COALESCE(s.stok,0) as stok, COALESCE(s.nilai_stok,0) as nilai_stok')
            ->orderByDesc('stok')->orderBy('p.nm_produk')->get();

        return view('dashboard', [
            'title' => 'Dashboard', 'tgl1' => $tgl1->toDateString(), 'tgl2' => $tgl2->toDateString(),
            'labaRugi' => $labaRugi, 'trend' => $trend, 'topBeban' => $topBeban,
            'stokTelur' => $stokTelur, 'stokPerencanaan' => $stokPerencanaan, 'stokUmum' => $stokUmum,
            'latestJournal' => DB::table('jurnal_perkiraan')->max('tanggal'),
        ]);
    }

    private function dateOrDefault(mixed $value, Carbon $default): Carbon
    {
        try {
            return $value ? Carbon::parse((string) $value)->startOfDay() : $default->copy()->startOfDay();
        } catch (\Throwable) {
            return $default->copy()->startOfDay();
        }
    }
}
