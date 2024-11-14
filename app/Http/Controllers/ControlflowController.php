<?php

namespace App\Http\Controllers;

use App\Models\CashIbuModel;
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

    public function getQueryPakanSelisih($jenis, $tgl1, $tgl2)
    {
        $pakanSelisih = DB::select("SELECT c.nm_satuan,a.admin,a.tgl,a.id_pakan,b.nm_produk,a.pcs,a.pcs_kredit,a.total_rp,a.biaya_dll,c.stok,d.sum_ttl_rp,d.pcs_sum_ttl_rp FROM `stok_produk_perencanaan` as a 
        LEFT JOIN tb_produk_perencanaan as b ON a.id_pakan = b.id_produk
        left join tb_satuan as c on c.id_satuan = b.dosis_satuan
        LEFT JOIN (
            SELECT a.id_pakan, (sum(a.pcs) - sum(a.pcs_kredit)) as stok
                    FROM stok_produk_perencanaan as a 
                    group by a.id_pakan
        ) as c ON a.id_pakan = c.id_pakan
        LEFT JOIN (
            SELECT a.id_pakan,sum(a.total_rp + a.biaya_dll) as sum_ttl_rp, sum(pcs) as pcs_sum_ttl_rp FROM stok_produk_perencanaan as a
                    WHERE a.h_opname = 'T' AND a.pcs != 0 and  a.admin not in('import','nanda')
                    GROUP BY a.id_pakan
        ) as d on a.id_pakan = d.id_pakan
        WHERE b.kategori IN ($jenis) AND a.tgl BETWEEN '2023-08-15' AND '$tgl2' AND a.h_opname = 'Y' GROUP BY a.id_pakan
        ORDER BY a.pcs DESC;");

        return $pakanSelisih;
    }

    public function index()
    {
        $tgl1 =  $this->tgl1;
        $tgl2 =  $this->tgl2;

        $telurSelisih = DB::select("SELECT a.nm_telur, sum(b.kg_selisih) as kg_selisih, sum(b.pcs_selisih) as pcs_selisih FROM telur_produk as a
        LEFT JOIN stok_telur as b ON a.id_produk_telur = b.id_telur
        WHERE b.jenis = 'Opname' AND b.id_gudang = 1 AND b.tgl BETWEEN '2023-08-12' AND '$tgl2' AND b.admin not in ('nanda', 'import')  GROUP BY b.id_telur;");

        $pakanSelisih = $this->getQueryPakanSelisih("'pakan'", $tgl1, $tgl2);
        $vitaminSelisih = $this->getQueryPakanSelisih("'obat_pakan', 'obat_air'", $tgl1, $tgl2);

        $data = [
            'title' => 'Dashboard Pembukuan',
            'tgl1' => $tgl1,
            'tgl2' => $tgl2,
            'akun_cashflow' => DB::selectOne("SELECT count(a.id_akun) as total_akun FROM akun as a where  a.cash_flow='T'"),
            'akun_ibu' => DB::selectOne("SELECT count(a.id_akun) as total_akun FROM akun as a where  a.cash_uang_ditarik='T'"),
            'telur_selisih' => $telurSelisih,
            'pakanSelisih' => $pakanSelisih,
            'vitaminSelisih' => $vitaminSelisih,
            'populasi' => CashIbuModel::ttl_ayam($tgl2)
        ];
        return view('controlflow.dashboard', $data);
    }

    public function total_cash_flow()
    {
        $ttl_cash_flow = DB::selectOne("SELECT count(a.id_akun) as total_akun FROM akun as a where  a.cash_flow='T'");

        echo $ttl_cash_flow->total_akun;
    }
    public function total_cash_ibu()
    {
        $ttl_cash_flow = DB::selectOne("SELECT count(a.id_akun) as total_akun FROM akun as a where  a.cash_uang_ditarik='T'");

        echo $ttl_cash_flow->total_akun;
    }
    public function total_cash_profit()
    {
        $ttl_cash_flow = DB::selectOne("SELECT count(a.id_akun) as total_akun FROM akun as a where  a.profit_loss='T'");

        echo $ttl_cash_flow->total_akun;
    }
    public function seleksi_cash_flow_ditarik(Request $r)
    {
        for ($x = 0; $x < count($r->id_akun); $x++) {

            $data = [
                'cash_flow' => $r->cash_flow[$x]
            ];
            DB::table('akun')->where('id_akun', $r->id_akun[$x])->update($data);
        }
        return redirect()->route('controlflow')->with('sukses', 'Berhasil input akun');
    }
    public function loadcontrolflow(Request $r)
    {
        $tgl1 =  $r->tgl1;
        $tgl2 =  $r->tgl2;


        $data = [
            'title' => 'load',
            'tgl1' => $tgl1,
            'tgl2' => $tgl2,
            'pendapatan' => DB::select("SELECT a.id_akun, c.nm_akun, b.debit, b.kredit
            FROM akuncontrol as a 
            left join (
            SELECT b.id_akun, sum(b.debit) as debit, sum(b.kredit) as kredit
                FROM jurnal as b
                WHERE b.id_buku = '6' and b.tgl between '$tgl1' and '$tgl2'
                group by b.id_akun
            ) as b on b.id_akun = a.id_akun
            left join akun as c on c.id_akun = a.id_akun
            where a.id_kategori_cashcontrol = '1';"),

            'biaya' => DB::select("SELECT a.id_akun, c.nm_akun, b.kredit, b.debit
            FROM akuncontrol as a 
            left join (
            SELECT b.id_akun, sum(b.debit) as debit, sum(b.kredit) as kredit
                FROM jurnal as b
                WHERE b.id_buku ='2' and b.tgl between '$tgl1' and '$tgl2'
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
            'akun' => DB::Select("SELECT * FROM akun as a where a.id_akun not in(SELECT b.id_akun FROM akuncontrol as b); "),
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

        DB::table('akun')->where('id_akun', $r->id_akun)->update(['cash_flow' => 'Y']);
    }
    public function deleteSubAkunCashflow(Request $r)
    {
        $id_akun =  DB::table('akuncontrol')->where('id_akuncontrol', $r->id_akuncontrol)->first();
        DB::table('akun')->where('id_akun', $id_akun->id_akun)->update(['cash_flow' => 'T']);
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

    public function akuncashflow(Request $r)
    {
        $data = [
            'akun' => DB::select("SELECT a.id_akun as akun_id, a.kode_akun , a.nm_akun, b.id_akun, a.cash_flow
            FROM akun as a 
            left join akuncontrol as b on b.id_akun = a.id_akun;")
        ];
        return view('controlflow.akuncashflow_2', $data);
    }
    public function akunuangditarik(Request $r)
    {
        $data = [
            'akun' => DB::select("SELECT a.id_akun as akun_id, a.kode_akun , a.nm_akun, b.id_akun, a.cash_uang_ditarik
            FROM akun as a 
            left join akuncash_ibu as b on b.id_akun = a.id_akun;")
        ];
        return view('controlflow.akuncashflow', $data);
    }
}
