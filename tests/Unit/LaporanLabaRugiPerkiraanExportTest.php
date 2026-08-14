<?php

namespace Tests\Unit;

use App\Exports\LaporanLabaRugiPerkiraanExport;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\TestCase;

class LaporanLabaRugiPerkiraanExportTest extends TestCase
{
    public function test_export_is_flat_and_keeps_amounts_numeric(): void
    {
        $periods = collect([Carbon::create(2026, 7, 1)]);
        $zero = ['2026-07' => '0.000000000000'];
        $revenue = ['2026-07' => '1250.500000000000'];
        $empty = collect();

        $result = [
            'periods' => $periods,
            'revenueRows' => collect([[
                'kode' => '410001', 'nama' => 'Penjualan Telur', 'depth' => 1,
                'values' => $revenue, 'total' => '1250.500000000000',
            ]]),
            'cogsRows' => $empty, 'operatingRows' => $empty, 'otherIncomeRows' => $empty,
            'otherExpenseRows' => $empty, 'depreciationRows' => $empty, 'taxRows' => $empty,
            'revenue' => $revenue, 'cogs' => $zero, 'gross' => $revenue, 'operating' => $zero,
            'operatingIncome' => $revenue, 'otherIncome' => $zero, 'otherExpense' => $zero,
            'otherNet' => $zero, 'beforeDepreciation' => $revenue, 'depreciationTotal' => $zero,
            'beforeTax' => $revenue, 'taxTotal' => $zero, 'afterTax' => $revenue,
        ];

        $path = tempnam(sys_get_temp_dir(), 'laporan').'.xlsx';
        file_put_contents($path, Excel::raw(new LaporanLabaRugiPerkiraanExport($result), \Maatwebsite\Excel\Excel::XLSX));
        $sheet = IOFactory::load($path)->getActiveSheet();

        $this->assertSame([], $sheet->getMergeCells());
        $this->assertSame('Kode Akun', $sheet->getCell('A1')->getValue());
        $this->assertSame('410001', $sheet->getCell('A3')->getValue());
        $this->assertIsFloat($sheet->getCell('C3')->getValue());
        $this->assertSame(1250.5, $sheet->getCell('C3')->getValue());

        @unlink($path);
    }
}
