<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class LaporanAkhirBulanController extends Controller
{
    public function index(Request $request): View
    {
        $data = $request->validate([
            'tgl1' => ['nullable', 'date_format:Y-m-d'],
            'tgl2' => ['nullable', 'date_format:Y-m-d'],
            'bulan' => ['nullable', 'integer', 'between:1,12'],
            'tahun' => ['nullable', 'integer', 'between:2000,2100'],
            'tipe' => ['nullable', 'array'],
            'tipe.*' => ['string', 'in:faktur_penjualan,penerimaan_penjualan,transfer_penjualan,jurnal_umum,lainnya'],
            'semua_tipe' => ['nullable', 'boolean'],
            'akun' => ['nullable', 'array'],
            'akun.*' => ['integer'],
            'tipe_penjualan' => ['nullable', 'array'],
            'tipe_penjualan.*' => ['string', 'in:faktur_penjualan,penerimaan_penjualan,transfer_penjualan,jurnal_umum,lainnya'],
            'semua_tipe_penjualan' => ['nullable', 'boolean'],
            'akun_penjualan' => ['nullable', 'array'],
            'akun_penjualan.*' => ['integer'],
        ]);

        [$startDate, $currentCutoff] = $this->reportPeriod($data);
        $previousCutoff = $startDate->copy()->subDay()->endOfDay();

        $transactionTypeOptions = $this->transactionTypeGroups();

        // 1. Piutang summary
        $accounts = DB::table('akun_perkiraan as a')
            ->where('a.aktif', true)
            ->where('a.nama', 'like', '%Piutang%')
            ->whereExists(fn ($query) => $query->selectRaw('1')->from('jurnal_perkiraan as j')
                ->whereColumn('j.id_akun_perkiraan', 'a.id_akun_perkiraan'))
            ->orderBy('a.kode_perkiraan')
            ->get(['a.id_akun_perkiraan', 'a.kode_perkiraan', 'a.nama']);

        $rows = $accounts->map(function ($account) use ($previousCutoff, $currentCutoff) {
            $previous = $this->balance((int) $account->id_akun_perkiraan, $previousCutoff);
            $current = $this->balance((int) $account->id_akun_perkiraan, $currentCutoff);

            return [
                'type' => $account->kode_perkiraan,
                'label' => $account->nama,
                'previous' => $previous,
                'current' => $current,
                'change' => $current - $previous,
            ];
        })->values();

        // Accounts list for modals
        $withdrawalAccountCodes = ['110103', '110105', '110107', '110108', '110109', '110110', '110111'];
        $penjualanDefaultAccountCodes = ['110103', '110105', '110107', '110108', '110109', '110110', '110111', '400001', '400002', '400003', '400004', '400005', '400006', '400007', '600003-02', '600011-01', '710010'];

        $availableAccounts = DB::table('akun_perkiraan as a')
            ->where('a.aktif', true)
            ->orderBy('a.kode_perkiraan')
            ->get(['id_akun_perkiraan', 'kode_perkiraan', 'nama']);

        $defaultAccountIds = $availableAccounts->whereIn('kode_perkiraan', $withdrawalAccountCodes)->pluck('id_akun_perkiraan')->map(fn ($id) => (int) $id)->all();
        $defaultPenjualanAccountIds = $availableAccounts->whereIn('kode_perkiraan', $penjualanDefaultAccountCodes)->pluck('id_akun_perkiraan')->map(fn ($id) => (int) $id)->all();
        $userId = auth()->id();

        // 2. Laporan Penarikan Uang (load from saved setting if no query params)
        $savedPenarikan = $this->getSavedSetting('penarikan', ['faktur_penjualan', 'penerimaan_penjualan'], $defaultAccountIds, $userId);
        $hasPenarikanInput = $request->has('tipe') || $request->has('akun') || $request->has('semua_tipe');

        if ($hasPenarikanInput) {
            $allTransactionTypes = (bool) ($data['semua_tipe'] ?? false);
            $selectedTransactionTypes = collect($allTransactionTypes ? [] : ($data['tipe'] ?? ['faktur_penjualan', 'penerimaan_penjualan']))->filter(fn ($type) => isset($transactionTypeOptions[$type]))->values()->all();
            $selectedAccountIds = array_key_exists('akun', $data)
                ? collect($data['akun'] ?? [])->map(fn ($id) => (int) $id)->intersect($availableAccounts->pluck('id_akun_perkiraan'))->values()->all()
                : $defaultAccountIds;

            $this->saveSetting('penarikan', $selectedTransactionTypes, $allTransactionTypes, $selectedAccountIds, $userId);
        } else {
            $allTransactionTypes = $savedPenarikan['semua_tipe'];
            $selectedTransactionTypes = $savedPenarikan['types'];
            $selectedAccountIds = collect($savedPenarikan['accounts'])->map(fn ($id) => (int) $id)->intersect($availableAccounts->pluck('id_akun_perkiraan'))->values()->all();
        }

        $selectedTypeCodes = collect($selectedTransactionTypes)->flatMap(fn ($type) => $transactionTypeOptions[$type]['codes'])->unique()->values()->all();
        $withdrawalRows = $this->queryLedgerTable($startDate, $currentCutoff, $selectedTypeCodes, $selectedAccountIds);
        $withdrawalDebit = (float) $withdrawalRows->sum('debit');
        $withdrawalCredit = (float) $withdrawalRows->sum('kredit');
        $withdrawalTotal = $withdrawalDebit - $withdrawalCredit;

        // 3. Laporan Uang Penjualan (load from saved setting if no query params)
        $savedPenjualan = $this->getSavedSetting('penjualan', ['faktur_penjualan', 'penerimaan_penjualan'], $defaultPenjualanAccountIds, $userId);
        $hasPenjualanInput = $request->has('tipe_penjualan') || $request->has('akun_penjualan') || $request->has('semua_tipe_penjualan');

        if ($hasPenjualanInput) {
            $allPenjualanTypes = (bool) ($data['semua_tipe_penjualan'] ?? false);
            $selectedPenjualanTypes = collect($allPenjualanTypes ? [] : ($data['tipe_penjualan'] ?? ['faktur_penjualan', 'penerimaan_penjualan']))->filter(fn ($type) => isset($transactionTypeOptions[$type]))->values()->all();
            $selectedPenjualanAccountIds = array_key_exists('akun_penjualan', $data)
                ? collect($data['akun_penjualan'] ?? [])->map(fn ($id) => (int) $id)->intersect($availableAccounts->pluck('id_akun_perkiraan'))->values()->all()
                : $defaultPenjualanAccountIds;

            $this->saveSetting('penjualan', $selectedPenjualanTypes, $allPenjualanTypes, $selectedPenjualanAccountIds, $userId);
        } else {
            $allPenjualanTypes = $savedPenjualan['semua_tipe'];
            $selectedPenjualanTypes = $savedPenjualan['types'];
            $selectedPenjualanAccountIds = collect($savedPenjualan['accounts'])->map(fn ($id) => (int) $id)->intersect($availableAccounts->pluck('id_akun_perkiraan'))->values()->all();
        }

        $selectedPenjualanTypeCodes = collect($selectedPenjualanTypes)->flatMap(fn ($type) => $transactionTypeOptions[$type]['codes'])->unique()->values()->all();
        $penjualanRows = $this->queryLedgerTable($startDate, $currentCutoff, $selectedPenjualanTypeCodes, $selectedPenjualanAccountIds);
        $penjualanDebit = (float) $penjualanRows->sum('debit');
        $penjualanCredit = (float) $penjualanRows->sum('kredit');
        $penjualanTotal = $penjualanDebit - $penjualanCredit;

        return view('laporan.akhir_bulan', [
            'title' => 'Laporan Akhir Bulan',
            'startDate' => $startDate,
            'previousCutoff' => $previousCutoff,
            'currentCutoff' => $currentCutoff,
            'rows' => $rows,
            'previousTotal' => (float) $rows->sum('previous'),
            'currentTotal' => (float) $rows->sum('current'),

            // Penarikan Uang
            'withdrawalRows' => $withdrawalRows,
            'withdrawalDebit' => $withdrawalDebit,
            'withdrawalCredit' => $withdrawalCredit,
            'withdrawalTotal' => $withdrawalTotal,
            'selectedTransactionTypes' => $selectedTransactionTypes,
            'allTransactionTypes' => $allTransactionTypes,
            'selectedAccountIds' => $selectedAccountIds,

            // Uang Penjualan
            'penjualanRows' => $penjualanRows,
            'penjualanDebit' => $penjualanDebit,
            'penjualanCredit' => $penjualanCredit,
            'penjualanTotal' => $penjualanTotal,
            'selectedPenjualanTypes' => $selectedPenjualanTypes,
            'allPenjualanTypes' => $allPenjualanTypes,
            'selectedPenjualanAccountIds' => $selectedPenjualanAccountIds,

            // Shared options
            'transactionTypeOptions' => $transactionTypeOptions,
            'availableAccounts' => $availableAccounts,
            'withdrawalAccounts' => $availableAccounts,
        ]);
    }

    public function detailPenarikan(Request $request, int $akun): View
    {
        $data = $request->validate([
            'tgl1' => ['nullable', 'date_format:Y-m-d'],
            'tgl2' => ['nullable', 'date_format:Y-m-d'],
            'bulan' => ['nullable', 'integer', 'between:1,12'],
            'tahun' => ['nullable', 'integer', 'between:2000,2100'],
            'tipe' => ['nullable', 'array'],
            'tipe.*' => ['string', 'in:faktur_penjualan,penerimaan_penjualan,transfer_penjualan,jurnal_umum,lainnya'],
            'semua_tipe' => ['nullable', 'boolean'],
            'akun_filter' => ['nullable', 'array'],
            'akun_filter.*' => ['integer'],
            'tipe_penjualan' => ['nullable', 'array'],
            'tipe_penjualan.*' => ['string', 'in:faktur_penjualan,penerimaan_penjualan,transfer_penjualan,jurnal_umum,lainnya'],
            'semua_tipe_penjualan' => ['nullable', 'boolean'],
            'akun_penjualan' => ['nullable', 'array'],
            'akun_penjualan.*' => ['integer'],
            'cari' => ['nullable', 'string', 'max:100'],
            'per_page' => ['nullable', 'integer', 'in:25,50,100'],
        ]);

        $account = DB::table('akun_perkiraan')
            ->where('id_akun_perkiraan', $akun)
            ->where('aktif', true)
            ->first();
        abort_unless($account, 404);

        [$start, $end] = $this->reportPeriod($data);

        $transactionTypeOptions = $this->transactionTypeGroups();
        $userId = auth()->id();

        $savedPenarikan = $this->getSavedSetting('penarikan', ['faktur_penjualan', 'penerimaan_penjualan'], [], $userId);
        $allTransactionTypes = array_key_exists('semua_tipe', $data) ? (bool) $data['semua_tipe'] : $savedPenarikan['semua_tipe'];
        $selectedTransactionTypes = array_key_exists('tipe', $data)
            ? collect($allTransactionTypes ? [] : $data['tipe'])->filter(fn ($type) => isset($transactionTypeOptions[$type]))->values()->all()
            : $savedPenarikan['types'];

        $selectedTypeCodes = collect($selectedTransactionTypes)->flatMap(fn ($type) => $transactionTypeOptions[$type]['codes'])->unique()->values()->all();

        $query = DB::table('jurnal_perkiraan as j')
            ->join('impor_jurnal_perkiraan as i', 'i.id_impor_jurnal_perkiraan', '=', 'j.id_impor_jurnal_perkiraan')
            ->where('i.status', 'aktif')
            ->where('j.id_akun_perkiraan', $account->id_akun_perkiraan)
            ->whereBetween('j.tanggal', [$start->toDateString(), $end->toDateString()])
            ->when($selectedTypeCodes !== [], fn ($query) => $query->whereIn('j.tipe_transaksi', $selectedTypeCodes))
            ->where(function ($q) {
                $q->whereNull('j.deskripsi')
                    ->orWhere(function ($w) {
                        $w->where('j.deskripsi', 'not like', '%tagihan%')
                            ->where('j.deskripsi', 'not like', '%bunga bank%')
                            ->where('j.deskripsi', 'not like', '%biaya adm%')
                            ->where('j.deskripsi', 'not like', '%biaya transportasi%');
                    });
            })
            ->when(trim((string) ($data['cari'] ?? '')), function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('j.nomor_transaksi', 'like', "%{$search}%")
                        ->orWhere('j.tipe_transaksi', 'like', "%{$search}%")
                        ->orWhere('j.deskripsi', 'like', "%{$search}%");
                });
            });

        $totals = (clone $query)->selectRaw('COALESCE(SUM(j.debit),0) debit, COALESCE(SUM(j.kredit),0) kredit')->first();
        $details = $query->select('j.tanggal', 'j.nomor_transaksi', 'j.tipe_transaksi', 'j.deskripsi', 'j.debit', 'j.kredit')
            ->orderBy('j.tanggal')->orderBy('j.nomor_transaksi')->orderBy('j.urutan_detail')
            ->paginate($data['per_page'] ?? 50)->withQueryString();

        $savedPenjualan = $this->getSavedSetting('penjualan', ['faktur_penjualan', 'penerimaan_penjualan'], [], $userId);

        return view('laporan.akhir_bulan_penarikan_detail', [
            'title' => 'Detail Penarikan Uang',
            'account' => $account,
            'start' => $start,
            'end' => $end,
            'details' => $details,
            'debitTotal' => (float) $totals->debit,
            'creditTotal' => (float) $totals->kredit,
            'withdrawalTotal' => (float) $totals->debit - (float) $totals->kredit,
            'search' => trim((string) ($data['cari'] ?? '')),
            'transactionTypeOptions' => $transactionTypeOptions,
            'selectedTransactionTypes' => $selectedTransactionTypes,
            'allTransactionTypes' => $allTransactionTypes,
            'selectedAccountIds' => collect($data['akun_filter'] ?? $savedPenarikan['accounts'])->map(fn ($id) => (int) $id)->values()->all(),
            'selectedPenjualanTypes' => $data['tipe_penjualan'] ?? $savedPenjualan['types'],
            'allPenjualanTypes' => array_key_exists('semua_tipe_penjualan', $data) ? (bool) $data['semua_tipe_penjualan'] : $savedPenajalan['semua_tipe'] ?? false,
            'selectedPenjualanAccountIds' => $data['akun_penjualan'] ?? $savedPenjualan['accounts'],
        ]);
    }

    public function detailPenjualan(Request $request, int $akun): View
    {
        $data = $request->validate([
            'tgl1' => ['nullable', 'date_format:Y-m-d'],
            'tgl2' => ['nullable', 'date_format:Y-m-d'],
            'bulan' => ['nullable', 'integer', 'between:1,12'],
            'tahun' => ['nullable', 'integer', 'between:2000,2100'],
            'tipe' => ['nullable', 'array'],
            'tipe.*' => ['string', 'in:faktur_penjualan,penerimaan_penjualan,transfer_penjualan,jurnal_umum,lainnya'],
            'semua_tipe' => ['nullable', 'boolean'],
            'akun_filter' => ['nullable', 'array'],
            'akun_filter.*' => ['integer'],
            'tipe_penjualan' => ['nullable', 'array'],
            'tipe_penjualan.*' => ['string', 'in:faktur_penjualan,penerimaan_penjualan,transfer_penjualan,jurnal_umum,lainnya'],
            'semua_tipe_penjualan' => ['nullable', 'boolean'],
            'akun_penjualan' => ['nullable', 'array'],
            'akun_penjualan.*' => ['integer'],
            'cari' => ['nullable', 'string', 'max:100'],
            'per_page' => ['nullable', 'integer', 'in:25,50,100'],
        ]);

        $account = DB::table('akun_perkiraan')
            ->where('id_akun_perkiraan', $akun)
            ->where('aktif', true)
            ->first();
        abort_unless($account, 404);

        [$start, $end] = $this->reportPeriod($data);

        $transactionTypeOptions = $this->transactionTypeGroups();
        $userId = auth()->id();

        $savedPenjualan = $this->getSavedSetting('penjualan', ['faktur_penjualan', 'penerimaan_penjualan'], [], $userId);
        $allPenjualanTypes = array_key_exists('semua_tipe_penjualan', $data) ? (bool) $data['semua_tipe_penjualan'] : $savedPenjualan['semua_tipe'];
        $selectedPenjualanTypes = array_key_exists('tipe_penjualan', $data)
            ? collect($allPenjualanTypes ? [] : $data['tipe_penjualan'])->filter(fn ($type) => isset($transactionTypeOptions[$type]))->values()->all()
            : $savedPenjualan['types'];

        $selectedPenjualanTypeCodes = collect($selectedPenjualanTypes)->flatMap(fn ($type) => $transactionTypeOptions[$type]['codes'])->unique()->values()->all();

        $query = DB::table('jurnal_perkiraan as j')
            ->join('impor_jurnal_perkiraan as i', 'i.id_impor_jurnal_perkiraan', '=', 'j.id_impor_jurnal_perkiraan')
            ->where('i.status', 'aktif')
            ->where('j.id_akun_perkiraan', $account->id_akun_perkiraan)
            ->whereBetween('j.tanggal', [$start->toDateString(), $end->toDateString()])
            ->when($selectedPenjualanTypeCodes !== [], fn ($query) => $query->whereIn('j.tipe_transaksi', $selectedPenjualanTypeCodes))
            ->where(function ($q) {
                $q->whereNull('j.deskripsi')
                    ->orWhere(function ($w) {
                        $w->where('j.deskripsi', 'not like', '%tagihan%')
                            ->where('j.deskripsi', 'not like', '%bunga bank%')
                            ->where('j.deskripsi', 'not like', '%biaya adm%')
                            ->where('j.deskripsi', 'not like', '%biaya transportasi%');
                    });
            })
            ->when(trim((string) ($data['cari'] ?? '')), function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('j.nomor_transaksi', 'like', "%{$search}%")
                        ->orWhere('j.tipe_transaksi', 'like', "%{$search}%")
                        ->orWhere('j.deskripsi', 'like', "%{$search}%");
                });
            });

        $totals = (clone $query)->selectRaw('COALESCE(SUM(j.debit),0) debit, COALESCE(SUM(j.kredit),0) kredit')->first();
        $details = $query->select('j.tanggal', 'j.nomor_transaksi', 'j.tipe_transaksi', 'j.deskripsi', 'j.debit', 'j.kredit')
            ->orderBy('j.tanggal')->orderBy('j.nomor_transaksi')->orderBy('j.urutan_detail')
            ->paginate($data['per_page'] ?? 50)->withQueryString();

        $savedPenarikan = $this->getSavedSetting('penarikan', ['faktur_penjualan', 'penerimaan_penjualan'], [], $userId);

        return view('laporan.akhir_bulan_penjualan_detail', [
            'title' => 'Detail Uang Penjualan',
            'account' => $account,
            'start' => $start,
            'end' => $end,
            'details' => $details,
            'debitTotal' => (float) $totals->debit,
            'creditTotal' => (float) $totals->kredit,
            'penjualanTotal' => (float) $totals->debit - (float) $totals->kredit,
            'search' => trim((string) ($data['cari'] ?? '')),
            'transactionTypeOptions' => $transactionTypeOptions,
            'selectedTransactionTypes' => $data['tipe'] ?? $savedPenarikan['types'],
            'allTransactionTypes' => array_key_exists('semua_tipe', $data) ? (bool) $data['semua_tipe'] : $savedPenarikan['semua_tipe'],
            'selectedAccountIds' => $data['akun_filter'] ?? $savedPenarikan['accounts'],
            'selectedPenjualanTypes' => $selectedPenjualanTypes,
            'allPenjualanTypes' => $allPenjualanTypes,
            'selectedPenjualanAccountIds' => collect($data['akun_penjualan'] ?? $savedPenjualan['accounts'])->map(fn ($id) => (int) $id)->values()->all(),
        ]);
    }

    private function reportPeriod(array $data): array
    {
        // Keep older month/year bookmarks working; explicit dates take precedence.
        $legacyStart = Carbon::create((int) ($data['tahun'] ?? now()->year), (int) ($data['bulan'] ?? now()->month), 1)->startOfDay();
        $defaultEnd = $legacyStart->isSameMonth(now()) ? now() : $legacyStart->copy()->endOfMonth();
        $start = Carbon::parse($data['tgl1'] ?? $legacyStart->toDateString())->startOfDay();
        $end = Carbon::parse($data['tgl2'] ?? $defaultEnd->toDateString())->endOfDay();
        if ($end->lt($start)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'tgl2' => 'Sampai tanggal harus sama atau setelah dari tanggal.',
            ]);
        }

        return [$start, $end];
    }

    private function getSavedSetting(string $kategori, array $defaultTypes, array $defaultAccountIds, ?int $userId): array
    {
        $setting = DB::table('laporan_akhir_bulan_setting')
            ->where('kategori', $kategori)
            ->where(function ($q) use ($userId) {
                if ($userId) {
                    $q->where('id_user', $userId)->orWhereNull('id_user');
                } else {
                    $q->whereNull('id_user');
                }
            })
            ->orderByDesc('id_user')
            ->first();

        if (! $setting) {
            return [
                'types' => $defaultTypes,
                'semua_tipe' => false,
                'accounts' => $defaultAccountIds,
            ];
        }

        return [
            'types' => $setting->tipe_transaksi ? json_decode($setting->tipe_transaksi, true) : $defaultTypes,
            'semua_tipe' => (bool) $setting->semua_tipe,
            'accounts' => $setting->akun_ids ? json_decode($setting->akun_ids, true) : $defaultAccountIds,
        ];
    }

    private function saveSetting(string $kategori, array $types, bool $semuaTipe, array $accountIds, ?int $userId): void
    {
        $exists = DB::table('laporan_akhir_bulan_setting')
            ->where('kategori', $kategori)
            ->where('id_user', $userId)
            ->first();

        if ($exists) {
            DB::table('laporan_akhir_bulan_setting')
                ->where('id', $exists->id)
                ->update([
                    'tipe_transaksi' => json_encode($types),
                    'semua_tipe' => $semuaTipe,
                    'akun_ids' => json_encode($accountIds),
                    'updated_at' => now(),
                ]);
        } else {
            DB::table('laporan_akhir_bulan_setting')->insert([
                'id_user' => $userId,
                'kategori' => $kategori,
                'tipe_transaksi' => json_encode($types),
                'semua_tipe' => $semuaTipe,
                'akun_ids' => json_encode($accountIds),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function queryLedgerTable(Carbon $startDate, Carbon $currentCutoff, array $selectedTypeCodes, array $selectedAccountIds)
    {
        // Exclude balance sheet / internal counterpart accounts that are not part of sales/deposit report:
        // Piutang (110301), Persediaan (1104xx), HPP (5101xx), Kas Kecil (110102), Biaya Adm Bank (720002xx)
        $excludedCodes = ['110301', '110401', '110402', '110405', '5101-01', '5101-02', '720002-01', '720002-03', '110102'];

        return DB::table('akun_perkiraan as a')
            ->leftJoin('jurnal_perkiraan as j', function ($join) use ($startDate, $currentCutoff, $selectedTypeCodes) {
                $join->on('j.id_akun_perkiraan', '=', 'a.id_akun_perkiraan')
                    ->whereBetween('j.tanggal', [
                        $startDate->toDateString(),
                        $currentCutoff->toDateString(),
                    ]);
                if ($selectedTypeCodes !== []) {
                    $join->whereIn('j.tipe_transaksi', $selectedTypeCodes);
                }
                $join->where(function ($q) {
                    $q->whereNull('j.deskripsi')
                        ->orWhere(function ($w) {
                            $w->where('j.deskripsi', 'not like', '%tagihan%')
                                ->where('j.deskripsi', 'not like', '%bunga bank%')
                                ->where('j.deskripsi', 'not like', '%biaya adm%')
                                ->where('j.deskripsi', 'not like', '%biaya transportasi%');
                        });
                });
            })
            ->leftJoin('impor_jurnal_perkiraan as i', function ($join) {
                $join->on('i.id_impor_jurnal_perkiraan', '=', 'j.id_impor_jurnal_perkiraan')
                    ->where('i.status', '=', 'aktif');
            })
            ->whereIn('a.id_akun_perkiraan', $selectedAccountIds)
            ->whereNotIn('a.kode_perkiraan', $excludedCodes)
            ->groupBy('a.id_akun_perkiraan', 'a.kode_perkiraan', 'a.nama')
            ->orderBy('a.kode_perkiraan', 'asc')
            ->select('a.id_akun_perkiraan', 'a.kode_perkiraan', 'a.nama')
            ->selectRaw('COALESCE(SUM(CASE WHEN i.id_impor_jurnal_perkiraan IS NOT NULL THEN j.debit ELSE 0 END), 0) as debit')
            ->selectRaw('COALESCE(SUM(CASE WHEN i.id_impor_jurnal_perkiraan IS NOT NULL THEN j.kredit ELSE 0 END), 0) as kredit')
            ->get()
            ->map(function ($row) {
                $row->debit = (float) $row->debit;
                $row->kredit = (float) $row->kredit;
                $row->total = $row->debit - $row->kredit;
                return $row;
            })
            ->filter(function ($row) {
                return $row->debit != 0 || $row->kredit != 0;
            })
            ->values();
    }

    private function balance(int $accountId, Carbon $cutoff): float
    {
        return (float) DB::table('jurnal_perkiraan as j')
            ->join('impor_jurnal_perkiraan as i', 'i.id_impor_jurnal_perkiraan', '=', 'j.id_impor_jurnal_perkiraan')
            ->where('i.status', 'aktif')
            ->where('j.id_akun_perkiraan', $accountId)
            ->whereDate('j.tanggal', '<=', $cutoff->toDateString())
            ->selectRaw('COALESCE(SUM(j.debit - j.kredit), 0) as saldo')
            ->value('saldo');
    }

    private function transactionTypeGroups(): array
    {
        return [
            'faktur_penjualan' => ['label' => 'Faktur Penjualan', 'codes' => [
                'FJ', 'SI', 'Faktur Penjualan',
                'Penjualan Telur', 'Penjualan Ayam', 'Penjualan Umum',
            ]],
            'penerimaan_penjualan' => ['label' => 'Penerimaan Penjualan', 'codes' => [
                'CP', 'KJ', 'KM', 'KN', 'KR', 'MU', 'Penerimaan Penjualan',
                'Pelunasan Piutang Telur', 'Pelunasan Piutang Ayam', 'Pelunasan Piutang Umum',
            ]],
            'transfer_penjualan' => ['label' => 'Transfer / Setoran Penjualan', 'codes' => [
                'BT', 'TB', 'Setoran Kas Penjualan',
            ]],
            'jurnal_umum' => ['label' => 'Jurnal Umum / Saldo Awal', 'codes' => ['JU', 'JV']],
            'lainnya' => ['label' => 'Transaksi Lainnya', 'codes' => ['Lainnya']],
        ];
    }
}
