<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Export Coretax pajak keluaran.
 *
 * Format dan urutan sheet disamakan persis dengan template upload DJP
 * (Faktur, DetailFaktur, REF, Keterangan).
 */
class LaporanFakturPajakExport
{
    public function __construct(
        private readonly string $tgl1,
        private readonly string $tgl2,
        private readonly string $npwpPenjual,
    ) {
    }

    public function unduh(string $namaFile): StreamedResponse
    {
        $spreadsheet = $this->bangun();
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $namaFile, ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
    }

    public function bangun(): Spreadsheet
    {
        $nota = $this->nota();
        $spreadsheet = new Spreadsheet();
        $spreadsheet->removeSheetByIndex(0);

        $this->isiFaktur($spreadsheet, $nota);
        $this->isiDetail($spreadsheet, $nota);
        $this->isiRef($spreadsheet);
        $this->isiKeterangan($spreadsheet);
        $spreadsheet->setActiveSheetIndex(0);

        return $spreadsheet;
    }

    /**
     * Satu baris per nota: nama dari customer pertama, NPWP/NIK/alamat
     * digabung (customer pertama dulu, lalu customer kedua).
     */
    public function nota(): array
    {
        $rows = DB::select(
            "SELECT a.no_nota, max(a.tgl) as tgl, a.lokasi, a.customer,
                min(a.id_invoice_telur) as idmin,
                b.nm_customer as nm1, b.npwp as npwp1, b.ktp as ktp1, b.alamat as al1,
                c.nm_customer as nm2, c.npwp as npwp2, c.ktp as ktp2, c.alamat as al2,
                sum(a.total_rp) as total_rp
            FROM invoice_telur as a
            left JOIN customer as b on b.id_customer = a.id_customer
            left JOIN customer as c on c.id_customer = a.id_customer2
            WHERE a.tgl between ? and ? and a.lokasi != 'opname'
            GROUP by a.no_nota, a.lokasi, a.customer,
                b.nm_customer, b.npwp, b.ktp, b.alamat,
                c.nm_customer, c.npwp, c.ktp, c.alamat
            ORDER BY idmin", [$this->tgl1, $this->tgl2]);

        return array_map(function ($f) {
            $nama = trim((string) ($f->nm1 ?? ''));
            if ($nama === '') {
                $nama = trim((string) ($f->customer ?? ''));
            }
            $npwp = self::digit16($f->npwp1) ?: self::digit16($f->npwp2);
            $nik = self::digit16($f->ktp1) ?: self::digit16($f->ktp2);
            $alamat = trim((string) ($f->al1 ?? ''));
            if ($alamat === '') {
                $alamat = trim((string) ($f->al2 ?? ''));
            }

            return (object) [
                'no_nota' => (string) $f->no_nota,
                'tgl' => (string) $f->tgl,
                'lokasi' => (string) $f->lokasi,
                'nama' => strtoupper($nama !== '' ? $nama : '-'),
                'npwp' => $npwp,
                'nik' => $nik,
                'alamat' => strtoupper($alamat),
            ];
        }, $rows);
    }

    private static function digit16($value): string
    {
        $digit = preg_replace('/\D/', '', (string) ($value ?? ''));

        return strlen($digit) === 16 ? $digit : '';
    }

    /**
     * Samakan tipe angka dengan template: bulat ditulis sebagai integer,
     * desimal sebagai float.
     */
    private static function angka(float $value): int|float
    {
        return $value == (int) $value ? (int) $value : $value;
    }

    /**
     * Baris detail per nota untuk sheet DetailFaktur.
     *
     * @return array<int, array{jml:float, harga:float, dpp:float, satuan:string}>
     */
    public static function detail(object $nota): array
    {
        return self::detailSemua([$nota])[$nota->no_nota] ?? [];
    }

    /**
     * Baris detail untuk banyak nota sekaligus (3 query, bukan N+1).
     *
     * @param  object[]  $nota
     * @return array<string, array<int, array{jml:float, harga:float, dpp:float, satuan:string}>>
     */
    public static function detailSemua(array $nota): array
    {
        $hasil = [];
        $mtd = [];
        $biasa = [];
        foreach ($nota as $n) {
            $hasil[$n->no_nota] = [];
            if ($n->lokasi === 'mtd') {
                $mtd[] = $n->no_nota;
            } else {
                $biasa[] = $n->no_nota;
            }
        }

        foreach (self::barisBiasa($biasa) as $noNota => $baris) {
            $hasil[$noNota] = $baris;
        }

        if ($mtd !== []) {
            $tanya = implode(',', array_fill(0, count($mtd), '?'));
            $lines = DB::select(
                "SELECT a.no_nota, 'pcs' as jenis, a.pcs_pcs as jml, a.rp_pcs as harga, (a.pcs_pcs * a.rp_pcs) as ttl
                FROM invoice_mtd as a where a.no_nota IN ($tanya)
                UNION ALL
                SELECT a.no_nota, 'ikat' as jenis, (a.kg_ikat - a.ikat) as jml, a.rp_ikat as harga, ((a.kg_ikat - a.ikat) * a.rp_ikat) as ttl
                FROM invoice_mtd as a where a.no_nota IN ($tanya)
                UNION ALL
                SELECT a.no_nota, 'kg' as jenis, a.kg_kg as jml, a.rp_kg as harga, (a.kg_kg * a.rp_kg) as ttl
                FROM invoice_mtd as a where a.no_nota IN ($tanya)", [...$mtd, ...$mtd, ...$mtd]);
            foreach ($lines as $l) {
                $jml = (float) $l->jml;
                $harga = (float) $l->harga;
                if ($jml == 0 && $harga == 0) {
                    continue;
                }
                $hasil[$l->no_nota][] = ['jml' => $jml, 'harga' => $harga, 'dpp' => (float) $l->ttl, 'satuan' => self::satuanMtd($l->jenis)];
            }

            // Nota mtd yang tidak punya baris invoice_mtd (terhapus/beda nomor)
            // memakai baris invoice_telur agar faktur tetap punya detail.
            $kosong = array_values(array_filter($mtd, fn ($noNota) => ($hasil[$noNota] ?? []) === []));
            foreach (self::barisBiasa($kosong) as $noNota => $baris) {
                $hasil[$noNota] = $baris;
            }
        }

        return $hasil;
    }

    /**
     * @param  string[]  $noNota
     * @return array<string, array<int, array{jml:float, harga:float, dpp:float, satuan:string}>>
     */
    private static function barisBiasa(array $noNota): array
    {
        $hasil = [];
        if ($noNota === []) {
            return $hasil;
        }

        $lines = DB::table('invoice_telur as a')
            ->whereIn('a.no_nota', array_values($noNota))
            ->orderBy('a.id_invoice_telur')
            ->get(['a.no_nota', 'a.tipe', 'a.pcs', 'a.kg_jual', 'a.rp_satuan', 'a.total_rp']);
        foreach ($lines as $l) {
            $isKg = strtoupper((string) ($l->tipe ?? '')) === 'KG';
            $hasil[$l->no_nota][] = [
                'jml' => $isKg ? (float) $l->kg_jual : (float) $l->pcs,
                'harga' => (float) $l->rp_satuan,
                'dpp' => (float) $l->total_rp,
                'satuan' => $isKg ? 'UM.0003' : 'UM.0021',
            ];
        }

        return $hasil;
    }

    /**
     * Kode satuan ukur Coretax untuk baris invoice_mtd.
     */
    private static function satuanMtd(?string $jenis): string
    {
        return match ($jenis) {
            'kg' => 'UM.0003',
            'ikat' => 'UM.0018',
            default => 'UM.0021',
        };
    }

    private function isiFaktur(Spreadsheet $spreadsheet, array $nota): void
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Faktur');

        $sheet->mergeCells('A1:B1');
        $sheet->setCellValue('A1', 'NPWP Penjual');
        $sheet->setCellValueExplicit('C1', $this->npwpPenjual, DataType::TYPE_STRING);
        $sheet->getStyle('A1')->getFont()->setBold(true);

        $header = ['Baris', 'Tanggal Faktur', 'Jenis Faktur', 'Kode Transaksi', 'Keterangan Tambahan', 'Dokumen Pendukung', 'Period Dok Pendukung', 'Referensi', 'Cap Fasilitas', 'ID TKU Penjual', 'NPWP/NIK Pembeli', 'Jenis ID Pembeli', 'Negara Pembeli', 'Nomor Dokumen Pembeli', 'Nama Pembeli', 'Alamat Pembeli', 'Email Pembeli', 'ID TKU Pembeli'];
        $sheet->fromArray($header, null, 'A3');
        $sheet->getStyle('A3:R3')->getFont()->setBold(true);

        $baris = 0;
        $row = 4;
        foreach ($nota as $n) {
            $baris++;
            $period = date('mY', strtotime($n->tgl));
            if ($n->npwp !== '') {
                $idPembeli = $n->npwp;
                $jenisId = 'TIN';
                $nomorDok = '0000000000000000';
                $tkuPembeli = $n->npwp.'000000';
            } else {
                $idPembeli = '0000000000000000';
                $jenisId = 'National ID';
                $nomorDok = $n->nik !== '' ? $n->nik : '0000000000000000';
                $tkuPembeli = '0000000000000000000000';
            }

            $sheet->setCellValue('A'.$row, $baris);
            $sheet->setCellValue('B'.$row, (int) ExcelDate::convertIsoDate($n->tgl));
            $sheet->setCellValue('C'.$row, 'Normal');
            $sheet->setCellValueExplicit('D'.$row, '08', DataType::TYPE_STRING);
            $sheet->setCellValue('E'.$row, 'TD.00501');
            $sheet->setCellValue('F'.$row, '-');
            $sheet->setCellValueExplicit('G'.$row, $period, DataType::TYPE_STRING);
            $sheet->setCellValue('H'.$row, null);
            $sheet->setCellValue('I'.$row, 'TD.01101');
            $sheet->setCellValueExplicit('J'.$row, $this->npwpPenjual.'000000', DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('K'.$row, $idPembeli, DataType::TYPE_STRING);
            $sheet->setCellValue('L'.$row, $jenisId);
            $sheet->setCellValue('M'.$row, 'IDN');
            $sheet->setCellValueExplicit('N'.$row, $nomorDok, DataType::TYPE_STRING);
            $sheet->setCellValue('O'.$row, $n->nama);
            $sheet->setCellValue('P'.$row, $n->alamat);
            $sheet->setCellValue('Q'.$row, null);
            $sheet->setCellValueExplicit('R'.$row, $tkuPembeli, DataType::TYPE_STRING);
            $row++;
        }

        $akhir = $row - 1;
        $sheet->getStyle('B4:B'.$akhir)->getNumberFormat()->setFormatCode('m/d/yyyy');
        foreach (['D', 'G', 'J', 'K', 'N', 'R'] as $kol) {
            $sheet->getStyle($kol.'4:'.$kol.$akhir)->getNumberFormat()->setFormatCode('@');
        }
        $sheet->setAutoFilter('A3:R'.$akhir);

        self::validasi($sheet, 'C4:C'.$akhir, '"Normal"');
        self::validasi($sheet, 'D4:D'.$akhir, 'REF!$A$6:$A$15');
        self::validasi($sheet, 'E4:E'.$akhir, 'REF!$A$17:$A$54');
        self::validasi($sheet, 'I4:I'.$akhir, 'REF!$A$56:$A$93');
        self::validasi($sheet, 'M4:M'.$akhir, 'REF!$A$135:$A$386');

        foreach (['B' => 16.1, 'C' => 11, 'D' => 17.3, 'E' => 18.4, 'F' => 29, 'G' => 22.6, 'H' => 11, 'I' => 18.9, 'J' => 29.7, 'K' => 21.1, 'L' => 27.7, 'M' => 21.4, 'N' => 29.1, 'O' => 12, 'P' => 23.4, 'Q' => 22, 'R' => 23.4] as $kol => $lebar) {
            $sheet->getColumnDimension($kol)->setWidth($lebar);
        }
    }

    private function isiDetail(Spreadsheet $spreadsheet, array $nota): void
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('DetailFaktur');

        $sheet->fromArray(['Baris', 'Barang/Jasa', 'Kode Barang Jasa', 'Nama Barang/Jasa', 'Nama Satuan Ukur', 'Harga Satuan', 'Jumlah Barang Jasa', 'Total Diskon', 'DPP', 'DPP Nilai Lain', 'Tarif PPN', 'PPN', 'Tarif PPnBM', 'PPnBM'], null, 'A1');

        $baris = 0;
        $row = 2;
        $detailMap = self::detailSemua($nota);
        foreach ($nota as $n) {
            $baris++;
            foreach ($detailMap[$n->no_nota] ?? [] as $d) {
                // DPP Nilai Lain dikeluarkan dari DPP (harga sudah termasuk
                // PPN) sehingga DPP Nilai Lain + PPN (12%) = DPP.
                $dppNilaiLain = $d['dpp'] / 1.12;
                $sheet->setCellValue('A'.$row, $baris);
                $sheet->setCellValue('B'.$row, 'A');
                $sheet->setCellValueExplicit('C'.$row, '040700', DataType::TYPE_STRING);
                $sheet->setCellValue('D'.$row, 'Telur Utuh');
                $sheet->setCellValue('E'.$row, $d['satuan'] ?? 'UM.0003');
                $sheet->setCellValue('F'.$row, self::angka($d['harga']));
                $sheet->setCellValue('G'.$row, self::angka($d['jml']));
                $sheet->setCellValue('H'.$row, 0);
                $sheet->setCellValue('I'.$row, self::angka($d['dpp']));
                $sheet->setCellValue('J'.$row, self::angka($dppNilaiLain));
                $sheet->setCellValue('K'.$row, 12);
                $sheet->setCellValue('L'.$row, self::angka($dppNilaiLain * 0.12));
                $sheet->setCellValue('M'.$row, 0);
                $sheet->setCellValue('N'.$row, 0);
                $row++;
            }
        }

        $akhir = $row - 1;
        $sheet->getStyle('G2:G'.$akhir)->getNumberFormat()->setFormatCode('0');
        foreach (['H', 'I', 'J', 'L', 'N'] as $kol) {
            $sheet->getStyle($kol.'2:'.$kol.$akhir)->getNumberFormat()->setFormatCode('0.00');
        }
        $sheet->setAutoFilter('A1:N'.$akhir);

        self::validasi($sheet, 'B2:B'.$akhir, 'REF!$A$3:$A$4');
        self::validasi($sheet, 'E2:E'.$akhir, 'REF!$A$100:$A$132');
        self::validasi($sheet, 'K2:K'.$akhir, '12');

        foreach (['C' => 16.9, 'D' => 17.3, 'E' => 17.4, 'F' => 14.3, 'G' => 21.9, 'H' => 11.9, 'I' => 15.6, 'J' => 13.3, 'L' => 11.6, 'M' => 11.7, 'N' => 13.7] as $kol => $lebar) {
            $sheet->getColumnDimension($kol)->setWidth($lebar);
        }
    }

    private static function validasi($sheet, string $range, string $formula): void
    {
        $validation = $sheet->getCell(explode(':', $range)[0])->getDataValidation();
        $validation->setType(DataValidation::TYPE_LIST);
        $validation->setAllowBlank(true);
        $validation->setShowDropDown(false);
        $validation->setShowInputMessage(false);
        $validation->setShowErrorMessage(false);
        $validation->setFormula1($formula);
        $validation->setSqref($range);
    }

    private function isiRef(Spreadsheet $spreadsheet): void
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('REF');

        $row = 1;
        foreach (self::refRows() as $cells) {
            $cells = array_values($cells);
            // Kolom A berisi kode referensi: paksa string agar '01'..'10'
            // tidak berubah menjadi angka. Sel kosong dibiarkan kosong.
            if (($cells[0] ?? null) !== null && $cells[0] !== '') {
                $sheet->setCellValueExplicit('A'.$row, (string) $cells[0], DataType::TYPE_STRING);
            }
            $sheet->setCellValue('B'.$row, $cells[1] ?? null);
            $sheet->setCellValue('C'.$row, $cells[2] ?? null);
            $row++;
        }

        foreach (['A2:B2', 'A5:B5', 'A16:B16', 'A55:B55', 'A94:B94', 'A99:B99', 'A134:B134', 'C18:C44', 'C45:C54', 'C57:C83', 'C84:C93'] as $range) {
            $sheet->mergeCells($range);
        }
        foreach ([2, 5, 16, 55, 94, 99, 134] as $r) {
            $sheet->getStyle('A'.$r.':B'.$r)->getFill()
                ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFFF00');
        }

        $sheet->getColumnDimension('A')->setWidth(16.6);
        $sheet->getColumnDimension('B')->setWidth(108.3);
    }

    private function isiKeterangan(Spreadsheet $spreadsheet): void
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Keterangan');

        $row = 1;
        foreach (self::keteranganRows() as $cells) {
            $sheet->fromArray(array_values($cells), null, 'A'.$row);
            $row++;
        }

        $sheet->mergeCells('A2:B2');
        $sheet->mergeCells('A21:B21');
        for ($r = 2; $r <= 21; $r++) {
            $sheet->getStyle('A'.$r)->getFont()->setBold(true);
        }

        $sheet->getColumnDimension('A')->setWidth(37);
        $sheet->getColumnDimension('B')->setWidth(18.6);
        $sheet->getColumnDimension('C')->setWidth(18.6);
        $sheet->getColumnDimension('D')->setWidth(94.7);
    }

    private static function refRows(): array
    {
        return [
            0 => [
                0 => 'Kode',
                1 => 'Keterangan',
                2 => null,
            ],
            1 => [
                0 => 'Barang/Jasa',
                1 => null,
                2 => null,
            ],
            2 => [
                0 => 'A',
                1 => 'Barang',
                2 => null,
            ],
            3 => [
                0 => 'B',
                1 => 'Jasa',
                2 => null,
            ],
            4 => [
                0 => 'Kode Transaksi',
                1 => null,
                2 => null,
            ],
            5 => [
                0 => '01',
                1 => '01 - kepada selain Pemungut PPN',
                2 => null,
            ],
            6 => [
                0 => '02',
                1 => '02 - kepada Pemungut PPN Instansi Pemerintah',
                2 => null,
            ],
            7 => [
                0 => '03',
                1 => '03 - kepada Pemungut PPN selain Instansi Pemerintah',
                2 => null,
            ],
            8 => [
                0 => '04',
                1 => '04 - DPP Nilai Lain',
                2 => null,
            ],
            9 => [
                0 => '05',
                1 => '05 - Besaran tertentu',
                2 => null,
            ],
            10 => [
                0 => '06',
                1 => '06 - kepada orang pribadi pemegang paspor luar negeri (16E UU PPN)',
                2 => null,
            ],
            11 => [
                0 => '07',
                1 => '07 - penyerahan dengan fasilitas PPN atau PPN dan PPnBM tidak dipungut/ditanggung pemerintah',
                2 => null,
            ],
            12 => [
                0 => '08',
                1 => '08 -  penyerahan dengan fasilitas dibebaskan PPN atau PPN dan PPnBM',
                2 => null,
            ],
            13 => [
                0 => '09',
                1 => '09 - penyerahan aktiva yang menurut tujuan semula tidak diperjualbelikan (16D UU PPN)',
                2 => null,
            ],
            14 => [
                0 => '10',
                1 => '10 - Penyerahan lainnya',
                2 => null,
            ],
            15 => [
                0 => 'Keterangan Tambahan',
                1 => null,
                2 => null,
            ],
            16 => [
                0 => null,
                1 => 'Tidak Ada',
                2 => null,
            ],
            17 => [
                0 => 'TD.00501',
                1 => '1 - Pajak Pertambahan Nilai Tidak Dipungut berdasarkan PP Nomor 10 Tahun 2012',
                2 => 'Kode Transaksi 07',
            ],
            18 => [
                0 => 'TD.00502',
                1 => '2 - Pajak Pertambahan Nilai atau Pajak Pertambahan Nilai dan Pajak Penjualan atas Barang Mewah tidak dipungut',
                2 => null,
            ],
            19 => [
                0 => 'TD.00503',
                1 => '3 - Pajak Pertambahan Nilai dan Pajak Penjualan atas Barang Mewah Tidak Dipungut',
                2 => null,
            ],
            20 => [
                0 => 'TD.00504',
                1 => '4 - Pajak Pertambahan Nilai Tidak Dipungut Sesuai PP Nomor 71 Tahun 2012',
                2 => null,
            ],
            21 => [
                0 => 'TD.00505',
                1 => '5 - (Tidak ada Cap)',
                2 => null,
            ],
            22 => [
                0 => 'TD.00506',
                1 => '6 - PPN dan/atau PPnBM tidak dipungut berdasarkan PMK No. 194/PMK.03/2012',
                2 => null,
            ],
            23 => [
                0 => 'TD.00507',
                1 => '7 - PPN Tidak Dipungut Berdasarkan PP Nomor 15 Tahun 2015',
                2 => null,
            ],
            24 => [
                0 => 'TD.00508',
                1 => '8 - PPN Tidak Dipungut Berdasarkan PP Nomor 69 Tahun 2015',
                2 => null,
            ],
            25 => [
                0 => 'TD.00509',
                1 => '9 - PPN Tidak Dipungut Berdasarkan PP Nomor 96 Tahun 2015',
                2 => null,
            ],
            26 => [
                0 => 'TD.00510',
                1 => '10 - PPN Tidak Dipungut Berdasarkan PP Nomor 106 Tahun 2015',
                2 => null,
            ],
            27 => [
                0 => 'TD.00511',
                1 => '11 - PPN Tidak Dipungut Sesuai PP Nomor 50 Tahun 2019',
                2 => null,
            ],
            28 => [
                0 => 'TD.00512',
                1 => '12 - PPN atau PPN dan PPnBM Tidak Dipungut Sesuai Dengan PP Nomor 27 Tahun 2017',
                2 => null,
            ],
            29 => [
                0 => 'TD.00513',
                1 => '13 - PPN ditanggung PEMERINTAH EX PMK 21/PMK.010/21',
                2 => null,
            ],
            30 => [
                0 => 'TD.00514',
                1 => '14 - PPN DITANGGUNG PEMERINTAH EKS PMK 102/PMK.010/2021',
                2 => null,
            ],
            31 => [
                0 => 'TD.00515',
                1 => '15 - PPN DITANGGUNG PEMERINTAH EKS PMK 239/PMK.03/2020',
                2 => null,
            ],
            32 => [
                0 => 'TD.00516',
                1 => '16 - Insentif PPN DITANGGUNG PEMERINTAH EKSEKUSI PMK NOMOR 103/PMK.010/2021',
                2 => null,
            ],
            33 => [
                0 => 'TD.00517',
                1 => '17 - PAJAK PERTAMBAHAN NILAI TIDAK DIPUNGUT BERDASARKAN PP NOMOR 40 TAHUN 2021',
                2 => null,
            ],
            34 => [
                0 => 'TD.00518',
                1 => '18 - PAJAK PERTAMBAHAN NILAI TIDAK DIPUNGUT BERDASARKAN PP NOMOR 41 TAHUN 2021',
                2 => null,
            ],
            35 => [
                0 => 'TD.00519',
                1 => '19 - PPN DITANGGUNG PEMERINTAH EKS PMK 6/PMK.010/2022',
                2 => null,
            ],
            36 => [
                0 => 'TD.00520',
                1 => '20 - PPN DITANGGUNG PEMERINTAH EKSEKUSI PMK NOMOR 226/PMK.03/2021',
                2 => null,
            ],
            37 => [
                0 => 'TD.00521',
                1 => '21 - PPN ATAU PPN DAN PPnBM TIDAK DIPUNGUT SESUAI DENGAN PP NOMOR 53 TAHUN 2017',
                2 => null,
            ],
            38 => [
                0 => 'TD.00522',
                1 => '22 - PPN tidak dipungut berdasarkan PP Nomor 70 Tahun 2021',
                2 => null,
            ],
            39 => [
                0 => 'TD.00523',
                1 => '23 - PPN ditanggung Pemerintah Ex PMK-125/PMK.01/2020',
                2 => null,
            ],
            40 => [
                0 => 'TD.00524',
                1 => '24 - (Tidak ada Cap)',
                2 => null,
            ],
            41 => [
                0 => 'TD.00525',
                1 => '25 - PPN tidak dipungut berdasarkan PP Nomor 49 Tahun 2022',
                2 => null,
            ],
            42 => [
                0 => 'TD.00526',
                1 => '26 - PPN tidak dipungut berdasarkan PP Nomor 12 Tahun 2023',
                2 => null,
            ],
            43 => [
                0 => 'TD.00527',
                1 => '27 - PPN ditanggung Pemerintah berdasarkan PMK Nomor 38 Tahun 2023',
                2 => null,
            ],
            44 => [
                0 => 'TD.00501',
                1 => '1 - PPN Dibebaskan Sesuai PP Nomor 146 Tahun 2000 Sebagaimana Telah Diubah Dengan PP Nomor 38 Tahun 2003',
                2 => 'Kode Transaksi 08',
            ],
            45 => [
                0 => 'TD.00502',
                1 => '2 - PPN Dibebaskan Sesuai PP Nomor 12 Tahun 2001 Sebagaimana Telah Beberapa Kali Diubah Terakhir Dengan PP Nomor 31 Tahun 2007',
                2 => null,
            ],
            46 => [
                0 => 'TD.00503',
                1 => '3 - PPN dibebaskan berdasarkan Peraturan Pemerintah Nomor 28 Tahun 2009',
                2 => null,
            ],
            47 => [
                0 => 'TD.00504',
                1 => '4 - (Tidak ada cap)',
                2 => null,
            ],
            48 => [
                0 => 'TD.00505',
                1 => '5 - PPN Dibebaskan Sesuai Dengan PP Nomor 81 Tahun 2015',
                2 => null,
            ],
            49 => [
                0 => 'TD.00506',
                1 => '6 - PPN Dibebaskan Berdasarkan PP Nomor 74 Tahun 2015',
                2 => null,
            ],
            50 => [
                0 => 'TD.00507',
                1 => '7 - (tanpa cap)',
                2 => null,
            ],
            51 => [
                0 => 'TD.00508',
                1 => '8 - PPN DIBEBASKAN SESUAI PP NOMOR 81 TAHUN 2015 SEBAGAIMANA TELAH DIUBAH DENGAN PP 48 TAHUN 2020',
                2 => null,
            ],
            52 => [
                0 => 'TD.00509',
                1 => '9 - PPN DIBEBASKAN BERDASARKAN PP NOMOR 47 TAHUN 2020',
                2 => null,
            ],
            53 => [
                0 => 'TD.00510',
                1 => '10  -PPN Dibebaskan berdasarkan PP Nomor 49 Tahun 2022',
                2 => null,
            ],
            54 => [
                0 => 'Cap Fasilitas',
                1 => null,
                2 => null,
            ],
            55 => [
                0 => null,
                1 => 'Tidak Ada',
                2 => null,
            ],
            56 => [
                0 => 'TD.01101',
                1 => '1 - untuk Kawasan Bebas',
                2 => 'Kode Transaksi 07',
            ],
            57 => [
                0 => 'TD.01102',
                1 => '2 - untuk Tempat Penimbunan Berikat',
                2 => null,
            ],
            58 => [
                0 => 'TD.01103',
                1 => '3 - untuk Hibah dan Bantuan Luar Negeri',
                2 => null,
            ],
            59 => [
                0 => 'TD.01104',
                1 => '4 - untuk Avtur',
                2 => null,
            ],
            60 => [
                0 => 'TD.01105',
                1 => '5 - untuk Lainnya',
                2 => null,
            ],
            61 => [
                0 => 'TD.01106',
                1 => '6 - untuk Kontraktor Perjanjian Karya Pengusahaan Pertambangan Batubara Generasi I',
                2 => null,
            ],
            62 => [
                0 => 'TD.01107',
                1 => '7 - untuk Penyerahan bahan bakar minyak untuk Kapal Angkutan Laut Luar Negeri',
                2 => null,
            ],
            63 => [
                0 => 'TD.01108',
                1 => '8 - untuk Penyerahan jasa kena pajak terkait alat angkutan tertentu',
                2 => null,
            ],
            64 => [
                0 => 'TD.01109',
                1 => '9 - untuk Penyerahan BKP Tertentu di KEK',
                2 => null,
            ],
            65 => [
                0 => 'TD.01110',
                1 => '10 - untuk BKP tertentu yang bersifat strategis berupa anode slime',
                2 => null,
            ],
            66 => [
                0 => 'TD.01111',
                1 => '11 - untuk Penyerahan alat angkutan tertentu dan/atau Jasa Kena Pajak terkait alat angkutan tertentu',
                2 => null,
            ],
            67 => [
                0 => 'TD.01112',
                1 => '12 - untuk Penyerahan kepada Kontraktor Kerja Sama Migas yang mengikuti ketentuan Peraturan Pemerintah Nomor 27 Tahun 2017',
                2 => null,
            ],
            68 => [
                0 => 'TD.01113',
                1 => '13 - Penyerahan Rumah Tapak dan Satuan Rumah Susun Rumah Susun Ditanggung Pemerintah Tahun Anggaran 2021',
                2 => null,
            ],
            69 => [
                0 => 'TD.01114',
                1 => '14 - Penyerahan Jasa Sewa Ruangan atau Bangunan Kepada Pedagang Eceran yang Ditanggung Pemerintah Tahun Anggaran 2021',
                2 => null,
            ],
            70 => [
                0 => 'TD.01115',
                1 => '15 - Penyerahan Barang dan Jasa Dalam Rangka Penanganan Pandemi COVID-19 (PMK 239/PMK. 03/2020)',
                2 => null,
            ],
            71 => [
                0 => 'TD.01116',
                1 => '16 - Insentif PMK-103/PMK.010/2021 berupa PPN atas Penyerahan Rumah Tapak dan Unit Hunian Rumah Susun yang Ditanggung Pemerintah Tahun Anggaran 2021',
                2 => null,
            ],
            72 => [
                0 => 'TD.01117',
                1 => '17 - Kawasan Ekonomi Khusus PP nomor 40 Tahun 2021',
                2 => null,
            ],
            73 => [
                0 => 'TD.01118',
                1 => '18 - Kawasan Bebas PP nomor 41 Tahun 2021',
                2 => null,
            ],
            74 => [
                0 => 'TD.01119',
                1 => '19 - Penyerahan Rumah Tapak dan Unit Hunian Rumah Susun yang Ditanggung Pemerintah Tahun Anggaran 2022',
                2 => null,
            ],
            75 => [
                0 => 'TD.01120',
                1 => '20 - PPN Ditanggung Pemerintah dalam rangka Penanganan Pandemi Corona Virus',
                2 => null,
            ],
            76 => [
                0 => 'TD.01121',
                1 => '21 - Penyerahan kepada Kontraktor Kerja Sama Migas yang mengikuti ketentuan Peraturan Pemerintah Nomor 53 Tahun 2017',
                2 => null,
            ],
            77 => [
                0 => 'TD.01122',
                1 => '22 - BKP strategis tertentu dalam bentuk anode slime dan emas butiran',
                2 => null,
            ],
            78 => [
                0 => 'TD.01123',
                1 => '23 - untuk penyerahan kertas koran dan/atau majalah',
                2 => null,
            ],
            79 => [
                0 => 'TD.01124',
                1 => '24 - PPN tidak dipungut oleh Pemerintah lainnya',
                2 => null,
            ],
            80 => [
                0 => 'TD.01125',
                1 => '25 - BKP dan JKP tertentu',
                2 => null,
            ],
            81 => [
                0 => 'TD.01126',
                1 => '26 - Penyerahan BKP dan JKP di Ibu Kota Negara baru',
                2 => null,
            ],
            82 => [
                0 => 'TD.01127',
                1 => '27 - Penyerahan kendaraan listrik berbasis baterai',
                2 => null,
            ],
            83 => [
                0 => 'TD.01101',
                1 => '1 - untuk BKP dan JKP Tertentu',
                2 => 'Kode Transaksi 08',
            ],
            84 => [
                0 => 'TD.01102',
                1 => '2 - untuk BKP Tertentu yang Bersifat Strategis',
                2 => null,
            ],
            85 => [
                0 => 'TD.01103',
                1 => '3 - untuk Jasa Kebandarudaraan',
                2 => null,
            ],
            86 => [
                0 => 'TD.01104',
                1 => '4 - untuk Lainnya',
                2 => null,
            ],
            87 => [
                0 => 'TD.01105',
                1 => '5 - untuk BKP Tertentu yang Bersifat Strategis sesuai PP Nomor 81 Tahun 2015',
                2 => null,
            ],
            88 => [
                0 => 'TD.01106',
                1 => '6 - untuk Penyerahan Jasa Kepelabuhan Tertentu untuk kegiatan angkutan laut Luar Negeri',
                2 => null,
            ],
            89 => [
                0 => 'TD.01107',
                1 => '7 - untuk Penyerahan Air Bersih',
                2 => null,
            ],
            90 => [
                0 => 'TD.01108',
                1 => '8 - Penyerahan BKP tertentu yang bersifat strategis berdasarkan PP 48 Tahun 2020',
                2 => null,
            ],
            91 => [
                0 => 'TD.01109',
                1 => '9 - Penyerahan kepada Perwakilan Negara Asing dan Badan Internasional serta Pejabatnya',
                2 => null,
            ],
            92 => [
                0 => 'TD.01110',
                1 => '10 - BKP dan JKP tertentu',
                2 => null,
            ],
            93 => [
                0 => 'Jenis ID Pembeli',
                1 => null,
                2 => null,
            ],
            94 => [
                0 => 'TIN',
                1 => 'NPWP ',
                2 => null,
            ],
            95 => [
                0 => 'National ID',
                1 => 'NIK',
                2 => null,
            ],
            96 => [
                0 => 'Passport',
                1 => 'Paspor',
                2 => null,
            ],
            97 => [
                0 => 'Other ID',
                1 => 'Dokumen Lainnya',
                2 => null,
            ],
            98 => [
                0 => 'Satuan Ukur',
                1 => null,
                2 => null,
            ],
            99 => [
                0 => 'UM.0003',
                1 => 'Kilogram',
                2 => null,
            ],
            100 => [
                0 => 'UM.0004',
                1 => 'Gram',
                2 => null,
            ],
            101 => [
                0 => 'UM.0005',
                1 => 'Karat',
                2 => null,
            ],
            102 => [
                0 => 'UM.0001',
                1 => 'Metrik Ton',
                2 => null,
            ],
            103 => [
                0 => 'UM.0002',
                1 => 'Wet Ton',
                2 => null,
            ],
            104 => [
                0 => 'UM.0006',
                1 => 'Kiloliter',
                2 => null,
            ],
            105 => [
                0 => 'UM.0007',
                1 => 'Liter',
                2 => null,
            ],
            106 => [
                0 => 'UM.0008',
                1 => 'Barrel',
                2 => null,
            ],
            107 => [
                0 => 'UM.0009',
                1 => 'MMBTU',
                2 => null,
            ],
            108 => [
                0 => 'UM.0010',
                1 => 'Ampere',
                2 => null,
            ],
            109 => [
                0 => 'UM.0011',
                1 => 'Sentimeter Kubik',
                2 => null,
            ],
            110 => [
                0 => 'UM.0012',
                1 => 'Meter Persegi',
                2 => null,
            ],
            111 => [
                0 => 'UM.0013',
                1 => 'Meter',
                2 => null,
            ],
            112 => [
                0 => 'UM.0014',
                1 => 'Inches',
                2 => null,
            ],
            113 => [
                0 => 'UM.0015',
                1 => 'Sentimeter',
                2 => null,
            ],
            114 => [
                0 => 'UM.0016',
                1 => 'Yard',
                2 => null,
            ],
            115 => [
                0 => 'UM.0017',
                1 => 'Lusin',
                2 => null,
            ],
            116 => [
                0 => 'UM.0018',
                1 => 'Unit',
                2 => null,
            ],
            117 => [
                0 => 'UM.0019',
                1 => 'Set',
                2 => null,
            ],
            118 => [
                0 => 'UM.0020',
                1 => 'Lembar',
                2 => null,
            ],
            119 => [
                0 => 'UM.0021',
                1 => 'Piece',
                2 => null,
            ],
            120 => [
                0 => 'UM.0022',
                1 => 'Boks',
                2 => null,
            ],
            121 => [
                0 => 'UM.0023',
                1 => 'Tahun',
                2 => null,
            ],
            122 => [
                0 => 'UM.0024',
                1 => 'Bulan',
                2 => null,
            ],
            123 => [
                0 => 'UM.0025',
                1 => 'Minggu',
                2 => null,
            ],
            124 => [
                0 => 'UM.0026',
                1 => 'Hari',
                2 => null,
            ],
            125 => [
                0 => 'UM.0027',
                1 => 'Jam',
                2 => null,
            ],
            126 => [
                0 => 'UM.0028',
                1 => 'Menit',
                2 => null,
            ],
            127 => [
                0 => 'UM.0029',
                1 => 'Persen',
                2 => null,
            ],
            128 => [
                0 => 'UM.0030',
                1 => 'Kegiatan',
                2 => null,
            ],
            129 => [
                0 => 'UM.0031',
                1 => 'Laporan',
                2 => null,
            ],
            130 => [
                0 => 'UM.0032',
                1 => 'Bahan',
                2 => null,
            ],
            131 => [
                0 => 'UM.0033',
                1 => 'Lainnya',
                2 => null,
            ],
            132 => [
                0 => null,
                1 => null,
                2 => null,
            ],
            133 => [
                0 => 'Kode Negara',
                1 => null,
                2 => null,
            ],
            134 => [
                0 => 'IDN',
                1 => 'Indonesia',
                2 => null,
            ],
            135 => [
                0 => 'AUS',
                1 => 'Australia',
                2 => null,
            ],
            136 => [
                0 => 'ABW',
                1 => 'Aruba',
                2 => null,
            ],
            137 => [
                0 => 'AFG',
                1 => 'Afghanistan',
                2 => null,
            ],
            138 => [
                0 => 'AGO',
                1 => 'Angola',
                2 => null,
            ],
            139 => [
                0 => 'AIA',
                1 => 'Anguilla',
                2 => null,
            ],
            140 => [
                0 => 'ALA',
                1 => 'Aland Islands',
                2 => null,
            ],
            141 => [
                0 => 'ALB',
                1 => 'Albania',
                2 => null,
            ],
            142 => [
                0 => 'AND',
                1 => 'Andorra',
                2 => null,
            ],
            143 => [
                0 => 'ARE',
                1 => 'United Arab Emirates',
                2 => null,
            ],
            144 => [
                0 => 'ARG',
                1 => 'Argentina',
                2 => null,
            ],
            145 => [
                0 => 'ARM',
                1 => 'Armenia',
                2 => null,
            ],
            146 => [
                0 => 'ASM',
                1 => 'American Samoa',
                2 => null,
            ],
            147 => [
                0 => 'ATA',
                1 => 'Antarctica',
                2 => null,
            ],
            148 => [
                0 => 'ATF',
                1 => 'French Southern Territories',
                2 => null,
            ],
            149 => [
                0 => 'ATG',
                1 => 'Antigua and Barbuda',
                2 => null,
            ],
            150 => [
                0 => 'AUT',
                1 => 'Austria',
                2 => null,
            ],
            151 => [
                0 => 'AZE',
                1 => 'Azerbaijan',
                2 => null,
            ],
            152 => [
                0 => 'BDI',
                1 => 'Burundi',
                2 => null,
            ],
            153 => [
                0 => 'BEL',
                1 => 'Belgium',
                2 => null,
            ],
            154 => [
                0 => 'BEN',
                1 => 'Benin',
                2 => null,
            ],
            155 => [
                0 => 'BES',
                1 => 'Bonaire, Sint Eustatius and Saba',
                2 => null,
            ],
            156 => [
                0 => 'BFA',
                1 => 'Burkina Faso',
                2 => null,
            ],
            157 => [
                0 => 'BGD',
                1 => 'Bangladesh',
                2 => null,
            ],
            158 => [
                0 => 'BGR',
                1 => 'Bulgaria',
                2 => null,
            ],
            159 => [
                0 => 'BHR',
                1 => 'Bahrain',
                2 => null,
            ],
            160 => [
                0 => 'BHS',
                1 => 'Bahamas',
                2 => null,
            ],
            161 => [
                0 => 'BIH',
                1 => 'Bosnia and Herzegovina',
                2 => null,
            ],
            162 => [
                0 => 'BLM',
                1 => 'Saint Barthelemy',
                2 => null,
            ],
            163 => [
                0 => 'BLR',
                1 => 'Belarus',
                2 => null,
            ],
            164 => [
                0 => 'BLZ',
                1 => 'Belize',
                2 => null,
            ],
            165 => [
                0 => 'BMU',
                1 => 'Bermuda',
                2 => null,
            ],
            166 => [
                0 => 'BOL',
                1 => 'Bolivia, Plurinational State of',
                2 => null,
            ],
            167 => [
                0 => 'BRA',
                1 => 'Brazil',
                2 => null,
            ],
            168 => [
                0 => 'BRB',
                1 => 'Barbados',
                2 => null,
            ],
            169 => [
                0 => 'BRN',
                1 => 'Brunei Darussalam',
                2 => null,
            ],
            170 => [
                0 => 'BTN',
                1 => 'Bhutan',
                2 => null,
            ],
            171 => [
                0 => 'BVT',
                1 => 'Bouvet Island',
                2 => null,
            ],
            172 => [
                0 => 'BWA',
                1 => 'Botswana',
                2 => null,
            ],
            173 => [
                0 => 'CAF',
                1 => 'Central African Republic',
                2 => null,
            ],
            174 => [
                0 => 'CAN',
                1 => 'Canada',
                2 => null,
            ],
            175 => [
                0 => 'CCK',
                1 => 'Cocos (Keeling) Islands',
                2 => null,
            ],
            176 => [
                0 => 'CHE',
                1 => 'Switzerland',
                2 => null,
            ],
            177 => [
                0 => 'CHL',
                1 => 'Chile',
                2 => null,
            ],
            178 => [
                0 => 'CHN',
                1 => 'China',
                2 => null,
            ],
            179 => [
                0 => 'CIV',
                1 => 'Cote dIvoire',
                2 => null,
            ],
            180 => [
                0 => 'CMR',
                1 => 'Cameroon',
                2 => null,
            ],
            181 => [
                0 => 'COD',
                1 => 'Congo, Democratic Republic of the',
                2 => null,
            ],
            182 => [
                0 => 'COG',
                1 => 'Congo',
                2 => null,
            ],
            183 => [
                0 => 'COK',
                1 => 'Cook Islands',
                2 => null,
            ],
            184 => [
                0 => 'COL',
                1 => 'Colombia',
                2 => null,
            ],
            185 => [
                0 => 'COM',
                1 => 'Comoros',
                2 => null,
            ],
            186 => [
                0 => 'CPV',
                1 => 'Cabo Verde',
                2 => null,
            ],
            187 => [
                0 => 'CRI',
                1 => 'Costa Rica',
                2 => null,
            ],
            188 => [
                0 => 'CUB',
                1 => 'Cuba',
                2 => null,
            ],
            189 => [
                0 => 'CUW',
                1 => 'Curacao',
                2 => null,
            ],
            190 => [
                0 => 'CXR',
                1 => 'Christmas Island',
                2 => null,
            ],
            191 => [
                0 => 'CY',
                1 => 'Cyprus',
                2 => null,
            ],
            192 => [
                0 => 'CYM',
                1 => 'Cayman Islands',
                2 => null,
            ],
            193 => [
                0 => 'CZE',
                1 => 'Czech Republic',
                2 => null,
            ],
            194 => [
                0 => 'DEU',
                1 => 'Germany',
                2 => null,
            ],
            195 => [
                0 => 'DJI',
                1 => 'Djibouti',
                2 => null,
            ],
            196 => [
                0 => 'DMA',
                1 => 'Dominica',
                2 => null,
            ],
            197 => [
                0 => 'DNK',
                1 => 'Denmark',
                2 => null,
            ],
            198 => [
                0 => 'DOM',
                1 => 'Dominican Republic',
                2 => null,
            ],
            199 => [
                0 => 'DZA',
                1 => 'Algeria',
                2 => null,
            ],
            200 => [
                0 => 'ECU',
                1 => 'Ecuador',
                2 => null,
            ],
            201 => [
                0 => 'EGY',
                1 => 'Egypt',
                2 => null,
            ],
            202 => [
                0 => 'ERI',
                1 => 'Eritrea',
                2 => null,
            ],
            203 => [
                0 => 'ESH',
                1 => 'Western Sahara',
                2 => null,
            ],
            204 => [
                0 => 'ESP',
                1 => 'Spain',
                2 => null,
            ],
            205 => [
                0 => 'EST',
                1 => 'Estonia',
                2 => null,
            ],
            206 => [
                0 => 'ETH',
                1 => 'Ethiopia',
                2 => null,
            ],
            207 => [
                0 => 'FIN',
                1 => 'Finland',
                2 => null,
            ],
            208 => [
                0 => 'FJ',
                1 => 'Fiji',
                2 => null,
            ],
            209 => [
                0 => 'FLK',
                1 => 'Falkland Islands (Malvinas)',
                2 => null,
            ],
            210 => [
                0 => 'FRA',
                1 => 'France',
                2 => null,
            ],
            211 => [
                0 => 'FRO',
                1 => 'Faroe Islands',
                2 => null,
            ],
            212 => [
                0 => 'FSM',
                1 => 'Micronesia, Federated States of',
                2 => null,
            ],
            213 => [
                0 => 'GAB',
                1 => 'Gabon',
                2 => null,
            ],
            214 => [
                0 => 'GBR',
                1 => 'United Kingdom',
                2 => null,
            ],
            215 => [
                0 => 'GEO',
                1 => 'Georgia',
                2 => null,
            ],
            216 => [
                0 => 'GGY',
                1 => 'Guernsey',
                2 => null,
            ],
            217 => [
                0 => 'GHA',
                1 => 'Ghana',
                2 => null,
            ],
            218 => [
                0 => 'GIB',
                1 => 'Gibraltar',
                2 => null,
            ],
            219 => [
                0 => 'GIN',
                1 => 'Guinea',
                2 => null,
            ],
            220 => [
                0 => 'GLP',
                1 => 'Guadeloupe',
                2 => null,
            ],
            221 => [
                0 => 'GMB',
                1 => 'Gambia',
                2 => null,
            ],
            222 => [
                0 => 'GNB',
                1 => 'Guinea-Bissau',
                2 => null,
            ],
            223 => [
                0 => 'GQ',
                1 => 'Equatorial Guinea',
                2 => null,
            ],
            224 => [
                0 => 'GRC',
                1 => 'Greece',
                2 => null,
            ],
            225 => [
                0 => 'GRD',
                1 => 'Grenada',
                2 => null,
            ],
            226 => [
                0 => 'GRL',
                1 => 'Greenland',
                2 => null,
            ],
            227 => [
                0 => 'GS',
                1 => 'South Georgia And The South Sandwich Islands',
                2 => null,
            ],
            228 => [
                0 => 'GTM',
                1 => 'Guatemala',
                2 => null,
            ],
            229 => [
                0 => 'GUF',
                1 => 'French Guiana',
                2 => null,
            ],
            230 => [
                0 => 'GUM',
                1 => 'Guam',
                2 => null,
            ],
            231 => [
                0 => 'GUY',
                1 => 'Guyana',
                2 => null,
            ],
            232 => [
                0 => 'HKG',
                1 => 'Hong Kong',
                2 => null,
            ],
            233 => [
                0 => 'HMD',
                1 => 'Heard Island and McDonald Islands',
                2 => null,
            ],
            234 => [
                0 => 'HND',
                1 => 'Honduras',
                2 => null,
            ],
            235 => [
                0 => 'HRV',
                1 => 'Croatia',
                2 => null,
            ],
            236 => [
                0 => 'HTI',
                1 => 'Haiti',
                2 => null,
            ],
            237 => [
                0 => 'HUN',
                1 => 'Hungary',
                2 => null,
            ],
            238 => [
                0 => 'IDN',
                1 => 'Indonesia',
                2 => null,
            ],
            239 => [
                0 => 'IM',
                1 => 'Isle of Man',
                2 => null,
            ],
            240 => [
                0 => 'IND',
                1 => 'India',
                2 => null,
            ],
            241 => [
                0 => 'IOT',
                1 => 'British Indian Ocean Territory',
                2 => null,
            ],
            242 => [
                0 => 'IRL',
                1 => 'Ireland',
                2 => null,
            ],
            243 => [
                0 => 'IRN',
                1 => 'Iran, Islamic Republic of',
                2 => null,
            ],
            244 => [
                0 => 'IRQ',
                1 => 'Iraq',
                2 => null,
            ],
            245 => [
                0 => 'ISL',
                1 => 'Iceland',
                2 => null,
            ],
            246 => [
                0 => 'ISR',
                1 => 'Israel',
                2 => null,
            ],
            247 => [
                0 => 'ITA',
                1 => 'Italy',
                2 => null,
            ],
            248 => [
                0 => 'JAM',
                1 => 'Jamaica',
                2 => null,
            ],
            249 => [
                0 => 'JE',
                1 => 'Jersey',
                2 => null,
            ],
            250 => [
                0 => 'JOR',
                1 => 'Jordan',
                2 => null,
            ],
            251 => [
                0 => 'JPN',
                1 => 'Japan',
                2 => null,
            ],
            252 => [
                0 => 'KAZ',
                1 => 'Kazakhstan',
                2 => null,
            ],
            253 => [
                0 => 'KEN',
                1 => 'Kenya',
                2 => null,
            ],
            254 => [
                0 => 'KGZ',
                1 => 'Kyrgyzstan',
                2 => null,
            ],
            255 => [
                0 => 'KHM',
                1 => 'Cambodia',
                2 => null,
            ],
            256 => [
                0 => 'KI',
                1 => 'Kiribati',
                2 => null,
            ],
            257 => [
                0 => 'KNA',
                1 => 'Saint Kitts and Nevis',
                2 => null,
            ],
            258 => [
                0 => 'KOR',
                1 => 'Korea, the Republic of',
                2 => null,
            ],
            259 => [
                0 => 'KR',
                1 => 'Kosovo',
                2 => null,
            ],
            260 => [
                0 => 'KWT',
                1 => 'Kuwait',
                2 => null,
            ],
            261 => [
                0 => 'LAO',
                1 => 'Lao Peoples Democratic Republic',
                2 => null,
            ],
            262 => [
                0 => 'LBN',
                1 => 'Lebanon',
                2 => null,
            ],
            263 => [
                0 => 'LBR',
                1 => 'Liberia',
                2 => null,
            ],
            264 => [
                0 => 'LBY',
                1 => 'Libya',
                2 => null,
            ],
            265 => [
                0 => 'LCA',
                1 => 'Saint Lucia',
                2 => null,
            ],
            266 => [
                0 => 'LIE',
                1 => 'Liechtenstein',
                2 => null,
            ],
            267 => [
                0 => 'LK',
                1 => 'Stateless',
                2 => null,
            ],
            268 => [
                0 => 'LKA',
                1 => 'Sri Langka',
                2 => null,
            ],
            269 => [
                0 => 'LSO',
                1 => 'Lesotho',
                2 => null,
            ],
            270 => [
                0 => 'LTU',
                1 => 'Lithuania',
                2 => null,
            ],
            271 => [
                0 => 'LUX',
                1 => 'Luxembourg',
                2 => null,
            ],
            272 => [
                0 => 'LVA',
                1 => 'Latvia',
                2 => null,
            ],
            273 => [
                0 => 'MAC',
                1 => 'Macao',
                2 => null,
            ],
            274 => [
                0 => 'MAF',
                1 => 'Saint Martin (French part)',
                2 => null,
            ],
            275 => [
                0 => 'MAR',
                1 => 'Morocco',
                2 => null,
            ],
            276 => [
                0 => 'MCO',
                1 => 'Monaco',
                2 => null,
            ],
            277 => [
                0 => 'MDA',
                1 => 'Moldova, the Republic of',
                2 => null,
            ],
            278 => [
                0 => 'MDG',
                1 => 'Madagascar',
                2 => null,
            ],
            279 => [
                0 => 'MDV',
                1 => 'Maldives',
                2 => null,
            ],
            280 => [
                0 => 'MEX',
                1 => 'Mexico',
                2 => null,
            ],
            281 => [
                0 => 'MHL',
                1 => 'Marshall Islands',
                2 => null,
            ],
            282 => [
                0 => 'MK',
                1 => 'Macedonia, The Former Yugoslav Republic of',
                2 => null,
            ],
            283 => [
                0 => 'MLI',
                1 => 'Mali',
                2 => null,
            ],
            284 => [
                0 => 'MLT',
                1 => 'Malta',
                2 => null,
            ],
            285 => [
                0 => 'MM',
                1 => 'Myanmar',
                2 => null,
            ],
            286 => [
                0 => 'MNE',
                1 => 'Montenegro',
                2 => null,
            ],
            287 => [
                0 => 'MNG',
                1 => 'Mongolia',
                2 => null,
            ],
            288 => [
                0 => 'MNP',
                1 => 'Northern Mariana Islands',
                2 => null,
            ],
            289 => [
                0 => 'MOZ',
                1 => 'Mozambique',
                2 => null,
            ],
            290 => [
                0 => 'MRT',
                1 => 'Mauritania',
                2 => null,
            ],
            291 => [
                0 => 'MSR',
                1 => 'Montserrat',
                2 => null,
            ],
            292 => [
                0 => 'MTQ',
                1 => 'Martinique',
                2 => null,
            ],
            293 => [
                0 => 'MUS',
                1 => 'Mauritius',
                2 => null,
            ],
            294 => [
                0 => 'MWI',
                1 => 'Malawi',
                2 => null,
            ],
            295 => [
                0 => 'MYS',
                1 => 'Malaysia',
                2 => null,
            ],
            296 => [
                0 => 'NAM',
                1 => 'Namibia',
                2 => null,
            ],
            297 => [
                0 => 'NCL',
                1 => 'New Caledonia',
                2 => null,
            ],
            298 => [
                0 => 'NER',
                1 => 'Niger',
                2 => null,
            ],
            299 => [
                0 => 'NFK',
                1 => 'Norfolk Island',
                2 => null,
            ],
            300 => [
                0 => 'NGA',
                1 => 'Nigeria',
                2 => null,
            ],
            301 => [
                0 => 'NIC',
                1 => 'Nicaragua',
                2 => null,
            ],
            302 => [
                0 => 'NIU',
                1 => 'Niue',
                2 => null,
            ],
            303 => [
                0 => 'NLD',
                1 => 'Netherlands',
                2 => null,
            ],
            304 => [
                0 => 'NOR',
                1 => 'Norway',
                2 => null,
            ],
            305 => [
                0 => 'NPL',
                1 => 'Nepal',
                2 => null,
            ],
            306 => [
                0 => 'NRU',
                1 => 'Nauru',
                2 => null,
            ],
            307 => [
                0 => 'NZL',
                1 => 'New Zealand',
                2 => null,
            ],
            308 => [
                0 => 'OAT',
                1 => 'Qatar',
                2 => null,
            ],
            309 => [
                0 => 'OMN',
                1 => 'Oman',
                2 => null,
            ],
            310 => [
                0 => 'PAK',
                1 => 'Pakistan',
                2 => null,
            ],
            311 => [
                0 => 'PAN',
                1 => 'Panama',
                2 => null,
            ],
            312 => [
                0 => 'PCN',
                1 => 'Pitcairn',
                2 => null,
            ],
            313 => [
                0 => 'PER',
                1 => 'Peru',
                2 => null,
            ],
            314 => [
                0 => 'PHL',
                1 => 'Philippines',
                2 => null,
            ],
            315 => [
                0 => 'PLW',
                1 => 'Palau',
                2 => null,
            ],
            316 => [
                0 => 'PNG',
                1 => 'Papua New Guinea',
                2 => null,
            ],
            317 => [
                0 => 'POL',
                1 => 'Poland',
                2 => null,
            ],
            318 => [
                0 => 'PRI',
                1 => 'Puerto Rico',
                2 => null,
            ],
            319 => [
                0 => 'PRK',
                1 => 'Korea, Democratic People\'s Republic of',
                2 => null,
            ],
            320 => [
                0 => 'PRT',
                1 => 'Portugal',
                2 => null,
            ],
            321 => [
                0 => 'PRY',
                1 => 'Paraguay',
                2 => null,
            ],
            322 => [
                0 => 'PS',
                1 => 'Palestine, State of',
                2 => null,
            ],
            323 => [
                0 => 'PYF',
                1 => 'French Polynesia',
                2 => null,
            ],
            324 => [
                0 => 'REU',
                1 => 'Reunion',
                2 => null,
            ],
            325 => [
                0 => 'ROU',
                1 => 'Romania',
                2 => null,
            ],
            326 => [
                0 => 'RUS',
                1 => 'Russian Federation',
                2 => null,
            ],
            327 => [
                0 => 'RWA',
                1 => 'Rwanda',
                2 => null,
            ],
            328 => [
                0 => 'SAU',
                1 => 'Saudi Arabia',
                2 => null,
            ],
            329 => [
                0 => 'SB',
                1 => 'Solomon Islands',
                2 => null,
            ],
            330 => [
                0 => 'SDN',
                1 => 'Sudan',
                2 => null,
            ],
            331 => [
                0 => 'SEN',
                1 => 'Senegal',
                2 => null,
            ],
            332 => [
                0 => 'SGP',
                1 => 'Singapore',
                2 => null,
            ],
            333 => [
                0 => 'SHN',
                1 => 'Saint Helena, Ascension and Tristan da Cunha',
                2 => null,
            ],
            334 => [
                0 => 'SJM',
                1 => 'Svalbard and Jan Mayen',
                2 => null,
            ],
            335 => [
                0 => 'SLE',
                1 => 'Sierra Leone',
                2 => null,
            ],
            336 => [
                0 => 'SLV',
                1 => 'El Salvador',
                2 => null,
            ],
            337 => [
                0 => 'SMR',
                1 => 'San Marino',
                2 => null,
            ],
            338 => [
                0 => 'SOM',
                1 => 'Somalia',
                2 => null,
            ],
            339 => [
                0 => 'SPM',
                1 => 'Saint Pierre and Miquelon',
                2 => null,
            ],
            340 => [
                0 => 'SRB',
                1 => 'Serbia',
                2 => null,
            ],
            341 => [
                0 => 'SSD',
                1 => 'South Sudan',
                2 => null,
            ],
            342 => [
                0 => 'STP',
                1 => 'Sao Tome and Principe',
                2 => null,
            ],
            343 => [
                0 => 'SUR',
                1 => 'Suriname',
                2 => null,
            ],
            344 => [
                0 => 'SVK',
                1 => 'Slovakia',
                2 => null,
            ],
            345 => [
                0 => 'SVN',
                1 => 'Slovenia',
                2 => null,
            ],
            346 => [
                0 => 'SWE',
                1 => 'Sweden',
                2 => null,
            ],
            347 => [
                0 => 'SWZ',
                1 => 'Swaziland',
                2 => null,
            ],
            348 => [
                0 => 'SXM',
                1 => 'Sint Maarten (Dutch part)',
                2 => null,
            ],
            349 => [
                0 => 'SYC',
                1 => 'Seychelles',
                2 => null,
            ],
            350 => [
                0 => 'SYR',
                1 => 'Syrian Arab Republic',
                2 => null,
            ],
            351 => [
                0 => 'TCA',
                1 => 'Turks and Caicos Islands',
                2 => null,
            ],
            352 => [
                0 => 'TCD',
                1 => 'Chad',
                2 => null,
            ],
            353 => [
                0 => 'TGO',
                1 => 'Togo',
                2 => null,
            ],
            354 => [
                0 => 'THA',
                1 => 'Thailand',
                2 => null,
            ],
            355 => [
                0 => 'TJK',
                1 => 'Tajikistan',
                2 => null,
            ],
            356 => [
                0 => 'TKL',
                1 => 'Tokelau',
                2 => null,
            ],
            357 => [
                0 => 'TKM',
                1 => 'Turkmenistan',
                2 => null,
            ],
            358 => [
                0 => 'TLS',
                1 => 'Timor-Leste',
                2 => null,
            ],
            359 => [
                0 => 'TON',
                1 => 'Tonga',
                2 => null,
            ],
            360 => [
                0 => 'TTO',
                1 => 'Trinidad and Tobago',
                2 => null,
            ],
            361 => [
                0 => 'TUN',
                1 => 'Tunisia',
                2 => null,
            ],
            362 => [
                0 => 'TUR',
                1 => 'Turkey',
                2 => null,
            ],
            363 => [
                0 => 'TUV',
                1 => 'Tuvalu',
                2 => null,
            ],
            364 => [
                0 => 'TWN',
                1 => 'Taiwan',
                2 => null,
            ],
            365 => [
                0 => 'TZ',
                1 => 'Tanzania, United Republic of',
                2 => null,
            ],
            366 => [
                0 => 'UGA',
                1 => 'Uganda',
                2 => null,
            ],
            367 => [
                0 => 'UKR',
                1 => 'Ukraine',
                2 => null,
            ],
            368 => [
                0 => 'UMI',
                1 => 'United States Minor Outlying Islands',
                2 => null,
            ],
            369 => [
                0 => 'URY',
                1 => 'Uruguay',
                2 => null,
            ],
            370 => [
                0 => 'USA',
                1 => 'United States of America',
                2 => null,
            ],
            371 => [
                0 => 'UZB',
                1 => 'Uzbekistan',
                2 => null,
            ],
            372 => [
                0 => 'VAT',
                1 => 'Holy See (Vatican City State)',
                2 => null,
            ],
            373 => [
                0 => 'VCT',
                1 => 'Saint Vincent and the Grenadines',
                2 => null,
            ],
            374 => [
                0 => 'VEN',
                1 => 'Venezuela, Bolivarian Republic of',
                2 => null,
            ],
            375 => [
                0 => 'VGB',
                1 => 'Virgin Islands, British',
                2 => null,
            ],
            376 => [
                0 => 'VIR',
                1 => 'Virgin Islands, U.S.',
                2 => null,
            ],
            377 => [
                0 => 'VNM',
                1 => 'Viet Nam',
                2 => null,
            ],
            378 => [
                0 => 'VUT',
                1 => 'Vanuatu',
                2 => null,
            ],
            379 => [
                0 => 'WLF',
                1 => 'Wallis and Futuna',
                2 => null,
            ],
            380 => [
                0 => 'WS',
                1 => 'Samoa',
                2 => null,
            ],
            381 => [
                0 => 'YEM',
                1 => 'Yemen',
                2 => null,
            ],
            382 => [
                0 => 'YT',
                1 => 'Mayotte',
                2 => null,
            ],
            383 => [
                0 => 'ZAF',
                1 => 'South Africa',
                2 => null,
            ],
            384 => [
                0 => 'ZMB',
                1 => 'Zambia',
                2 => null,
            ],
            385 => [
                0 => 'ZWE',
                1 => 'Zimbabwe',
                2 => null,
            ],
        ];
    }

    private static function keteranganRows(): array
    {
        return [
            0 => [
                0 => 'Kolom',
                1 => 'Mandatory',
                2 => 'Validasi DJP',
                3 => 'Keterangan',
            ],
            1 => [
                0 => 'Faktur',
                1 => null,
                2 => null,
                3 => null,
            ],
            2 => [
                0 => 'Baris',
                1 => 'Ya',
                2 => 'Tidak',
                3 => 'Urut dari angka 1',
            ],
            3 => [
                0 => 'Tanggal Faktur',
                1 => 'Ya',
                2 => 'Tidak',
                3 => 'Format DD/MM/YYYY',
            ],
            4 => [
                0 => 'Jenis Faktur',
                1 => 'Ya',
                2 => 'Tidak',
                3 => 'Selalu diisi :  Normal',
            ],
            5 => [
                0 => 'Kode Transaksi',
                1 => 'Ya',
                2 => 'Ya',
                3 => 'Ikut Referensi',
            ],
            6 => [
                0 => 'Keterangan Tambahan',
                1 => 'Tidak',
                2 => 'Ya',
                3 => 'Wajib diisi untuk Kode Transaksi 07 atau 08',
            ],
            7 => [
                0 => 'Dokumen Pendukung',
                1 => 'Tidak',
                2 => 'Tidak',
                3 => 'Isikan dengan Nomor Dokumen Pendukung : 
contoh untuk SPPB (BC4.0) : nomor aju 
untuk PPBJ : nomor PPBJ
untuk PJKEK : nomor PJKEK',
            ],
            8 => [
                0 => 'Period Dok Pendukung',
                1 => 'Tidak',
                2 => 'Tidak',
                3 => 'Diisi dengan bulan+tahun dari Dokumen SPPB (diambilkan dari bulan dan tahun Tanggal SPPB), contoh tanggal SPPB : 05-01-2025 , dituliskan menjadi : 012025',
            ],
            9 => [
                0 => 'Referensi',
                1 => 'Tidak',
                2 => 'Tidak',
                3 => null,
            ],
            10 => [
                0 => 'Cap Fasilitas',
                1 => 'Tidak',
                2 => 'Ya',
                3 => 'Wajib diisi untuk Kode Transaksi 07 atau 08',
            ],
            11 => [
                0 => 'ID TKU Penjual',
                1 => 'Ya',
                2 => 'Ya',
                3 => 'Wajib diisi 22 digit NITKU',
            ],
            12 => [
                0 => 'NPWP/NIK Pembeli',
                1 => 'Ya',
                2 => 'Ya',
                3 => 'Wajib diisi, isikan dengan 0000000000000000 jika Jenis ID Pembeli selain TIN',
            ],
            13 => [
                0 => 'Jenis ID Pembeli',
                1 => 'Ya',
                2 => 'Ya',
                3 => 'Ikut Referensi',
            ],
            14 => [
                0 => 'Negara Pembeli',
                1 => 'Ya',
                2 => 'Ya',
                3 => 'Ikut Referensi',
            ],
            15 => [
                0 => 'Nomor Dokumen Pembeli',
                1 => 'Ya',
                2 => 'Ya',
                3 => 'Isikan dengan - jika Jenis ID adalah TIN',
            ],
            16 => [
                0 => 'Nama Pembeli',
                1 => 'Ya',
                2 => 'Ya',
                3 => 'Isikan dengan nama Pembeli. Untuk Jenis ID Pembeli TIN dan Pasport akan diisikan ke sistem dengan data prepop',
            ],
            17 => [
                0 => 'Alamat Pembeli',
                1 => 'Ya',
                2 => 'Ya',
                3 => 'Isikan dengan alamat Pembeli. Untuk Jenis ID Pembeli TIN akan diisikan ke sistem dengan data prepop',
            ],
            18 => [
                0 => 'Email Pembeli',
                1 => 'Tidak',
                2 => 'Tidak',
                3 => null,
            ],
            19 => [
                0 => 'ID TKU Pembeli',
                1 => 'Ya',
                2 => 'Ya',
                3 => 'Wajib diisi 22 digit NITKU untuk Jenis ID Pembeli TIN, jika selain TIN isikan dengan 000000',
            ],
            20 => [
                0 => 'DetailFaktur',
                1 => null,
                2 => null,
                3 => null,
            ],
            21 => [
                0 => 'Baris',
                1 => 'Ya',
                2 => 'Tidak',
                3 => 'Wajib diisi sesuai kolom Baris  dari sheet Faktur',
            ],
            22 => [
                0 => 'Barang/Jasa',
                1 => 'Ya',
                2 => 'Ya',
                3 => 'Ikut Referensi',
            ],
            23 => [
                0 => 'Kode Barang Jasa',
                1 => 'Tidak',
                2 => 'Ya',
                3 => 'Jika diisi pastikan sesuai referensi yang disediakan',
            ],
            24 => [
                0 => 'Nama Barang/Jasa',
                1 => 'Ya',
                2 => 'Tidak',
                3 => null,
            ],
            25 => [
                0 => 'Nama Satuan Ukur',
                1 => 'Ya',
                2 => 'Ya',
                3 => 'Ikut Referensi',
            ],
            26 => [
                0 => 'Harga Satuan',
                1 => 'Ya',
                2 => 'Tidak',
                3 => 'Maks 2 digit di belakang koma, ikut aturan pembulatan komersial',
            ],
            27 => [
                0 => 'Jumlah Barang Jasa',
                1 => 'Ya',
                2 => 'Tidak',
                3 => 'Maks 2 digit di belakang koma, ikut aturan pembulatan komersial',
            ],
            28 => [
                0 => 'Total Diskon',
                1 => 'Ya',
                2 => 'Tidak',
                3 => 'Maks 2 digit di belakang koma, ikut aturan pembulatan komersial, jika tidak ada isikan dengan 0',
            ],
            29 => [
                0 => 'DPP',
                1 => 'Ya',
                2 => 'Tidak',
                3 => 'Maks 2 digit di belakang koma, ikut aturan pembulatan komersial',
            ],
            30 => [
                0 => 'DPP Nilai Lain',
                1 => 'Ya',
                2 => 'Tidak',
                3 => 'Maks 2 digit di belakang koma, ikut aturan pembulatan komersial, jika tidak menggunakan DPP Nilai Lain, isikan dengan nilai yang sama dengan kolom DPP',
            ],
            31 => [
                0 => 'Tarif PPN',
                1 => 'Ya',
                2 => 'Ya',
                3 => 'Ikut tarif yang berlaku',
            ],
            32 => [
                0 => 'PPN',
                1 => 'Ya',
                2 => 'Tidak',
                3 => 'Tarif PPN * DPP Nilai Lain. Bisa diisi dengan nilai lain untuk kode transaksi selain : 01, 04 dan 09',
            ],
            33 => [
                0 => 'Tarif PPnBM',
                1 => 'Ya',
                2 => 'Tidak',
                3 => 'Isikan dengan 0 jika tidak ada PPnBM',
            ],
            34 => [
                0 => 'PPnBM',
                1 => 'Ya',
                2 => 'Tidak',
                3 => 'Tarif PPN * DPP Nilai Lain. Bisa diisi dengan nilai lain.Isikan dengan 0 jika tidak ada PPnBM',
            ],
        ];
    }
}
