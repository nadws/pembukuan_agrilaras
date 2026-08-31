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
        $this->total('Jumlah Pendapatan', $this->result['revenue'], $this->result['revenueBudget'], true);

        $this->section('BIAYA POKOK PENJUALAN');
        $this->accounts($this->result['cogsRows']);
        $this->total('Jumlah Biaya Pokok Penjualan', $this->result['cogs'], $this->result['cogsBudget'], false);
        $this->total('LABA KOTOR', $this->result['gross'], $this->result['grossBudget'], true, true);

        $this->section('BIAYA OPERASIONAL');
        $this->accounts($this->result['operatingRows']);
        $this->total('Jumlah Biaya Operasional', $this->result['operating'], $this->result['operatingBudget'], false);
        $this->total('PENDAPATAN OPERASIONAL', $this->result['operatingIncome'], $this->result['operatingIncomeBudget'], true, true);

        $this->section('PENDAPATAN DAN BIAYA NON OPERASIONAL');
        $this->section('Pendapatan Non Operasional');
        $this->accounts($this->result['otherIncomeRows']);
        $this->total('Jumlah Pendapatan Non Operasional', $this->result['otherIncome'], $this->result['otherIncomeBudget'], true);
        $this->section('Biaya Non Operasional');
        $this->accounts($this->result['otherExpenseRows']);
        $this->total('Jumlah Biaya Non Operasional', $this->result['otherExpense'], $this->result['otherExpenseBudget'], false);
        $this->total('Jumlah Pendapatan dan Biaya Non Operasional', $this->result['otherNet'], $this->result['otherNetBudget'], true);
        $this->total('LABA/RUGI SEBELUM PENYUSUTAN', $this->result['beforeDepreciation'], $this->result['beforeDepreciationBudget'], true, true);

        $this->section('BIAYA PENYUSUTAN');
        $this->accounts($this->result['depreciationRows']);
        $this->total('Jumlah Biaya Penyusutan', $this->result['depreciationTotal'], $this->result['depreciationBudget'], false);
        $this->total('LABA/RUGI BERSIH (Sebelum Pajak)', $this->result['beforeTax'], $this->result['beforeTaxBudget'], true, true);

        if ($this->result['taxRows']->isNotEmpty()) {
            $this->section('PAJAK PENGHASILAN');
            $this->accounts($this->result['taxRows']);
            $this->total('Jumlah Pajak Penghasilan', $this->result['taxTotal'], $this->result['taxBudget'], false);
        }
        $this->total('LABA/RUGI BERSIH (Setelah Pajak)', $this->result['afterTax'], $this->result['afterTaxBudget'], true, true);
    }

    private function section(string $label): void
    {
        $this->rows[] = array_merge(['', $label], array_fill(0, $this->periods->count() + 3, null));
        $this->sectionRows[] = count($this->rows) + 1;
    }

    private function accounts(Collection $accounts): void
    {
        foreach ($accounts as $account) {
            $values = $this->numericValues($account['values']);
            $this->rows[] = array_merge([
                (string) $account['kode'],
                str_repeat('    ', max(0, $account['depth'] - 1)).$account['nama'],
            ], $values, [
                (float) $account['total'],
                (float) $account['budget_total'],
                $this->variance($account['total'], $account['budget_total'], $account['is_income']),
            ]);
        }
    }

    private function total(string $label, array $values, array $budget, bool $isIncome, bool $highlight = false): void
    {
        $actualTotal = $this->sum($values);
        $budgetTotal = $this->sum($budget);
        $this->rows[] = array_merge(['', $label], $this->numericValues($values), [
            (float) $actualTotal,
            (float) $budgetTotal,
            $this->variance($actualTotal, $budgetTotal, $isIncome),
        ]);
        $excelRow = count($this->rows) + 1;
        if ($highlight) {
            $this->highlightRows[] = $excelRow;
        } else {
            $this->totalRows[] = $excelRow;
        }
    }

    private function numericValues(array $values): array
    {
        return $this->periods->map(fn ($period) => (float) $values[$period->format('Y-m')])->all();
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
