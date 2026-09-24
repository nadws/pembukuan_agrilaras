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

class JurnalUmumExport extends DefaultValueBinder implements FromCollection, WithHeadings, WithMapping, WithColumnFormatting, ShouldAutoSize, WithStyles, WithCustomValueBinder, WithEvents
{
    private int $rowNumber = 0;

    public function __construct(
        private readonly Collection $data,
        private readonly string $judul,
        private readonly string $tanggalAwal,
        private readonly string $tanggalAkhir,
        private readonly ?string $cari = null
    ) {
    }

    public function collection(): Collection
    {
        return $this->data;
    }

    public function headings(): array
    {
        $headings = [
            [strtoupper($this->judul)],
            ['Periode: ' . date('d/m/Y', strtotime($this->tanggalAwal)) . ' s/d ' . date('d/m/Y', strtotime($this->tanggalAkhir))],
        ];

        if (! empty($this->cari)) {
            $headings[] = ['Pencarian: ' . $this->cari];
        }

        $headings[] = [];
        $headings[] = ['No', 'Tanggal', 'No. Transaksi', 'Tipe Transaksi', 'Kode Akun', 'Nama Akun', 'Keterangan', 'Debit (Rp)', 'Kredit (Rp)'];

        return $headings;
    }

    private function headerRow(): int
    {
        return empty($this->cari) ? 4 : 5;
    }

    private function firstDataRow(): int
    {
        return $this->headerRow() + 1;
    }

    public function map($row): array
    {
        $this->rowNumber++;

        return [
            $this->rowNumber,
            date('d/m/Y', strtotime($row->tanggal)),
            (string) $row->nomor_transaksi,
            (string) ($row->tipe_transaksi ?? '-'),
            (string) ($row->kode_perkiraan ?? '-'),
            (string) ($row->nama_akun ?? '-'),
            (string) ($row->deskripsi ?? '-'),
            (float) ($row->debit ?? 0),
            (float) ($row->kredit ?? 0),
        ];
    }

    public function bindValue(Cell $cell, $value): bool
    {
        $first = $this->firstDataRow();
        if ($cell->getRow() >= $first && in_array($cell->getColumn(), ['C', 'E'], true)) {
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
            'E' => NumberFormat::FORMAT_TEXT,
            'H' => '#,##0',
            'I' => '#,##0',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $header = $this->headerRow();
        $lastCol = 'I';

        $sheet->mergeCells("A1:{$lastCol}1");
        $sheet->mergeCells("A2:{$lastCol}2");
        if (! empty($this->cari)) {
            $sheet->mergeCells("A3:{$lastCol}3");
        }

        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A2')->getFont()->setSize(11)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('555555'));
        if (! empty($this->cari)) {
            $sheet->getStyle('A3')->getFont()->setSize(11)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('555555'));
        }

        $sheet->getStyle("A{$header}:{$lastCol}{$header}")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '29468F']],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
        ]);

        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $header = $this->headerRow();
                $first = $this->firstDataRow();
                $totalRows = $this->data->count();
                $lastRow = $header + $totalRows;

                $summaryRow = $lastRow + 1;
                $sheet->setCellValue("A{$summaryRow}", 'TOTAL');
                $sheet->mergeCells("A{$summaryRow}:G{$summaryRow}");
                if ($totalRows > 0) {
                    $sheet->setCellValue("H{$summaryRow}", "=SUM(H{$first}:H{$lastRow})");
                    $sheet->setCellValue("I{$summaryRow}", "=SUM(I{$first}:I{$lastRow})");
                } else {
                    $sheet->setCellValue("H{$summaryRow}", 0);
                    $sheet->setCellValue("I{$summaryRow}", 0);
                }

                $sheet->getStyle("A{$header}:I{$summaryRow}")->applyFromArray([
                    'borders' => [
                        'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'D0D7DE']],
                    ],
                ]);

                $sheet->getStyle("A{$summaryRow}:I{$summaryRow}")->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'EAEFF8']],
                ]);

                if ($totalRows > 0) {
                    $sheet->getStyle("A{$first}:A{$summaryRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("B{$first}:B{$summaryRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                }
            },
        ];
    }
}
