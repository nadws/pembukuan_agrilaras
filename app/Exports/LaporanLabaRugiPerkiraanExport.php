<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LaporanLabaRugiPerkiraanExport extends DefaultValueBinder implements FromArray, ShouldAutoSize, WithCustomValueBinder, WithHeadings, WithStyles
{
    private array $rows = [];

    private array $sectionRows = [];

    private array $totalRows = [];

    private array $highlightRows = [];

    /** @var array<int,array{depth:int,kode:string,nama:string,childRows:int[],is_income:bool}> */
    private array $parentStack = [];

    /** @var int[] Excel row numbers that are direct children/subtotals of the current section */
    private array $sectionChildRows = [];

    /** @var array<string,int> label => excel row for cross-referencing derived totals */
    private array $totalRowByLabel = [];

    /** @var array<string,bool> description labels already used, to avoid duplicate subtotal labels */
    private array $usedLabels = [];

    /** @var array<int,string> subtotal excel row => its description label */
    private array $subtotalLabels = [];

    private Collection $periods;

    public function __construct(private readonly array $result)
    {
        $this->periods = $result['periods'];
        $this->buildRows();
    }

    public function array(): array
    {
        return $this->rows;
    }

    public function bindValue(Cell $cell, $value): bool
    {
        if ($cell->getColumn() === 'A' && $cell->getRow() > 1) {
            $cell->setValueExplicit((string) $value, DataType::TYPE_STRING);

            return true;
        }

        return parent::bindValue($cell, $value);
    }

    public function headings(): array
    {
        return array_merge(
            ['Kode Akun', 'Deskripsi'],
            $this->periods->map(fn ($period) => $period->translatedFormat('F Y').' (IDR)')->all(),
            ['Total Aktual (IDR)', 'Budget (IDR)', 'Selisih (IDR)']
        );
    }

    public function styles(Worksheet $sheet): array
    {
        $lastColumn = $sheet->getHighestColumn();
        $sheet->freezePane('C2');
        $sheet->getStyle("A1:{$lastColumn}1")->getFont()->setBold(true);

        foreach ($this->sectionRows as $row) {
            $sheet->getStyle("A{$row}:{$lastColumn}{$row}")->getFont()->setBold(true);
        }
        foreach ($this->totalRows as $row) {
            $style = $sheet->getStyle("A{$row}:{$lastColumn}{$row}");
            $style->getFont()->setBold(true);
            $style->getBorders()->getTop()->setBorderStyle(Border::BORDER_THIN);
            $style->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THIN);
        }
        foreach ($this->highlightRows as $row) {
            $style = $sheet->getStyle("A{$row}:{$lastColumn}{$row}");
            $style->getFont()->setBold(true);
            $style->getBorders()->getTop()->setBorderStyle(Border::BORDER_MEDIUM);
            $style->getBorders()->getBottom()->setBorderStyle(Border::BORDER_DOUBLE);
        }

        return [];
    }

    private function buildRows(): void
    {
        $this->section('PENDAPATAN');
        $this->accounts($this->result['revenueRows']);
        $revRow = $this->sumTotal('Jumlah Pendapatan', true, false);

        $this->section('BIAYA POKOK PENJUALAN');
        $this->accounts($this->result['cogsRows']);
        $cogsRow = $this->sumTotal('Jumlah Biaya Pokok Penjualan', false, false);
        $grossRow = $this->derivedTotal('LABA KOTOR', true, true, $revRow, $cogsRow, '-');

        $this->section('BIAYA OPERASIONAL');
        $this->accounts($this->result['operatingRows']);
        $opsRow = $this->sumTotal('Jumlah Biaya Operasional', false, false);
        $opsIncomeRow = $this->derivedTotal('PENDAPATAN OPERASIONAL', true, true, $grossRow, $opsRow, '-');

        $this->section('PENDAPATAN DAN BIAYA NON OPERASIONAL');
        $this->section('Pendapatan Non Operasional');
        $this->accounts($this->result['otherIncomeRows']);
        $oincRow = $this->sumTotal('Jumlah Pendapatan Non Operasional', true, false);
        $this->section('Biaya Non Operasional');
        $this->accounts($this->result['otherExpenseRows']);
        $oexpRow = $this->sumTotal('Jumlah Biaya Non Operasional', false, false);
        $otherNetRow = $this->derivedTotal('Jumlah Pendapatan dan Biaya Non Operasional', true, false, $oincRow, $oexpRow, '-');
        $beforeDepRow = $this->derivedTotal('LABA/RUGI SEBELUM PENYUSUTAN', true, true, $opsIncomeRow, $otherNetRow, '+');

        $this->section('BIAYA PENYUSUTAN');
        $this->accounts($this->result['depreciationRows']);
        $depRow = $this->sumTotal('Jumlah Biaya Penyusutan', false, false);
        $beforeTaxRow = $this->derivedTotal('LABA/RUGI BERSIH (Sebelum Pajak)', true, true, $beforeDepRow, $depRow, '-');

        $taxRow = null;
        if ($this->result['taxRows']->isNotEmpty()) {
            $this->section('PAJAK PENGHASILAN');
            $this->accounts($this->result['taxRows']);
            $taxRow = $this->sumTotal('Jumlah Pajak Penghasilan', false, false);
        }
        if ($taxRow) {
            $this->derivedTotal('LABA/RUGI BERSIH (Setelah Pajak)', true, true, $beforeTaxRow, $taxRow, '-');
        } else {
            $this->copyTotal('LABA/RUGI BERSIH (Setelah Pajak)', true, true, $beforeTaxRow);
        }
    }

    private function section(string $label): void
    {
        $this->rows[] = array_merge(['', $label], array_fill(0, $this->periods->count() + 3, null));
        $this->sectionRows[] = count($this->rows) + 1;
        // Parent stack should never leak across sections; section titles reset grouping.
        while (! empty($this->parentStack)) {
            $this->closeParent();
        }
        $this->sectionChildRows = [];
    }

    /**
     * Head account (has_children) is written WITHOUT numbers as a header row.
     * Its total is written again BELOW its children as "Jumlah {kode} {nama}" with SUM formulas.
     */
    private function accounts(Collection $accounts): void
    {
        $this->parentStack = [];
        foreach ($accounts as $account) {
            $depth = (int) ($account['depth'] ?? 1);
            while (! empty($this->parentStack) && $this->parentStack[count($this->parentStack) - 1]['depth'] >= $depth) {
                $this->closeParent();
            }

            $hasChildren = (bool) ($account['has_children'] ?? false);
            if ($hasChildren) {
                $excelRow = count($this->rows) + 2;
                $this->rows[] = array_merge([
                    (string) $account['kode'],
                    str_repeat('    ', max(0, $depth - 1)).$account['nama'],
                ], array_fill(0, $this->periods->count() + 3, null));
                $this->sectionRows[] = $excelRow;
                $this->parentStack[] = [
                    'depth' => $depth,
                    'kode' => (string) $account['kode'],
                    'nama' => (string) $account['nama'],
                    'childRows' => [],
                    'is_income' => (bool) ($account['is_income'] ?? true),
                ];
                continue;
            }

            $excelRow = count($this->rows) + 2;
            $values = $this->numericValues($account['values'] ?? []);
            $actualTotal = (float) ($account['total'] ?? 0);
            $budgetTotal = (float) ($account['budget_total'] ?? 0);
            $isIncome = (bool) ($account['is_income'] ?? true);
            $totalCol = $this->columnLetter(3 + $this->periods->count());
            $budgetCol = $this->columnLetter(4 + $this->periods->count());
            $this->rows[] = array_merge([
                (string) $account['kode'],
                str_repeat('    ', max(0, $depth - 1)).$account['nama'],
            ], $values, [
                $actualTotal,
                $budgetTotal,
                $this->varianceFormula($isIncome, $totalCol.$excelRow, $budgetCol.$excelRow),
            ]);

            if (! empty($this->parentStack)) {
                $this->parentStack[count($this->parentStack) - 1]['childRows'][] = $excelRow;
            } else {
                $this->sectionChildRows[] = $excelRow;
            }
        }

        while (! empty($this->parentStack)) {
            $this->closeParent();
        }
    }

    private function closeParent(): void
    {
        $parent = array_pop($this->parentStack);
        if ($parent === null) {
            return;
        }
        $childRows = array_values(array_unique($parent['childRows']));
        $excelRow = count($this->rows) + 2;

        if (empty($childRows)) {
            // No detail children (e.g. filtered out); keep header only, no subtotal.
            return;
        }

        $periodFormulas = [];
        foreach ($this->periods as $index => $period) {
            $col = $this->columnLetter(3 + $index);
            $periodFormulas[] = $this->sumFormula($col, $childRows);
        }
        $firstPeriodCol = $this->columnLetter(3);
        $lastPeriodCol = $this->columnLetter(2 + $this->periods->count());
        $totalCol = $this->columnLetter(3 + $this->periods->count());
        $budgetCol = $this->columnLetter(4 + $this->periods->count());
        $label = 'Jumlah '.$parent['nama'];
        if (isset($this->usedLabels[$label])) {
            $label = 'Jumlah '.$parent['kode'].' '.$parent['nama'];
        }
        $this->usedLabels[$label] = true;
        $this->rows[] = array_merge([
            '',
            $label,
        ], $periodFormulas, [
            "=SUM({$firstPeriodCol}{$excelRow}:{$lastPeriodCol}{$excelRow})",
            $this->sumFormula($budgetCol, $childRows),
            $this->varianceFormula($parent['is_income'], $totalCol.$excelRow, $budgetCol.$excelRow),
        ]);
        $this->totalRows[] = $excelRow;
        $this->subtotalLabels[$excelRow] = $label;

        if (! empty($this->parentStack)) {
            $this->parentStack[count($this->parentStack) - 1]['childRows'][] = $excelRow;
        } else {
            $this->sectionChildRows[] = $excelRow;
        }
    }

    /**
     * Section "Jumlah ..." total: SUM of the section's direct children/subtotals.
     * Falls back to static values when the section has no account rows.
     */
    private function sumTotal(string $label, bool $isIncome, bool $highlight = false): ?int
    {
        $childRows = array_values(array_unique($this->sectionChildRows));
        $excelRow = count($this->rows) + 2;

        if (count($childRows) === 1 && ($this->subtotalLabels[$childRows[0]] ?? null) === $label) {
            // The single group subtotal already carries the section label
            // (e.g. head 6000 "Biaya Operasional"); reuse it instead of
            // emitting a duplicate "Jumlah ..." row.
            $this->registerTotalRow($label, $childRows[0], $highlight);
            $this->sectionChildRows = [];

            return $childRows[0];
        }

        if (! empty($childRows)) {
            $periodFormulas = [];
            foreach ($this->periods as $index => $period) {
                $periodFormulas[] = $this->sumFormula($this->columnLetter(3 + $index), $childRows);
            }
            $firstPeriodCol = $this->columnLetter(3);
            $lastPeriodCol = $this->columnLetter(2 + $this->periods->count());
            $totalCol = $this->columnLetter(3 + $this->periods->count());
            $budgetCol = $this->columnLetter(4 + $this->periods->count());
            $this->rows[] = array_merge(['', $label], $periodFormulas, [
                "=SUM({$firstPeriodCol}{$excelRow}:{$lastPeriodCol}{$excelRow})",
                $this->sumFormula($budgetCol, $childRows),
                $this->varianceFormula($isIncome, $totalCol.$excelRow, $budgetCol.$excelRow),
            ]);
        } else {
            $values = $this->staticTotalValues($label);
            $this->rows[] = array_merge(['', $label], $this->numericValues($values['actual']), [
                (float) $this->sum($values['actual']),
                (float) $this->sum($values['budget']),
                $this->variance($this->sum($values['actual']), $this->sum($values['budget']), $isIncome),
            ]);
        }

        $this->registerTotalRow($label, $excelRow, $highlight);
        $this->sectionChildRows = [];

        return $excelRow;
    }

    /**
     * Derived total from two previously emitted total rows, e.g. LABA KOTOR = Pendapatan - HPP.
     */
    private function derivedTotal(string $label, bool $isIncome, bool $highlight, ?int $leftRow, ?int $rightRow, string $operator): ?int
    {
        $excelRow = count($this->rows) + 2;

        if ($leftRow && $rightRow) {
            $periodFormulas = [];
            foreach ($this->periods as $index => $period) {
                $col = $this->columnLetter(3 + $index);
                $periodFormulas[] = "={$col}{$leftRow}{$operator}{$col}{$rightRow}";
            }
            $firstPeriodCol = $this->columnLetter(3);
            $lastPeriodCol = $this->columnLetter(2 + $this->periods->count());
            $totalCol = $this->columnLetter(3 + $this->periods->count());
            $budgetCol = $this->columnLetter(4 + $this->periods->count());
            $budgetLeft = $this->columnLetter(4 + $this->periods->count()).$leftRow;
            $budgetRight = $this->columnLetter(4 + $this->periods->count()).$rightRow;
            $this->rows[] = array_merge(['', $label], $periodFormulas, [
                "=SUM({$firstPeriodCol}{$excelRow}:{$lastPeriodCol}{$excelRow})",
                "={$budgetLeft}{$operator}{$budgetRight}",
                $this->varianceFormula($isIncome, $totalCol.$excelRow, $budgetCol.$excelRow),
            ]);
        } else {
            $values = $this->staticTotalValues($label);
            $this->rows[] = array_merge(['', $label], $this->numericValues($values['actual']), [
                (float) $this->sum($values['actual']),
                (float) $this->sum($values['budget']),
                $this->variance($this->sum($values['actual']), $this->sum($values['budget']), $isIncome),
            ]);
        }

        $this->registerTotalRow($label, $excelRow, $highlight);

        return $excelRow;
    }

    /** After-tax row when there is no tax section: mirror the before-tax row with formulas. */
    private function copyTotal(string $label, bool $isIncome, bool $highlight, ?int $sourceRow): ?int
    {
        $excelRow = count($this->rows) + 2;

        if ($sourceRow) {
            $periodFormulas = [];
            foreach ($this->periods as $index => $period) {
                $col = $this->columnLetter(3 + $index);
                $periodFormulas[] = "={$col}{$sourceRow}";
            }
            $firstPeriodCol = $this->columnLetter(3);
            $lastPeriodCol = $this->columnLetter(2 + $this->periods->count());
            $totalCol = $this->columnLetter(3 + $this->periods->count());
            $budgetCol = $this->columnLetter(4 + $this->periods->count());
            $this->rows[] = array_merge(['', $label], $periodFormulas, [
                "=SUM({$firstPeriodCol}{$excelRow}:{$lastPeriodCol}{$excelRow})",
                "={$budgetCol}{$sourceRow}",
                $this->varianceFormula($isIncome, $totalCol.$excelRow, $budgetCol.$excelRow),
            ]);
        } else {
            $values = $this->staticTotalValues($label);
            $this->rows[] = array_merge(['', $label], $this->numericValues($values['actual']), [
                (float) $this->sum($values['actual']),
                (float) $this->sum($values['budget']),
                $this->variance($this->sum($values['actual']), $this->sum($values['budget']), $isIncome),
            ]);
        }

        $this->registerTotalRow($label, $excelRow, $highlight);

        return $excelRow;
    }

    private function numericValues(array $values): array
    {
        return $this->periods->map(fn ($period) => (float) ($values[$period->format('Y-m')] ?? 0))->all();
    }

    private function columnLetter(int $index): string
    {
        return \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($index);
    }

    private function sumFormula(string $column, array $rows): string
    {
        $refs = array_map(fn ($row) => "{$column}{$row}", $rows);

        return count($refs) === 1 ? "={$refs[0]}" : '=SUM('.implode(',', $refs).')';
    }

    private function varianceFormula(bool $isIncome, string $totalRef, string $budgetRef): string
    {
        return $isIncome ? "={$totalRef}-{$budgetRef}" : "={$budgetRef}-{$totalRef}";
    }

    private function registerTotalRow(string $label, int $excelRow, bool $highlight): void
    {
        $this->totalRowByLabel[$label] = $excelRow;
        $this->usedLabels[$label] = true;
        if ($highlight) {
            $this->highlightRows[] = $excelRow;
        } else {
            $this->totalRows[] = $excelRow;
        }
    }

    /** Static fallback values keyed by export label (used when a section has no rows). */
    private function staticTotalValues(string $label): array
    {
        $map = [
            'Jumlah Pendapatan' => ['revenue', 'revenueBudget'],
            'Jumlah Biaya Pokok Penjualan' => ['cogs', 'cogsBudget'],
            'LABA KOTOR' => ['gross', 'grossBudget'],
            'Jumlah Biaya Operasional' => ['operating', 'operatingBudget'],
            'PENDAPATAN OPERASIONAL' => ['operatingIncome', 'operatingIncomeBudget'],
            'Jumlah Pendapatan Non Operasional' => ['otherIncome', 'otherIncomeBudget'],
            'Jumlah Biaya Non Operasional' => ['otherExpense', 'otherExpenseBudget'],
            'Jumlah Pendapatan dan Biaya Non Operasional' => ['otherNet', 'otherNetBudget'],
            'LABA/RUGI SEBELUM PENYUSUTAN' => ['beforeDepreciation', 'beforeDepreciationBudget'],
            'Jumlah Biaya Penyusutan' => ['depreciationTotal', 'depreciationBudget'],
            'LABA/RUGI BERSIH (Sebelum Pajak)' => ['beforeTax', 'beforeTaxBudget'],
            'Jumlah Pajak Penghasilan' => ['taxTotal', 'taxBudget'],
            'LABA/RUGI BERSIH (Setelah Pajak)' => ['afterTax', 'afterTaxBudget'],
        ];
        $keys = $map[$label] ?? null;
        $zero = [];
        foreach ($this->periods as $period) {
            $zero[$period->format('Y-m')] = '0.000000000000';
        }

        if (! $keys) {
            return ['actual' => $zero, 'budget' => $zero];
        }

        return [
            'actual' => $this->result[$keys[0]] ?? $zero,
            'budget' => $this->result[$keys[1]] ?? $zero,
        ];
    }

    private function sum(array $values): string
    {
        return array_reduce($values, fn ($carry, $value) => bcadd($carry, $value, 12), '0.000000000000');
    }

    private function variance(string $actual, string $budget, bool $isIncome): float
    {
        return (float) ($isIncome ? bcsub($actual, $budget, 12) : bcsub($budget, $actual, 12));
    }

}
