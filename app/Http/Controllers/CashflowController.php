<?php

namespace App\Http\Controllers;

use App\Models\CashflowModel;
use App\Models\CashIbuModel;
use App\Models\ProfitModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

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
        $tgl_back1 = date('Y-m-1', strtotime('previous month', strtotime($tgl2_pref)));


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
            WHERE a.tgl BETWEEN '$tgl_back1' AND '$tgl_back' AND a.id_akun = 36"),
            'kerugianBulanIni' => DB::selectOne("SELECT a.id_akun,a.tgl, a.no_nota, sum(a.debit) AS debit, sum(a.kredit) AS kredit
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

            'biaya_proyek' => DB::select("SELECT a.id_akun, a.no_nota, c.nm_akun, sum(a.debit) as debit , c.id_klasifikasi
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

            'bunga_bank' => DB::selectOne("SELECT b.tgl, b.id_akun, b.no_nota, b.debit, sum(b.kredit) as kredit
            FROM jurnal as b 
            left join akun as c on c.id_akun = b.id_akun
            where b.kredit != 0 and b.id_akun in('8','101') and b.id_buku in ('7') 
            and b.tgl BETWEEN '$tgl1' and '$tgl2'
           "),
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
        $tahun2 = $tahun - 1;

        $id_akun1 = ['23', '66', '99', '36'];
        $id_akun2 = ['26', '37', '38', '39', '81', '83', '84'];
        $id_buku = ['6'];
        $pendapatan = CashflowModel::cashflow_uangmasuk_setahun($id_akun1, $id_akun2, $tahun, $id_buku);
        $ttl_pendapatan = CashflowModel::ttl_cashflow_uangmasuk_setahun($id_akun1, $id_akun2, $tahun2, $id_buku);

        $id_akun3 = ['26', '37', '38', '39', '81', '83', '84', '36'];
        $id_akun4 = ['23', '66', '99'];
        $id_buku = ['6'];
        $piutang = CashflowModel::cashflow_uangmasuk_setahun($id_akun3, $id_akun4, $tahun, $id_buku);
        $ttl_piutang = CashflowModel::ttl_cashflow_uangmasuk_setahun($id_akun3, $id_akun4, $tahun2, $id_buku);

        $id_akun3 = ['26', '37', '38', '39', '81', '83', '84', '36'];
        $id_akun4 = ['19', '103'];
        $id_buku = ['7', '14'];
        $hutang = CashflowModel::cashflow_uangmasuk_setahun($id_akun3, $id_akun4, $tahun, $id_buku);
        $ttl_hutang = CashflowModel::ttl_cashflow_uangmasuk_setahun($id_akun3, $id_akun4, $tahun2, $id_buku);

        $id_akun3 = ['26', '37', '38', '39', '81', '83', '84', '36'];
        $id_akun4 = ['8', '101'];
        $id_buku = ['6', '12', '7'];
        $bunga_bank = CashflowModel::cashflow_uangmasuk_setahun($id_akun3, $id_akun4, $tahun, $id_buku);
        $ttl_bunga_bank = CashflowModel::ttl_cashflow_uangmasuk_setahun($id_akun3, $id_akun4, $tahun2, $id_buku);

        $id_akun3 = ['26', '37', '38', '39', '81', '83', '84', '36'];
        $id_akun4 = ['19'];
        $id_buku = ['7', '14'];
        $bayar_hutang = CashflowModel::cashflow_bayar_uangmasuk_setahun($id_akun3, $id_akun4, $tahun, $id_buku);
        $ttl_bayar_hutang = CashflowModel::ttl_cashflow_bayar_uangmasuk_setahun($id_akun3, $id_akun4, $tahun2, $id_buku);

        $id_akun3 = ['26', '37', '38', '39', '81', '83', '84', '36'];
        $id_akun4 = ['8'];
        $id_buku = ['6'];
        $biaya_admin_pen = CashflowModel::cashflow_bayar_uangmasuk_setahun($id_akun3, $id_akun4, $tahun, $id_buku);
        $ttl_biaya_admin_pen = CashflowModel::ttl_cashflow_bayar_uangmasuk_setahun($id_akun3, $id_akun4, $tahun2, $id_buku);

        $uang_cost = CashflowModel::cashflow_uang_cost($tahun, '6');
        $ttl_uang_cost = CashflowModel::ttl_cashflow_uang_cost($tahun2, '6');
        $uang_proyek = CashflowModel::cashflow_uang_cost($tahun, '7');
        $ttl_uang_proyek = CashflowModel::ttl_cashflow_uang_cost($tahun2, '7');

        $data = [];
        foreach ($pendapatan as $transaction) {

            $month = date('F', strtotime("{$transaction->tahun}-{$transaction->bulan}-01"));

            // Ubah bulan dan tahun menjadi format yang benar
            $nominal = $transaction->debit; // Menghitung nominal

            // Menambahkan data akun dan nominal ke struktur data
            if (!isset($data[$transaction->id_akun]) && empty($data[$transaction->id_akun])) {
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
            if (!isset($data2[$transaction->id_akun]) && empty($data2[$transaction->id_akun])) {
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
            if (!isset($data3[$transaction->id_akun]) && empty($data3[$transaction->id_akun])) {
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
            if (!isset($data4[$transaction->id_akun]) && empty($data4[$transaction->id_akun])) {
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
            if (!isset($data5[$transaction->id_akun]) && empty($data5[$transaction->id_akun])) {
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
        $data6 = [];
        foreach ($bunga_bank as $transaction) {

            $month = date('F', strtotime("{$transaction->tahun}-{$transaction->bulan}-01"));

            // Ubah bulan dan tahun menjadi format yang benar
            $nominal = $transaction->debit; // Menghitung nominal

            // Menambahkan data akun dan nominal ke struktur data
            if (!isset($data6[$transaction->id_akun]) && empty($data6[$transaction->id_akun])) {
                $data6[$transaction->id_akun] = [
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
            $data6[$transaction->id_akun][$month] = $nominal;
        }
        $data7 = [];
        foreach ($bayar_hutang as $transaction) {

            $month = date('F', strtotime("{$transaction->tahun}-{$transaction->bulan}-01"));

            // Ubah bulan dan tahun menjadi format yang benar
            $nominal = $transaction->kredit; // Menghitung nominal

            // Menambahkan data akun dan nominal ke struktur data
            if (!isset($data7[$transaction->id_akun]) && empty($data7[$transaction->id_akun])) {
                $data7[$transaction->id_akun] = [
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
            $data7[$transaction->id_akun][$month] = $nominal;
        }

        $data8 = [];
        foreach ($biaya_admin_pen as $transaction) {

            $month = date('F', strtotime("{$transaction->tahun}-{$transaction->bulan}-01"));

            // Ubah bulan dan tahun menjadi format yang benar
            $nominal = $transaction->kredit; // Menghitung nominal

            // Menambahkan data akun dan nominal ke struktur data
            if (!isset($data8[$transaction->id_akun]) && empty($data8[$transaction->id_akun])) {
                $data8[$transaction->id_akun] = [
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
            $data8[$transaction->id_akun][$month] = $nominal;
        }

        $datas = [
            'title' => 'Cashflow Setahun',
            'tahun' => DB::select("SELECT YEAR(a.tgl) as tahun FROM jurnal as a where YEAR(a.tgl) != 0 group by YEAR(a.tgl);"),
            'thn' => $tahun,
            'ttl1' => $ttl_pendapatan->debit,
            'ttl2' => $ttl_piutang->debit,
            'ttl3' => $ttl_bunga_bank->debit,
            'ttl4' => $ttl_biaya_admin_pen->kredit,
            'ttl5' => $ttl_hutang->debit,
            'ttl6' => $ttl_bayar_hutang->kredit,
            'ttl7' => $ttl_uang_cost->debit,
            'ttl8' => $ttl_uang_proyek->debit
        ];

        return view('cashflow.cashflow_setahun', compact('data', 'data2', 'data3', 'data4', 'data5', 'data6', 'data7', 'data8'), $datas);
    }

    public function cashflowUangMasukSetahun(Request $r)
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
        $response = [
            'status' => 'success',
            'message' => 'Data Cashflow berhasil diambil',
            'data' => [
                'pendapatan' => $pendapatan,
                'piutang' => $piutang,
            ],

        ];
        return response()->json($response);
    }

    function detail_proyek(Request $r)
    {
        $tgl1 =  $r->tgl1;
        $tgl2 =  $r->tgl2;
        $data = [
            'title' => 'Detail ',
            'detail' => DB::select("SELECT a.tgl, a.ket, a.no_nota, a.no_urut, a.no_dokumen, a.ket, a.debit
            FROM `jurnal` as a
            left join tb_post_center as b on b.id_post_center = a.id_post_center
            WHERE a.id_post_center = '$r->id_post' and a.id_akun = '$r->id_akun' and a.tgl between '$tgl1' and '$tgl2' and a.debit != '0'
            order by a.saldo DESC, a.tgl ASC
            "),
            'tgl1' => $tgl1,
            'tgl2' => $tgl2,
            'nm_post' => DB::table('tb_post_center')->where('id_post_center', $r->id_post)->first(),

        ];
        return view('cashflow.detail', $data);
    }


    public function print_uang_ditarik(Request $r)
    {
        $tgl1 =  $r->tgl1;
        $tgl2 =  $r->tgl2;
        $tgl2_pref = date('Y-m-15', strtotime($tgl2));
        $tgl_back = date('Y-m-t', strtotime('previous month', strtotime($tgl2_pref)));
        $tgl_back1 = date('Y-m-1', strtotime('previous month', strtotime($tgl2_pref)));


        $data = [
            'title' => 'Cashflow',
            'piutang' => CashIbuModel::piutang($tgl_back),
            'penjualan' => CashIbuModel::penjualan($tgl1, $tgl2),
            'uang' => CashIbuModel::uang($tgl1, $tgl2),
            'piutang2' => CashIbuModel::piutang2($tgl2),
            'kerugian' => CashIbuModel::kerugian($tgl_back1, $tgl_back),
            'kerugianBulanIni' => CashIbuModel::kerugianBulanIni($tgl1, $tgl2),

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

            'biaya' => CashIbuModel::biaya($tgl1, $tgl2),
            'biaya_proyek' => CashIbuModel::biaya_proyek($tgl1, $tgl2),
            'uangbiayacosh' => CashflowModel::uangBiaya($tgl1, $tgl2, '6'),
            'uangbiayacoshbalance' => CashflowModel::uangBiayabalance($tgl2, '6'),
            'uangbiayaproyekbalance' => CashflowModel::uangBiayabalance($tgl2, '7'),
            'uangbiayaproyek' => CashflowModel::uangBiaya($tgl1, $tgl2, '7'),
            'biaya_admin' => CashIbuModel::biaya_admin($tgl1, $tgl2),
            'hutang_herry' => CashIbuModel::hutang_herry($tgl1, $tgl2),
            'bunga_bank' => CashIbuModel::bunga_bank($tgl1, $tgl2),
            'tgl_back' => $tgl_back,
            'tgl2' => $tgl2,
            'tgl1' => $tgl1,
        ];
        return view('cashflow.cashflow_print', $data);
    }


    public function export_uang_ditarik(Request $r)
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

        $style_sub = [
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
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => [
                    'argb' => 'FFFF00', // Contoh warna kuning
                ],
            ],
        ];
        $style_sub2 = [
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
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => [
                    'argb' => 'f0afff', // Contoh warna kuning
                ],
            ],
        ];
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

        $tgl1 =  $r->tgl1;
        $tgl2 =  $r->tgl2;
        $tgl2_pref = date('Y-m-15', strtotime($tgl2));
        $tgl_back = date('Y-m-t', strtotime('previous month', strtotime($tgl2_pref)));
        $tgl_back1 = date('Y-m-1', strtotime('previous month', strtotime($tgl2_pref)));

        $title = 'Cost Partai';
        $worksheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadsheet, "$title");
        $sheet  = $spreadsheet->addSheet($worksheet);
        $sheet->getStyle("A1:B1")->applyFromArray($style_atas);
        $sheet->getColumnDimension('A')->setWidth(27.65);
        $sheet->getColumnDimension('B')->setWidth(11.55);
        $sheet->getColumnDimension('D')->setWidth(27.65);
        $sheet->getColumnDimension('E')->setWidth(11.55);
        $sheet->getColumnDimension('G')->setWidth(27.65);
        $sheet->getColumnDimension('H')->setWidth(11.55);
        $sheet->getColumnDimension('J')->setWidth(27.65);
        $sheet->getColumnDimension('K')->setWidth(11.55);
        $sheet->setCellValue('A1', 'Akun');
        $sheet->setCellValue('B1', 'Rupiah');

        $piutang = CashIbuModel::piutang($tgl_back);
        $kerugian = CashIbuModel::kerugian($tgl_back1, $tgl_back);
        $sheet->setCellValue('A2', 'Piutang Bulan Lalu');
        $sheet->setCellValue('B2', round(sumBk($piutang, 'debit') - sumBk($piutang, 'kredit') + $kerugian->debit, 0));
        $sheet->getStyle("A2:B2")->applyFromArray($style_sub);
        $kolom = 3;
        foreach ($piutang as  $p) {
            $sheet->setCellValue('A' . $kolom, ucwords(strtolower($p->nm_akun)) . '(' . date('F Y', strtotime($tgl_back)) . ')');
            $sheet->setCellValue('B' . $kolom, round($p->debit - $p->kredit, 0));
            $kolom++;
        }
        $sheet->setCellValue('A' . $kolom, "Biaya Kerugian Piutang");
        $sheet->setCellValue('B' . $kolom, round($kerugian->debit, 0));
        $sheet->getStyle("A3:B" . $kolom)->applyFromArray($style);

        $penjualan = CashIbuModel::penjualan($tgl1, $tgl2);
        $sheet->setCellValue('A' . $kolom + 1, 'Penjualan');
        $sheet->setCellValue('B' . $kolom + 1, round(sumBk($penjualan, 'kredit'), 0));
        $sheet->getStyle('A' . $kolom + 1 . ':B' . $kolom + 1)->applyFromArray($style_sub);

        $kolom2 = $kolom + 2;
        foreach ($penjualan as  $p) {
            $sheet->setCellValue('A' . $kolom2, ucwords(strtolower($p->nm_akun)));
            $sheet->setCellValue('B' . $kolom2, round($p->kredit, 0));
            $kolom2++;
        }
        $sheet->getStyle("A" . $kolom + 2 . ":B" . $kolom2 - 1)->applyFromArray($style);

        $piutang2 = CashIbuModel::piutang2($tgl2);
        $kerugianBulanIni = CashIbuModel::kerugianBulanIni($tgl1, $tgl2);
        $sheet->setCellValue('A' . $kolom2, 'Piutang bulan ini');
        $sheet->setCellValue('B' . $kolom2, round(sumBk($piutang2, 'debit') - sumBk($piutang2, 'kredit') + $kerugianBulanIni->debit, 0));
        $sheet->getStyle('A' . $kolom2  . ':B' . $kolom2)->applyFromArray($style_sub);

        $kolom3 = $kolom2 + 1;
        foreach ($piutang2 as  $p) {
            $sheet->setCellValue('A' . $kolom3, ucwords(strtolower($p->nm_akun)) . '(' . date('F Y', strtotime($tgl2)) . ')');
            $sheet->setCellValue('B' . $kolom3, round($p->debit - $p->kredit, 0));
            $kolom3++;
        }
        $sheet->setCellValue('A' . $kolom3, "Biaya Kerugian Piutang");
        $sheet->setCellValue('B' . $kolom3, round($kerugianBulanIni->debit, 0));
        $sheet->getStyle("A" . $kolom2 + 1 . ":B" . $kolom3)->applyFromArray($style);

        $ttl_piutang_lalu = sumBk($piutang, 'debit') - sumBk($piutang, 'kredit') + $kerugian->debit;
        $ttl_piutang_ini = sumBk($piutang2, 'debit') - sumBk($piutang2, 'kredit') + $kerugianBulanIni->debit;

        $sheet->setCellValue('A' . $kolom3 + 1, "Total");
        $sheet->setCellValue('B' . $kolom3 + 1, round($ttl_piutang_lalu + sumBk($penjualan, 'kredit') - $ttl_piutang_ini, 0));


        $bunga_bank = CashIbuModel::bunga_bank($tgl1, $tgl2);
        $sheet->setCellValue('A' . $kolom3 + 2, "Bunga Bank");
        $sheet->setCellValue('B' . $kolom3 + 2, round($bunga_bank->kredit, 0));

        $biaya_admin = CashIbuModel::biaya_admin($tgl1, $tgl2);
        $sheet->setCellValue('A' . $kolom3 + 3, "Biaya Administrasi");
        $sheet->setCellValue('B' . $kolom3 + 3, round($biaya_admin->debit, 0));

        $bg_bank = $bunga_bank->kredit ?? 0;

        $sheet->setCellValue('A' . $kolom3 + 4, "Grand Total");
        $sheet->setCellValue('B' . $kolom3 + 4, round($ttl_piutang_lalu + sumBk($penjualan, 'kredit') - $ttl_piutang_ini + $bg_bank + $biaya_admin->debit, 0));

        $sheet->getStyle('A' . $kolom3 + 1  . ':B' . $kolom3 + 4)->applyFromArray($style_sub);

        //BATAS

        $sheet->getStyle("D1:E1")->applyFromArray($style_atas);
        $sheet->mergeCells("D2:E2");
        $sheet->setCellValue('D1', 'Akun');
        $sheet->setCellValue('E1', 'Rupiah');
        $sheet->setCellValue('D2', 'Uang Ditarik (Piutang & penjualan yg ditarik)');

        $uang = CashIbuModel::uang($tgl1, $tgl2);
        $kolom_s1 = 3;
        foreach ($uang as  $p) {
            $sheet->setCellValue('D' . $kolom_s1, ucwords(strtolower($p->nm_akun)));
            $sheet->setCellValue('E' . $kolom_s1, round($p->debit, 0));
            $kolom_s1++;
        }
        $sheet->getStyle("D2:E" .  $kolom_s1 - 1)->applyFromArray($style);


        $sheet->setCellValue('D' . $kolom3 + 1, "Total");
        $sheet->setCellValue('E' . $kolom3 + 1, round(sumBk($uang, 'debit'), 0));


        $bunga_bank = CashIbuModel::bunga_bank($tgl1, $tgl2);
        $sheet->setCellValue('D' . $kolom3 + 2, "Bunga Bank");
        $sheet->setCellValue('E' . $kolom3 + 2, round($bunga_bank->kredit, 0));

        $biaya_admin = CashIbuModel::biaya_admin($tgl1, $tgl2);
        $sheet->setCellValue('D' . $kolom3 + 3, "Biaya Administrasi");
        $sheet->setCellValue('E' . $kolom3 + 3, round($biaya_admin->debit, 0));

        $bg_bank = $bunga_bank->kredit ?? 0;

        $sheet->setCellValue('D' . $kolom3 + 4, "Grand Total");
        $sheet->setCellValue('E' . $kolom3 + 4, round(sumBk($uang, 'debit') + $bg_bank + $biaya_admin->debit, 0));


        $sheet->getStyle('D' . $kolom3 + 1  . ':E' . $kolom3 + 4)->applyFromArray($style_sub);
        $sheet->getStyle('D3'  . ':E' . $kolom3 + 4)->applyFromArray($style);

        $this->uangKeluar($sheet, $style_atas, $style, $style_sub, $style_sub2, $tgl1, $tgl2);
        $this->uangKeluar2($sheet, $style_atas, $style, $style_sub, $style_sub2, $tgl1, $tgl2);





        $namafile = "Cash Flow.xlsx";

        $writer = new Xlsx($spreadsheet);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename=' . $namafile);
        header('Cache-Control: max-age=0');


        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');
        exit();
    }


    private function uangKeluar($sheet, $style_atas, $style, $style_sub, $style_sub2, $tgl1, $tgl2)
    {
        $sheet->getStyle("G1:H1")->applyFromArray($style_atas);
        $sheet->setCellValue('G1', 'Akun');
        $sheet->setCellValue('H1', 'Rupiah');
        $biaya = CashIbuModel::biaya($tgl1, $tgl2);

        $sheet->setCellValue('G2', 'Biaya Cost');
        $sheet->setCellValue('H2', round(sumBk($biaya, 'debit'), 0));
        $sheet->getStyle('G2:H2')->applyFromArray($style_sub);
        $kolom_s2 = 3;
        foreach ($biaya as  $p) {
            $sheet->setCellValue('G' . $kolom_s2, ucwords(strtolower($p->nm_akun)));
            $sheet->setCellValue('H' . $kolom_s2, round($p->debit, 0));
            $kolom_s2++;
        }
        $sheet->getStyle("G2:H" .  $kolom_s2 - 1)->applyFromArray($style);

        $biaya_proyek = CashIbuModel::biaya_proyek($tgl1, $tgl2);
        $sheet->setCellValue('G' . $kolom_s2, 'Biaya Proyek');
        $sheet->setCellValue('H' . $kolom_s2, round(sumBk($biaya_proyek, 'debit'), 0));
        $sheet->getStyle('G' . $kolom_s2 . ':H' . $kolom_s2)->applyFromArray($style_sub);
        $kolom_s2_1 = $kolom_s2 + 1;
        foreach ($biaya_proyek as  $p) {
            $sheet->setCellValue('G' . $kolom_s2_1, ucwords(strtolower($p->nm_akun)));
            $sheet->setCellValue('H' . $kolom_s2_1, round($p->debit, 0));
            $sheet->getStyle("G" .  $kolom_s2_1 . ":H" .  $kolom_s2_1)->applyFromArray($style_sub2);
            $kolom_s2_1++;

            $detail = CashIbuModel::detail_proyek($p->id_akun, $tgl1, $tgl2);
            foreach ($detail as $d) {
                $sheet->setCellValue('G' . $kolom_s2_1, '   ' . $d->nm_post);
                $sheet->setCellValue('H' . $kolom_s2_1, round($d->debit, 0));
                $kolom_s2_1++;
                $sheet->getStyle("G" .  $kolom_s2_1 - 1 . ":H" .  $kolom_s2_1 - 1)->applyFromArray($style);
            }
        }
        $sheet->setCellValue('G' . $kolom_s2_1, 'Total');
        $sheet->setCellValue('H' . $kolom_s2_1, round(sumBk($biaya_proyek, 'debit') + sumBk($biaya, 'debit'), 0));
        $sheet->getStyle("G" .  $kolom_s2_1 . ":H" .  $kolom_s2_1)->applyFromArray($style_sub);
    }

    private function uangKeluar2($sheet, $style_atas, $style, $style_sub, $style_sub2, $tgl1, $tgl2)
    {
        $sheet->getStyle("J1:K1")->applyFromArray($style_atas);
        $sheet->setCellValue('J1', 'Akun');
        $sheet->setCellValue('K1', 'Rupiah');
        $uangbiayacosh = CashflowModel::uangBiaya($tgl1, $tgl2, '6');

        $sheet->setCellValue('J2', 'Cost');
        $sheet->setCellValue('K2', round(sumBk($uangbiayacosh, 'kredit'), 0));
        $sheet->getStyle('J2:K2')->applyFromArray($style_sub);
        $kolom_s3 = 3;
        foreach ($uangbiayacosh as  $p) {
            $sheet->setCellValue('J' . $kolom_s3, ucwords(strtolower($p->nm_akun)));
            $sheet->setCellValue('K' . $kolom_s3, round($p->kredit, 0));
            $kolom_s3++;
        }
        $sheet->getStyle("J2:K" .  $kolom_s3 - 1)->applyFromArray($style);

        $uangbiayaproyek = CashflowModel::uangBiaya($tgl1, $tgl2, '7');
        $sheet->setCellValue('J' . $kolom_s3, 'Proyek');
        $sheet->setCellValue('K' . $kolom_s3, round(sumBk($uangbiayaproyek, 'kredit'), 0));
        $sheet->getStyle('J' . $kolom_s3 . ':K' . $kolom_s3)->applyFromArray($style_sub);
        $kolom_s3_1 = $kolom_s3 + 1;
        foreach ($uangbiayaproyek as  $p) {
            $sheet->setCellValue('J' . $kolom_s3_1, ucwords(strtolower($p->nm_akun)));
            $sheet->setCellValue('K' . $kolom_s3_1, round($p->kredit, 0));

            $kolom_s3_1++;
        }
        $sheet->getStyle("J" .  $kolom_s3 + 1 . ":K" .  $kolom_s3_1 - 1)->applyFromArray($style);
        $sheet->setCellValue('J' . $kolom_s3_1, "Total");
        $sheet->setCellValue('K' . $kolom_s3_1, round(sumBk($uangbiayacosh, 'kredit') + sumBk($uangbiayaproyek, 'kredit'), 0));
        $sheet->getStyle("J" .  $kolom_s3_1 . ":K" .  $kolom_s3_1)->applyFromArray($style_sub);
    }
}
