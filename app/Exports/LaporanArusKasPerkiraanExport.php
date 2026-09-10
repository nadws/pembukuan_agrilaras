<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LaporanArusKasPerkiraanExport implements FromArray, ShouldAutoSize, WithHeadings, WithStyles
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

    public function headings(): array
    {
        return array_merge(
            ['Kode', 'Akun Lawan'],
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
        $this->section('ARUS KAS MASUK');
        $this->accounts($this->result['incomingRows']);
        $this->total('Jumlah Arus Kas Masuk', $this->result['incomingTotals']);

        $this->section('ARUS KAS KELUAR');
        $this->accounts($this->result['outgoingRows']);
        $this->total('Jumlah Arus Kas Keluar', $this->result['outgoingTotals']);

        $this->section('RINGKASAN');
        $this->total('Arus Kas Bersih', $this->result['netTotals'], true);
        $this->singleTotal('Saldo Awal', (float) ($this->result['openingBalance'] ?? 0));
        $this->singleTotal('Saldo Akhir', (float) ($this->result['closingBalance'] ?? 0), true);
    }

    private function section(string $label): void
    {
        $this->rows[] = array_merge(['', $label], array_fill(0, $this->periods->count() + 1, null));
        $this->sectionRows[] = count($this->rows) + 1;
    }

    private function accounts(Collection $accounts): void
    {
        foreach ($accounts as $account) {
            $this->rows[] = array_merge(
                [(string) ($account['kode'] ?? ''), (string) ($account['nama'] ?? '')],
                $this->numericValues($account['values'] ?? []),
                [(float) ($account['total'] ?? 0)]
            );
        }
    }

    private function total(string $label, array $values, bool $highlight = false): void
    {
        $this->rows[] = array_merge(
            ['', $label],
            $this->numericValues($values),
            [(float) array_sum($values)]
        );
        $excelRow = count($this->rows) + 1;
        if ($highlight) {
            $this->highlightRows[] = $excelRow;
        } else {
            $this->totalRows[] = $excelRow;
        }
    }

    private function singleTotal(string $label, float $amount, bool $highlight = false): void
    {
        $this->rows[] = array_merge(
            ['', $label],
            array_fill(0, $this->periods->count(), null),
            [$amount]
        );
        $excelRow = count($this->rows) + 1;
        if ($highlight) {
            $this->highlightRows[] = $excelRow;
        } else {
            $this->totalRows[] = $excelRow;
        }
    }

    private function numericValues(array $values): array
    {
        return $this->periods->map(fn ($period) => (float) ($values[$period->format('Y-m')] ?? 0))->all();
    }
}
