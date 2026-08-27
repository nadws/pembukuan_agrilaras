<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class MasterDataSpreadsheetService
{
    public function supplierTemplate(array $categories): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data Supplier');
        $headers = ['kategori', 'nama_supplier', 'email', 'alamat', 'telepon', 'npwp'];
        $sheet->fromArray($headers, null, 'A1');
        $sheet->fromArray(['Pemasok Umum', 'Contoh Supplier', 'supplier@example.com', 'Alamat supplier', '081234567890', ''], null, 'A2');
        $sheet->setCellValueExplicit('E2', '081234567890', DataType::TYPE_STRING);
        $this->styleDataSheet($sheet, 'A1:F1', [
            'A' => 23, 'B' => 30, 'C' => 30, 'D' => 38, 'E' => 20, 'F' => 24,
        ]);
        $sheet->getStyle('E2:F2')->getNumberFormat()->setFormatCode('@');

        $guide = $spreadsheet->createSheet();
        $guide->setTitle('Panduan');
        $guide->setCellValue('A1', 'PANDUAN IMPORT MASTER SUPPLIER');
        $guide->mergeCells('A1:C1');
        $guide->fromArray([
            ['Kolom', 'Wajib', 'Keterangan'],
            ['kategori', 'Ya', 'Pilih salah satu kategori pada daftar referensi.'],
            ['nama_supplier', 'Ya', 'Nama supplier, maksimal 100 karakter.'],
            ['email', 'Tidak', 'Harus berupa alamat email yang valid jika diisi.'],
            ['alamat', 'Tidak', 'Alamat supplier, maksimal 225 karakter.'],
            ['telepon', 'Tidak', 'Gunakan format teks agar angka 0 di depan tidak hilang.'],
            ['npwp', 'Tidak', 'Gunakan format teks.'],
        ], null, 'A3');
        $guide->setCellValue('E1', 'REFERENSI KATEGORI');
        $guide->setCellValue('E3', 'kategori');
        foreach (array_values($categories) as $index => $category) {
            $guide->setCellValue('E' . ($index + 4), $category);
        }
        $this->styleGuideSheet($guide, ['A1:C1', 'E1:E1'], ['A3:C3', 'E3:E3']);
        foreach (['A' => 23, 'B' => 12, 'C' => 60, 'E' => 25] as $column => $width) {
            $guide->getColumnDimension($column)->setWidth($width);
        }
        $guide->getStyle('A3:C9')->getAlignment()->setWrapText(true)->setVertical(Alignment::VERTICAL_TOP);

        if ($categories !== []) {
            $lastReferenceRow = count($categories) + 3;
            $validation = new DataValidation();
            $validation->setType(DataValidation::TYPE_LIST)
                ->setErrorStyle(DataValidation::STYLE_STOP)
                ->setAllowBlank(false)
                ->setShowErrorMessage(true)
                ->setShowDropDown(true)
                ->setErrorTitle('Kategori tidak valid')
                ->setError('Pilih kategori yang tersedia pada sheet Panduan.')
                ->setFormula1("'Panduan'!\$E\$4:\$E\$" . $lastReferenceRow);
            $sheet->getCell('A2')->setDataValidation($validation);
        }

        $spreadsheet->setActiveSheetIndex(0);
        return $spreadsheet;
    }

    public function customerTemplate(): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data Customer');
        $headers = ['kode_customer', 'nama_customer', 'alamat', 'telepon', 'npwp', 'ktp', 'status_aktif'];
        $sheet->fromArray($headers, null, 'A1');
        $sheet->fromArray(['', 'Contoh Customer', 'Alamat customer', '081234567890', '', '', 'Y'], null, 'A2');
        $sheet->setCellValueExplicit('D2', '081234567890', DataType::TYPE_STRING);
        $this->styleDataSheet($sheet, 'A1:G1', [
            'A' => 20, 'B' => 30, 'C' => 38, 'D' => 20, 'E' => 24, 'F' => 24, 'G' => 16,
        ]);
        $sheet->getStyle('A2:A2')->getNumberFormat()->setFormatCode('@');
        $sheet->getStyle('D2:F2')->getNumberFormat()->setFormatCode('@');

        $guide = $spreadsheet->createSheet();
        $guide->setTitle('Panduan');
        $guide->setCellValue('A1', 'PANDUAN IMPORT DATA CUSTOMER');
        $guide->mergeCells('A1:C1');
        $guide->fromArray([
            ['Kolom', 'Wajib', 'Keterangan'],
            ['kode_customer', 'Tidak', 'Kosongkan agar dibuat otomatis dengan format C.xxxxx.'],
            ['nama_customer', 'Ya', 'Nama customer, maksimal 225 karakter.'],
            ['alamat', 'Tidak', 'Alamat customer, maksimal 225 karakter.'],
            ['telepon', 'Tidak', 'Gunakan format teks agar angka 0 di depan tidak hilang.'],
            ['npwp', 'Tidak', 'Gunakan format teks.'],
            ['ktp', 'Tidak', 'Gunakan format teks.'],
            ['status_aktif', 'Tidak', 'Y = aktif, T = nonaktif. Default Y jika dikosongkan.'],
        ], null, 'A3');
        $guide->setCellValue('E1', 'REFERENSI STATUS');
        $guide->fromArray([['status_aktif', 'arti'], ['Y', 'Aktif'], ['T', 'Nonaktif']], null, 'E3');
        $this->styleGuideSheet($guide, ['A1:C1', 'E1:F1'], ['A3:C3', 'E3:F3']);
        foreach (['A' => 23, 'B' => 12, 'C' => 62, 'E' => 18, 'F' => 18] as $column => $width) {
            $guide->getColumnDimension($column)->setWidth($width);
        }
        $guide->getStyle('A3:C10')->getAlignment()->setWrapText(true)->setVertical(Alignment::VERTICAL_TOP);

        $validation = new DataValidation();
        $validation->setType(DataValidation::TYPE_LIST)
            ->setErrorStyle(DataValidation::STYLE_STOP)
            ->setAllowBlank(true)
            ->setShowErrorMessage(true)
            ->setShowDropDown(true)
            ->setErrorTitle('Status tidak valid')
            ->setError('Isi Y untuk aktif atau T untuk nonaktif.')
            ->setFormula1("'Panduan'!\$E\$4:\$E\$5");
        $sheet->getCell('G2')->setDataValidation($validation);

        $spreadsheet->setActiveSheetIndex(0);
        return $spreadsheet;
    }

    public function readRows(UploadedFile $file): array
    {
        $rows = IOFactory::load($file->getRealPath())
            ->getActiveSheet()
            ->toArray(null, true, true, false);

        if (count($rows) < 2) {
            throw new \RuntimeException('File tidak memiliki data untuk diimport.');
        }

        $headers = array_map(function ($value) {
            $value = preg_replace('/^\xEF\xBB\xBF/', '', (string) $value);
            return strtolower(trim($value));
        }, $rows[0]);

        return [$headers, array_slice($rows, 1)];
    }

    private function styleDataSheet($sheet, string $headerRange, array $widths): void
    {
        preg_match('/:([A-Z]+)\d+$/', $headerRange, $matches);
        $lastColumn = $matches[1] ?? 'A';
        $sheet->getStyle($headerRange)->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
        $sheet->getStyle($headerRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF304F9E');
        $sheet->getStyle($headerRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("A2:{$lastColumn}2")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF3F6FC');
        $sheet->freezePane('A2');
        $sheet->setAutoFilter($headerRange);
        foreach ($widths as $column => $width) {
            $sheet->getColumnDimension($column)->setWidth($width);
        }
    }

    private function styleGuideSheet($sheet, array $titleRanges, array $headerRanges): void
    {
        foreach ($titleRanges as $range) {
            $sheet->getStyle($range)->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
            $sheet->getStyle($range)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF304F9E');
            $sheet->getStyle($range)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }
        foreach ($headerRanges as $range) {
            $sheet->getStyle($range)->getFont()->setBold(true);
            $sheet->getStyle($range)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFDCE6F8');
        }
        $sheet->freezePane('A4');
        $sheet->setShowGridlines(false);
    }
}
