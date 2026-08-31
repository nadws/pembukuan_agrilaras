<?php

namespace App\Services;

use App\Models\AkunPerkiraan;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class LaporanNeracaPerkiraanService
{
    private const DEBIT_NORMAL = ['BANK', 'AREC', 'INTR', 'OCAS', 'Aset Lancar Lainnya', 'FASS'];

    private const CREDIT_NORMAL = ['DEPR', 'APAY', 'OCLY', 'LTLY', 'EQTY'];

    /**
     * Some imported account masters use the literal Indonesian label instead
     * of the normalized OCAS type. Treat both as other current assets so the
     * balance sheet includes every account in that section.
     */
    private const OTHER_CURRENT_TYPES = ['OCAS', 'Aset Lancar Lainnya'];

    private Collection $accounts;

    private array $raw = [];

    public function buat(Carbon $tanggal): array
    {
        $balanceTypes = array_merge(self::DEBIT_NORMAL, self::OTHER_CURRENT_TYPES, self::CREDIT_NORMAL);
        $this->accounts = AkunPerkiraan::query()
            ->whereIn('tipe_akun', $balanceTypes)
            ->where('aktif', true)
            ->orderBy('kode_perkiraan')
            ->get()
            ->keyBy('id_akun_perkiraan');

        DB::table('jurnal_perkiraan as j')
            ->join('impor_jurnal_perkiraan as i', 'i.id_impor_jurnal_perkiraan', '=', 'j.id_impor_jurnal_perkiraan')
            ->where('i.status', 'aktif')
            ->whereDate('j.tanggal', '<=', $tanggal->toDateString())
            ->whereIn('j.id_akun_perkiraan', $this->accounts->keys())
            ->groupBy('j.id_akun_perkiraan')
            ->select('j.id_akun_perkiraan')
            ->selectRaw('SUM(j.debit) as debit, SUM(j.kredit) as kredit')
            ->get()
            ->each(function ($item) {
                $account = $this->accounts->get($item->id_akun_perkiraan);
                $this->raw[$account->getKey()] = in_array($account->tipe_akun, self::DEBIT_NORMAL, true)
                    ? bcsub((string) $item->debit, (string) $item->kredit, 12)
                    : bcsub((string) $item->kredit, (string) $item->debit, 12);
            });

        $cashRows = $this->rowsForTypes(['BANK']);
        $receivableRows = $this->rowsForTypes(['AREC']);
        $inventoryRows = $this->rowsForTypes(['INTR']);
        $otherCurrentRows = $this->rowsForTypes(self::OTHER_CURRENT_TYPES);
        $fixedAssetRows = $this->rowsForTypes(['FASS']);
        $depreciationRows = $this->rowsForTypes(['DEPR']);
        $payableRows = $this->rowsForTypes(['APAY']);
        $otherCurrentLiabilityRows = $this->rowsForTypes(['OCLY']);
        $longTermLiabilityRows = $this->rowsForTypes(['LTLY']);
        $equityRows = $this->rowsForTypes(['EQTY']);

        $cash = $this->sumTypes(['BANK']);
        $receivable = $this->sumTypes(['AREC']);
        $inventory = $this->sumTypes(['INTR']);
        $otherCurrent = $this->sumTypes(self::OTHER_CURRENT_TYPES);
        $currentAssets = $this->add($cash, $receivable, $inventory, $otherCurrent);
        $fixedAssets = $this->sumTypes(['FASS']);
        $accumulatedDepreciation = $this->sumTypes(['DEPR']);
        $netFixedAssets = bcsub($fixedAssets, $accumulatedDepreciation, 12);
        $totalAssets = bcadd($currentAssets, $netFixedAssets, 12);

        $payable = $this->sumTypes(['APAY']);
        $otherCurrentLiability = $this->sumTypes(['OCLY']);
        $currentLiabilities = bcadd($payable, $otherCurrentLiability, 12);
        $longTermLiabilities = $this->sumTypes(['LTLY']);
        $totalLiabilities = bcadd($currentLiabilities, $longTermLiabilities, 12);
        $baseEquity = $this->sumTypes(['EQTY']);
        $currentProfit = $this->currentProfit($tanggal);
        $totalEquity = bcadd($baseEquity, $currentProfit, 12);
        $liabilitiesAndEquity = bcadd($totalLiabilities, $totalEquity, 12);
        $difference = bcsub($totalAssets, $liabilitiesAndEquity, 12);

        $firstJournalDate = DB::table('jurnal_perkiraan as j')
            ->join('impor_jurnal_perkiraan as i', 'i.id_impor_jurnal_perkiraan', '=', 'j.id_impor_jurnal_perkiraan')
            ->where('i.status', 'aktif')
            ->whereDate('j.tanggal', '<=', $tanggal->toDateString())
            ->min('j.tanggal');

        return compact(
            'cashRows', 'receivableRows', 'inventoryRows', 'otherCurrentRows', 'fixedAssetRows',
            'depreciationRows', 'payableRows', 'otherCurrentLiabilityRows', 'longTermLiabilityRows',
            'equityRows', 'cash', 'receivable', 'inventory', 'otherCurrent', 'currentAssets',
            'fixedAssets', 'accumulatedDepreciation', 'netFixedAssets', 'totalAssets', 'payable',
            'otherCurrentLiability', 'currentLiabilities', 'longTermLiabilities', 'totalLiabilities',
            'baseEquity', 'currentProfit', 'totalEquity', 'liabilitiesAndEquity', 'difference',
            'firstJournalDate'
        );
    }

    private function rowsForTypes(array $types): Collection
    {
        $typeAccounts = $this->accounts->whereIn('tipe_akun', $types);
        $roots = $typeAccounts->filter(function ($account) use ($typeAccounts) {
            return ! $account->id_akun_induk || ! $typeAccounts->has($account->id_akun_induk);
        });

        return $roots->flatMap(fn ($root) => $this->renderNode($root, 0, $typeAccounts))->values();
    }

    private function renderNode(AkunPerkiraan $account, int $depth, Collection $typeAccounts): array
    {
        $children = $typeAccounts->where('id_akun_induk', $account->getKey())->sortBy('kode_perkiraan');
        $value = $this->aggregateNode($account->getKey(), $typeAccounts);
        $childRows = $children->flatMap(fn ($child) => $this->renderNode($child, $depth + 1, $typeAccounts))->all();

        if (bccomp($value, '0.000000000000', 12) === 0 && ! $childRows) {
            return [];
        }

        return array_merge([[
            'id' => $account->getKey(),
            'kode' => $account->kode_perkiraan,
            'nama' => $account->nama,
            'depth' => $depth,
            'has_children' => $children->isNotEmpty(),
            'value' => $value,
        ]], $childRows);
    }

    private function aggregateNode(int $id, Collection $typeAccounts): string
    {
        $total = $this->raw[$id] ?? '0.000000000000';
        foreach ($typeAccounts->where('id_akun_induk', $id) as $child) {
            $total = bcadd($total, $this->aggregateNode($child->getKey(), $typeAccounts), 12);
        }

        return $total;
    }

    private function sumTypes(array $types): string
    {
        return $this->accounts->whereIn('tipe_akun', $types)->keys()->reduce(
            fn ($total, $id) => bcadd($total, $this->raw[$id] ?? '0.000000000000', 12),
            '0.000000000000'
        );
    }

    private function currentProfit(Carbon $tanggal): string
    {
        $totals = DB::table('jurnal_perkiraan as j')
            ->join('impor_jurnal_perkiraan as i', 'i.id_impor_jurnal_perkiraan', '=', 'j.id_impor_jurnal_perkiraan')
            ->join('akun_perkiraan as a', 'a.id_akun_perkiraan', '=', 'j.id_akun_perkiraan')
            ->where('i.status', 'aktif')
            ->where('a.aktif', true)
            ->whereDate('j.tanggal', '<=', $tanggal->toDateString())
            ->whereIn('a.tipe_akun', ['REVE', 'COGS', 'EXPS', 'OINC', 'OEXP'])
            ->groupBy('a.tipe_akun')
            ->select('a.tipe_akun')
            ->selectRaw('SUM(j.debit) as debit, SUM(j.kredit) as kredit')
            ->get()
            ->keyBy('tipe_akun');

        $income = '0.000000000000';
        foreach (['REVE', 'OINC'] as $type) {
            $income = bcadd($income, bcsub((string) ($totals[$type]->kredit ?? 0), (string) ($totals[$type]->debit ?? 0), 12), 12);
        }
        $expense = '0.000000000000';
        foreach (['COGS', 'EXPS', 'OEXP'] as $type) {
            $expense = bcadd($expense, bcsub((string) ($totals[$type]->debit ?? 0), (string) ($totals[$type]->kredit ?? 0), 12), 12);
        }

        return bcsub($income, $expense, 12);
    }

    private function add(string ...$values): string
    {
        return array_reduce($values, fn ($total, $value) => bcadd($total, $value, 12), '0.000000000000');
    }
}
