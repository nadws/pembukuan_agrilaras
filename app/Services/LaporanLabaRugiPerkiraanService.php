<?php

namespace App\Services;

use App\Models\AkunPerkiraan;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class LaporanLabaRugiPerkiraanService
{
    private Collection $accounts;

    private array $raw = [];

    private array $budgetRaw = [];

    private array $periodKeys = [];

    public function buat(Carbon $awal, Carbon $akhir): array
    {
        $this->raw = [];
        $this->budgetRaw = [];
        $periods = collect(CarbonPeriod::create($awal->copy()->startOfMonth(), '1 month', $akhir->copy()->startOfMonth()))
            ->map(fn ($date) => Carbon::instance($date)->startOfMonth());
        $this->periodKeys = $periods->map->format('Y-m')->all();
        $this->accounts = AkunPerkiraan::query()
            ->whereIn('tipe_akun', ['REVE', 'COGS', 'EXPS', 'OINC', 'OEXP'])
            ->where('aktif', true)
            ->orderBy('kode_perkiraan')
            ->get()
            ->keyBy('id_akun_perkiraan');

        DB::table('budget_laba_rugi')
            ->whereRaw('(tahun * 100 + bulan) BETWEEN ? AND ?', [
                (int) $awal->format('Ym'), (int) $akhir->format('Ym'),
            ])
            ->whereIn('id_akun_perkiraan', $this->accounts->keys())
            ->get(['id_akun_perkiraan', 'tahun', 'bulan', 'nominal'])
            ->each(function ($item) {
                $period = sprintf('%04d-%02d', $item->tahun, $item->bulan);
                $this->budgetRaw[$item->id_akun_perkiraan][$period] = (string) $item->nominal;
            });

        DB::table('jurnal_perkiraan as j')
            ->join('impor_jurnal_perkiraan as i', 'i.id_impor_jurnal_perkiraan', 'j.id_impor_jurnal_perkiraan')
            ->where('i.status', 'aktif')
            ->whereBetween('j.tanggal', [$awal->copy()->startOfMonth()->toDateString(), $akhir->copy()->endOfMonth()->toDateString()])
            ->whereIn('j.id_akun_perkiraan', $this->accounts->keys())
            ->groupBy('j.id_akun_perkiraan', 'j.tanggal')
            ->select('j.id_akun_perkiraan', 'j.tanggal')
            ->selectRaw('SUM(j.debit) as debit, SUM(j.kredit) as kredit')
            ->get()
            ->each(function ($item) {
                $account = $this->accounts->get($item->id_akun_perkiraan);
                $period = substr((string) $item->tanggal, 0, 7);
                $income = in_array($account->tipe_akun, ['REVE', 'OINC'], true);
                $value = $income
                    ? bcsub((string) $item->kredit, (string) $item->debit, 12)
                    : bcsub((string) $item->debit, (string) $item->kredit, 12);
                $this->raw[$account->getKey()][$period] = bcadd(
                    $this->raw[$account->getKey()][$period] ?? '0.000000000000',
                    $value,
                    12
                );
            });

        $depreciation = $this->accounts->firstWhere('kode_perkiraan', '600040');
        $tax = $this->accounts->firstWhere('kode_perkiraan', '7301');
        $depreciationIds = $depreciation ? $this->subtreeIds($depreciation->getKey()) : [];
        $taxIds = $tax ? $this->subtreeIds($tax->getKey()) : [];

        $revenueRows = $this->rowsForType('REVE');
        $cogsRows = $this->rowsForType('COGS');
        $operatingRows = $this->rowsForType('EXPS', $depreciationIds);
        $otherIncomeRows = $this->rowsForType('OINC');
        $otherExpenseRows = $this->rowsForType('OEXP', $taxIds);
        $depreciationRows = $depreciation
            ? $this->children($depreciation->getKey())->flatMap(fn ($child) => $this->renderNode($child, 2))->values()
            : collect();
        $taxRows = $tax ? collect($this->renderNode($tax, 1)) : collect();

        $revenue = $this->sumAccountIds($this->idsForType('REVE'));
        $cogs = $this->sumAccountIds($this->idsForType('COGS'));
        $operating = $this->sumAccountIds(array_diff($this->idsForType('EXPS'), $depreciationIds));
        $otherIncome = $this->sumAccountIds($this->idsForType('OINC'));
        $otherExpense = $this->sumAccountIds(array_diff($this->idsForType('OEXP'), $taxIds));
        $depreciationTotal = $this->sumAccountIds($depreciationIds);
        $taxTotal = $this->sumAccountIds($taxIds);
        $gross = $this->subtract($revenue, $cogs);
        $operatingIncome = $this->subtract($gross, $operating);
        $otherNet = $this->subtract($otherIncome, $otherExpense);
        $beforeDepreciation = $this->add($operatingIncome, $otherNet);
        $beforeTax = $this->subtract($beforeDepreciation, $depreciationTotal);
        $afterTax = $this->subtract($beforeTax, $taxTotal);

        $revenueBudget = $this->sumBudgetAccountIds($this->idsForType('REVE'));
        $cogsBudget = $this->sumBudgetAccountIds($this->idsForType('COGS'));
        $operatingBudget = $this->sumBudgetAccountIds(array_diff($this->idsForType('EXPS'), $depreciationIds));
        $otherIncomeBudget = $this->sumBudgetAccountIds($this->idsForType('OINC'));
        $otherExpenseBudget = $this->sumBudgetAccountIds(array_diff($this->idsForType('OEXP'), $taxIds));
        $depreciationBudget = $this->sumBudgetAccountIds($depreciationIds);
        $taxBudget = $this->sumBudgetAccountIds($taxIds);
        $grossBudget = $this->subtract($revenueBudget, $cogsBudget);
        $operatingIncomeBudget = $this->subtract($grossBudget, $operatingBudget);
        $otherNetBudget = $this->subtract($otherIncomeBudget, $otherExpenseBudget);
        $beforeDepreciationBudget = $this->add($operatingIncomeBudget, $otherNetBudget);
        $beforeTaxBudget = $this->subtract($beforeDepreciationBudget, $depreciationBudget);
        $afterTaxBudget = $this->subtract($beforeTaxBudget, $taxBudget);

        return compact(
            'periods', 'revenueRows', 'cogsRows', 'operatingRows', 'otherIncomeRows', 'otherExpenseRows',
            'depreciationRows', 'taxRows', 'revenue', 'cogs', 'gross', 'operating', 'operatingIncome',
            'otherIncome', 'otherExpense', 'otherNet', 'beforeDepreciation', 'depreciationTotal',
            'beforeTax', 'taxTotal', 'afterTax', 'revenueBudget', 'cogsBudget', 'grossBudget',
            'operatingBudget', 'operatingIncomeBudget', 'otherIncomeBudget', 'otherExpenseBudget',
            'otherNetBudget', 'beforeDepreciationBudget', 'depreciationBudget', 'beforeTaxBudget',
            'taxBudget', 'afterTaxBudget'
        );
    }

    private function rowsForType(string $type, array $skipIds = []): Collection
    {
        $roots = $this->accounts->where('tipe_akun', $type)->filter(function ($account) use ($type) {
            $parent = $this->accounts->get($account->id_akun_induk);

            return ! $parent || $parent->tipe_akun !== $type;
        });

        return $roots->flatMap(fn ($root) => $this->renderNode($root, 1, $skipIds))->values();
    }

    private function renderNode(AkunPerkiraan $account, int $depth, array $skipIds = []): array
    {
        if (in_array($account->getKey(), $skipIds, true)) {
        return [];
        }

        $values = $this->aggregateNode($account->getKey());
        $children = $this->children($account->getKey())
            ->flatMap(fn ($child) => $this->renderNode($child, $depth + 1, $skipIds))->all();

        if (! $this->hasValue($values) && ! $children) {
        return [];
        }

        return array_merge([[
            'id' => $account->getKey(),
            'kode' => $account->kode_perkiraan,
            'nama' => $account->nama,
            'depth' => $depth,
            'has_children' => count($children) > 0,
            'values' => $values,
            'total' => $this->total($values),
            'budget_total' => $this->total($this->aggregateBudgetNode($account->getKey())),
            'is_income' => in_array($account->tipe_akun, ['REVE', 'OINC'], true),
        ]], $children);
    }

    private function aggregateNode(int $id): array
    {
        $ids = $this->subtreeIds($id);

        return $this->sumAccountIds($ids);
    }

    private function sumAccountIds(array $ids): array
    {
        $result = array_fill_keys($this->periodKeys, '0.000000000000');
        foreach ($ids as $id) {
            foreach ($this->periodKeys as $period) {
                $result[$period] = bcadd($result[$period], $this->raw[$id][$period] ?? '0.000000000000', 12);
            }
        }

        return $result;
    }

    private function aggregateBudgetNode(int $id): array
    {
        return $this->sumBudgetAccountIds($this->subtreeIds($id));
    }

    private function sumBudgetAccountIds(array $ids): array
    {
        $result = array_fill_keys($this->periodKeys, '0.000000000000');
        foreach ($ids as $id) {
            foreach ($this->periodKeys as $period) {
                $result[$period] = bcadd($result[$period], $this->budgetRaw[$id][$period] ?? '0.000000000000', 12);
            }
        }

        return $result;
    }

    private function subtreeIds(int $id): array
    {
        $ids = [$id];
        foreach ($this->children($id) as $child) {
            $ids = array_merge($ids, $this->subtreeIds($child->getKey()));
        }

        return $ids;
    }

    private function children(int $id): Collection
    {
        return $this->accounts->where('id_akun_induk', $id)->sortBy('kode_perkiraan');
    }

    private function idsForType(string $type): array
    {
        return $this->accounts->where('tipe_akun', $type)->keys()->map(fn ($id) => (int) $id)->all();
    }

    private function add(array $left, array $right): array
    {
        return collect($this->periodKeys)->mapWithKeys(fn ($period) => [
            $period => bcadd($left[$period], $right[$period], 12),
        ])->all();
    }

    private function subtract(array $left, array $right): array
    {
        return collect($this->periodKeys)->mapWithKeys(fn ($period) => [
            $period => bcsub($left[$period], $right[$period], 12),
        ])->all();
    }

    private function total(array $values): string
    {
        return array_reduce($values, fn ($carry, $value) => bcadd($carry, $value, 12), '0.000000000000');
    }

    private function hasValue(array $values): bool
    {
        return collect($values)->contains(fn ($value) => bccomp($value, '0.000000000000', 12) !== 0);
    }
}
