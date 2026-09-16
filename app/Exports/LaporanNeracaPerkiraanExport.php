<?php

namespace App\Exports;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LaporanNeracaPerkiraanExport implements FromArray, ShouldAutoSize, WithHeadings, WithStyles
{
    private array $rows = [];

    private array $sectionRows = [];

    private array $totalRows = [];

    private array $highlightRows = [];

    public function __construct(
        private readonly array $result,
        private readonly Carbon $reportDate,
    ) {
        $this->buildRows();
    }

    public function array(): array
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return [
            'ASET',
            'NILAI (IDR)',
            'KEWAJIBAN DAN EKUITAS',
            'NILAI (IDR)',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $lastColumn = $sheet->getHighestColumn();
        $lastRow = $sheet->getHighestRow();
        $sheet->freezePane('A2');
        $sheet->getStyle("A1:{$lastColumn}1")->getFont()->setBold(true);
        $sheet->getStyle("B2:B{$lastRow}")->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle("D2:D{$lastRow}")->getNumberFormat()->setFormatCode('#,##0');

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
        // Kolom kiri = ASET, kolom kanan = KEWAJIBAN + EKUITAS (sampingan kayak halaman cetak).
        // Baris bernilai Rp 0 disembunyikan; nilai minus tetap ditampilkan.
        $hasRows = fn ($rows) => $rows instanceof Collection ? $rows->isNotEmpty() : collect($rows)->isNotEmpty();
        $nonZero = fn ($value) => abs((float) $value) > 0;
        $showCash = $hasRows($this->result['cashRows']) || $nonZero($this->result['cash']);
        $showReceivable = $hasRows($this->result['receivableRows']) || $nonZero($this->result['receivable']);
        $showInventory = $hasRows($this->result['inventoryRows']) || $nonZero($this->result['inventory']);
        $showOtherCurrent = $hasRows($this->result['otherCurrentRows']) || $nonZero($this->result['otherCurrent']);
        $showCurrentAssets = $showCash || $showReceivable || $showInventory || $showOtherCurrent;
        $showFixed = $hasRows($this->result['fixedAssetRows']) || $nonZero($this->result['fixedAssets']);
        $showDepr = $hasRows($this->result['depreciationRows']) || $nonZero($this->result['accumulatedDepreciation']);
        $showNetFixed = $showFixed || $showDepr;
        $showPayable = $hasRows($this->result['payableRows']) || $nonZero($this->result['payable']);
        $showOtherCL = $hasRows($this->result['otherCurrentLiabilityRows']) || $nonZero($this->result['otherCurrentLiability']);
        $showCurrentLiab = $showPayable || $showOtherCL;
        $showLongTerm = $hasRows($this->result['longTermLiabilityRows']) || $nonZero($this->result['longTermLiabilities']);
        $showEquity = $hasRows($this->result['equityRows']) || $nonZero($this->result['currentProfit']);

        $left = [];
        if ($showCurrentAssets) {
            $left[] = $this->line('ASET LANCAR', null, 'section');
            if ($showCash) {
                $left[] = $this->line('Kas dan Bank', null, 'section');
                $this->appendAccounts($left, $this->result['cashRows']);
                $left[] = $this->line('Jumlah Kas dan Bank', $this->result['cash'], 'total');
            }
            if ($showReceivable) {
                $left[] = $this->line('Piutang dan Uang Muka', null, 'section');
                $this->appendAccounts($left, $this->result['receivableRows']);
                $left[] = $this->line('Jumlah Piutang dan Uang Muka', $this->result['receivable'], 'total');
            }
            if ($showInventory) {
                $left[] = $this->line('Persediaan', null, 'section');
                $this->appendAccounts($left, $this->result['inventoryRows']);
                $left[] = $this->line('Jumlah Persediaan', $this->result['inventory'], 'total');
            }
            if ($showOtherCurrent) {
                $left[] = $this->line('Aset Lancar Lainnya', null, 'section');
                $this->appendAccounts($left, $this->result['otherCurrentRows']);
                $left[] = $this->line('Jumlah Aset Lancar Lainnya', $this->result['otherCurrent'], 'total');
            }
            $left[] = $this->line('JUMLAH ASET LANCAR', $this->result['currentAssets'], 'total');
        }
        if ($showNetFixed) {
            $left[] = $this->line('ASET TETAP', null, 'section');
            if ($showFixed) {
                $this->appendAccounts($left, $this->result['fixedAssetRows']);
                $left[] = $this->line('Jumlah Harga Perolehan', $this->result['fixedAssets'], 'total');
            }
            if ($showDepr) {
                $left[] = $this->line('Akumulasi Penyusutan', null, 'section');
                $this->appendAccounts($left, $this->result['depreciationRows'], '-1');
                $left[] = $this->line('Jumlah Akumulasi Penyusutan', bcmul($this->result['accumulatedDepreciation'], '-1', 12), 'total');
            }
            $left[] = $this->line('JUMLAH ASET TETAP NETO', $this->result['netFixedAssets'], 'total');
        }
        $left[] = $this->line('TOTAL ASET', $this->result['totalAssets'], 'highlight');

        $right = [];
        if ($showCurrentLiab) {
            $right[] = $this->line('KEWAJIBAN JANGKA PENDEK', null, 'section');
            if ($showPayable) {
                $right[] = $this->line('Hutang Usaha', null, 'section');
                $this->appendAccounts($right, $this->result['payableRows']);
                $right[] = $this->line('Jumlah Hutang Usaha', $this->result['payable'], 'total');
            }
            if ($showOtherCL) {
                $right[] = $this->line('Kewajiban Lancar Lainnya', null, 'section');
                $this->appendAccounts($right, $this->result['otherCurrentLiabilityRows']);
                $right[] = $this->line('Jumlah Kewajiban Lancar Lainnya', $this->result['otherCurrentLiability'], 'total');
            }
            $right[] = $this->line('JUMLAH KEWAJIBAN JANGKA PENDEK', $this->result['currentLiabilities'], 'total');
        }
        if ($showLongTerm) {
            $right[] = $this->line('KEWAJIBAN JANGKA PANJANG', null, 'section');
            $this->appendAccounts($right, $this->result['longTermLiabilityRows']);
            $right[] = $this->line('JUMLAH KEWAJIBAN JANGKA PANJANG', $this->result['longTermLiabilities'], 'total');
        }
        $right[] = $this->line('TOTAL KEWAJIBAN', $this->result['totalLiabilities'], 'total');
        if ($showEquity) {
            $right[] = $this->line('EKUITAS', null, 'section');
            $this->appendAccounts($right, $this->result['equityRows']);
            if ($nonZero($this->result['currentProfit'])) {
                $right[] = $this->line('Laba/Rugi Tahun Ini', $this->result['currentProfit']);
            }
            $right[] = $this->line('JUMLAH EKUITAS', $this->result['totalEquity'], 'total');
        }
        $right[] = $this->line('TOTAL KEWAJIBAN DAN EKUITAS', $this->result['liabilitiesAndEquity'], 'highlight');

        $rowCount = max(count($left), count($right));
        for ($i = 0; $i < $rowCount; $i++) {
            $l = $left[$i] ?? null;
            $r = $right[$i] ?? null;
            $this->rows[] = [
                $l['label'] ?? null,
                $l['value'] ?? null,
                $r['label'] ?? null,
                $r['value'] ?? null,
            ];
            $excelRow = count($this->rows) + 1;
            foreach (['l' => $l, 'r' => $r] as $item) {
                if (! $item) {
                    continue;
                }
                if ($item['kind'] === 'section') {
                    $this->sectionRows[] = $excelRow;
                } elseif ($item['kind'] === 'total') {
                    $this->totalRows[] = $excelRow;
                } elseif ($item['kind'] === 'highlight') {
                    $this->highlightRows[] = $excelRow;
                }
            }
        }
        $this->sectionRows = array_values(array_unique($this->sectionRows));
        $this->totalRows = array_values(array_unique($this->totalRows));
        $this->highlightRows = array_values(array_unique($this->highlightRows));

        // Baris status selisih di bawah tabel sampingan.
        $this->rows[] = [
            abs((float) $this->result['difference']) <= 1 ? 'NERACA SEIMBANG' : 'NERACA BELUM SEIMBANG',
            null,
            'Selisih Aset dengan Kewajiban + Ekuitas',
            (float) $this->result['difference'],
        ];
        $this->totalRows[] = count($this->rows) + 1;
    }

    private function line(?string $label, mixed $value, string $kind = ''): array
    {
        return [
            'label' => $label,
            'value' => $value === null ? null : (float) $value,
            'kind' => $kind,
        ];
    }

    private function appendAccounts(array &$target, Collection $accounts, string $multiplier = '1'): void
    {
        foreach ($accounts as $account) {
            $target[] = $this->line(
                ($account['kode'] ? $account['kode'].' - ' : '').str_repeat('    ', max(0, $account['depth'])).$account['nama'],
                bcmul((string) $account['value'], $multiplier, 12),
            );
        }
    }
}
