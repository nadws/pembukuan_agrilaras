<?php

namespace App\Http\Controllers;

use App\Models\Buku_besar;
use App\Models\Jurnal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\Buku_besarExport;
use App\Exports\KlasifikasiExport;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class BukuBesarController extends Controller
{
    protected $tgl1, $tgl2, $id_akun;
    public function __construct(Request $r)
    {
        if (empty($r->period)) {
            $this->tgl1 = date('2022-01-01');
            $this->tgl2 = date('Y-m-t');
        } elseif ($r->period == 'daily') {
            $this->tgl1 = date('Y-m-d');
            $this->tgl2 = date('Y-m-d');
        } elseif ($r->period == 'weekly') {
            $this->tgl1 = date('Y-m-d', strtotime("-6 days"));
            $this->tgl2 = date('Y-m-d');
        } elseif ($r->period == 'mounthly') {
            $bulan = $r->bulan;
            $tahun = $r->tahun;
            $tglawal = "$tahun" . "-" . "$bulan" . "-" . "01";
            $tglakhir = "$tahun" . "-" . "$bulan" . "-" . "01";

            $this->tgl1 = date('Y-m-01', strtotime($tglawal));
            $this->tgl2 = date('Y-m-t', strtotime($tglakhir));
        } elseif ($r->period == 'costume') {
            $this->tgl1 = $r->tgl1;
            $this->tgl2 = $r->tgl2;
        } elseif ($r->period == 'years') {
            $tahun = $r->tahunfilter;
            $tgl_awal = "$tahun" . "-" . "01" . "-" . "01";
            $tgl_akhir = "$tahun" . "-" . "12" . "-" . "01";

            $this->tgl1 = date('Y-m-01', strtotime($tgl_awal));
            $this->tgl2 = date('Y-m-t', strtotime($tgl_akhir));
        }

        $this->id_proyek = $r->id_proyek ?? 0;
        $this->id_buku = $r->id_buku ?? 2;

        $this->id_akun = $r->id_akun;
    }
    public function index(Request $r)
    {
        $tgl1 =  $this->tgl1;
        $tgl2 =  $this->tgl2;

        $buku = DB::select("SELECT a.id_akun, a.kode_akun , a.nm_akun, b.debit , b.kredit, c.debit as debit_saldo, c.kredit as kredit_saldo
        FROM akun as a

        left JOIN(
            SELECT b.id_akun , sum(b.debit) as debit, sum(b.kredit) as kredit
            FROM jurnal as b
            where b.penutup = 'T' and b.tgl BETWEEN '$tgl1' and '$tgl2'
            group by b.id_akun
        ) as b on b.id_akun = a.id_akun

        left JOIN (
            SELECT c.id_akun , sum(c.debit) as debit, sum(c.kredit) as kredit
            FROM jurnal_saldo as c 
            where  c.tgl BETWEEN '$tgl1' and '$tgl2'
            group by c.id_akun
        ) as c on c.id_akun = a.id_akun
        group by a.id_akun
        ORDER by a.kode_akun ASC;
        ");

        $ditutup = DB::selectOne("SELECT * FROM `jurnal` as a WHERE tgl BETWEEN '2023-05-01' AND '2023-05-31';");

        $data =  [
            'title' => 'Summary Buku Besar',
            'buku' => $buku,
            'penutup' => $ditutup,
            'tgl1' => $tgl1,
            'tgl2' => $tgl2

        ];
        return view('sum_buku.index', $data);
    }

    public function export_buku_besar(Request $r)
    {
        $tgl1 = $r->tgl1;
        $tgl2 = $r->tgl2;
        $buku = DB::select("SELECT a.id_akun, a.kode_akun , a.nm_akun, b.debit , b.kredit, c.debit as debit_saldo, c.kredit as kredit_saldo
        FROM akun as a

        left JOIN(
            SELECT b.id_akun , sum(b.debit) as debit, sum(b.kredit) as kredit
            FROM jurnal as b
            where b.penutup = 'T' and b.tgl BETWEEN '$tgl1' and '$tgl2'
            group by b.id_akun
        ) as b on b.id_akun = a.id_akun

        left JOIN (
            SELECT c.id_akun , sum(c.debit) as debit, sum(c.kredit) as kredit
            FROM jurnal_saldo as c 
            where  c.tgl BETWEEN '$tgl1' and '$tgl2'
            group by c.id_akun
        ) as c on c.id_akun = a.id_akun
        group by a.id_akun
        ORDER by a.kode_akun ASC;
        ");

        $data =  [
            'title' => 'Summary Buku Besar',
            'buku' => $buku,
            'tgl1' => $tgl1,
            'tgl2' => $tgl2

        ];
        return view('sum_buku.export', $data);
    }

    public function detail(Request $r)
    {
        $tgl1 =  $r->tgl1;
        $tgl2 =  $r->tgl2;
        $data = [
            'title' => 'Detail ',
            'detail' => DB::select("SELECT d.no_cfm, d.ket as ket2, a.ket, a.tgl,a.id_akun, d.nm_akun, a.no_nota, a.debit, a.kredit, a.saldo , b.nm_post
            FROM `jurnal` as a
            left join tb_post_center as b on b.id_post_center = a.id_post_center
                        LEFT JOIN (
                            SELECT j.no_nota, j.id_akun, GROUP_CONCAT(DISTINCT j.no_urut SEPARATOR ', ') as no_cfm, GROUP_CONCAT(DISTINCT j.ket SEPARATOR ', ') as ket, GROUP_CONCAT(DISTINCT b.nm_akun SEPARATOR ', ') as nm_akun 
                            FROM jurnal as j
                            LEFT JOIN akun as b ON b.id_akun = j.id_akun
                            WHERE j.id_akun != '$r->id_akun'
                            GROUP BY j.no_nota
                        ) d ON a.no_nota = d.no_nota AND d.id_akun != a.id_akun
                        WHERE a.id_akun = '$r->id_akun' and a.tgl between '$tgl1' and '$tgl2' 
                        order by a.saldo DESC, a.tgl ASC
            "),
            'id_akun' => $r->id_akun,
            'id_klasifikasi' => DB::table('akun')->where('id_akun', $r->id_akun)->first()->id_klasifikasi,
            'tgl1' => $tgl1,
            'tgl2' => $tgl2,
            'nm_akun' => DB::table('akun')->where('id_akun', $r->id_akun)->first()
        ];
        return view('sum_buku.detail', $data);
    }

    public function export_detail(Request $r)
    {
        $tgl1 =  $r->tgl1;
        $tgl2 =  $r->tgl2;

        $id_akun =  $r->id_akun;
        $id_klasifikasi =  $r->id_klasifikasi;

        $total = DB::selectOne("SELECT count(a.id_jurnal) as jumlah FROM jurnal as a where a.id_akun = '$id_akun' and a.tgl between '$tgl1' and '$tgl2' AND a.penutup = 'T'");



        // return Excel::download(new Buku_besarExport($tgl1, $tgl2, $id_akun, $totalrow), 'detail_buku_besar.xlsx');


        $akun = DB::table('akun')->where('id_klasifikasi', $id_klasifikasi)->get();
        $style = [
            'borders' => [
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                ],
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
                ],
            ],
        ];
        $spreadsheet = new Spreadsheet();
        foreach ($akun as $i => $r) {

            $detail = DB::select("SELECT b.nm_post, d.no_cfm, d.ket as ket2, a.ket, a.tgl,a.id_akun, d.nm_akun, a.no_nota, a.debit, a.kredit, a.saldo 
            FROM `jurnal` as a
            left join tb_post_center as b on b.id_post_center = a.id_post_center
                    LEFT JOIN (
                        SELECT c.nm_post,j.no_nota, j.id_akun,  GROUP_CONCAT(DISTINCT j.ket SEPARATOR ', ') as ket, GROUP_CONCAT(DISTINCT j.no_urut SEPARATOR ', ') as no_cfm, GROUP_CONCAT(DISTINCT b.nm_akun SEPARATOR ', ') as nm_akun 
                        FROM jurnal as j
                        LEFT JOIN akun as b ON b.id_akun = j.id_akun
                        LEFT JOIN tb_post_center as c ON c.id_post_center = j.id_post_center
                        WHERE j.id_akun != '$r->id_akun'
                        GROUP BY j.no_nota
                    ) d ON a.no_nota = d.no_nota AND d.id_akun != a.id_akun
                    WHERE a.id_akun = '$r->id_akun' and a.tgl between '$tgl1' and '$tgl2'
                    order by a.saldo DESC, a.tgl ASC");

            $spreadsheet->createSheet();
            $spreadsheet->setActiveSheetIndex($i);
            $s = 'sheet' . $i;
            $s = $spreadsheet->getActiveSheet();
            $s->setTitle(ucwords(substr($r->nm_akun, 0, 30)));

            if (empty($detail)) {
                $s->setCellValue('A1', 'Akun : ' . strtoupper($r->nm_akun));
                $s->setCellValue('A2', '#')
                    ->setCellValue('B2', 'No Urut Jurnal')
                    ->setCellValue('C2', 'No Urut Akun')
                    ->setCellValue('D2', 'Tanggal ' . $tgl1)
                    ->setCellValue('E2', 'Nama Akun Lawan')
                    ->setCellValue('F2', 'Sub Akun')
                    ->setCellValue('G2', 'Keterangan')
                    ->setCellValue('H2', 'Debit')
                    ->setCellValue('I2', 'Kredit')
                    ->setCellValue('J2', 'Saldo');
                $s->getStyle('A2:J2')->applyFromArray($style);
                $s->getStyle("A1")->getFont()->setBold(true);
            } else {
                $kolom = 3;
                $s
                    ->setCellValue('A1', 'Akun : ' . strtoupper($r->nm_akun));
                $s
                    ->setCellValue('A2', '#')
                    ->setCellValue('B2', 'No Urut Jurnal')
                    ->setCellValue('C2', 'No Urut Akun')
                    ->setCellValue('D2', 'Tanggal')
                    ->setCellValue('E2', 'Nama Akun Lawan')
                    ->setCellValue('F2', 'Sub Akun')
                    ->setCellValue('G2', 'Keterangan')
                    ->setCellValue('H2', 'Debit')
                    ->setCellValue('I2', 'Kredit')
                    ->setCellValue('J2', 'Saldo');

                $saldo = 0;

                foreach ($detail as $no => $d) {
                    $saldo += $d->debit - $d->kredit;
                    $s
                        ->setCellValue("A$kolom", $no + 1)
                        ->setCellValue("B$kolom", $d->no_nota)
                        ->setCellValue("C$kolom", $d->no_cfm)
                        ->setCellValue("D$kolom", $d->tgl)
                        ->setCellValue("E$kolom", $d->saldo == 'Y' ? 'Saldo Awal' : ucwords(strtolower($d->nm_akun)))
                        ->setCellValue("F$kolom", ucwords($d->nm_post))
                        ->setCellValue("G$kolom", ucwords($d->ket))
                        ->setCellValue("H$kolom", $d->debit)
                        ->setCellValue("I$kolom", $d->kredit)
                        ->setCellValue("J$kolom", $saldo);
                    $kolom++;
                }

                $bataskun = $kolom - 1;
                $s->getStyle('A2:J' . $bataskun)->applyFromArray($style);
                $s->getStyle("A1")->getFont()->setBold(true);
            }
        }

        $namafile = "Detail Buku Besar $tgl1 ~ $tgl2.xlsx";

        $writer = new Xlsx($spreadsheet);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename=' . $namafile);
        header('Cache-Control: max-age=0');


        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');
        exit();
    }

    public function export_detail_format(Request $r)
    {
        $style_atas = array(
            'font' => [
                'bold' => true, // Mengatur teks menjadi tebal
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
                ]
            ],
        );
        $style_bawah = array(
            'font' => [
                'bold' => true, // Mengatur teks menjadi tebal
            ],
            'borders' => [
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                ],
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
                ],
            ],
        );

        $style = [
            'borders' => [
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                ],
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
                ],
            ],
        ];
        $spreadsheet = new Spreadsheet();
        $akun = DB::table('akun')->where('id_akun', $r->id_akun)->first();

        $spreadsheet->setActiveSheetIndex(0);
        $sheet1 = $spreadsheet->getActiveSheet();
        $sheet1->setTitle('Sheet1');


        $sheet1->getStyle("A1:J1")->applyFromArray($style_atas);

        $sheet1->setCellValue('A1', 'ID');
        $sheet1->setCellValue('B1', 'AGRIKA GATYA ARUM PT');
        $sheet1->setCellValue('C1', 'Cfm');
        $sheet1->setCellValue('D1', 'Tanggal');
        $sheet1->setCellValue('E1', 'Post Center');
        $sheet1->setCellValue('F1', 'Keterangan');
        $sheet1->setCellValue('G1', 'Keterangan2');
        $sheet1->setCellValue('H1', 'Debit');
        $sheet1->setCellValue('I1', 'Kredit');
        $sheet1->setCellValue('J1', 'Balance');

        $kolom = 2;

        $detail = DB::select("SELECT b.nm_akun, a.no_nota, a.tgl, c.nm_akun as nm_akun2, a.ket, a.debit, a.kredit, a.saldo, c.ket2
        FROM jurnal as a 
        left join akun as b on b.id_akun = a.id_akun 
        left join ( SELECT c.id_akun, c.no_nota, GROUP_CONCAT(DISTINCT d.nm_akun SEPARATOR ', ') as nm_akun ,GROUP_CONCAT(DISTINCT c.ket SEPARATOR ', ') as ket2
        FROM jurnal as c left join akun as d on d.id_akun = c.id_akun where c.id_akun != '$r->id_akun' 
        group by c.no_nota ) as c on c.no_nota = a.no_nota AND c.id_akun != a.id_akun 
        WHERE a.id_akun = '$r->id_akun' and a.tgl BETWEEN '$r->tgl1' and '$r->tgl2' 
        order by a.saldo DESC, a.tgl ASC");

        $saldo = 0;
        foreach ($detail as $no => $g) {
            $saldo += $g->debit - $g->kredit;
            $sheet1->setCellValue('A' . $kolom, $no + 1);
            $sheet1->setCellValue('B' . $kolom, $g->nm_akun);
            $sheet1->setCellValue('C' . $kolom, $g->no_nota);

            $sheet1->setCellValue('D' . $kolom, $g->tgl);
            $sheet1->setCellValue('E' . $kolom, $g->saldo == 'Y' ? 'Saldo Awal' : $g->nm_akun2);

            $sheet1->setCellValue('F' . $kolom, $g->ket2);
            $sheet1->setCellValue('G' . $kolom, '');

            $sheet1->setCellValue('H' . $kolom, $g->debit);
            $sheet1->setCellValue('I' . $kolom, $g->kredit);
            $sheet1->setCellValue('J' . $kolom, $saldo);
            $kolom++;
        }
        $sheet1->getStyle('A2:J' . $kolom - 1)->applyFromArray($style);


        $namafile = "Detail buku besar.xlsx";

        $writer = new Xlsx($spreadsheet);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename=' . $namafile);
        header('Cache-Control: max-age=0');


        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');
        exit();
    }

    public function buku_besar_new(Request $r)
    {
        $tgl1 =  $this->tgl1;
        $tgl2 =  $this->tgl2;

        $buku = DB::select("SELECT a.id_klasifikasi, d.nm_subklasifikasi, a.id_akun, a.kode_akun , a.nm_akun, sum(b.debit) as debit , sum(b.kredit) as kredit, sum(c.debit) as debit_saldo, sum(c.kredit) as kredit_saldo
        FROM akun as a

        left JOIN(
            SELECT b.id_akun , sum(b.debit) as debit, sum(b.kredit) as kredit
            FROM jurnal as b
            where b.penutup = 'T' and b.tgl BETWEEN '$tgl1' and '$tgl2'
            group by b.id_akun
        ) as b on b.id_akun = a.id_akun

        left JOIN (
            SELECT c.id_akun , sum(c.debit) as debit, sum(c.kredit) as kredit
            FROM jurnal_saldo as c 
            where  c.tgl BETWEEN '$tgl1' and '$tgl2'
            group by c.id_akun
        ) as c on c.id_akun = a.id_akun
        left join subklasifikasi_akun as d on d.id_subklasifikasi_akun = a.id_klasifikasi
        group by a.id_klasifikasi
        ORDER by d.nm_subklasifikasi ASC;
        ");
        $data =  [
            'title' => 'Summary Buku Besar',
            'tgl1' => $tgl1,
            'tgl2' => $tgl2,
            'buku' => $buku

        ];
        return view('sum_buku.buku_besar_new', $data);
    }

    public function loadDetail(Request $r)
    {
        $tgl1 =  $r->tgl1;
        $tgl2 =  $r->tgl2;

        $buku = DB::select("SELECT a.id_akun, a.kode_akun , a.nm_akun, b.debit , b.kredit, c.debit as debit_saldo, c.kredit as kredit_saldo
        FROM akun as a
        left JOIN(
            SELECT b.id_akun , sum(b.debit) as debit, sum(b.kredit) as kredit
            FROM jurnal as b
            where b.penutup = 'T' and b.tgl BETWEEN '$tgl1' and '$tgl2'
            group by b.id_akun
        ) as b on b.id_akun = a.id_akun

        left JOIN (
            SELECT c.id_akun , sum(c.debit) as debit, sum(c.kredit) as kredit
            FROM jurnal_saldo as c 
            where  c.tgl BETWEEN '$tgl1' and '$tgl2'
            group by c.id_akun
        ) as c on c.id_akun = a.id_akun
        where a.id_klasifikasi = $r->id_klasifikasi
        group by a.id_akun
        ORDER by a.kode_akun ASC;
        ");
        $data = [
            'detail' => $buku,
            'tgl1' => $tgl1,
            'tgl2' => $tgl2
        ];
        return view('sum_buku.loadDetail', $data);
    }
}
