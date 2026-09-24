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

class AktivaGantungJurnalExport extends DefaultValueBinder implements FromCollection, WithHeadings, WithMapping, WithColumnFormatting, ShouldAutoSize, WithStyles, WithCustomValueBinder, WithEvents
{
    private int $rowNumber = 0;

    public function __construct(
        private readonly Collection $data,
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
            ['JURNAL UMUM - AKTIVA GANTUNG'],
            ['Periode: ' . date('d/m/Y', strtotime($this->tanggalAwal)) . ' s/d ' . date('d/m/Y', strtotime($this->tanggalAkhir))],
        ];

        if (! empty($this->cari)) {
            $headings[] = ['Pencarian: ' . $this->cari];
        }

        $headings[] = [];
        $headings[] = ['No', 'Kode Aset', 'Nama Aset', 'Status', 'Tanggal', 'No. Transaksi', 'Akun Penampung', 'Dibayar Dari', 'Keterangan', 'Jumlah (Rp)'];

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

        $dibayarDari = ($row->sumber ?? 'transaksi') === 'saldo_awal'
            ? 'Saldo awal (tanpa jurnal)'
            : trim(($row->kode_akun_kas ?? '') . ' - ' . ($row->nama_akun_kas ?? ''));

        return [
            $this->rowNumber,
            (string) ($row->kode_aset ?? '-'),
            (string) ($row->nama_aset ?? '-'),
            (string) ($row->status_aset ?? '-'),
            date('d/m/Y', strtotime($row->tanggal)),
            (string) $row->nomor_transaksi,
            trim(($row->kode_akun_aktiva ?? '') . ' - ' . ($row->nama_akun_aktiva ?? '')),
            $dibayarDari,
            (string) ($row->keterangan ?? '-'),
            (float) ($row->jumlah ?? 0),
        ];
    }

    public function bindValue(Cell $cell, $value): bool
    {
        $first = $this->firstDataRow();
        if ($cell->getRow() >= $first && in_array($cell->getColumn(), ['B', 'F'], true)) {
            $cell->setValueExplicit((string) $value, DataType::TYPE_STRING);

            return true;
        }

        return parent::bindValue($cell, $value);
    }

    public function columnFormats(): array
    {
        return [
            'B' => NumberFormat::FORMAT_TEXT,
            'E' => NumberFormat::FORMAT_DATE_DDMMYYYY,
            'F' => NumberFormat::FORMAT_TEXT,
            'J' => '#,##0',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $header = $this->headerRow();
        $lastCol = 'J';

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
                $sheet->mergeCells("A{$summaryRow}:I{$summaryRow}");
                if ($totalRows > 0) {
                    $sheet->setCellValue("J{$summaryRow}", "=SUM(J{$first}:J{$lastRow})");
                } else {
                    $sheet->setCellValue("J{$summaryRow}", 0);
                }

                $sheet->getStyle("A{$header}:J{$summaryRow}")->applyFromArray([
                    'borders' => [
                        'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'D0D7DE']],
                    ],
                ]);

                $sheet->getStyle("A{$summaryRow}:J{$summaryRow}")->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'EAEFF8']],
                ]);

                if ($totalRows > 0) {
                    $sheet->getStyle("A{$first}:A{$summaryRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("E{$first}:E{$summaryRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                }
            },
        ];
    }
}
