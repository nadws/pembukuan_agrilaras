<?php

namespace App\Http\Controllers;

use App\Models\NeracaAldi;
use App\Models\NeracaModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx\Rels;

class NeracaController extends Controller
{
    public function index(Request $r)
    {
        $tahun =  $r->tahun ?? date('Y');

        $data = [
            'title' => 'Laporan Neraca',
            'tahun' => DB::select("SELECT YEAR(a.tgl) as tahun FROM jurnal as a where YEAR(a.tgl) != 0 group by YEAR(a.tgl);"),
            'thn' => $tahun,
            'bulans' => DB::table('bulan')->get(),

        ];
        return view('neraca.index', $data);
    }
    public function loadneraca(Request $r)
    {
        $tgl1 =  '2020-01-01';
        $tgl2 = $r->tgl2;

        $tgl_1 = date('Y-m-01', strtotime($r->tgl2));;

        $kas  = NeracaModel::GetKas($tgl1, $tgl2, 1);
        $bank  = NeracaModel::GetKas($tgl1, $tgl2, 2);
        $piutang  = NeracaModel::GetKas($tgl1, $tgl2, 7);
        $persediaan  = NeracaModel::GetKas($tgl1, $tgl2, 6);

        $hutang  = NeracaModel::GetKas($tgl1, $tgl2, 9);
        $ekuitas  = NeracaModel::GetKas2($tgl1, $tgl2);
        $ekuitas2  = NeracaModel::GetKas3($tgl1, $tgl2);
        $laba_berjalan = NeracaModel::laba_berjalan_pendapatan($tgl1, $tgl2);
        $laba_berjalan2 = NeracaModel::laba_berjalan_biaya($tgl1, $tgl2);

        $peralatan  = NeracaModel::Getakumulasi($tgl1, $tgl2, 16);
        $aktiva  = NeracaModel::Getakumulasi($tgl1, $tgl2, 9);
        $aktiva_gantung  = NeracaModel::Getakumulasi($tgl1, $tgl2, 43);
        $peralatan_gantung  = NeracaModel::Getakumulasi($tgl1, $tgl2, 61);
        $pullet_gantung  = NeracaModel::Getakumulasi($tgl1, $tgl2, 76);

        $akumulasi_aktiva  = NeracaModel::Getakumulasi($tgl1, $tgl2, 52);
        $akumulasi_peralatan  = NeracaModel::Getakumulasi($tgl1, $tgl2, 59);

        $data = [
            'kas' => $kas,
            'bank' => $bank,
            'piutang' => $piutang,
            'persediaan' => $persediaan,
            'peralatan' => $peralatan,
            'peralatan_gantung' => $peralatan_gantung,
            'pullet_gantung' => $pullet_gantung,
            'akumulasi' => $akumulasi_aktiva,
            'akumulasi_peralatan' => $akumulasi_peralatan,
            'aktiva' => $aktiva,
            'aktiva_gantung' => $aktiva_gantung,
            'hutang' => $hutang,
            'ekuitas' => $ekuitas,
            'ekuitas2' => $ekuitas2,
            'tgl1' => $tgl1,
            'tgl2' => $tgl2,
            'laba_pendapatan' => $laba_berjalan,
            'laba_biaya' => $laba_berjalan2,

        ];
        return view('neraca.load', $data);
    }

    function print_neraca(Request $r)
    {
        $tgl1 =  '2020-01-01';
        $tgl2 = $r->tgl2;

        $tgl_1 = date('Y-m-01', strtotime($r->tgl2));

        $kas  = NeracaModel::GetKas($tgl1, $tgl2, 1);
        $bank  = NeracaModel::GetKas($tgl1, $tgl2, 2);
        $piutang  = NeracaModel::GetKas($tgl1, $tgl2, 7);
        $hutang  = NeracaModel::GetKas($tgl1, $tgl2, 9);
        $persediaan  = NeracaModel::GetKas($tgl1, $tgl2, 6);

        $ekuitas  = NeracaModel::GetKas2($tgl1, $tgl2);
        $ekuitas2  = NeracaModel::GetKas3($tgl1, $tgl2);
        $laba_berjalan = NeracaModel::laba_berjalan_pendapatan($tgl1, $tgl2);
        $laba_berjalan2 = NeracaModel::laba_berjalan_biaya($tgl1, $tgl2);

        $peralatan  = NeracaModel::Getakumulasi($tgl1, $tgl2, 16);
        $aktiva  = NeracaModel::Getakumulasi($tgl1, $tgl2, 9);
        $aktiva_gantung  = NeracaModel::Getakumulasi($tgl1, $tgl2, 43);
        $peralatan_gantung  = NeracaModel::Getakumulasi($tgl1, $tgl2, 61);
        $pullet_gantung  = NeracaModel::Getakumulasi($tgl1, $tgl2, 76);

        $akumulasi_aktiva  = NeracaModel::Getakumulasi($tgl1, $tgl2, 52);
        $akumulasi_peralatan  = NeracaModel::Getakumulasi($tgl1, $tgl2, 59);

        $data = [
            'kas' => $kas,
            'bank' => $bank,
            'piutang' => $piutang,
            'peralatan' => $peralatan,
            'peralatan_gantung' => $peralatan_gantung,
            'pullet_gantung' => $pullet_gantung,
            'akumulasi' => $akumulasi_aktiva,
            'akumulasi_peralatan' => $akumulasi_peralatan,
            'aktiva' => $aktiva,
            'aktiva_gantung' => $aktiva_gantung,
            'hutang' => $hutang,
            'ekuitas' => $ekuitas,
            'ekuitas2' => $ekuitas2,
            'persediaan' => $persediaan,
            'tgl1' => $tgl1,
            'tgl2' => $tgl2,
            'laba_pendapatan' => $laba_berjalan,
            'laba_biaya' => $laba_berjalan2,

        ];
        return view('neraca.print', $data);
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
    public function load_pasiva(Request $r)
    {
        $tahun =  $r->tahun ?? date('Y');

        $data =[
            'title' => 'asd',
            'bulans' => DB::table('bulan')->get(),
            'tahun' => DB::select("SELECT YEAR(a.tgl) as tahun FROM jurnal as a where YEAR(a.tgl) != 0 group by YEAR(a.tgl);"),
            'thn' => $tahun,
        ];
        return view('neraca.load_pasiva',$data);
    }
}
