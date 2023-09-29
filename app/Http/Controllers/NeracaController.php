<?php

namespace App\Http\Controllers;

use App\Models\NeracaModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx\Rels;

class NeracaController extends Controller
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
        $tgl1 =  $this->tgl1;
        $tgl2 =  $this->tgl2;
        $data = [
            'title' => 'Laporan Neraca',
            'tgl1' => $tgl1,
            'tgl2' => $tgl2
        ];
        return view('neraca.index', $data);
    }

    public function loadneraca(Request $r)
    {
        $tgl1 =  '2020-01-01';
        $tgl2 = $r->tgl2;

        $kas  = NeracaModel::GetKas($tgl1, $tgl2, 1);
        $bank  = NeracaModel::GetKas($tgl1, $tgl2, 2);
        $piutang  = NeracaModel::GetKas($tgl1, $tgl2, 7);
        $hutang  = NeracaModel::GetKas($tgl1, $tgl2, 9);
        $persediaan  = NeracaModel::GetKas($tgl1, $tgl2, 6);
        $ekuitas  = NeracaModel::GetKas2($tgl1, $tgl2);


        $peralatan  = NeracaModel::Getakumulasi($tgl1, $tgl2, 16);
        $aktiva  = NeracaModel::Getakumulasi($tgl1, $tgl2, 9);

        $akumulasi_aktiva  = NeracaModel::Getakumulasi($tgl1, $tgl2, 52);
        $akumulasi_peralatan  = NeracaModel::Getakumulasi($tgl1, $tgl2, 59);

        $data = [
            'kas' => $kas,
            'bank' => $bank,
            'piutang' => $piutang,
            'peralatan' => $peralatan,
            'akumulasi' => $akumulasi_aktiva,
            'akumulasi_peralatan' => $akumulasi_peralatan,
            'aktiva' => $aktiva,
            'hutang' => $hutang,
            'ekuitas' => $ekuitas,
            'persediaan' => $persediaan,
            'tgl1' => $tgl1,
            'tgl2' => $tgl2
        ];
        return view('neraca.load', $data);
    }

    public function loadinputSub_neraca(Request $r)
    {
        $data = [
            'subkategori' => DB::table('sub_kategori_neraca')->where('id_kategori', $r->kategori)->get(),
            'kategori' => $r->kategori
        ];
        return view('neraca.inputSub', $data);
    }

    public function saveSub_neraca(Request $r)
    {
        $data = [
            'nama_sub_kategori' => $r->nama_sub_kategori,
            'id_kategori' => $r->kategori,
            'urutan' => $r->urutan
        ];
        DB::table('sub_kategori_neraca')->insert($data);
    }

    public function loadinputAkun_neraca(Request $r)
    {
        $data = [
            'akun_neraca' => DB::select("SELECT a.id_akun_neraca, a.id_akun, b.nm_akun, c.debit , c.kredit
            FROM akun_neraca as a
            left join akun as b on b.id_akun = a.id_akun
            left join (
            SELECT c.id_akun, sum(c.debit) as debit, sum(c.kredit) as kredit
                FROM jurnal as c
                where c.tgl BETWEEN '2023-01-01' and '$r->tgl2'
                group by c.id_akun
            ) as c on c.id_akun = a.id_akun
            WHERE a.id_sub_kategori = '$r->id_sub_kategori';"),
            'id_sub_kategori' => $r->id_sub_kategori,
            'akun' => DB::select("SELECT * FROM akun as a where a.id_akun not in(SELECT b.id_akun FROM akun_neraca as b)")
        ];
        return view('neraca.inputAkun', $data);
    }

    public function saveAkunNeraca(Request $r)
    {
        $data = [
            'id_akun' => $r->id_akun,
            'id_sub_kategori' => $r->id_sub_kategori,
        ];
        DB::table('akun_neraca')->insert($data);
    }

    public function delete_akun_neraca(Request $r)
    {
        DB::table('akun_neraca')->where('id_akun_neraca', $r->id_akun_neraca)->delete();
    }
    public function view_akun_neraca()
    {
        $data = [
            'akun' => DB::Select("SELECT a.id_akun,a.nm_akun, b.id_akun as ada FROM akun as a
            LEFT JOIN akun_neraca as b ON a.id_akun= b.id_akun"),
        ];

        return view('neraca.view_akun', $data);
    }
}
