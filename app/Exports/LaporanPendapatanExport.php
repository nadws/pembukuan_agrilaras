<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LaporanPendapatanExport implements FromArray, ShouldAutoSize, WithColumnFormatting, WithHeadings, WithStyles, WithTitle
{
    private array $rows = [];

    private array $totalRows = [];

    private array $highlightRows = [];

    private array $sectionRows = [];

    private bool $hasSummary = false;

    public function __construct(
        private readonly Collection $notaRows,
        private readonly string $tanggalAwal,
        private readonly string $tanggalAkhir,
        Collection|array $summary = [],
        Collection|array $paySummary = []
    ) {
        $summary = $summary instanceof Collection ? $summary : collect($summary);
        $paySummary = $paySummary instanceof Collection ? $paySummary : collect($paySummary);
        $this->hasSummary = $summary->isNotEmpty() || $paySummary->isNotEmpty();
        $this->buildRows($summary, $paySummary);
    }

    public function array(): array
    {
        return $this->rows;
    }

    public function title(): string
    {
        return 'Pendapatan';
    }

    public function headings(): array
    {
        $headings = [
            'No',
            'Tipe',
            "Tanggal ({$this->tanggalAwal} s/d {$this->tanggalAkhir})",
            'Invoice',
            'Lokasi',
            'Customer',
            'Total (IDR)',
            'Bayar Via',
        ];

        if ($this->hasSummary) {
            $headings = array_merge($headings, ['', 'No', 'Produk', 'Tipe', 'Qty', 'Total Rangkuman (IDR)']);
        }

        return $headings;
    }

    public function columnFormats(): array
    {
        return ['G' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1, 'N' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1];
    }

    public function styles(Worksheet $sheet): array
    {
        $lastColumn = $sheet->getHighestColumn();
        $sheet->freezePane('C2');
        $sheet->getStyle("A1:{$lastColumn}1")->getFont()->setBold(true);

        foreach ($this->sectionRows as $row) {
            $sheet->getStyle("J{$row}:N{$row}")->getFont()->setBold(true);
        }
        foreach ($this->totalRows as $row) {
            $style = $sheet->getStyle("A{$row}:{$lastColumn}{$row}");
            $style->getFont()->setBold(true);
            $style->getBorders()->getTop()->setBorderStyle(Border::BORDER_THIN);
            $style->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THIN);
        }
        foreach ($this->highlightRows as $row) {
            $style = $sheet->getStyle("J{$row}:N{$row}");
            $style->getFont()->setBold(true);
            $style->getBorders()->getTop()->setBorderStyle(Border::BORDER_MEDIUM);
            $style->getBorders()->getBottom()->setBorderStyle(Border::BORDER_DOUBLE);
        }

        return [];
    }

    private function buildRows(Collection $summary, Collection $paySummary): void
    {
        $detail = [];
        foreach ($this->notaRows as $index => $row) {
            $detail[] = [
                $index + 1,
                ucfirst((string) $row['kategori']),
                (string) $row['tgl'],
                (string) $row['no_nota'],
                (string) ($row['lokasi'] ?? '-'),
                (string) $row['customer'],
                (float) $row['total'],
                (string) ($row['pembayaran'] ?? '-'),
            ];
        }
        $detailTotalIndex = count($detail);
        $detail[] = ['', '', '', '', '', 'TOTAL', (float) $this->notaRows->sum('total'), ''];

        $sum = [];
        if ($this->hasSummary) {
            foreach ($summary as $index => $item) {
                $qty = [];
                if ((float) $item['pcs'] > 0) {
                    $qty[] = number_format((float) $item['pcs'], 0, '.', ',').' pcs';
                }
                if ((float) $item['kg'] > 0) {
                    $qty[] = number_format((float) $item['kg'], 2, '.', ',').' kg';
                }
                $sum[] = [
                    $index + 1,
                    (string) $item['produk'],
                    ucfirst((string) $item['tipe']),
                    implode(' / ', $qty),
                    (float) $item['total'],
                ];
            }
            $sum[] = ['', '', '', 'TOTAL RANGKUMAN', (float) $summary->sum('total')];
            // Blok kanan digabung mulai baris 0 → baris Excel = index + 2.
            $this->highlightRows[] = count($sum) + 1;
        }

        if ($paySummary->isNotEmpty()) {
            if ($summary->isNotEmpty()) {
                $sum[] = ['', '', '', '', ''];
            }
            $sum[] = ['', 'TOTAL PER PEMBAYARAN', '', '', ''];
            $this->sectionRows[] = count($sum) + 1;
            foreach ($paySummary as $index => $pay) {
                $sum[] = [
                    $index + 1,
                    (string) $pay['pembayaran'],
                    (int) $pay['jumlah'].' nota',
                    '',
                    (float) $pay['total'],
                ];
            }
            $sum[] = ['', '', '', 'TOTAL PEMBAYARAN', (float) $paySummary->sum('total')];
            $this->highlightRows[] = count($sum) + 1;
        }

        $lines = max(count($detail), count($sum));
        for ($i = 0; $i < $lines; $i++) {
            $left = $detail[$i] ?? array_fill(0, 8, '');
            if (! $this->hasSummary) {
                $this->rows[] = $left;
                continue;
            }
            $right = $sum[$i] ?? array_fill(0, 5, '');
            $this->rows[] = array_merge($left, [''], $right);
        }

        // Baris Excel = index + 2 (baris 1 header).
        $this->totalRows[] = $detailTotalIndex + 2;
    }
}
