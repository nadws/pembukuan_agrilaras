<?php

namespace App\Http\Controllers;

use App\Services\LaporanLabaRugiPerkiraanService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReviewKomposisiController extends Controller
{
    public function index(Request $request, LaporanLabaRugiPerkiraanService $service)
    {
        $data = $request->validate(['periode' => ['nullable', 'date_format:Y-m']]);
        $start = Carbon::createFromFormat('!Y-m', $data['periode'] ?? now()->format('Y-m'));
        $end = $start->copy()->endOfMonth();
        $result = $service->buat($start, $end);
        $sum = fn ($values) => array_reduce($values, fn ($total, $value) => bcadd($total, $value, 12), '0');
        $revenue = (float) $sum($result['revenue']);
        $otherIncome = (float) $sum($result['otherIncome']);
        $reportProfit = (float) $sum($result['afterTax']);
        $income = $revenue + $otherIncome;

        $ledgerAccounts = DB::table('akun_perkiraan as a')
            ->leftJoin('jurnal_perkiraan as j', function ($join) use ($start, $end) {
                $join->on('j.id_akun_perkiraan', '=', 'a.id_akun_perkiraan')
                    ->whereBetween('j.tanggal', [$start->toDateString(), $end->toDateString()]);
            })
            ->leftJoin('impor_jurnal_perkiraan as i', 'i.id_impor_jurnal_perkiraan', '=', 'j.id_impor_jurnal_perkiraan')
            ->where('a.aktif', true)->whereIn('a.tipe_akun', ['REVE', 'OINC', 'COGS', 'EXPS', 'OEXP'])
            ->groupBy('a.id_akun_perkiraan', 'a.kode_perkiraan', 'a.nama', 'a.tipe_akun')
            ->select('a.id_akun_perkiraan', 'a.kode_perkiraan', 'a.nama', 'a.tipe_akun')
            ->selectRaw("COALESCE(SUM(CASE WHEN i.status = 'aktif' THEN CASE WHEN a.tipe_akun IN ('REVE', 'OINC') THEN j.kredit - j.debit ELSE j.debit - j.kredit END ELSE 0 END), 0) AS nilai")
            ->orderBy('a.kode_perkiraan')->get();
        $accounts = $ledgerAccounts->whereIn('tipe_akun', ['COGS', 'EXPS', 'OEXP']);
        $incomeAccounts = $ledgerAccounts->whereIn('tipe_akun', ['REVE', 'OINC']);
        $defaults = ['600004-01', '600004-03', '600004-04', '600004-05', '600010', '600011-01', '600011-02'];
        $selected = $request->session()->get('review_komposisi.produksi_akun', $accounts->whereIn('kode_perkiraan', $defaults)->pluck('id_akun_perkiraan')->map(fn ($id) => (int) $id)->all());
        $feed = (float) $accounts->where('kode_perkiraan', '5101-04')->sum('nilai');
        $utilities = (float) $accounts->whereIn('id_akun_perkiraan', $selected)->where('kode_perkiraan', '!=', '5101-04')->sum('nilai');
        $profit = $reportProfit;
        $totalCosts = (float) $accounts->sum('nilai');
        $outsideCosts = $totalCosts - $feed - $utilities;
        $reconciliation = $income - $totalCosts - $profit;
        $chartTotal = $feed + $utilities + $profit;
        $chartReady = $income > 0 && $chartTotal > 0 && $feed >= 0 && $utilities >= 0 && $profit >= 0;

        return view('review-komposisi.index', compact('start', 'end', 'revenue', 'otherIncome', 'profit', 'reportProfit', 'income', 'accounts', 'incomeAccounts', 'selected', 'feed', 'utilities', 'totalCosts', 'outsideCosts', 'reconciliation', 'chartTotal', 'chartReady'));
    }

    public function settings(Request $request)
    {
        $data = $request->validate([
            'periode' => ['required', 'date_format:Y-m'],
            'akun' => ['nullable', 'array'],
            'akun.*' => ['integer', 'distinct'],
        ]);
        $ids = DB::table('akun_perkiraan')->where('aktif', true)
            ->whereIn('tipe_akun', ['COGS', 'EXPS', 'OEXP'])->where('kode_perkiraan', '!=', '5101-04')
            ->whereIn('id_akun_perkiraan', $data['akun'] ?? [])->pluck('id_akun_perkiraan')->map(fn ($id) => (int) $id)->all();
        $request->session()->put('review_komposisi.produksi_akun', $ids);

        return redirect()->route('review-komposisi.index', ['periode' => $data['periode']]);
    }

    public function transactions(Request $request, int $akun)
    {
        $data = $request->validate(['periode' => ['required', 'date_format:Y-m']]);
        $start = Carbon::createFromFormat('!Y-m', $data['periode']);
        $account = DB::table('akun_perkiraan')->where('id_akun_perkiraan', $akun)->where('aktif', true)
            ->whereIn('tipe_akun', ['REVE', 'OINC', 'COGS', 'EXPS', 'OEXP'])->first();
        abort_unless($account, 404);
        $query = DB::table('jurnal_perkiraan as j')->join('impor_jurnal_perkiraan as i', 'i.id_impor_jurnal_perkiraan', '=', 'j.id_impor_jurnal_perkiraan')
            ->where('i.status', 'aktif')->where('j.id_akun_perkiraan', $akun)
            ->whereBetween('j.tanggal', [$start->toDateString(), $start->copy()->endOfMonth()->toDateString()]);
        $totals = (clone $query)->selectRaw('COALESCE(SUM(j.debit), 0) as debit, COALESCE(SUM(j.kredit), 0) as kredit')->first();
        $rows = $query->select('j.*', 'i.nama_file')->orderBy('j.tanggal')->orderBy('j.id_jurnal_perkiraan')->paginate(50)->withQueryString();

        return view('review-komposisi.transactions', compact('account', 'start', 'totals', 'rows'));
    }
}
