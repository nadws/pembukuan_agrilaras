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

class BukuBesarBaruDetailExport extends DefaultValueBinder implements FromCollection, WithHeadings, WithMapping, WithColumnFormatting, ShouldAutoSize, WithStyles, WithCustomValueBinder, WithEvents
{
    private int $rowNumber = 0;

    public function __construct(
        private readonly object $akun,
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
            ['DETAIL BUKU BESAR'],
            ['Akun: ' . $this->akun->kode_perkiraan . ' - ' . $this->akun->nama],
            ['Periode: ' . date('d/m/Y', strtotime($this->tgl1)) . ' s/d ' . date('d/m/Y', strtotime($this->tgl2))],
            [],
            ['No', 'Tanggal', 'No. Transaksi', 'Tipe Transaksi', 'Keterangan', 'Debit (Rp)', 'Kredit (Rp)', 'Saldo (Rp)']
        ];
    }

    public function map($row): array
    {
        $this->rowNumber++;

        return [
            $this->rowNumber,
            date('d/m/Y', strtotime($row->tanggal)),
            (string) $row->nomor_transaksi,
            $row->tipe_transaksi,
            $row->deskripsi,
            (float) $row->debit,
            (float) $row->kredit,
            (float) $row->saldo,
        ];
    }

    public function bindValue(Cell $cell, $value): bool
    {
        if ($cell->getColumn() === 'C' && $cell->getRow() >= 6) {
            $cell->setValueExplicit((string) $value, DataType::TYPE_STRING);

            return true;
        }

        return parent::bindValue($cell, $value);
    }

    public function columnFormats(): array
    {
        return [
            'B' => NumberFormat::FORMAT_DATE_DDMMYYYY,
            'C' => NumberFormat::FORMAT_TEXT,
            'F' => '#,##0',
            'G' => '#,##0',
            'H' => '#,##0',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->mergeCells('A1:H1');
        $sheet->mergeCells('A2:H2');
        $sheet->mergeCells('A3:H3');

        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(11);
        $sheet->getStyle('A3')->getFont()->setSize(11)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('555555'));

        $sheet->getStyle('A5:H5')->applyFromArray([
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
                $lastRow = 5 + $totalRows;

                $summaryRow = $lastRow + 1;
                $sheet->setCellValue("A{$summaryRow}", 'TOTAL');
                $sheet->mergeCells("A{$summaryRow}:E{$summaryRow}");
                if ($totalRows > 0) {
                    $sheet->setCellValue("F{$summaryRow}", "=SUM(F6:F{$lastRow})");
                    $sheet->setCellValue("G{$summaryRow}", "=SUM(G6:G{$lastRow})");
                    $sheet->setCellValue("H{$summaryRow}", "=H{$lastRow}");
                } else {
                    $sheet->setCellValue("F{$summaryRow}", 0);
                    $sheet->setCellValue("G{$summaryRow}", 0);
                    $sheet->setCellValue("H{$summaryRow}", 0);
                }

                $sheet->getStyle("A5:H{$summaryRow}")->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'D0D7DE'],
                        ],
                    ],
                ]);

                $sheet->getStyle("A{$summaryRow}:H{$summaryRow}")->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'EAEFF8'],
                    ],
                ]);

                $sheet->getStyle("A6:A{$summaryRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("B6:B{$summaryRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("D6:D{$summaryRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            },
        ];
    }
}
