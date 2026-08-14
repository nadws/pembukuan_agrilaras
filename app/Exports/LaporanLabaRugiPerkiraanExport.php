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
            ['Total (IDR)']
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
        $this->total('Jumlah Pendapatan', $this->result['revenue']);

        $this->section('BIAYA POKOK PENJUALAN');
        $this->accounts($this->result['cogsRows']);
        $this->total('Jumlah Biaya Pokok Penjualan', $this->result['cogs']);
        $this->total('LABA KOTOR', $this->result['gross'], true);

        $this->section('BIAYA OPERASIONAL');
        $this->accounts($this->result['operatingRows']);
        $this->total('Jumlah Biaya Operasional', $this->result['operating']);
        $this->total('PENDAPATAN OPERASIONAL', $this->result['operatingIncome'], true);

        $this->section('PENDAPATAN DAN BIAYA NON OPERASIONAL');
        $this->section('Pendapatan Non Operasional');
        $this->accounts($this->result['otherIncomeRows']);
        $this->total('Jumlah Pendapatan Non Operasional', $this->result['otherIncome']);
        $this->section('Biaya Non Operasional');
        $this->accounts($this->result['otherExpenseRows']);
        $this->total('Jumlah Biaya Non Operasional', $this->result['otherExpense']);
        $this->total('Jumlah Pendapatan dan Biaya Non Operasional', $this->result['otherNet']);
        $this->total('LABA/RUGI SEBELUM PENYUSUTAN', $this->result['beforeDepreciation'], true);

        $this->section('BIAYA PENYUSUTAN');
        $this->accounts($this->result['depreciationRows']);
        $this->total('Jumlah Biaya Penyusutan', $this->result['depreciationTotal']);
        $this->total('LABA/RUGI BERSIH (Sebelum Pajak)', $this->result['beforeTax'], true);

        if ($this->result['taxRows']->isNotEmpty()) {
            $this->section('PAJAK PENGHASILAN');
            $this->accounts($this->result['taxRows']);
            $this->total('Jumlah Pajak Penghasilan', $this->result['taxTotal']);
        }
        $this->total('LABA/RUGI BERSIH (Setelah Pajak)', $this->result['afterTax'], true);
    }

    private function section(string $label): void
    {
        $this->rows[] = array_merge(['', $label], array_fill(0, $this->periods->count() + 1, null));
        $this->sectionRows[] = count($this->rows) + 1;
    }

    private function accounts(Collection $accounts): void
    {
        foreach ($accounts as $account) {
            $values = $this->numericValues($account['values']);
            $this->rows[] = array_merge([
                (string) $account['kode'],
                str_repeat('    ', max(0, $account['depth'] - 1)).$account['nama'],
            ], $values, [(float) $account['total']]);
        }
    }

    private function total(string $label, array $values, bool $highlight = false): void
    {
        $this->rows[] = array_merge(['', $label], $this->numericValues($values), [(float) $this->sum($values)]);
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
}
