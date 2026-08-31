<?php

namespace App\Http\Controllers;

use App\Exports\TemplateJurnalPerkiraanExport;
use App\Exports\LaporanLabaRugiPerkiraanExport;
use App\Http\Requests\PratinjauJurnalPerkiraanRequest;
use App\Models\AkunPerkiraan;
use App\Models\ImporJurnalPerkiraan;
use App\Models\JurnalPerkiraan;
use App\Services\ImporJurnalPerkiraanService;
use App\Services\LaporanArusKasPerkiraanService;
use App\Services\LaporanLabaRugiPerkiraanService;
use App\Services\LaporanNeracaPerkiraanService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class JurnalPerkiraanController extends Controller
{
    public function index(): View
    {
        return view('jurnal_perkiraan.index', [
            'title' => 'Jurnal Perkiraan',
            'batch' => ImporJurnalPerkiraan::latest('id_impor_jurnal_perkiraan')->get(),
            'preview' => session('preview_jurnal_perkiraan'),
        ]);
    }

    public function pratinjau(PratinjauJurnalPerkiraanRequest $request, ImporJurnalPerkiraanService $service): RedirectResponse
    {
        $preview = $service->pratinjau($request->file('file'));
        $token = (string) Str::uuid();
        Cache::put($this->cacheKey($token), $preview, now()->addMinutes(30));

        return back()->with('preview_jurnal_perkiraan', [
            'token' => $token,
            'ringkasan' => collect($preview)->except('detail')->all(),
        ]);
    }

    public function simpan(Request $request, ImporJurnalPerkiraanService $service): RedirectResponse
    {
        $request->validate(['token' => ['required', 'uuid']]);
        $preview = Cache::pull($this->cacheKey($request->token));
        abort_unless(is_array($preview), 419, 'Preview sudah kedaluwarsa. Silakan upload ulang.');

        $batch = $service->simpan($preview, auth()->id());

        return redirect()->route('jurnal-perkiraan.detail-batch', $batch)
            ->with('sukses', "{$batch->jumlah_detail} detail jurnal berhasil diimport.");
    }

    public function batalkan(ImporJurnalPerkiraan $impor_jurnal_perkiraan): RedirectResponse
    {
        DB::transaction(function () use ($impor_jurnal_perkiraan) {
            DB::update(
                '
                    UPDATE stok_produk_perencanaan AS s
                    INNER JOIN jurnal_perkiraan_stok_perencanaan AS m
                        ON m.id_stok_telur = s.id_stok_telur
                    SET s.`check` = m.check_sebelum,
                        s.cek_admin = m.cek_admin_sebelum
                    WHERE m.id_impor_jurnal_perkiraan = ?
                ',
                [$impor_jurnal_perkiraan->getKey()]
            );

            $impor_jurnal_perkiraan->detail()->delete();
            $impor_jurnal_perkiraan->delete();
        });

        return redirect()->route('jurnal-perkiraan.index')
            ->with('sukses', 'Batch import dan seluruh detail jurnal berhasil dihapus permanen.');
    }

    public function detailBatch(Request $request, ImporJurnalPerkiraan $impor_jurnal_perkiraan): View
    {
        $filters = $request->validate([
            'cari' => ['nullable', 'string', 'max:100'],
            'tipe' => ['nullable', 'string', 'max:100'],
            'per_page' => ['nullable', 'integer', 'in:25,50,100'],
        ]);
        $query = $impor_jurnal_perkiraan->detail()
            ->with('akun:id_akun_perkiraan,kode_perkiraan,nama')
            ->when($filters['tipe'] ?? null, fn ($query, $tipe) => $query->where('tipe_transaksi', $tipe))
            ->when($filters['cari'] ?? null, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('nomor_transaksi', 'like', "%{$search}%")
                        ->orWhere('tipe_transaksi', 'like', "%{$search}%")
                        ->orWhere('deskripsi', 'like', "%{$search}%")
                        ->orWhereHas('akun', fn ($account) => $account
                            ->where('kode_perkiraan', 'like', "%{$search}%")
                            ->orWhere('nama', 'like', "%{$search}%"));
                });
            })
            ->orderBy('tanggal')->orderBy('nomor_transaksi')->orderBy('urutan_detail');

        return view('jurnal_perkiraan.detail_batch', [
            'title' => 'Detail Import Jurnal',
            'batch' => $impor_jurnal_perkiraan,
            'detail' => $query->paginate($filters['per_page'] ?? 50)->withQueryString(),
            'tipeOptions' => $impor_jurnal_perkiraan->detail()->whereNotNull('tipe_transaksi')
                ->distinct()->orderBy('tipe_transaksi')->pluck('tipe_transaksi'),
        ]);
    }

    public function labaRugi(Request $request, LaporanLabaRugiPerkiraanService $service): View
    {
        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
            7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];
        $years = range(now()->year - 5, now()->year + 1);
        $result = null;
        $start = null;
        $end = null;

        if ($request->filled('bulan_dari')) {
            $data = $request->validate([
                'bulan_dari' => ['required', 'integer', 'between:1,12'],
                'tahun_dari' => ['required', 'integer', 'between:2000,2100'],
                'bulan_sampai' => ['required', 'integer', 'between:1,12'],
                'tahun_sampai' => ['required', 'integer', 'between:2000,2100'],
            ]);
            $start = Carbon::create($data['tahun_dari'], $data['bulan_dari'], 1)->startOfMonth();
            $end = Carbon::create($data['tahun_sampai'], $data['bulan_sampai'], 1)->startOfMonth();
            if ($start->gt($end)) {
                throw ValidationException::withMessages(['bulan_sampai' => 'Periode akhir harus setelah periode awal.']);
            }
            if ($start->diffInMonths($end) > 23) {
                throw ValidationException::withMessages(['bulan_sampai' => 'Maksimal laporan 24 bulan.']);
            }
            $result = $service->buat($start, $end);
        }

        return view('jurnal_perkiraan.laba_rugi', [
            'title' => 'Laba/Rugi (Multi Periode)',
            'months' => $months,
            'years' => $years,
            'result' => $result,
            'start' => $start,
            'end' => $end,
        ]);
    }

    public function budgetLabaRugi(Request $request): View
    {
        $tahun = (int) $request->input('tahun', now()->year);
        if ($tahun < 2000 || $tahun > 2100) {
            $tahun = now()->year;
        }

        $accounts = AkunPerkiraan::query()
            ->whereIn('tipe_akun', ['REVE', 'COGS', 'EXPS', 'OINC', 'OEXP'])
            ->where('aktif', true)
            ->whereNotIn('id_akun_perkiraan', AkunPerkiraan::query()
                ->whereNotNull('id_akun_induk')->select('id_akun_induk'))
            ->orderBy('tipe_akun')->orderBy('kode_perkiraan')->get();

        $budget = DB::table('budget_laba_rugi')
            ->where('tahun', $tahun)
            ->get()->groupBy('id_akun_perkiraan')
            ->map(fn ($rows) => $rows->keyBy('bulan'));

        return view('jurnal_perkiraan.budget_laba_rugi', [
            'title' => 'Kelola Budget Laba Rugi',
            'tahun' => $tahun,
            'years' => range(now()->year - 5, now()->year + 3),
            'months' => [1=>'Jan',2=>'Feb',3=>'Mar',4=>'Apr',5=>'Mei',6=>'Jun',7=>'Jul',8=>'Agu',9=>'Sep',10=>'Okt',11=>'Nov',12=>'Des'],
            'accounts' => $accounts,
            'budget' => $budget,
        ]);
    }

    public function arusKas(Request $request, LaporanArusKasPerkiraanService $service): View
    {
        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
            7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];
        $cashRoot = AkunPerkiraan::query()->where('kode_perkiraan', '1101')->first();
        $cashAccounts = AkunPerkiraan::query()
            ->where('aktif', true)
            ->when($cashRoot, fn ($query) => $query->where('id_akun_induk', $cashRoot->getKey()), fn ($query) => $query
                ->where(function ($query) {
                    $query->where('nama', 'like', '%Kas%')
                        ->orWhere('nama', 'like', '%Bank%')
                        ->orWhere('nama', 'like', '%BCA%');
                }))
            ->whereExists(fn ($query) => $query->selectRaw('1')->from('jurnal_perkiraan as jp')
                ->whereColumn('jp.id_akun_perkiraan', 'akun_perkiraan.id_akun_perkiraan'))
            ->orderBy('kode_perkiraan')
            ->get();

        $filters = $request->validate([
            'akun' => ['nullable', 'array'],
            'akun.*' => ['integer'],
            'bulan_dari' => ['nullable', 'integer', 'between:1,12'],
            'tahun_dari' => ['nullable', 'integer', 'between:2000,2100'],
            'bulan_sampai' => ['nullable', 'integer', 'between:1,12'],
            'tahun_sampai' => ['nullable', 'integer', 'between:2000,2100'],
        ]);
        $preferred = $cashAccounts->firstWhere('nama', 'BCA 0513277722 (Cost-1)');
        $selectedIds = collect($filters['akun'] ?? [])->map(fn ($id) => (int) $id)->intersect($cashAccounts->pluck('id_akun_perkiraan'))->values();
        $selectedAccounts = $cashAccounts->whereIn('id_akun_perkiraan', $selectedIds)->values();
        if ($selectedAccounts->isEmpty()) {
            $selectedAccounts = collect([$preferred ?? $cashAccounts->first()])->filter()->values();
        }
        $selectedAccount = $selectedAccounts->first();
        $start = Carbon::create(
            (int) ($filters['tahun_dari'] ?? now()->year),
            (int) ($filters['bulan_dari'] ?? 1),
            1
        )->startOfMonth();
        $end = Carbon::create(
            (int) ($filters['tahun_sampai'] ?? now()->year),
            (int) ($filters['bulan_sampai'] ?? now()->month),
            1
        )->endOfMonth();
        if ($start->gt($end)) {
            throw ValidationException::withMessages(['bulan_sampai' => 'Periode akhir harus setelah periode awal.']);
        }
        if ($start->diffInMonths($end) > 23) {
            throw ValidationException::withMessages(['bulan_sampai' => 'Maksimal laporan 24 bulan.']);
        }

        return view('jurnal_perkiraan.arus_kas', [
            'title' => 'Laporan Arus Kas',
            'months' => $months,
            'years' => range(now()->year - 5, now()->year + 1),
            'cashAccounts' => $cashAccounts,
            'selectedAccount' => $selectedAccount,
            'selectedAccounts' => $selectedAccounts,
            'start' => $start,
            'end' => $end,
            'result' => $selectedAccounts->isNotEmpty() ? $service->buat($selectedAccounts, $start, $end) : null,
        ]);
    }

    public function detailArusKas(Request $request): View
    {
        $data = $request->validate([
            'akun_kas' => ['required'],
            'akun_lawan' => ['required', 'integer', 'exists:akun_perkiraan,id_akun_perkiraan'],
            'kategori' => ['nullable', 'string', 'max:60'],
            'nilai' => ['nullable', 'numeric', 'min:0'],
            'tanggal_awal' => ['required', 'date'],
            'tanggal_akhir' => ['required', 'date', 'after_or_equal:tanggal_awal'],
        ]);
        $akunKasIds = is_array($data['akun_kas']) ? array_map('intval', $data['akun_kas']) : [(int) $data['akun_kas']];
        $akunKasAccounts = AkunPerkiraan::whereIn('id_akun_perkiraan', $akunKasIds)->get();
        abort_if($akunKasAccounts->count() !== count(array_unique($akunKasIds)), 404);
        $akunKas = $akunKasAccounts->first();
        $akunLawan = AkunPerkiraan::findOrFail($data['akun_lawan']);
        $kategori = trim((string) ($data['kategori'] ?? ''));
        $rows = JurnalPerkiraan::query()
            ->with('akun:id_akun_perkiraan,kode_perkiraan,nama')
            ->where('id_akun_perkiraan', $akunLawan->getKey())
            ->whereBetween('tanggal', [$data['tanggal_awal'], $data['tanggal_akhir']])
            ->whereHas('impor', fn ($query) => $query->where('status', 'aktif'))
            ->whereExists(function ($query) use ($data, $akunKasIds) {
                $query->selectRaw('1')->from('jurnal_perkiraan as kas')
                    ->whereColumn('kas.id_impor_jurnal_perkiraan', 'jurnal_perkiraan.id_impor_jurnal_perkiraan')
                    ->whereColumn('kas.tanggal', 'jurnal_perkiraan.tanggal')
                    ->whereColumn('kas.nomor_transaksi', 'jurnal_perkiraan.nomor_transaksi')
                    ->whereRaw("COALESCE(kas.tipe_transaksi, '') = COALESCE(jurnal_perkiraan.tipe_transaksi, '')")
                    ->whereIn('kas.id_akun_perkiraan', $akunKasIds);
            })
            ->orderBy('tanggal')->orderBy('nomor_transaksi')->orderBy('urutan_detail')->get();
        if ($kategori && $akunLawan->tipe_akun === 'APAY') {
            $descriptionCache = [];
            $rows = $rows->filter(function ($row) use ($kategori, &$descriptionCache) {
                $key = implode('|', [$row->id_impor_jurnal_perkiraan, $row->tanggal, $row->nomor_transaksi, $row->tipe_transaksi]);
                $text = strtolower((string) ($descriptionCache[$key] ??= DB::table('jurnal_perkiraan')
                    ->where('id_impor_jurnal_perkiraan', $row->id_impor_jurnal_perkiraan)
                    ->whereDate('tanggal', $row->tanggal)
                    ->where('nomor_transaksi', $row->nomor_transaksi)
                    ->whereRaw("COALESCE(tipe_transaksi, '') = ?", [(string) $row->tipe_transaksi])
                    ->pluck('deskripsi')->filter()->implode(' ')));
                $needle = strtolower($kategori);
                if ($needle === 'pakan') return (bool) preg_match('/pakan|pokh?pan|newhope|al100|524a/', $text);
                if ($needle === 'vitamin & vaksin') return (bool) preg_match('/vaksin|vitamin|obat|virkon|flytox|hostazym|elitox/', $text);
                if ($needle === 'pullet / ayam') return (bool) preg_match('/pullet|ayam/', $text);
                if ($needle === 'telur') return (bool) preg_match('/telur|rak telur/', $text);
                return ! preg_match('/pakan|pokh?pan|newhope|al100|524a|vaksin|vitamin|obat|virkon|flytox|hostazym|elitox|pullet|ayam|telur|rak telur/', $text);
            })->values();
        }
        $rawTotal = (float) $rows->sum(fn ($row) => abs((float) $row->debit - (float) $row->kredit));
        $expectedTotal = array_key_exists('nilai', $data) ? (float) $data['nilai'] : $rawTotal;
        $scale = $rawTotal > 0 ? $expectedTotal / $rawTotal : 1;
        $rows = $rows->map(function ($row) use ($scale) {
            $row->nilai_detail = round(abs((float) $row->debit - (float) $row->kredit) * $scale, 2);
            return $row;
        });
        if ($rows->isNotEmpty()) {
            $rounding = round($expectedTotal - (float) $rows->sum('nilai_detail'), 2);
            $rows->last()->nilai_detail = round($rows->last()->nilai_detail + $rounding, 2);
        }
        return view('jurnal_perkiraan.arus_kas_detail', [
            'title' => 'Detail Arus Kas', 'rows' => $rows, 'akunKas' => $akunKas, 'akunKasAccounts' => $akunKasAccounts, 'akunLawan' => $akunLawan,
            'kategori' => $kategori, 'tanggalAwal' => $data['tanggal_awal'], 'tanggalAkhir' => $data['tanggal_akhir'],
            'total' => $expectedTotal,
        ]);
    }

    public function simpanBudgetLabaRugi(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'tahun' => ['required', 'integer', 'between:2000,2100'],
            'budget' => ['nullable', 'array'],
            'budget.*' => ['array'],
            'budget.*.*' => ['nullable', 'numeric', 'min:0'],
        ]);

        $accountIds = AkunPerkiraan::query()
            ->whereIn('tipe_akun', ['REVE', 'COGS', 'EXPS', 'OINC', 'OEXP'])
            ->where('aktif', true)->pluck('id_akun_perkiraan')->map(fn ($id) => (int) $id)->flip();
        $rows = [];
        $now = now();
        foreach ($data['budget'] ?? [] as $accountId => $months) {
            if (! $accountIds->has((int) $accountId)) {
                continue;
            }
            foreach ($months as $month => $nominal) {
                if ((int) $month < 1 || (int) $month > 12 || $nominal === null || $nominal === '') {
                    continue;
                }
                $rows[] = [
                    'id_akun_perkiraan' => (int) $accountId,
                    'tahun' => (int) $data['tahun'],
                    'bulan' => (int) $month,
                    'nominal' => (float) $nominal,
                    'dibuat_oleh' => auth()->id(),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        DB::transaction(function () use ($data, $rows) {
            DB::table('budget_laba_rugi')->where('tahun', $data['tahun'])->delete();
            if ($rows) {
                DB::table('budget_laba_rugi')->insert($rows);
            }
        });

        return redirect()->route('jurnal-perkiraan.laba-rugi.budget', ['tahun' => $data['tahun']])
            ->with('sukses', 'Budget laba rugi tahun '.$data['tahun'].' berhasil disimpan.');
    }

    public function neraca(Request $request, LaporanNeracaPerkiraanService $service): View
    {
        $latestJournalDate = DB::table('jurnal_perkiraan as j')
            ->join('impor_jurnal_perkiraan as i', 'i.id_impor_jurnal_perkiraan', '=', 'j.id_impor_jurnal_perkiraan')
            ->where('i.status', 'aktif')
            ->max('j.tanggal');

        $data = $request->validate([
            'tanggal' => ['nullable', 'date'],
        ]);
        $reportDate = Carbon::parse($data['tanggal'] ?? $latestJournalDate ?? now()->toDateString())->startOfDay();

        return view('jurnal_perkiraan.neraca', [
            'title' => 'Laporan Neraca',
            'reportDate' => $reportDate,
            'result' => $service->buat($reportDate),
        ]);
    }

    public function cetakNeraca(Request $request, LaporanNeracaPerkiraanService $service): View
    {
        $latestJournalDate = DB::table('jurnal_perkiraan as j')
            ->join('impor_jurnal_perkiraan as i', 'i.id_impor_jurnal_perkiraan', '=', 'j.id_impor_jurnal_perkiraan')
            ->where('i.status', 'aktif')
            ->max('j.tanggal');

        $data = $request->validate(['tanggal' => ['nullable', 'date']]);
        $reportDate = Carbon::parse($data['tanggal'] ?? $latestJournalDate ?? now()->toDateString())->startOfDay();

        return view('jurnal_perkiraan.neraca_cetak', [
            'title' => 'Cetak Laporan Neraca',
            'reportDate' => $reportDate,
            'result' => $service->buat($reportDate),
        ]);
    }

    public function detailAkun(Request $request, AkunPerkiraan $akun_perkiraan): View
    {
        $request->validate(['tanggal_awal' => ['required', 'date'], 'tanggal_akhir' => ['required', 'date', 'after_or_equal:tanggal_awal']]);

        $accountIds = $this->accountSubtreeIds($akun_perkiraan);
        $detail = JurnalPerkiraan::with(['impor', 'akun'])
            ->whereIn('id_akun_perkiraan', $accountIds)
            ->whereBetween('tanggal', [$request->tanggal_awal, $request->tanggal_akhir])
            ->whereHas('impor', fn ($query) => $query->where('status', 'aktif'))
            ->orderBy('tanggal')->orderBy('nomor_transaksi')->orderBy('urutan_detail')->get();
        $totalDebit = $detail->reduce(fn ($total, $item) => bcadd($total, $item->debit, 12), '0.000000000000');
        $totalKredit = $detail->reduce(fn ($total, $item) => bcadd($total, $item->kredit, 12), '0.000000000000');
        $nilaiLaporan = in_array($akun_perkiraan->tipe_akun, ['REVE', 'OINC'], true)
            ? bcsub($totalKredit, $totalDebit, 12)
            : bcsub($totalDebit, $totalKredit, 12);

        return view('jurnal_perkiraan.detail_akun', [
            'title' => 'Detail Jurnal '.$akun_perkiraan->nama,
            'akun' => $akun_perkiraan,
            'tanggalAwal' => $request->tanggal_awal,
            'tanggalAkhir' => $request->tanggal_akhir,
            'detail' => $detail,
            'totalDebit' => $totalDebit,
            'totalKredit' => $totalKredit,
            'nilaiLaporan' => $nilaiLaporan,
            'jumlahAkun' => count($accountIds),
        ]);
    }

    public function exportLabaRugi(Request $request, LaporanLabaRugiPerkiraanService $service): BinaryFileResponse
    {
        $data = $request->validate([
            'bulan_dari' => ['required', 'integer', 'between:1,12'],
            'tahun_dari' => ['required', 'integer', 'between:2000,2100'],
            'bulan_sampai' => ['required', 'integer', 'between:1,12'],
            'tahun_sampai' => ['required', 'integer', 'between:2000,2100'],
        ]);

        $start = Carbon::create($data['tahun_dari'], $data['bulan_dari'], 1)->startOfMonth();
        $end = Carbon::create($data['tahun_sampai'], $data['bulan_sampai'], 1)->startOfMonth();
        if ($start->gt($end)) {
            throw ValidationException::withMessages(['bulan_sampai' => 'Periode akhir harus setelah periode awal.']);
        }
        if ($start->diffInMonths($end) > 23) {
            throw ValidationException::withMessages(['bulan_sampai' => 'Maksimal laporan 24 bulan.']);
        }

        $filename = "laba-rugi-{$start->format('Y-m')}-sampai-{$end->format('Y-m')}.xlsx";

        return Excel::download(new LaporanLabaRugiPerkiraanExport($service->buat($start, $end)), $filename);
    }

    public function template(): BinaryFileResponse
    {
        return Excel::download(new TemplateJurnalPerkiraanExport(), 'template-jurnal-perkiraan.xlsx');
    }

    private function cacheKey(string $token): string
    {
        return 'preview_jurnal_perkiraan:'.auth()->id().':'.$token;
    }

    private function accountSubtreeIds(AkunPerkiraan $account): array
    {
        $ids = [$account->getKey()];
        foreach ($account->akunAnak as $child) {
            $ids = array_merge($ids, $this->accountSubtreeIds($child));
        }

        return $ids;
    }
}
