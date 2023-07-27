<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProfitController extends Controller
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
        $data =  [
            'title' => 'Profit and Loss',
            'tgl1' => $this->tgl1,
            'tgl2' => $this->tgl2,
        ];
        return view('profit.index', $data);
    }

    public function load(Request $r)
    {
        $tgl1 =  $r->tgl1;
        $tgl2 =  $r->tgl2;
        $akun = DB::table('akun')->get();

        $data = [
            'title' => 'Load Profit',
            'tgl1' => $tgl1,
            'tgl2' => $tgl2,
            'akun' => $akun,
            'subKategori1' => DB::table('sub_kategori_cashflow')
                ->where('jenis', 1)
                ->orderBy('urutan', 'ASC')
                ->get(),
            'subKategori2' => DB::table('sub_kategori_cashflow')
                ->where('jenis', 2)
                ->orderBy('urutan', 'ASC')
                ->get(),

        ];
        return view('profit.load', $data);
    }

    public function modal(Request $r)
    {
        $akunProfit = DB::table('profit_akun as a')
            ->join('akun as b', 'a.id_akun', 'b.id_akun')
            ->where('a.kategori_id', $r->id_kategori)
            ->orderBy('a.urutan', 'ASC')
            ->get();
        $akun = DB::select("SELECT * FROM akun as a 
        where a.id_akun not in(SELECT b.id_akun FROM profit_akun as b)");
        $data = [
            'akunProfit' => $akunProfit,
            'akun' => $akun
        ];
        return view('profit.modal', $data);
    }

    public function delete(Request $r)
    {
        DB::table('profit_akun')->where('id_profit_akun', $r->id_profit)->delete();
    }

    public function add(Request $r)
    {
        DB::table('profit_akun')->insert([
            'urutan' => $r->urutan,
            'id_akun' => $r->id_akun,
            'kategori_id' => $r->kategori_id,
        ]);
    }

    public function getQueryProfit($id_kategori, $jenis, $tgl1, $tgl2)
    {
        return DB::select("SELECT c.nm_akun, b.kredit, b.debit
        FROM profit_akun as a 
        left join (
        SELECT b.id_akun, sum(b.debit) as debit, sum(b.kredit) as kredit
            FROM jurnal as b
            WHERE b.id_buku not in('1','5') and $jenis != 0 and b.penutup = 'T' and b.tgl between '$tgl1' and '$tgl2'
            group by b.id_akun
        ) as b on b.id_akun = a.id_akun
        left join akun as c on c.id_akun = a.id_akun
        where a.kategori_id = '$id_kategori';");
    }

    public function print(Request $r)
    {
        $tgl1 =  $r->tgl1;
        $tgl2 =  $r->tgl2;

        $data =  [
            'title' => 'Profit and Loss',
            'tgl1' => $tgl1,
            'tgl2' => $tgl2,
            'subKategori1' => DB::table('sub_kategori_cashflow')
                ->where('jenis', 1)
                ->orderBy('urutan', 'ASC')
                ->get(),
            'subKategori2' => DB::table('sub_kategori_cashflow')
                ->where('jenis', 2)
                ->orderBy('urutan', 'ASC')
                ->get()

        ];
        return view('profit.print', $data);
    }

    public function load_uraian(Request $r)
    {
        $data = [
            'subKategori' => DB::table('sub_kategori_cashflow')->where('jenis', $r->jenis)->orderBy('urutan', 'ASC')->get()
        ];
        return view('profit.load_uraian', $data);
    }

    public function save_subkategori(Request $r)
    {
        DB::table('sub_kategori_cashflow')->insert($r->all());
    }

    public function delete_subkategori(Request $r)
    {
        DB::table('sub_kategori_cashflow')->where('id', $r->id)->delete();
    }

    public function update(Request $r)
    {
        for ($i = 0; $i < count($r->id_edit); $i++) {
            DB::table('sub_kategori_cashflow')->where('id', $r->id_edit[$i])->update([
                'sub_kategori' => $r->nm_kategori[$i],
                'urutan' => $r->urutan[$i],
            ]);
        }
    }
    public function count_sisa(Request $r)
    {

        $sisa = DB::selectOne("SELECT COUNT(*) as sisa FROM akun as a
        LEFT JOIN profit_akun as b ON a.id_akun= b.id_akun
        WHERE b.id_akun IS null");

        $sisa2 = DB::selectOne("SELECT COUNT(*) as sisa FROM akun as a
        LEFT JOIN akun_neraca as b ON a.id_akun= b.id_akun
        WHERE b.id_akun IS null");

        echo $r->jenis == 'profit' ? $sisa->sisa : $sisa2->sisa;
    }
    public function view_akun()
    {
        $data = [
            'akun' => DB::Select("SELECT a.id_akun,a.nm_akun, b.id_akun as ada FROM akun as a
            LEFT JOIN profit_akun as b ON a.id_akun= b.id_akun"),
        ];

        return view('profit.view_akun', $data);
    }
}
