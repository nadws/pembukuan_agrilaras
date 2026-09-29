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
                'nama' => 'AKHMAD',
                'npwp' => '6371010404770014',
                'nik' => '',
                'alamat' => 'ALAMAT 1',
            ],
            (object) [
                'no_nota' => 'NT-002',
                'tgl' => '2026-09-11',
                'lokasi' => 'alpa',
                'nama' => 'PT SAHABAT ABADI HOTELINDO',
                'npwp' => '0020432118731000',
                'nik' => '',
                'alamat' => 'ALAMAT 2',
            ],
            (object) [
                'no_nota' => 'NT-003',
                'tgl' => '2026-09-12',
                'lokasi' => 'alpa',
                'nama' => 'CV TAMA KIAT MANUNGGAL',
                'npwp' => '0967440710736000',
                'nik' => '',
                'alamat' => 'ALAMAT 3',
            ],
            (object) [
                'no_nota' => 'NT-004',
                'tgl' => '2026-09-13',
                'lokasi' => 'alpa',
                'nama' => 'KHAIDIR YUNAN, S.AB',
                'npwp' => '',
                'nik' => '6371010409830005',
                'alamat' => 'ALAMAT 4',
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

        // Row 4: NIK di kolom NPWP, nama di luar daftar -> Kolom L National ID
        $this->assertSame('6371010404770014', $sheet->getCell('K4')->getValue());
        $this->assertSame('National ID', $sheet->getCell('L4')->getValue());

        // Row 5: nama di daftar NPWP -> Kolom L TIN
        $this->assertSame('0020432118731000', $sheet->getCell('K5')->getValue());
        $this->assertSame('TIN', $sheet->getCell('L5')->getValue());

        // Row 6: NPWP berawalan nol -> nol depan wajib utuh, Kolom L TIN
        $this->assertSame('0967440710736000', $sheet->getCell('K6')->getValue());
        $this->assertSame('TIN', $sheet->getCell('L6')->getValue());

        // Row 7: NIK dari kolom KTP -> Kolom K NIK, Kolom L National ID
        $this->assertSame('6371010409830005', $sheet->getCell('K7')->getValue());
        $this->assertSame('National ID', $sheet->getCell('L7')->getValue());

        // Kolom O: nama pembeli untuk cek customer per baris
        $this->assertSame('AKHMAD', $sheet->getCell('O4')->getValue());
        $this->assertSame('PT SAHABAT ABADI HOTELINDO', $sheet->getCell('O5')->getValue());
        $this->assertSame('CV TAMA KIAT MANUNGGAL', $sheet->getCell('O6')->getValue());
        $this->assertSame('KHAIDIR YUNAN, S.AB', $sheet->getCell('O7')->getValue());
        $this->assertSame('@', $sheet->getStyle('K6')->getNumberFormat()->getFormatCode());
        $this->assertTrue($sheet->getStyle('K6')->getQuotePrefix());

        // Round-trip: simpan ke xlsx lalu baca lagi, string 16 digit harus utuh
        // (regresi bug: 0967440710736000 menjadi 9674407107360000).
        $tmp = tempnam(sys_get_temp_dir(), 'coretax').'.xlsx';
        try {
            (new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet))->save($tmp);
            $reloaded = \PhpOffice\PhpSpreadsheet\IOFactory::load($tmp)->getSheetByName('Faktur');
            $this->assertSame('0967440710736000', (string) $reloaded->getCell('K6')->getValue());
            $this->assertSame('0020432118731000', (string) $reloaded->getCell('K5')->getValue());
            $this->assertSame('6371010404770014', (string) $reloaded->getCell('K4')->getValue());
            $this->assertSame('6371010409830005', (string) $reloaded->getCell('K7')->getValue());
        } finally {
            if (file_exists($tmp)) {
                unlink($tmp);
            }
        }
    }

    public function test_normalisasi_npwp_format_lama(): void
    {
        $reflection = new \ReflectionClass(LaporanFakturPajakExport::class);
        $method = $reflection->getMethod('digit16');
        $method->setAccessible(true);

        // NPWP 16 digit baru: utuh apa adanya
        $this->assertSame('0967440710736000', $method->invoke(null, '0967440710736000'));
        $this->assertSame('0020391769056000', $method->invoke(null, '0020391769056000'));
        // NPWP format lama 15 digit: tambah nol depan
        $this->assertSame('0020391769056000', $method->invoke(null, '02.039.176.9-056.000'));
        $this->assertSame('0020432118731000', $method->invoke(null, '02.043.211.8-731.000'));
        // NPWP format lama korup (nol depan hilang + kelebihan nol belakang)
        $this->assertSame('0967440710736000', $method->invoke(null, '96.744.071.0-736.0000'));
        $this->assertSame('0020432118731000', $method->invoke(null, '02.043.211.8-731.0000'));
        // Sampah Excel / nilai pendek: tolak
        $this->assertSame('', $method->invoke(null, '6.37101E+15'));
        $this->assertSame('', $method->invoke(null, '2.27003E+13'));
        $this->assertSame('', $method->invoke(null, '123'));
        $this->assertSame('', $method->invoke(null, ''));
    }

    public function test_daftar_customer_npwp(): void
    {
        $this->assertTrue(LaporanFakturPajakExport::isCustomerNpwp('CV.TAMA KIAT MANUNGGAL'));
        $this->assertTrue(LaporanFakturPajakExport::isCustomerNpwp('CV TAMA KIAT MANUNGGAL'));
        $this->assertTrue(LaporanFakturPajakExport::isCustomerNpwp('PT. REKSO NASIONAL FOOD'));
        $this->assertTrue(LaporanFakturPajakExport::isCustomerNpwp('BADAN PRIMAFOOD INTERNATIONAL'));
        $this->assertTrue(LaporanFakturPajakExport::isCustomerNpwp('SHIN DJAYA BERSAMA'));
        $this->assertTrue(LaporanFakturPajakExport::isCustomerNpwp('PT NEW BARITO HOTEL'));
        $this->assertTrue(LaporanFakturPajakExport::isCustomerNpwp('PT SAHABAT ABADI HOTELINDO'));
        $this->assertFalse(LaporanFakturPajakExport::isCustomerNpwp('AKHMAD'));
        $this->assertFalse(LaporanFakturPajakExport::isCustomerNpwp('KHAIDIR YUNAN, S.AB'));
        $this->assertFalse(LaporanFakturPajakExport::isCustomerNpwp('VITA'));
    }
}
