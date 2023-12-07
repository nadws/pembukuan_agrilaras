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

        // $buku = DB::select("SELECT a.no_nota,a.id_akun, b.kode_akun, b.nm_akun, sum(a.debit) as debit , sum(a.kredit) as kredit 
        // FROM jurnal as a 
        // left join akun as b on b.id_akun = a.id_akun
        // WHERE a.tgl BETWEEN '$tgl1' and '$tgl2'  and a.id_buku != '5'
        // group by a.id_akun
        // ORDER by b.kode_akun ASC;");

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
                $s
                    ->setCellValue('A1', 'Akun : ' . strtoupper($r->nm_akun));
                $s
                    ->setCellValue('A2', '#')
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
}
