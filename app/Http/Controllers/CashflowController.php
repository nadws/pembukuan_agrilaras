<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CashflowController extends Controller
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
    public function index(Request $r)
    {
        $tgl1 =  $r->tgl1;
        $tgl2 =  $r->tgl2;
        $tgl2_pref = date('Y-m-15', strtotime($tgl2));
        $tgl_back = date('Y-m-t', strtotime('previous month', strtotime($tgl2_pref)));


        $data = [
            'title' => 'Cashflow',
            'piutang' => DB::select("SELECT a.nm_akun, b.debit, b.kredit
            FROM akun as a
            left join (
            SELECT b.id_akun , sum(b.debit) as debit , sum(b.kredit) as kredit
            FROM jurnal as b
            where b.tgl BETWEEN '2020-01-01' and '$tgl_back' and  b.id_buku in('6','1')
            group by b.id_akun
            ) as b on b.id_akun = a.id_akun
            where a.id_akun in(SELECT t.id_akun FROM akuncash_ibu as t where t.kategori = '1');"),

            'penjualan' => DB::select("SELECT a.nm_akun, b.debit, b.kredit
            FROM akun as a
            left join (
            SELECT b.id_akun, sum(b.debit) as debit , sum(b.kredit) as kredit
            FROM jurnal as b
            where b.tgl BETWEEN '$tgl1' and '$tgl2' and b.id_buku = '6'
            group by b.id_akun
            ) as b on b.id_akun = a.id_akun
            where a.id_akun in (SELECT t.id_akun FROM akuncash_ibu as t where t.kategori = '2');"),

            'uang' => DB::select("SELECT a.nm_akun, b.debit, b.kredit
            FROM akun as a
            left join (
            SELECT b.id_akun, sum(b.debit) as debit , sum(b.kredit) as kredit
            FROM jurnal as b
            where b.tgl BETWEEN '$tgl1' and '$tgl2' and b.id_buku = '6'
            group by b.id_akun
            ) as b on b.id_akun = a.id_akun
            where a.id_akun in (SELECT t.id_akun FROM akuncash_ibu as t where t.kategori = '3');"),

            'piutang2' => DB::select("SELECT a.nm_akun, b.debit, b.kredit
            FROM akun as a
            left join (
            SELECT b.id_akun, sum(b.debit) as debit , sum(b.kredit) as kredit
            FROM jurnal as b
            where b.tgl BETWEEN '2020-01-01' and '$tgl2' and b.id_buku in('6','1')
            group by b.id_akun
            ) as b on b.id_akun = a.id_akun
            where a.id_akun in (SELECT t.id_akun FROM akuncash_ibu as t where t.kategori = '4');"),

            'kerugian' => DB::selectOne("SELECT a.tgl, a.no_nota, sum(a.debit) AS debit, sum(a.kredit) AS kredit
            FROM jurnal AS a
            LEFT JOIN akun AS b ON b.id_akun = a.id_akun
            WHERE a.tgl BETWEEN '$tgl1' AND '$tgl2' AND a.id_akun = 36"),

            'biaya' => DB::select("SELECT a.nm_akun, b.debit, b.kredit
            FROM akun as a
            left join (
             SELECT b.id_akun, sum(b.debit) as debit , sum(b.kredit) as kredit, c.akunvs
             FROM jurnal as b
             left join (
                 SELECT c.no_nota, c.id_akun as akunvs
                FROM jurnal as c 
                WHERE c.id_akun in (SELECT t.id_akun FROM akuncash_ibu as t where t.kategori = '6')
                group by c.no_nota
             ) as c on c.no_nota = b.no_nota
             where b.tgl BETWEEN '$tgl1' and '$tgl2' and b.id_buku = '2' and c.akunvs is not null
             group by b.id_akun
            ) as b on b.id_akun = a.id_akun
            where a.id_akun in (SELECT t.id_akun FROM akuncash_ibu as t where t.kategori = '5'); "),

            'uangbiaya' => DB::select("SELECT ak.nm_akun, a.debit , a.kredit
            FROM akun as ak
            
            left join (
            SELECT a.id_akun , sum(a.debit) as debit , sum(a.kredit) as kredit
                FROM jurnal as a 
                left join (
                	SELECT j.no_nota, j.id_akun
                    FROM jurnal as j
                    LEFT JOIN akun as b ON b.id_akun = j.id_akun
                    WHERE j.debit != '0'
                    GROUP BY j.no_nota
                ) d ON a.no_nota = d.no_nota AND d.id_akun != a.id_akun
                WHERE  a.tgl between '$tgl1' and '$tgl2'  and a.id_buku = '2'
                 group by a.id_akun
            ) as a on a.id_akun = ak.id_akun
            left join akuncash_ibu as acb on acb.id_akun = ak.id_akun and acb.kategori = '6'
            WHERE ak.id_akun in (SELECT t.id_akun FROM akuncash_ibu as t where t.kategori = '6')
            order by acb.urutan ASC
            "),

            'tgl_back' => $tgl_back,
            'tgl2' => $tgl2
        ];
        return view('cashflow.cashflow', $data);
    }

    public function loadInputKontrol(Request $r)
    {
        $data = [
            'title' => 'load',
            'akun1' => DB::Select("SELECT * FROM akun as a where a.id_akun not in (SELECT t.id_akun FROM akuncash_ibu as t where t.kategori = '$r->kategori')"),
            'cash' => DB::select("SELECT *
            FROM akuncash_ibu as a 
            left join akun as b on b.id_akun = a.id_akun
            where a.kategori = '$r->kategori'
            order by a.urutan ASC
            "),
            'kategori' => $r->kategori

        ];
        return view('cashflow.loadinputakun', $data);
    }

    public function save_akun_ibu(Request $r)
    {
        $data = [
            'id_akun' => $r->id_akun,
            'kategori' => $r->kategori,
            'urutan' => $r->urutan
        ];
        DB::table('akuncash_ibu')->insert($data);
        DB::table('akun')->where('id_akun', $r->id_akun)->update(['cash_uang_ditarik' => 'Y']);
    }

    public function delete_akun_ibu(Request $r)
    {
        $id_akun =  DB::table('akuncash_ibu')->where('id_akuncashibu', $r->id_akuncashibu)->first();

        DB::table('akun')->where('id_akun', $id_akun->id_akun)->update(['cash_uang_ditarik' => 'T']);
        DB::table('akuncash_ibu')->where('id_akuncashibu', $r->id_akuncashibu)->delete();
    }

    public function edit_akun_ibu(Request $r)
    {
        for ($x = 0; $x < count($r->id_akuncashibu); $x++) {
            $data = [
                'urutan' => $r->urutan[$x]
            ];
            DB::table('akuncash_ibu')->where('id_akuncashibu', $r->id_akuncashibu[$x])->update($data);
        }
    }

    public function seleksi_akun_control_ditarik(Request $r)
    {
        for ($x = 0; $x < count($r->id_akun); $x++) {
 
            $data = [
                'cash_uang_ditarik' => $r->cash_uang_ditarik[$x]
            ];
            DB::table('akun')->where('id_akun', $r->id_akun[$x])->update($data);
        }
        return redirect()->route('controlflow')->with('sukses', 'Berhasil input akun');
    }
}
