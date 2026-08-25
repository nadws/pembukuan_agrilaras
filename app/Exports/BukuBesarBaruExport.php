<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BukuBesarBaruExport extends DefaultValueBinder implements FromCollection, WithHeadings, WithMapping, WithColumnFormatting, ShouldAutoSize, WithStyles, WithCustomValueBinder, WithEvents
{
    private int $rowNumber = 0;

    public function __construct(
        private readonly Collection $data,
        private readonly string $tgl1,
        private readonly string $tgl2
    ) {
    }

    public function collection(): Collection
    {
        return $this->data;
    }

    public function headings(): array
    {
        return [
            ['BUKU BESAR'],
            ['Periode: ' . date('d/m/Y', strtotime($this->tgl1)) . ' s/d ' . date('d/m/Y', strtotime($this->tgl2))],
            [],
            ['No', 'Kode Akun', 'Nama Akun', 'Tipe Akun', 'Debit (Rp)', 'Kredit (Rp)', 'Saldo (Rp)']
        ];
    }

    public function map($row): array
    {
        $this->rowNumber++;

        return [
            $this->rowNumber,
            (string) $row->kode_perkiraan,
            $row->nama,
            $row->tipe_akun,
            (float) $row->debit,
            (float) $row->kredit,
            (float) $row->saldo,
        ];
    }

    public function bindValue(Cell $cell, $value): bool
    {
        if ($cell->getColumn() === 'B' && $cell->getRow() >= 5) {
            $cell->setValueExplicit((string) $value, DataType::TYPE_STRING);

            return true;
        }

        return parent::bindValue($cell, $value);
    }

    public function columnFormats(): array
    {
        return [
            'B' => NumberFormat::FORMAT_TEXT,
            'E' => '#,##0',
            'F' => '#,##0',
            'G' => '#,##0',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->mergeCells('A1:G1');
        $sheet->mergeCells('A2:G2');

        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A2')->getFont()->setSize(11)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('555555'));

        $sheet->getStyle('A4:G4')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '304F9E'],
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $totalRows = $this->data->count();
                $lastRow = 4 + $totalRows;

                $summaryRow = $lastRow + 1;
                $sheet->setCellValue("A{$summaryRow}", 'TOTAL');
                $sheet->mergeCells("A{$summaryRow}:D{$summaryRow}");
                if ($totalRows > 0) {
                    $sheet->setCellValue("E{$summaryRow}", "=SUM(E5:E{$lastRow})");
                    $sheet->setCellValue("F{$summaryRow}", "=SUM(F5:F{$lastRow})");
                    $sheet->setCellValue("G{$summaryRow}", "=SUM(G5:G{$lastRow})");
                } else {
                    $sheet->setCellValue("E{$summaryRow}", 0);
                    $sheet->setCellValue("F{$summaryRow}", 0);
                    $sheet->setCellValue("G{$summaryRow}", 0);
                }

                $sheet->getStyle("A4:G{$summaryRow}")->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'D0D7DE'],
                        ],
                    ],
                ]);

                $sheet->getStyle("A{$summaryRow}:G{$summaryRow}")->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'EAEFF8'],
                    ],
                ]);

                $sheet->getStyle("A5:A{$summaryRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("D5:D{$summaryRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            },
        ];
    }
}
