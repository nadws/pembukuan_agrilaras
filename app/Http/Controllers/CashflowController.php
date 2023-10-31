<?php

namespace App\Http\Controllers;

use App\Models\CashflowModel;
use App\Models\ProfitModel;
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
            'piutang' => DB::select("SELECT a.id_akun,a.nm_akun, b.debit, b.kredit
            FROM akun as a
            left join (
            SELECT b.id_akun , sum(b.debit) as debit , sum(b.kredit) as kredit
            FROM jurnal as b
            where b.tgl BETWEEN '2020-01-01' and '$tgl_back' and  b.id_buku in('6','1')
            group by b.id_akun
            ) as b on b.id_akun = a.id_akun
            where a.id_akun in(SELECT t.id_akun FROM akuncash_ibu as t where t.kategori = '1');"),

            'penjualan' => DB::select("SELECT a.id_akun,a.nm_akun, b.debit, b.kredit
            FROM akun as a
            left join (
            SELECT b.id_akun, sum(b.debit) as debit , sum(b.kredit) as kredit
            FROM jurnal as b
            where b.tgl BETWEEN '$tgl1' and '$tgl2' and b.id_buku = '6'
            group by b.id_akun
            ) as b on b.id_akun = a.id_akun
            where a.id_akun in (SELECT t.id_akun FROM akuncash_ibu as t where t.kategori = '2');"),

            'uang' => DB::select("SELECT a.id_akun, a.nm_akun, b.debit, b.kredit
            FROM akun as a
            left join (
            SELECT b.id_akun, sum(b.debit) as debit , sum(b.kredit) as kredit
            FROM jurnal as b
            where b.tgl BETWEEN '$tgl1' and '$tgl2' and b.id_buku = '6'
            group by b.id_akun
            ) as b on b.id_akun = a.id_akun
            where a.id_akun in (SELECT t.id_akun FROM akuncash_ibu as t where t.kategori = '3');"),

            'piutang2' => DB::select("SELECT a.id_akun,a.nm_akun, b.debit, b.kredit
            FROM akun as a
            left join (
            SELECT b.id_akun, sum(b.debit) as debit , sum(b.kredit) as kredit
            FROM jurnal as b
            where b.tgl BETWEEN '2020-01-01' and '$tgl2' and b.id_buku in('6','1')
            group by b.id_akun
            ) as b on b.id_akun = a.id_akun
            where a.id_akun in (SELECT t.id_akun FROM akuncash_ibu as t where t.kategori = '4');"),

            'kerugian' => DB::selectOne("SELECT a.id_akun,a.tgl, a.no_nota, sum(a.debit) AS debit, sum(a.kredit) AS kredit
            FROM jurnal AS a
            LEFT JOIN akun AS b ON b.id_akun = a.id_akun
            WHERE a.tgl BETWEEN '$tgl1' AND '$tgl2' AND a.id_akun = 36"),

            // 'biaya' => DB::select("SELECT a.id_akun, a.nm_akun, b.debit 
            // FROM akun as a 
            // LEFT JOIN ( SELECT a.id_akun, a.no_nota, c.nm_akun, sum(a.debit) as debit 
            // FROM jurnal as a 
            // left join ( SELECT b.no_nota , b.id_akun, c.nm_akun FROM jurnal as b 
            // left join akun as c on c.id_akun = b.id_akun 
            // where b.id_akun in (SELECT t.id_akun FROM akuncash_ibu as t where t.kategori in ('6','7')) and b.tgl BETWEEN '$tgl1' and '$tgl2' and b.kredit != 0 and b.id_buku in(2,10,12) 
            // GROUP by b.no_nota ) as b on b.no_nota = a.no_nota 
            // left join akun as c on c.id_akun = a.id_akun 
            // where a.id_buku in(2,10,12) and a.debit != 0 and a.tgl BETWEEN '$tgl1' and '$tgl2' and b.id_akun is not null 
            // group by a.id_akun ) AS b on b.id_akun = a.id_akun 
            // where a.id_klasifikasi in('3','6','11','12')"),

            'biaya' => DB::select("SELECT a.id_akun, a.no_nota, c.nm_akun, sum(a.debit) as debit 
            FROM jurnal as a 
            left join ( SELECT b.no_nota , b.id_akun, c.nm_akun FROM jurnal as b 
            left join akun as c on c.id_akun = b.id_akun 
            where b.id_akun in (SELECT t.id_akun FROM akuncash_ibu as t where t.kategori in ('6')) and b.tgl BETWEEN '$tgl1' and '$tgl2' and b.kredit != 0 and b.id_buku in(2,10,12) 
            GROUP by b.no_nota ) as b on b.no_nota = a.no_nota 
            left join akun as c on c.id_akun = a.id_akun 
            where a.id_buku in(2,10,12) and a.debit != 0 and a.tgl BETWEEN '$tgl1' and '$tgl2' and b.id_akun is not null 
            group by a.id_akun"),
            'biaya_proyek' => DB::select("SELECT a.id_akun, a.no_nota, c.nm_akun, sum(a.debit) as debit 
            FROM jurnal as a 
            left join ( SELECT b.no_nota , b.id_akun, c.nm_akun FROM jurnal as b 
            left join akun as c on c.id_akun = b.id_akun 
            where b.id_akun in (SELECT t.id_akun FROM akuncash_ibu as t where t.kategori in ('7')) and b.tgl BETWEEN '$tgl1' and '$tgl2' and b.kredit != 0 and b.id_buku in(2,10,12) 
            GROUP by b.no_nota ) as b on b.no_nota = a.no_nota 
            left join akun as c on c.id_akun = a.id_akun 
            where a.id_buku in(2,10,12) and a.debit != 0 and a.tgl BETWEEN '$tgl1' and '$tgl2' and b.id_akun is not null 
            group by a.id_akun"),

            'uangbiayacosh' => CashflowModel::uangBiaya($tgl1, $tgl2, '6'),
            'uangbiayacoshbalance' => CashflowModel::uangBiayabalance($tgl2, '6'),
            'uangbiayaproyekbalance' => CashflowModel::uangBiayabalance($tgl2, '7'),
            'uangbiayaproyek' => CashflowModel::uangBiaya($tgl1, $tgl2, '7'),
            'biaya_admin' => DB::selectOne("SELECT sum(a.debit) as debit FROM jurnal as a where a.id_akun = '8' and a.tgl between
            '$tgl1' and '$tgl2' and a.id_buku = '6' "),
            'hutang_herry' => DB::selectOne("SELECT a.id_akun, a.nm_akun, b.debit, b.kredit
            FROM akun as a 
            left join (
            SELECT a.id_akun, sum(a.kredit) as kredit , sum(a.debit) as debit
            FROM jurnal as a 
            where a.tgl BETWEEN '$tgl1' and '$tgl2' and a.id_akun = '19'
            ) as b on b.id_akun = a.id_akun
            where a.id_akun = '19';"),
            'tgl_back' => $tgl_back,
            'tgl2' => $tgl2,
            'tgl1' => $tgl1,
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

    function cashflowsetahun(Request $r)
    {
        if (empty($r->tahun)) {
            $tahun = date('Y');
        } else {
            $tahun = $r->tahun;
        }

        $id_akun1 = ['23', '66', '99', '36'];
        $id_akun2 = ['26', '37', '38', '39', '81', '83', '84'];
        $pendapatan = CashflowModel::cashflow_uangmasuk_setahun($id_akun1, $id_akun2, $tahun, '6');

        $id_akun3 = ['26', '37', '38', '39', '81', '83', '84', '36'];
        $id_akun4 = ['23', '66', '99'];
        $piutang = CashflowModel::cashflow_uangmasuk_setahun($id_akun3, $id_akun4, $tahun, '6');

        $id_akun3 = ['26', '37', '38', '39', '81', '83', '84', '36'];
        $id_akun4 = ['19'];
        $hutang = CashflowModel::cashflow_uangmasuk_setahun($id_akun3, $id_akun4, $tahun, '7');


        $uang_cost = CashflowModel::cashflow_uang_cost($tahun, '6');
        $uang_proyek = CashflowModel::cashflow_uang_cost($tahun, '7');

        $data = [];
        foreach ($pendapatan as $transaction) {

            $month = date('F', strtotime("{$transaction->tahun}-{$transaction->bulan}-01"));

            // Ubah bulan dan tahun menjadi format yang benar
            $nominal = $transaction->debit; // Menghitung nominal

            // Menambahkan data akun dan nominal ke struktur data
            if (!isset($data[$transaction->id_akun])) {
                $data[$transaction->id_akun] = [
                    'January' => 0,
                    'February' => 0,
                    'March' => 0,
                    'April' => 0,
                    'May' => 0,
                    'June' => 0,
                    'July' => 0,
                    'August' => 0,
                    'September' => 0,
                    'October' => 0,
                    'November' => 0,
                    'December' => 0,
                ];
            }
            // Menambahkan data nominal ke struktur data
            $data[$transaction->id_akun][$month] = $nominal;
        }
        $data2 = [];
        foreach ($hutang as $transaction) {

            $month = date('F', strtotime("{$transaction->tahun}-{$transaction->bulan}-01"));

            // Ubah bulan dan tahun menjadi format yang benar
            $nominal = $transaction->debit; // Menghitung nominal

            // Menambahkan data akun dan nominal ke struktur data
            if (!isset($data2[$transaction->id_akun])) {
                $data2[$transaction->id_akun] = [
                    'January' => 0,
                    'February' => 0,
                    'March' => 0,
                    'April' => 0,
                    'May' => 0,
                    'June' => 0,
                    'July' => 0,
                    'August' => 0,
                    'September' => 0,
                    'October' => 0,
                    'November' => 0,
                    'December' => 0,
                ];
            }
            // Menambahkan data nominal ke struktur data
            $data2[$transaction->id_akun][$month] = $nominal;
        }
        $data3 = [];
        foreach ($uang_cost as $transaction) {

            $month = date('F', strtotime("{$transaction->tahun}-{$transaction->bulan}-01"));

            // Ubah bulan dan tahun menjadi format yang benar
            $nominal = $transaction->debit; // Menghitung nominal

            // Menambahkan data akun dan nominal ke struktur data
            if (!isset($data3[$transaction->id_akun])) {
                $data3[$transaction->id_akun] = [
                    'January' => 0,
                    'February' => 0,
                    'March' => 0,
                    'April' => 0,
                    'May' => 0,
                    'June' => 0,
                    'July' => 0,
                    'August' => 0,
                    'September' => 0,
                    'October' => 0,
                    'November' => 0,
                    'December' => 0,
                ];
            }
            // Menambahkan data nominal ke struktur data
            $data3[$transaction->id_akun][$month] = $nominal;
        }
        $data4 = [];
        foreach ($uang_proyek as $transaction) {

            $month = date('F', strtotime("{$transaction->tahun}-{$transaction->bulan}-01"));

            // Ubah bulan dan tahun menjadi format yang benar
            $nominal = $transaction->debit; // Menghitung nominal

            // Menambahkan data akun dan nominal ke struktur data
            if (!isset($data4[$transaction->id_akun])) {
                $data4[$transaction->id_akun] = [
                    'January' => 0,
                    'February' => 0,
                    'March' => 0,
                    'April' => 0,
                    'May' => 0,
                    'June' => 0,
                    'July' => 0,
                    'August' => 0,
                    'September' => 0,
                    'October' => 0,
                    'November' => 0,
                    'December' => 0,
                ];
            }
            // Menambahkan data nominal ke struktur data
            $data4[$transaction->id_akun][$month] = $nominal;
        }
        $data5 = [];
        foreach ($piutang as $transaction) {

            $month = date('F', strtotime("{$transaction->tahun}-{$transaction->bulan}-01"));

            // Ubah bulan dan tahun menjadi format yang benar
            $nominal = $transaction->debit; // Menghitung nominal

            // Menambahkan data akun dan nominal ke struktur data
            if (!isset($data5[$transaction->id_akun])) {
                $data5[$transaction->id_akun] = [
                    'January' => 0,
                    'February' => 0,
                    'March' => 0,
                    'April' => 0,
                    'May' => 0,
                    'June' => 0,
                    'July' => 0,
                    'August' => 0,
                    'September' => 0,
                    'October' => 0,
                    'November' => 0,
                    'December' => 0,
                ];
            }
            // Menambahkan data nominal ke struktur data
            $data5[$transaction->id_akun][$month] = $nominal;
        }

        $datas = [
            'title' => 'Cashflow Setahun',
            'tahun' => DB::select("SELECT YEAR(a.tgl) as tahun FROM jurnal as a where YEAR(a.tgl) != 0 group by YEAR(a.tgl);"),
            'thn' => $tahun
        ];

        return view('cashflow.cashflow_setahun', compact('data', 'data2', 'data3', 'data4', 'data5'), $datas);
    }
}
