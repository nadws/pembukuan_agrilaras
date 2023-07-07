<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx\Rels;

class ControlflowController extends Controller
{
    protected $tgl1, $tgl2, $period;
    public function __construct(Request $r)
    {
        if (empty($r->period)) {
            $this->tgl1 = date('Y-m-01');
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
    }
    public function index()
    {
        $tgl1 =  $this->tgl1;
        $tgl2 =  $this->tgl2;
        $data = [
            'title' => 'Dashboard Pembukuan',
            'tgl1' => $tgl1,
            'tgl2' => $tgl2
        ];
        return view('controlflow.dashboard', $data);
    }
    public function loadcontrolflow(Request $r)
    {
        $tgl1 =  $r->tgl1;
        $tgl2 =  $r->tgl2;


        $data = [
            'title' => 'load',
            'tgl1' => $tgl1,
            'tgl2' => $tgl2,
            'pendapatan' => DB::select("SELECT a.id_akun, c.nm_akun, b.kredit
            FROM akuncontrol as a 
            left join (
            SELECT b.id_akun, sum(b.debit) as debit, sum(b.kredit) as kredit
                FROM jurnal as b
                WHERE b.id_buku not in('1','5') and b.kredit != 0 and b.penutup = 'T' and b.tgl between '$tgl1' and '$tgl2'
                group by b.id_akun
            ) as b on b.id_akun = a.id_akun
            left join akun as c on c.id_akun = a.id_akun
            where a.id_kategori_cashcontrol = '1';"),

            'biaya' => DB::select("SELECT a.id_akun, c.nm_akun, b.kredit, b.debit
            FROM akuncontrol as a 
            left join (
            SELECT b.id_akun, sum(b.debit) as debit, sum(b.kredit) as kredit
                FROM jurnal as b
                WHERE b.id_buku not in('1','5') and b.debit != 0 and b.penutup = 'T' and b.tgl between '$tgl1' and '$tgl2'
                group by b.id_akun
            ) as b on b.id_akun = a.id_akun
            left join akun as c on c.id_akun = a.id_akun
            where a.id_kategori_cashcontrol = '2';"),

        ];
        return view('controlflow.load', $data);
    }

    public function loadInputAkunCashflow(Request $r)
    {
        $data = [
            'title' => 'load',
            'akun1' => DB::Select("SELECT * FROM akun as a where a.id_akun not in (SELECT b.id_akun FROM kategori_cashcontrol as b where b.jenis = '$r->jenis') "),
            'cash' => DB::select("SELECT a.*, b.nm_akun FROM kategori_cashcontrol as a left join akun as b on b.id_akun = a.id_akun where a.jenis = '$r->jenis'"),
            'jenis' => $r->jenis

        ];
        return view('controlflow.loadinputakun', $data);
    }

    public function save_kategoriCashcontrol(Request $r)
    {
        $data = [
            'id_akun' => $r->id_akun,
            'jenis' => $r->jenis,
            'urutan' => $r->urutan
        ];
        DB::table('kategori_cashcontrol')->insert($data);
    }

    public function edit_kategoriCashcontrol(Request $r)
    {
        for ($x = 0; $x < count($r->urutan); $x++) {
            $data = [
                'urutan' => $r->urutan[$x],
            ];
            DB::table('kategori_cashcontrol')->where('id_kategori_cashcontrol', $r->id_kategori_cashcontrol[$x])->update($data);
        }
    }

    public function loadInputsub(Request $r)
    {
        $tgl1 =  $r->tgl1;
        $tgl2 =  $r->tgl2;




        $data = [
            'akun' => DB::Select("SELECT * FROM akun as a  "),
            'akun2' => DB::select("SELECT a.*, b.nm_akun, c.debit, c.kredit, d.jenis
            FROM akuncontrol as a 
            left join akun as b on b.id_akun = a.id_akun
            left join (
                SELECT sum(c.debit) as debit , sum(c.kredit) as kredit , c.id_akun
                FROM jurnal as c
                where c.tgl between '$tgl1' and '$tgl2'
                group by c.id_akun
            ) as c on c.id_akun = a.id_akun
            left join kategori_cashcontrol as d on d.id_kategori_cashcontrol = a.id_kategori_cashcontrol
            where a.id_kategori_cashcontrol = '$r->id_kategori_akun'"),
            'id_kategori' => $r->id_kategori_akun
        ];
        return view('controlflow.loadtambahakun', $data);
    }

    public function SaveSubAkunCashflow(Request $r)
    {
        $data = [
            'id_kategori_cashcontrol' => $r->id_kategori,
            'id_akun' => $r->id_akun
        ];
        DB::table('akuncontrol')->insert($data);
    }
    public function deleteSubAkunCashflow(Request $r)
    {
        DB::table('akuncontrol')->where('id_akuncontrol', $r->id_akuncontrol)->delete();
    }
    public function deleteAkunCashflow(Request $r)
    {
        DB::table('akuncontrol')->where('id_kategori_cashcontrol', $r->id_kategori)->delete();
        DB::table('kategori_cashcontrol')->where('id_kategori_cashcontrol', $r->id_kategori)->delete();
    }

    public function view_akun()
    {
        $data = [
            'akun' => DB::Select("SELECT * FROM akun as a where a.id_akun not in (SELECT b.id_akun FROM akuncontrol as b ) "),
        ];

        return view('controlflow.view_akun', $data);
    }

    public function print(Request $r)
    {
        $tgl1 =  $r->tgl1;
        $tgl2 =  $r->tgl2;
        $cash = DB::select("SELECT a.id_kategori_cashcontrol,  a.nama, b.debit , b.kredit
        FROM kategori_cashcontrol as a 
        left join (
        SELECT b.id_akuncontrol, b.id_kategori_cashcontrol, sum(c.debit) as debit , sum(c.kredit) as kredit
            FROM akuncontrol as b 
            left join (
            SELECT c.id_akun , sum(c.debit) as debit , sum(c.kredit) as kredit
                FROM jurnal as c
                where c.tgl between '$tgl1' and '$tgl2'
                group by c.id_akun
            ) as c on c.id_akun = b.id_akun
            group by b.id_kategori_cashcontrol
        ) as b on b.id_kategori_cashcontrol = a.id_kategori_cashcontrol
        where a.jenis = '1'
        order by a.urutan ASC;");

        $pengeluaran = DB::select("SELECT a.id_kategori_cashcontrol,  a.nama, b.debit , b.kredit
        FROM kategori_cashcontrol as a 
        left join (
        SELECT b.id_akuncontrol, b.id_kategori_cashcontrol, sum(c.debit) as debit , sum(c.kredit) as kredit
            FROM akuncontrol as b 
            left join (
            SELECT c.id_akun , sum(c.debit) as debit , sum(c.kredit) as kredit
                FROM jurnal as c
                where c.tgl between '$tgl1' and '$tgl2'
                group by c.id_akun
            ) as c on c.id_akun = b.id_akun
            group by b.id_kategori_cashcontrol
        ) as b on b.id_kategori_cashcontrol = a.id_kategori_cashcontrol
        where a.jenis = '2'
        order by a.urutan ASC;");

        $data = [
            'title' => 'Print',
            'cash' => $cash,
            'pengeluaran' => $pengeluaran,
            'tgl1' => $tgl1,
            'tgl2' => $tgl2

        ];
        return view('controlflow.print', $data);
    }
}
