<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TagihanMcdExport
{
    private const TEMPLATE = 'resources/templates/tagihan_mcd_format.xlsx';

    private const WRIN_NUM = '99353-001';

    private const UNIT_PRICE = 281250;

    private const PCS_PER_RAK = 150;

    public function __construct(
        private readonly string $tgl1,
        private readonly string $tgl2,
    ) {
    }

    public function unduh(string $namaFile): StreamedResponse
    {
        $spreadsheet = $this->bangun();
        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(
            static fn () => $writer->save('php://output'),
            $namaFile,
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
        );
    }

    public function baris(): Collection
    {
        return DB::table('invoice_telur as i')
            ->leftJoin('customer as c', 'c.id_customer', '=', 'i.id_customer')
            ->where('i.lokasi', 'mtd')
            ->whereBetween('i.tgl', [$this->tgl1, $this->tgl2])
            ->where(function ($query) {
                $query
                    ->whereRaw("UPPER(COALESCE(c.nm_customer, '')) LIKE '%MC DONALD%'")
                    ->orWhereRaw("UPPER(COALESCE(i.customer, '')) LIKE '%MC DONALD%'");
            })
            ->groupBy('i.no_nota', 'i.tgl')
            ->select([
                'i.no_nota',
                'i.tgl',
            ])
            ->selectRaw('MAX(c.nm_customer) as nm_customer')
            ->selectRaw('MAX(i.customer) as customer')
            ->selectRaw("SUM(CASE WHEN LOWER(i.tipe) IN ('pcs', 'kg') THEN COALESCE(i.pcs, 0) ELSE 0 END) as total_pcs")
            ->orderBy('i.tgl')
            ->orderBy('i.no_nota')
            ->get()
            ->map(function ($row) {
                $customer = trim((string) ($row->nm_customer ?: $row->customer));
                $customerUpper = strtoupper($customer);
                $segment = str_contains($customerUpper, 'BANJARBARU')
                    ? '36901'
                    : (str_contains($customerUpper, 'BANJARMASIN') ? '33001' : null);

                if ($segment === null) {
                    return null;
                }

                $totalPcs = (float) $row->total_pcs;

                return [
                    'item_id' => 'Direct',
                    'wrin_num' => self::WRIN_NUM,
                    'segment3' => $segment,
                    'quantity' => round($totalPcs / self::PCS_PER_RAK, 2),
                    'sub_category' => 'FOOD NON TAX',
                    'currency_code' => 'IDR',
                    'unit_price' => self::UNIT_PRICE,
                    'tgl' => (string) $row->tgl,
                    'no_nota' => (string) $row->no_nota,
                ];
            })
            ->filter()
            ->values();
    }

    public function bangun(): Spreadsheet
    {
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load(base_path(self::TEMPLATE));
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $this->baris();

        $this->hapusCatatanPribadi($sheet);
        $this->siapkanBarisData($sheet, $rows->count());

        foreach ($rows as $index => $row) {
            $excelRow = $index + 2;
            $date = ExcelDate::PHPToExcel(new \DateTimeImmutable($row['tgl']));

            $values = [
                'A' => $row['item_id'],
                'B' => $row['wrin_num'],
                'C' => $row['segment3'],
                'D' => $row['quantity'],
                'E' => $row['sub_category'],
                'F' => $row['currency_code'],
                'G' => $row['unit_price'],
                'H' => null,
                'I' => null,
                'J' => null,
                'K' => null,
                'L' => null,
                'M' => null,
                'N' => null,
                'O' => $date,
                'P' => $row['no_nota'],
                'Q' => null,
                'R' => $row['no_nota'],
                'S' => $date,
                'T' => null,
                'U' => $date,
                'V' => null,
            ];

            foreach ($values as $column => $value) {
                $sheet->setCellValue($column.$excelRow, $value);
            }

            $sheet->getStyle('G'.$excelRow)
                ->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()
                ->setRGB('DDEBF7');
            $sheet->getStyle('D'.$excelRow)->getNumberFormat()->setFormatCode('#,##0.##');
            $sheet->getStyle('G'.$excelRow)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle('O'.$excelRow.':U'.$excelRow)->getNumberFormat()->setFormatCode('[$-409]dd\-mmm\-yy;@');
        }

        $sheet->removeColumn('W');
        $sheet->setAutoFilter('A1:V'.max(1, $rows->count() + 1));
        $sheet->freezePane('A2');
        $sheet->setSelectedCell('A1');
        $spreadsheet->setActiveSheetIndex(0);

        return $spreadsheet;
    }

    private function hapusCatatanPribadi($sheet): void
    {
        $sheet->setCellValue('T9', null);
        $sheet->setCellValue('O16', null);
        $sheet->setCellValue('V16', null);
        $sheet->setCellValue('N18', null);
        $sheet->setCellValue('N19', null);
        $sheet->setCellValue('G20', null);
        $sheet->setCellValue('H20', null);
        $sheet->setCellValue('I20', null);
        $sheet->setCellValue('N20', null);
    }

    private function siapkanBarisData($sheet, int $jumlahData): void
    {
        for ($row = 2; $row <= 15; $row++) {
            for ($column = 1; $column <= 23; $column++) {
                $sheet->setCellValueByColumnAndRow($column, $row, null);
            }
        }

        if ($jumlahData <= 14) {
            return;
        }

        $extraRows = $jumlahData - 14;
        $sheet->insertNewRowBefore(16, $extraRows);
        for ($row = 16; $row < 16 + $extraRows; $row++) {
            $sheet->duplicateStyle($sheet->getStyle('A2:W2'), 'A'.$row.':W'.$row);
            $sheet->getRowDimension($row)->setRowHeight($sheet->getRowDimension(2)->getRowHeight());
        }
    }
}
