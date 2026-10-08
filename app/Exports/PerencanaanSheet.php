<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PerencanaanSheet extends DefaultValueBinder implements FromArray, WithCustomValueBinder, WithHeadings, WithStyles, WithStrictNullComparison, WithTitle
{
    public function __construct(private string $name, private array $columns, private array $rows, private string $scope, private string $note)
    {
    }

    public function title(): string
    {
        return $this->name;
    }

    public function headings(): array
    {
        return [[$this->name.' — Laporan Perencanaan'], [$this->scope], [$this->note], [''], $this->columns];
    }

    public function array(): array
    {
        return $this->rows;
    }

    public function bindValue(Cell $cell, $value): bool
    {
        if ($cell->getRow() > 5 && $cell->getColumn() === 'A' && $this->columns[0] === 'Tanggal' && is_string($value) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            $cell->setValueExplicit(Date::PHPToExcel(new \DateTimeImmutable($value)), DataType::TYPE_NUMERIC);
            $cell->getStyle()->getNumberFormat()->setFormatCode('dd mmm yyyy');

            return true;
        }
        // Database text stays literal, including leading zeroes and formula-like notes.
        if (is_string($value)) {
            $cell->setValueExplicit($value, DataType::TYPE_STRING);

            return true;
        }
        if (is_int($value) || is_float($value)) {
            $cell->getStyle()->getNumberFormat()->setFormatCode('#,##0.00####');
        }

        return parent::bindValue($cell, $value);
    }

    public function styles(Worksheet $sheet): array
    {
        $last = $sheet->getHighestColumn();
        $end = max(5, $sheet->getHighestRow());
        $sheet->freezePane('C6');
        $sheet->setAutoFilter("A5:{$last}{$end}");
        foreach ([1, 2, 3] as $row) {
        $sheet->mergeCells("A{$row}:{$last}{$row}");
        }
        $sheet->getRowDimension(1)->setRowHeight(30);
        $sheet->getRowDimension(2)->setRowHeight(24);
        $sheet->getRowDimension(3)->setRowHeight(48);
        $sheet->getRowDimension(5)->setRowHeight(36);
        $sheet->getStyle("A1:{$last}{$end}")->getAlignment()->setWrapText(true)->setVertical('center');
        foreach ($this->columns as $index => $label) {
            $column = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($index + 1);
            $width = match (true) {
                str_contains($label, 'Keterangan') => 38,
                str_contains($label, 'Nama') || $label === 'Produk' => 30,
                str_contains($label, 'Tanggal') => 17,
                str_contains($label, 'Satuan') => 13,
                default => 22,
            };
            $sheet->getColumnDimension($column)->setWidth($width);
            if (str_contains($label, '(Rp)')) {
            $sheet->getStyle("{$column}6:{$column}".($end + 1))->getNumberFormat()->setFormatCode('#,##0.00');
            }
        }
        $previous = null;
        foreach ($this->rows as $index => $values) {
            $row = $index + 6;
            $sheet->getRowDimension($row)->setRowHeight(32);
            if ($index % 2 === 0) {
            $sheet->getStyle("A{$row}:{$last}{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F0F5F1');
            }
            $group = $this->name === 'Mutasi Stok' || $this->name === 'Saldo Produk' ? $values[$this->name === 'Saldo Produk' ? 0 : 1] : $values[0].'|'.$values[1];
            if ($group !== $previous) {
            $sheet->getStyle("A{$row}:{$last}{$row}")->getBorders()->getTop()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('A5BAAC');
            }
            $previous = $group;
            foreach ($values as $columnIndex => $value) {
                if (is_string($value) && ($value === 'Cocok' || $value === 'Jurnal seimbang' || str_starts_with($value, 'Periksa:') || $value === 'Jurnal belum ada')) {
                    $cell = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($columnIndex + 1).$row;
                    $sheet->getStyle($cell)->getFont()->setBold(true)->getColor()->setRGB($value === 'Cocok' || $value === 'Jurnal seimbang' ? '24633B' : 'B42318');
                }
            }
        }
        $totals = [];
        foreach ($this->columns as $index => $label) {
            if (str_contains($label, '(Rp)')) {
            $totals[$index] = array_sum(array_column($this->rows, $index));
            }
        }
        if ($this->rows && $totals) {
            $footer = $end + 1;
            $sheet->setCellValue('A'.$footer, 'TOTAL');
            foreach ($totals as $index => $total) {
            $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($index + 1).$footer, $total);
            }
            $sheet->getStyle("A{$footer}:{$last}{$footer}")->getFont()->setBold(true);
            $sheet->getStyle("A{$footer}:{$last}{$footer}")->getBorders()->getTop()->setBorderStyle(Border::BORDER_DOUBLE);
        }
        $sheet->getPageSetup()->setOrientation('landscape')->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A3)->setFitToWidth(1)->setFitToHeight(0)->setRowsToRepeatAtTopByStartAndEnd(1, 5);
        $sheet->getPageSetup()->setPrintArea("A1:{$last}".$sheet->getHighestRow());

        return [1 => ['font' => ['bold' => true, 'size' => 18, 'color' => ['rgb' => '315A47']]],
            2 => ['font' => ['bold' => true, 'size' => 11]],
            3 => ['font' => ['size' => 10, 'color' => ['rgb' => '555555']]],
            5 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '315A47']]]];
    }
}
