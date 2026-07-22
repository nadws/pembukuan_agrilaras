<?php

namespace App\Http\Controllers;

use App\Models\LaporanLayerModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Laporan_layerController extends Controller
{
    public function index(Request $r)
    {
        if (empty($r->tgl)) {
            $tgl = date("Y-m-d", strtotime("-1 day"));
        } else {
            $tgl = $r->tgl;
        }
        $tgl_sebelumnya = date("Y-m-d", strtotime($tgl . " -6 days"));
        $tgl_kemarin = date("Y-m-d", strtotime($tgl . " -1 days"));

        $tgl_minggu_kemaren = date("Y-m-d", strtotime($tgl_sebelumnya . " -1 days"));
        $tgl_minggu_sebelumnya = date("Y-m-d", strtotime($tgl_minggu_kemaren . " -6 days"));

        $tgl1 = date('Y-m-01', strtotime($tgl));

        $tgl_awal_harga = date("Y-m-d", strtotime($tgl . "-30 days"));

        $harga = DB::selectOne("SELECT b.nm_produk, a.tgl, sum(a.pcs / 1000) as pcs , sum(a.total_rp) as ttl_rupiah, a.admin
        FROM stok_produk_perencanaan as a 
        left join tb_produk_perencanaan as b on b.id_produk = a.id_pakan
        where a.h_opname = 'T' and a.tgl BETWEEN '$tgl_awal_harga' and '$tgl' and b.kategori = 'pakan' and a.pcs != 0;");

        $harga_pakan = DB::table('harga_pakan as h1')
            ->select('id_pakan', 'ttl_gr', 'ttl_rp', 'rp_lain')
            ->whereRaw('id_harga_pakan = (select max(id_harga_pakan) from harga_pakan as h2 where h2.id_pakan = h1.id_pakan)')
            ->get()
            ->keyBy('id_pakan');

        $data = [
            'title' => 'Laporan Layer',
            'tgl' => $tgl,
            'tgl_sebelum' => $tgl_sebelumnya,
            'tgl_kemarin' => $tgl_kemarin,
            'harga' => $harga,
            'kandang' => LaporanLayerModel::getLaporanLayer($tgl, $tgl_sebelumnya, $tgl_kemarin, $tgl_minggu_sebelumnya, $tgl_minggu_kemaren),
            'harga_pakan' => $harga_pakan
        ];
        return view('laporan.layer2', $data);
    }

    public function export(Request $request)
    {
        $request->validate([
            'tgl' => ['nullable', 'date'],
            'tgl_mulai' => ['nullable', 'date'],
            'tgl_selesai' => ['nullable', 'date', 'after_or_equal:tgl_mulai'],
        ]);

        $tgl = $request->tgl_selesai ?: ($request->tgl ?: date('Y-m-d', strtotime('-1 day')));
        $tglMulai = $request->tgl_mulai
            ?: Carbon::parse($tgl)->subDays(20)->format('Y-m-d');
        $tglSebelumnya = date('Y-m-d', strtotime($tgl . ' -6 days'));
        $tglKemarin = date('Y-m-d', strtotime($tgl . ' -1 day'));
        $tglMingguKemarin = date('Y-m-d', strtotime($tglSebelumnya . ' -1 day'));
        $tglMingguSebelumnya = date('Y-m-d', strtotime($tglMingguKemarin . ' -6 days'));

        $kandang = collect(LaporanLayerModel::getLaporanLayer(
            $tgl,
            $tglSebelumnya,
            $tglKemarin,
            $tglMingguSebelumnya,
            $tglMingguKemarin
        ));

        $spreadsheet = new Spreadsheet();
        $spreadsheet->removeSheetByIndex(0);

        foreach ($kandang as $item) {
            $namaSheet = $this->namaSheetTelur($item);
            $sheet = new Worksheet($spreadsheet, $namaSheet);
            $spreadsheet->addSheet($sheet);
            $this->buatSheetDataTelur($sheet, $item, $tglMulai, $tgl);
        }

        if ($spreadsheet->getSheetCount() === 0) {
            $sheet = new Worksheet($spreadsheet, 'Data Telur');
            $spreadsheet->addSheet($sheet);
            $sheet->setCellValue('A1', 'Data telur tidak tersedia pada tanggal laporan.');
        }

        $spreadsheet->setActiveSheetIndex(0);
        $writer = new Xlsx($spreadsheet);
        $namaFile = 'Laporan Layer ' . date('d-m-Y', strtotime($tglMulai)) . ' s.d. ' .
            date('d-m-Y', strtotime($tgl)) . '.xlsx';

        return response()->streamDownload(
            function () use ($writer, $spreadsheet) {
                $writer->save('php://output');
                $spreadsheet->disconnectWorksheets();
            },
            $namaFile,
            [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]
        );
    }

    private function buatSheetDataTelur(
        Worksheet $sheet,
        object $kandang,
        string $tglMulai,
        string $tglSelesai
    ): void
    {
        $sheet->setShowGridlines(false);

        $jenisAyam = DB::table('kandang as a')
            ->leftJoin('strain as b', 'b.id_strain', '=', 'a.id_strain')
            ->where('a.id_kandang', $kandang->id_kandang)
            ->value('b.nm_strain') ?: '-';

        if (Carbon::parse($tglSelesai)->lt(Carbon::parse($kandang->chick_in))) {
            $this->isiIdentitasSheetTelur(
                $sheet,
                $kandang,
                $jenisAyam,
                'F',
                $tglMulai,
                $tglSelesai
            );
            $sheet->mergeCells('A6:F6');
            $sheet->setCellValue('A6', 'Data belum tersedia pada tanggal laporan.');
            return;
        }

        $tanggalAwal = Carbon::parse($tglMulai)->startOfDay();
        $tanggalLaporan = Carbon::parse($tglSelesai)->startOfDay();
        $awalDataKandang = $tanggalAwal->copy()->max(
            Carbon::parse($kandang->chick_in)->startOfDay()
        );
        $tanggalReferensi = collect();
        $cursorTanggal = $tanggalLaporan->copy();

        while ($cursorTanggal->gte($awalDataKandang)) {
            $tanggalReferensi->prepend($cursorTanggal->format('Y-m-d'));
            $cursorTanggal->subWeeks(3);
        }

        $dataMingguan = [];
        $produkPakan = collect();
        $pemakaianPakan = [];
        $hargaPakan = [];

        foreach ($tanggalReferensi as $tanggalReferensiExport) {
            $view = $this->hdTigaMinggu(Request::create('/', 'GET', [
                'id_kandang' => $kandang->id_kandang,
                'tgl' => $tanggalReferensiExport,
                'tgl_batas_data' => $tglSelesai,
            ]));
            $data = $view->getData();

            $dataMingguan = array_merge($dataMingguan, $data['dataMingguan']);
            $produkPakan = $produkPakan
                ->concat($data['produkPakan'])
                ->unique('id_pakan')
                ->sortBy('nm_produk')
                ->values();
            $pemakaianPakan = array_replace($pemakaianPakan, $data['pemakaianPakan']);
            $hargaPakan = array_replace($hargaPakan, $data['hargaPakan']);
        }

        $dataMingguan = collect($dataMingguan)
            ->unique('mgg')
            ->sortBy('mgg')
            ->map(function ($minggu) use ($tanggalAwal, $tanggalLaporan) {
                $minggu['tanggal_harian'] = collect($minggu['tanggal_harian'])
                    ->filter(function ($tanggal) use ($tanggalAwal, $tanggalLaporan) {
                        return Carbon::parse($tanggal)->betweenIncluded(
                            $tanggalAwal,
                            $tanggalLaporan
                        );
                    })
                    ->values()
                    ->all();

                return $minggu;
            })
            ->filter(fn ($minggu) => !empty($minggu['tanggal_harian']))
            ->values()
            ->all();

        $tanggal = [];
        $urutanHariMinggu = [];
        foreach ($dataMingguan as $minggu) {
            $awalMinggu = Carbon::parse($kandang->chick_in)
                ->startOfDay()
                ->addDays((($minggu['mgg'] - 1) * 7) + 1);

            foreach ($minggu['tanggal_harian'] as $tglHari) {
                $tanggal[] = $tglHari;
                $urutanHariMinggu[] = max(
                    1,
                    min(7, $awalMinggu->diffInDays(Carbon::parse($tglHari), false) + 1)
                );
            }
        }

        $lastColumnIndex = 2 + count($tanggal);
        $lastColumn = Coordinate::stringFromColumnIndex($lastColumnIndex);
        $this->isiIdentitasSheetTelur(
            $sheet,
            $kandang,
            $jenisAyam,
            $lastColumn,
            $tglMulai,
            $tglSelesai
        );
        $sheet->setCellValue('A6', 'Ket');
        $sheet->setCellValue('B6', ':');

        $columnIndex = 3;
        foreach ($dataMingguan as $minggu) {
            $startColumn = Coordinate::stringFromColumnIndex($columnIndex);
            $endColumn = Coordinate::stringFromColumnIndex($columnIndex + count($minggu['tanggal_harian']) - 1);
            $sheet->mergeCells($startColumn . '6:' . $endColumn . '6');
            $sheet->setCellValue($startColumn . '6', 'Minggu ke-' . $minggu['mgg']);
            $columnIndex += count($minggu['tanggal_harian']);
        }

        foreach ($tanggal as $index => $tglHari) {
            $cell = Coordinate::stringFromColumnIndex($index + 3) . '7';
            $sheet->setCellValue(
                $cell,
                $urutanHariMinggu[$index] . '/7' . PHP_EOL . date('d/m', strtotime($tglHari))
            );
        }

        $metrics = $this->metrikDataTelurExport(
            $kandang,
            $dataMingguan,
            $produkPakan,
            $pemakaianPakan,
            $hargaPakan,
            $tanggalLaporan
        );

        $row = 8;
        foreach ($metrics as $metric) {
            if (!empty($metric['section'])) {
                $sheet->mergeCells('A' . $row . ':' . $lastColumn . $row);
                $sheet->setCellValue('A' . $row, $metric['section']);
                $sheet->getStyle('A' . $row . ':' . $lastColumn . $row)->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '233E82']],
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
                ]);
                $row++;
                continue;
            }

            $sheet->fromArray(array_merge([$metric['label'], ':'], $metric['values']), null, 'A' . $row);
            if ($row % 2 === 0) {
                $sheet->getStyle('A' . $row . ':' . $lastColumn . $row)
                    ->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('F5F7FC');
            }
            $sheet->getStyle('A' . $row . ':B' . $row)
                ->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB('E5EBF6');
            $sheet->getStyle('C' . $row . ':' . $lastColumn . $row)
                ->getNumberFormat()->setFormatCode($metric['format'] ?? '#,##0.00');

            if (!empty($metric['blue'])) {
                $sheet->getStyle('C' . $row . ':' . $lastColumn . $row)
                    ->getFont()->setBold(true)->getColor()->setRGB('315DDB');
            }

            if (isset($metric['danger'])) {
                foreach ($metric['values'] as $index => $value) {
                    if ((float) $value >= $metric['danger']) {
                        $cell = Coordinate::stringFromColumnIndex($index + 3) . $row;
                        $sheet->getStyle($cell)->getFont()->setBold(true)->getColor()->setRGB('C6283D');
                    }
                }
            }

            $row++;
        }

        $lastRow = $row - 1;
        $sheet->getStyle('A1:' . $lastColumn . '1')->applyFromArray([
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F397D']],
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 15],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $this->styleHeaderExcel($sheet, 'A6:' . $lastColumn . '7');
        $sheet->getStyle('C7:' . $lastColumn . '7')->getAlignment()->setWrapText(true);
        $sheet->getStyle('A6:' . $lastColumn . $lastRow)->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN)
            ->getColor()->setRGB('98A8C2');
        $sheet->getStyle('A6:' . $lastColumn . $lastRow)->getBorders()->getOutline()
            ->setBorderStyle(Border::BORDER_MEDIUM)
            ->getColor()->setRGB('53698F');
        $sheet->getStyle('A6:A' . $lastRow)->getFont()->setBold(true);
        $sheet->getStyle('A6:' . $lastColumn . $lastRow)->getFont()->setSize(10);
        $sheet->getStyle('A6:' . $lastColumn . $lastRow)->getAlignment()
            ->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->freezePane('C8');

        $sheet->getColumnDimension('A')->setWidth(24);
        $sheet->getColumnDimension('B')->setWidth(3);
        for ($column = 3; $column <= $lastColumnIndex; $column++) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($column))->setWidth(11);
        }
        $sheet->getRowDimension(1)->setRowHeight(28);
        $sheet->getRowDimension(6)->setRowHeight(26);
        $sheet->getRowDimension(7)->setRowHeight(36);
        $sheet->getPageSetup()->setOrientation('landscape');
        $sheet->getPageSetup()->setFitToWidth(1)->setFitToHeight(0);
    }

    private function isiIdentitasSheetTelur(
        Worksheet $sheet,
        object $kandang,
        string $jenisAyam,
        string $lastColumn,
        string $tglMulai,
        string $tglSelesai
    ): void {
        $sheet->mergeCells('A1:' . $lastColumn . '1');
        $sheet->setCellValue(
            'A1',
            'DATA TELUR PERIODE ' . date('d/m/Y', strtotime($tglMulai)) . ' - ' .
                date('d/m/Y', strtotime($tglSelesai))
        );

        $sheet->setCellValue('A2', 'Kandang');
        $sheet->setCellValue('B2', ':');
        $sheet->setCellValue('C2', $kandang->nm_kandang ?? '-');
        $sheet->setCellValue('A3', 'Chick In');
        $sheet->setCellValue('B3', ':');
        $sheet->setCellValue('C3', !empty($kandang->chick_in)
            ? ExcelDate::dateTimeToExcel(Carbon::parse($kandang->chick_in))
            : '-');
        if (!empty($kandang->chick_in)) {
            $sheet->getStyle('C3')->getNumberFormat()->setFormatCode('dd/mm/yyyy');
        }
        $sheet->setCellValue('A4', 'Jenis Ayam');
        $sheet->setCellValue('B4', ':');
        $sheet->setCellValue('C4', $jenisAyam);

        $sheet->getStyle('A1:' . $lastColumn . '1')->applyFromArray([
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F397D']],
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 15],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $sheet->getStyle('A2:A4')->getFont()->setBold(true);
        $sheet->getStyle('A2:C4')->applyFromArray([
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E3EAF7']],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_MEDIUM,
                    'color' => ['rgb' => '7184A8'],
                ],
            ],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getStyle('C2:C4')->getFont()->setBold(true)->getColor()->setRGB('2D478F');
    }

    private function metrikDataTelurExport(
        object $kandang,
        array $dataMingguan,
        $produkPakan,
        array $pemakaianPakan,
        array $hargaPakan,
        Carbon $tanggalLaporan
    ): array {
        $metrics = [];
        $d = [];
        $c = [];
        $butir = [];
        $kgBersih = [];
        $gramButir = [];
        $targetBerat = [];
        $hd = [];
        $hdl = [];
        $targetHd = [];
        $fcrD = [];
        $fcrDPlus = [];
        $hdW = [];
        $fcrW = [];
        $fcrWPlus = [];

        foreach ($dataMingguan as $minggu) {
            $telurPcsHdWeek = 0;
            $jumlahHari = 0;
            $kgKotorFcrWeek = 0;
            $butirFcrWeek = 0;
            $kgPakanFcrWeek = 0;
            $vitaminFcrWeek = 0;
            $vaksinFcrWeek = 0;
            $operasionalFcrWeek = 0;

            foreach ($minggu['tanggal_harian'] as $tglHari) {
                $butirHarian = (float) ($minggu['butir2'][$tglHari] ?? 0);
                $kgKotorHarian = (float) ($minggu['kg_kotor2'][$tglHari] ?? 0);
                $kgPakanHarian = (float) ($minggu['kg_pakan_d'][$tglHari] ?? 0);
                $vitaminHarian = (float) ($minggu['vi_fcr_d'][$tglHari] ?? 0);
                $vaksinHarian = (float) ($minggu['va_fcr_d'][$tglHari] ?? 0);
                $operasionalHarian = (float) ($minggu['op_fcr_d'][$tglHari] ?? 0);
                $bersihHarian = $kgKotorHarian - $butirHarian / 180;
                $sudahTerjadi = Carbon::parse($tglHari)->lte($tanggalLaporan);

                $d[] = (float) ($minggu['popD'][$tglHari] ?? 0);
                $c[] = (float) ($minggu['popC'][$tglHari] ?? 0);
                $butir[] = $butirHarian;
                $kgBersih[] = $bersihHarian;
                $gramButir[] = $butirHarian > 0 ? ($bersihHarian * 1000) / $butirHarian : 0;
                $targetBerat[] = (float) data_get($minggu, 'peformance.berat_telur', 0);
                $hd[] = (float) ($minggu['pop_kurang_per_hari'][$tglHari] ?? 0);
                $hdl[] = (float) ($minggu['hdl'][$tglHari] ?? 0);
                $targetHd[] = (float) data_get($minggu, 'peformance.telur', 0);
                $fcrD[] = $bersihHarian > 0 ? ($kgPakanHarian / 1000) / $bersihHarian : 0;
                $fcrDPlus[] = $bersihHarian > 0
                    ? ($kgPakanHarian / 1000 + $vitaminHarian + $vaksinHarian + $operasionalHarian) /
                        $bersihHarian
                    : 0;

                if ($sudahTerjadi) {
                    $telurPcsHdWeek += $butirHarian;
                    $jumlahHari++;
                    $kgKotorFcrWeek += $kgKotorHarian;
                    $butirFcrWeek += $butirHarian;
                    $kgPakanFcrWeek += $kgPakanHarian;
                    $vitaminFcrWeek += $vitaminHarian;
                    $vaksinFcrWeek += $vaksinHarian;
                    $operasionalFcrWeek += $operasionalHarian;
                }

                $populasiAktif = (float) ($kandang->stok_awal ?? 0) -
                    (float) ($minggu['pop_akihir_week'][$tglHari] ?? 0);
                $kgBersihWeek = $kgKotorFcrWeek - $butirFcrWeek / 180;

                $hdW[] = $sudahTerjadi && $jumlahHari > 0 && $populasiAktif > 0
                    ? ($telurPcsHdWeek / $jumlahHari / $populasiAktif) * 100
                    : 0;
                $fcrW[] = $sudahTerjadi && $butirHarian > 0 && $kgBersihWeek > 0
                    ? ($kgPakanFcrWeek / 1000) / $kgBersihWeek
                    : 0;
                $fcrWPlus[] = $sudahTerjadi && $butirHarian > 0 && $kgBersihWeek > 0
                    ? ($kgPakanFcrWeek / 1000 +
                            $vitaminFcrWeek +
                            $vaksinFcrWeek +
                            $operasionalFcrWeek) /
                        $kgBersihWeek
                    : 0;
            }
        }

        $metrics[] = ['label' => 'D', 'values' => $d, 'format' => '#,##0'];
        $metrics[] = ['label' => 'C', 'values' => $c, 'format' => '#,##0'];
        $metrics[] = ['label' => 'Butir', 'values' => $butir, 'format' => '#,##0'];
        $metrics[] = ['label' => 'Kg bersih', 'values' => $kgBersih, 'format' => '#,##0.0'];
        $metrics[] = ['label' => 'Gr (butir)', 'values' => $gramButir, 'format' => '#,##0.0'];
        $metrics[] = ['label' => 'Target berat telur', 'values' => $targetBerat, 'format' => '0.0', 'blue' => true];
        $metrics[] = ['label' => 'HD', 'values' => $hd, 'format' => '0.0'];
        $metrics[] = ['label' => 'HDL', 'values' => $hdl, 'format' => '0.0'];
        $metrics[] = ['label' => 'Target HD', 'values' => $targetHd, 'format' => '0.0', 'blue' => true];

        $allDates = collect($dataMingguan)->flatMap(fn ($minggu) => $minggu['tanggal_harian'])->values();
        foreach ($produkPakan as $produk) {
            $pemakaian = [];
            $harga = [];
            foreach ($allDates as $tglHari) {
                $key = $produk->id_pakan . '|' . $tglHari;
                $pemakaian[] = (float) ($pemakaianPakan[$key] ?? 0) / 1000;
                $harga[] = (float) ($hargaPakan[$key] ?? 0);
            }
            $metrics[] = ['label' => $produk->nm_produk . ' (kg)', 'values' => $pemakaian, 'format' => '#,##0.0'];
            $metrics[] = ['label' => 'Harga ' . $produk->nm_produk, 'values' => $harga, 'format' => '#,##0'];
        }

        $metrics[] = ['label' => 'FCR D', 'values' => $fcrD, 'format' => '0.00', 'danger' => 2.5];
        $metrics[] = ['label' => 'FCR D+ Biaya', 'values' => $fcrDPlus, 'format' => '0.00', 'danger' => 2.2];
        $metrics[] = ['section' => 'HD & FCR WEEK'];
        $metrics[] = ['label' => 'HD W', 'values' => $hdW, 'format' => '0.0'];
        $metrics[] = ['label' => 'FCR W', 'values' => $fcrW, 'format' => '0.00', 'danger' => 2.5];
        $metrics[] = ['label' => 'FCR W+ Biaya', 'values' => $fcrWPlus, 'format' => '0.00', 'danger' => 2.5];

        return $metrics;
    }

    private function styleHeaderExcel(Worksheet $sheet, string $range): void
    {
        $sheet->getStyle($range)->applyFromArray([
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '29468F']],
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_MEDIUM,
                    'color' => ['rgb' => 'D6DEEF'],
                ],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);
    }

    private function namaSheetTelur(object $kandang): string
    {
        $nama = preg_replace('/[\\\\\/\?\*\[\]:]/', '-', 'Telur ' . $kandang->nm_kandang);
        return mb_substr($nama . ' ' . $kandang->id_kandang, 0, 31);
    }

    public function rumus_layer(Request $r)
    {
        if ($r->rumus == 'butir_today') {
            echo "<b>Butir Today - Yesterday =</b> <em >telur sekarang perbutir - telur kemarin perbutir</em>";
        }
        if ($r->rumus == 'hh') {
            echo "<b>Hen House =</b> <em >(Jumlah telur hari ini/Jumlah ayam awal)  x 100%</em>";
        }
        if ($r->rumus == 'hhkum') {
            echo "<b>Hen House Komulatif =</b> <em >(Jumlah telur dari awal sampai hari ini/Jumlah ayam awal)  x 100%</em>";
        }
        if ($r->rumus == 'kg_today') {
            echo "<b>Kg Today - Yesterday =</b> <em >telur sekarang kg - telur kemarin kg</em> <br><br>";
            echo "<b>Note =</b> <em >Jika minus maka akan merah</em>";
        }
        if ($r->rumus == 'hh_kg') {
            echo "<b>Hen House Kg =</b> <em >(Jumlah telur hari ini (kg)/Jumlah ayam awal)  x 100%</em>";
        }
        if ($r->rumus == 'hh_kgkum') {
            echo "<b>Hen House Komulatif Kg =</b> <em >(Jumlah telur dari awal sampai hari ini(kg)/Jumlah ayam awal)  x 100%</em>";
        }
        if ($r->rumus == 'gr_butir') {
            echo "<b>Gram Perbutir =</b> <em >(Jumlah telur hari ini (gr) - (jumlah pcs hari ini / 180)) / jumlah pcs hari ini)</em>";
        }
        if ($r->rumus == 'hd_day') {
            echo "<b>HD perday =</b> <em >(Jumlah telur hari ini/Jumlah ayam akhir) x 100%</em><br><br>";
            echo "<b>HH perday =</b> <em >(Jumlah telur hari ini/Jumlah ayam awal) x 100%</em><br><br>";
        }
        if ($r->rumus == 'hd_past') {
            echo "<b>HD past =</b> <em >(Jumlah telur kemarin/Jumlah ayam akhir kemarin) x 100%</em>";
        }
        if ($r->rumus == 'hd_week') {
            echo "<b>HD Week =</b> <em >(PCS Telur minggu ini/Jumlah ayam akhir minggu ini) x 100</em> <br><br>";
            echo "<b>HD Past Week =</b> <em >(PCS Telur minggu lalu/Jumlah ayam akhir minggu lalu) x 100</em>";
        }
        if ($r->rumus == 'fcr_week') {
            echo "<b>FCR week =</b> <em >Jumlah pakan minggu ini (kg)/(Jumlah telur minggu ini (kg) - (pcs telur minggu ini / 180))</em> <br><br>";
            echo "<b>FCR week + =</b> <em >(Jumlah pakan minggu ini (kg) + (Rupiah vitamin minggu ini /7000))/(Jumlah telur minggu ini (kg) - (pcs telur minggu ini / 180))</em> <br><br>";
            echo "<b>Note :</b> Jika Fcr diatas 2.2 maka kolom berwarna merah";
        }
        if ($r->rumus == 'fcrplus_week') {
            echo "<b>FCR+ week =</b> <em >(Jumlah pakan yang diberikan selama 1 minggu (kg) + (total rupiah vaksin & vitamin / 7000))/(Jumlah telur selama 1 minggu (kg) - (pcs telur selama 1 minggu / 180))</em>";
        }
        if ($r->rumus == 'd_c') {
            echo "<b>Note :</b> Jika mati lebih dari 3 maka kolom berwarna merah";
        }
        if ($r->rumus == 'mgg') {
            echo "<b>Note :</b> Jika Minggu mencapai 80 minggu atau lebih  maka kolom berwarna merah";
        }
        if ($r->rumus == 'butir') {
            echo "<b>Butir =</b> <em >telur sekarang pcs - telur kemarin pcs</em><br><br>";
            echo "<b>Note =</b> <em >Jika minus maka akan merah</em>";
        }
    }

    function get_history_produk(Request $r)
    {
        if (empty($r->tgl1)) {
            $tgl1 = date('Y-m-01');
            $tgl2 = date('Y-m-d');
        } else {
            $tgl1 = $r->tgl1;
            $tgl2 = $r->tgl2;
        }

        $history = DB::table('tb_produk_perencanaan')->where('id_produk', $r->id_produk)->first();
        $kandang = DB::table('kandang')->where('id_kandang', $r->id_kandang)->first();

        $data = [
            'history' => $history,
            'id_kandang' => $r->id_kandang,
            'id_produk' => $r->id_produk,
            'kandang' => $kandang,
            'tgl1' => $tgl1,
            'tgl2' => $tgl2,
        ];

        return view('laporan.history_produk', $data);
    }

    public function hdTigaMinggu(Request $request)
    {
        $request->validate([
            'id_kandang' => ['required'],
            'tgl' => ['required', 'date'],
            'tgl_batas_data' => ['nullable', 'date'],
        ]);

        /*
     * Jika sebelumnya $k berasal dari query yang lebih lengkap,
     * ganti query ini dengan query lama yang menghasilkan $k.
     */
        $k = DB::table('kandang')
            ->where('id_kandang', $request->id_kandang)
            ->first();

        abort_if(!$k, 404, 'Data kandang tidak ditemukan.');

        $tgl = Carbon::parse($request->tgl)->startOfDay();
        $tanggalBatasData = Carbon::parse(
            $request->tgl_batas_data ?: $request->tgl
        )->startOfDay();
        $chickIn = Carbon::parse($k->chick_in)->startOfDay();

        $selisihHari = $chickIn->diffInDays($tgl, false);

        abort_if(
            $selisihHari < 0,
            422,
            'Tanggal laporan tidak boleh sebelum tanggal chick-in.'
        );

        /*
     * Mengikuti perhitungan lama:
     * hari 1-7   = minggu 1
     * hari 8-14  = minggu 2
     */
        $mingguAktif = max(1, (int) ceil($selisihHari / 7));

        $awalMingguAktif = $chickIn
            ->copy()
            ->addDays((($mingguAktif - 1) * 7) + 1);

        $dataMingguan = [];

        // Urutannya: minggu 25, 26, 27
        for ($mundur = 2; $mundur >= 0; $mundur--) {
            $nomorMinggu = $mingguAktif - $mundur;

            if ($nomorMinggu < 1) {
                continue;
            }

            $awalMinggu = $awalMingguAktif
                ->copy()
                ->subWeeks($mundur);

            $dataMingguan[] = $this->ambilDataPerMinggu(
                $k,
                $nomorMinggu,
                $awalMinggu,
                $tanggalBatasData
            );
        }

        $semuaTanggal = collect($dataMingguan)
            ->flatMap(function ($minggu) {
                return $minggu['tanggal_harian'];
            })
            ->values();

        $awalPeriode = $semuaTanggal->first();
        $akhirPeriode = $semuaTanggal->last();

        /*
 * Daftar produk pakan yang digunakan selama tiga minggu.
 */
        $produkPakan = DB::table('stok_produk_perencanaan as a')
            ->join(
                'tb_produk_perencanaan as b',
                'b.id_produk',
                '=',
                'a.id_pakan'
            )
            ->whereBetween('a.tgl', [$awalPeriode, $akhirPeriode])
            ->where('a.id_kandang', $k->id_kandang)
            ->where('b.kategori', 'pakan')
            ->select(
                'a.id_pakan',
                'b.nm_produk'
            )
            ->distinct()
            ->orderBy('b.nm_produk')
            ->get();

        /*
 * Ambil pemakaian semua produk untuk seluruh tanggal sekaligus.
 */
        $pemakaianPakan = DB::table('stok_produk_perencanaan as a')
            ->join(
                'tb_produk_perencanaan as b',
                'b.id_produk',
                '=',
                'a.id_pakan'
            )
            ->whereBetween('a.tgl', [$awalPeriode, $akhirPeriode])
            ->where('a.id_kandang', $k->id_kandang)
            ->where('b.kategori', 'pakan')
            ->selectRaw(
                '
            a.tgl,
            a.id_pakan,
            SUM(COALESCE(a.pcs_kredit, 0)) AS pcs_kredit
        '
            )
            ->groupBy(
                'a.tgl',
                'a.id_pakan'
            )
            ->get()
            ->mapWithKeys(function ($item) {
                $key = $item->id_pakan . '|' . $item->tgl;

                return [
                    $key => (float) $item->pcs_kredit,
                ];
            })
            ->all();

        $idPakan = $produkPakan
            ->pluck('id_pakan')
            ->all();

        $riwayatHarga = empty($idPakan)
            ? collect()
            : DB::table('harga_pakan')
            ->whereIn('id_pakan', $idPakan)
            ->whereDate('tgl', '<=', $akhirPeriode)
            ->select(
                'id_harga_pakan',
                'id_pakan',
                'tgl',
                'ttl_gr',
                'ttl_rp',
                'rp_lain'
            )
            ->orderBy('id_harga_pakan')
            ->get()
            ->groupBy('id_pakan');

        $hargaPakan = [];

        foreach ($produkPakan as $produk) {
            $riwayatProduk = $riwayatHarga->get(
                $produk->id_pakan,
                collect()
            );

            foreach ($semuaTanggal as $tanggal) {
                /*
         * Mengikuti query lama:
         * mengambil id_harga_pakan terbesar sampai tanggal terkait.
         */
                $hargaTerakhir = $riwayatProduk
                    ->filter(function ($harga) use ($tanggal) {
                        return $harga->tgl <= $tanggal;
                    })
                    ->sortByDesc('id_harga_pakan')
                    ->first();

                $ttlGr = (float) ($hargaTerakhir->ttl_gr ?? 0);
                $ttlRp = (float) ($hargaTerakhir->ttl_rp ?? 0);
                $rpLain = (float) ($hargaTerakhir->rp_lain ?? 0);

                $key = $produk->id_pakan . '|' . $tanggal;

                $hargaPakan[$key] = $ttlGr > 0
                    ? ($ttlRp + $rpLain) / $ttlGr
                    : 0;
            }
        }

        return view(
            'laporan.partials.hd-tiga-minggu',
            compact(
                'k',
                'tgl',
                'dataMingguan',
                'produkPakan',
                'pemakaianPakan',
                'hargaPakan'
            )
        );
    }

    private function ambilDataPerMinggu(
        object $k,
        int $nomorMinggu,
        Carbon $awalMinggu,
        Carbon $tanggalLaporan
    ): array {
        $tanggalHarian = [];

        $popKurangPerHari = [];
        $hKuml = [];
        $popD = [];
        $popC = [];
        $butir2 = [];
        $kgKotor2 = [];
        $kgPakanD = [];
        $viFcrD = [];
        $vaFcrD = [];
        $opFcrD = [];
        $popAkhirWeek = [];
        $hdl = [];

        for ($hari = 0; $hari < 7; $hari++) {
            $tanggal = $awalMinggu
                ->copy()
                ->addDays($hari)
                ->format('Y-m-d');

            $tanggalHarian[] = $tanggal;

            // Nilai default
            $popKurangPerHari[$tanggal] = 0;
            $hKuml[$tanggal] = 0;
            $popD[$tanggal] = 0;
            $popC[$tanggal] = 0;
            $butir2[$tanggal] = 0;
            $kgKotor2[$tanggal] = 0;
            $kgPakanD[$tanggal] = 0;
            $viFcrD[$tanggal] = 0;
            $vaFcrD[$tanggal] = 0;
            $opFcrD[$tanggal] = 0;
            $popAkhirWeek[$tanggal] = 0;
            $hdl[$tanggal] = 0;

            // Jangan mengambil data tanggal yang belum terjadi
            if (Carbon::parse($tanggal)->gt($tanggalLaporan)) {
                continue;
            }

            $populasiKumulatif = DB::selectOne(
                "SELECT
                    SUM(
                        COALESCE(mati, 0) +
                        COALESCE(jual, 0) +
                        COALESCE(afkir, 0)
                    ) AS total
                FROM populasi
                WHERE id_kandang = ?
                    AND tgl BETWEEN ? AND ?
            ",
                [
                    $k->id_kandang,
                    $k->chick_in,
                    $tanggal,
                ]
            );

            $populasiHarian = DB::selectOne(
                "SELECT
                    SUM(COALESCE(mati, 0)) AS d,
                    SUM(COALESCE(jual, 0)) AS j,
                    SUM(COALESCE(afkir, 0)) AS c
                FROM populasi
                WHERE id_kandang = ?
                    AND tgl = ?
            ",
                [
                    $k->id_kandang,
                    $tanggal,
                ]
            );

            $telurHarian = DB::selectOne(
                "SELECT
                    SUM(COALESCE(pcs, 0)) AS pcs,
                    SUM(COALESCE(kg, 0)) AS kg
                FROM stok_telur
                WHERE tgl = ?
                    AND id_kandang = ?
            ",
                [
                    $tanggal,
                    $k->id_kandang,
                ]
            );

            $telurKumulatif = DB::selectOne(
                "SELECT
                    SUM(COALESCE(pcs, 0)) AS kuml_pcs,
                    SUM(COALESCE(kg, 0)) AS kuml_kg
                FROM stok_telur
                WHERE tgl BETWEEN ? AND ?
                    AND pcs != 0
                    AND id_kandang = ?
            ",
                [
                    $k->chick_in,
                    $tanggal,
                    $k->id_kandang,
                ]
            );

            $pakanHarian = DB::selectOne(
                "SELECT
                    SUM(COALESCE(d.pcs_kredit, 0)) AS kg_pakan
                FROM stok_produk_perencanaan AS d
                LEFT JOIN tb_produk_perencanaan AS e
                    ON e.id_produk = d.id_pakan
                WHERE d.tgl = ?
                    AND e.kategori = 'pakan'
                    AND d.id_kandang = ?
            ",
                [
                    $tanggal,
                    $k->id_kandang,
                ]
            );

            $vitaminHarian = DB::selectOne(
                "
                SELECT
                    SUM(COALESCE(debit, 0)) AS rp_vitamin
                FROM jurnal_accurate
                WHERE tgl = ?
                    AND nm_departemen = ?
                    AND kode = '5101-03'
            ",
                [
                    $tanggal,
                    $k->nm_kandang,
                ]
            );

            $vaksinHarian = DB::selectOne(
                "
                SELECT
                    SUM(COALESCE(ttl_rp, 0)) AS rp_vaksin
                FROM tb_vaksin_perencanaan
                WHERE tgl = ?
                    AND id_kandang = ?
            ",
                [
                    $tanggal,
                    $k->id_kandang,
                ]
            );

            /*
             * Biaya seperti listrik dibayar pada tanggal tertentu, tetapi
             * manfaatnya berlaku sepanjang bulan. Ratakan total biaya bulan
             * per hari agar FCR tidak melonjak hanya pada hari pembayaran.
             * Untuk bulan berjalan, gunakan data sampai tanggal laporan.
             */
            $tanggalOperasional = Carbon::parse($tanggal);
            $awalBulanOperasional = $tanggalOperasional->copy()->startOfMonth();
            $akhirBulanOperasional = $tanggalOperasional->copy()->endOfMonth();
            $akhirAlokasiOperasional = $tanggalLaporan->lt($akhirBulanOperasional)
                ? $tanggalLaporan->copy()
                : $akhirBulanOperasional;
            $jumlahHariOperasional = $awalBulanOperasional
                ->diffInDays($akhirAlokasiOperasional) + 1;

            $operasionalHarian = DB::selectOne(
                "SELECT
                    CASE
                        WHEN EXISTS (
                            SELECT 1
                            FROM stok_produk_perencanaan AS aktivitas_target
                            WHERE aktivitas_target.tgl = ?
                                AND aktivitas_target.id_kandang = ?
                        )
                        THEN
                            COALESCE((
                                SELECT SUM(jurnal.debit)
                                FROM jurnal_accurate AS jurnal
                                WHERE jurnal.tgl BETWEEN ? AND ?
                                    AND jurnal.buku = '2'
                            ), 0) / ? * ? /
                            NULLIF(COALESCE((
                                SELECT SUM(kandang_aktif.stok_awal)
                                FROM kandang AS kandang_aktif
                                WHERE EXISTS (
                                    SELECT 1
                                    FROM stok_produk_perencanaan AS aktivitas
                                    WHERE aktivitas.tgl = ?
                                        AND aktivitas.id_kandang = kandang_aktif.id_kandang
                                )
                            ), 0), 0)
                        ELSE 0
                    END AS rp_operasional
            ",
                [
                    $tanggal,
                    $k->id_kandang,
                    $awalBulanOperasional->format('Y-m-d'),
                    $akhirAlokasiOperasional->format('Y-m-d'),
                    $jumlahHariOperasional,
                    (float) ($k->stok_awal ?? 0),
                    $tanggal,
                ]
            );

            $stokAwal = (float) ($k->stok_awal ?? 0);
            $totalPengurangan = (float) ($populasiKumulatif->total ?? 0);
            $populasiAkhir = $stokAwal - $totalPengurangan;

            $pcsTelur = (float) ($telurHarian->pcs ?? 0);
            $kgTelur = (float) ($telurHarian->kg ?? 0);
            $kumulatifPcs = (float) ($telurKumulatif->kuml_pcs ?? 0);

            $popD[$tanggal] = (float) ($populasiHarian->d ?? 0);
            $popC[$tanggal] = (float) ($populasiHarian->c ?? 0);
            $butir2[$tanggal] = $pcsTelur;
            $kgKotor2[$tanggal] = $kgTelur;
            $kgPakanD[$tanggal] = (float) ($pakanHarian->kg_pakan ?? 0);
            $viFcrD[$tanggal] = ((float) ($vitaminHarian->rp_vitamin ?? 0)) / 7000;
            $vaFcrD[$tanggal] = ((float) ($vaksinHarian->rp_vaksin ?? 0)) / 7000;
            $opFcrD[$tanggal] = ((float) ($operasionalHarian->rp_operasional ?? 0)) / 7000;
            $popAkhirWeek[$tanggal] = $totalPengurangan;

            $popKurangPerHari[$tanggal] = $populasiAkhir > 0
                ? ($pcsTelur / $populasiAkhir) * 100
                : 0;

            $hKuml[$tanggal] = $stokAwal > 0
                ? ($kumulatifPcs / $stokAwal) * 100
                : 0;

            /*
         * HDL: data pada hari yang sama di minggu sebelumnya.
         */
            $tanggalMingguLalu = Carbon::parse($tanggal)
                ->subWeek()
                ->format('Y-m-d');

            $populasiMingguLalu = DB::selectOne(
                "
                SELECT
                    SUM(
                        COALESCE(mati, 0) +
                        COALESCE(jual, 0) +
                        COALESCE(afkir, 0)
                    ) AS total
                FROM populasi
                WHERE id_kandang = ?
                    AND tgl BETWEEN ? AND ?
            ",
                [
                    $k->id_kandang,
                    $k->chick_in,
                    $tanggalMingguLalu,
                ]
            );

            $telurMingguLalu = DB::selectOne(
                "
                SELECT SUM(COALESCE(pcs, 0)) AS pcs
                FROM stok_telur
                WHERE tgl = ?
                    AND id_kandang = ?
            ",
                [
                    $tanggalMingguLalu,
                    $k->id_kandang,
                ]
            );

            $populasiAkhirMingguLalu =
                $stokAwal -
                (float) ($populasiMingguLalu->total ?? 0);

            $hdl[$tanggal] = $populasiAkhirMingguLalu > 0
                ? (
                    (float) ($telurMingguLalu->pcs ?? 0) /
                    $populasiAkhirMingguLalu
                ) * 100
                : 0;
        }

        $akhirMinggu = $awalMinggu
            ->copy()
            ->addDays(6)
            ->format('Y-m-d');

        $dtKdng = DB::select(
            "SELECT
                a.id_pakan,
                b.nm_produk,
                a.id_kandang
            FROM stok_produk_perencanaan AS a
            LEFT JOIN tb_produk_perencanaan AS b
                ON b.id_produk = a.id_pakan
            WHERE a.tgl BETWEEN ? AND ?
                AND a.id_kandang = ?
                AND b.kategori = 'pakan'
            GROUP BY
                a.id_pakan,
                b.nm_produk,
                a.id_kandang
        ",
            [
                $awalMinggu->format('Y-m-d'),
                $akhirMinggu,
                $k->id_kandang,
            ]
        );

        $peformance = DB::table('peformance')
            ->where('umur', $nomorMinggu)
            ->where('id_strain', $k->id_strain)
            ->first();



        return [
            'mgg'                  => $nomorMinggu,
            'awal_minggu'         => $awalMinggu->format('Y-m-d'),
            'akhir_minggu'        => $akhirMinggu,
            'tanggal_harian'      => $tanggalHarian,
            'pop_kurang_per_hari' => $popKurangPerHari,
            'h_kuml'              => $hKuml,
            'popD'                => $popD,
            'popC'                => $popC,
            'butir2'              => $butir2,
            'kg_kotor2'           => $kgKotor2,
            'kg_pakan_d'          => $kgPakanD,
            'vi_fcr_d'            => $viFcrD,
            'va_fcr_d'            => $vaFcrD,
            'op_fcr_d'            => $opFcrD,
            'pop_akihir_week'     => $popAkhirWeek,
            'hdl'                 => $hdl,
            'dt_kdng'             => $dtKdng,
            'peformance'           => $peformance,
        ];
    }
}
