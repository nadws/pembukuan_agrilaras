<?php

namespace Tests\Unit;

use App\Exports\LaporanFakturPajakExport;
use Tests\TestCase;

class LaporanFakturPajakExportTest extends TestCase
{
    public function test_nik_validation_helper(): void
    {
        // 6371010409830005 = NIK (Laki-laki 04 Sept 1983) -> Valid -> TIN
        $this->assertTrue(LaporanFakturPajakExport::isNikValid('6371010409830005'));

        // 6371014409830005 = NIK (Perempuan 04 Sept 1983, 44 - 40 = 04) -> Valid -> TIN
        $this->assertTrue(LaporanFakturPajakExport::isNikValid('6371014409830005'));

        // 0020391769056000 = NPWP (Bulan 69 invalid) -> Invalid NIK -> National ID
        $this->assertFalse(LaporanFakturPajakExport::isNikValid('0020391769056000'));
    }

    public function test_export_faktur_pajak_kolom_k_dan_l(): void
    {
        $export = new LaporanFakturPajakExport('2026-09-01', '2026-09-30', '0858196173732000');

        $notaList = [
            (object) [
                'no_nota' => 'NT-001',
                'tgl' => '2026-09-10',
                'lokasi' => 'alpa',
                'nama' => 'CUSTOMER NIK VALID',
                'npwp' => '',
                'nik' => '6371010409830005',
                'alamat' => 'ALAMAT 1',
            ],
            (object) [
                'no_nota' => 'NT-002',
                'tgl' => '2026-09-11',
                'lokasi' => 'alpa',
                'nama' => 'CUSTOMER NPWP 16 DIGIT',
                'npwp' => '0020391769056000',
                'nik' => '',
                'alamat' => 'ALAMAT 2',
            ],
        ];

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $spreadsheet->removeSheetByIndex(0);

        $reflection = new \ReflectionClass(LaporanFakturPajakExport::class);
        $method = $reflection->getMethod('isiFaktur');
        $method->setAccessible(true);
        $method->invoke($export, $spreadsheet, $notaList);

        $sheet = $spreadsheet->getSheetByName('Faktur');

        // Check header row 3
        $this->assertSame('NPWP/NIK Pembeli', $sheet->getCell('K3')->getValue());
        $this->assertSame('Jenis ID Pembeli', $sheet->getCell('L3')->getValue());

        // Row 4: 6371010409830005 -> NIK valid -> Kolom K 6371010409830005, Kolom L TIN
        $this->assertSame('6371010409830005', $sheet->getCell('K4')->getValue());
        $this->assertSame('TIN', $sheet->getCell('L4')->getValue());

        // Row 5: 0020391769056000 -> NPWP -> Kolom K 0020391769056000, Kolom L National ID
        $this->assertSame('0020391769056000', $sheet->getCell('K5')->getValue());
        $this->assertSame('National ID', $sheet->getCell('L5')->getValue());
    }
}
