<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use SettingHal;

class AktivaController extends Controller
{
    public function index()
    {
        $id_user = auth()->user()->id;
        $data =  [
            'title' => 'Aktiva',
            'tahun' => DB::select("SELECT YEAR(a.tgl) as tahun, a.tgl
            FROM depresiasi_aktiva as a
            group by YEAR(a.tgl)
            order by YEAR(a.tgl) ASC;"),
            'aktiva' => DB::select("SELECT a.*, b.*, c.beban FROM aktiva as a 
            left join kelompok_aktiva as b on b.id_kelompok = a.id_kelompok
            left join(
            SELECT sum(c.b_penyusutan) as beban , c.id_aktiva
                FROM depresiasi_aktiva as c
                group by c.id_aktiva
            ) as c on c.id_aktiva = a.id_aktiva
            order by a.id_aktiva DESC"),

            'user' => User::where('posisi_id', 1)->get(),
            'halaman' => 10,
            'create' => SettingHal::btnHal(41, $id_user),
            'print' => SettingHal::btnHal(42, $id_user),
            'edit' => SettingHal::btnHal(43, $id_user),
            'delete' => SettingHal::btnHal(44, $id_user),
            'detail' => SettingHal::btnHal(45, $id_user),

        ];
        return view('aktiva.index', $data);
    }

    public function add()
    {
        $data =  [
            'title' => 'Add Aktiva',
        ];
        return view('aktiva.add', $data);
    }

    public function load_aktiva()
    {
        $data =  [
            'title' => 'Add Aktiva',
            'kelompok' => DB::table('kelompok_aktiva')->get()
        ];
        return view('aktiva.load_aktiva', $data);
    }

    public function tambah_baris_aktiva(Request $r)
    {
        $data =  [
            'kelompok' => DB::table('kelompok_aktiva')->get(),
            'count' => $r->count

        ];
        return view('aktiva.tbh_baris', $data);
    }
    public function get_data_kelompok(Request $r)
    {
        $id_kelompok = $r->id_kelompok;
        $kelompok =  DB::table('kelompok_aktiva')->where('id_kelompok', $id_kelompok)->first();

        $data = [
            'nilai_persen' => $kelompok->tarif,
            'tahun' => $kelompok->umur
        ];
        echo json_encode($data);
    }

    public function save_aktiva(Request $r)
    {
        $id_kelompok = $r->id_kelompok;
        $nm_aktiva = $r->nm_aktiva;
        $tgl = $r->tgl;
        $h_perolehan = $r->h_perolehan;

        for ($x = 0; $x < count($id_kelompok); $x++) {
            $kelompok =  DB::table('kelompok_aktiva')->where('id_kelompok', $id_kelompok[$x])->first();
            $biaya_depresiasi = ($h_perolehan[$x] * $kelompok->tarif) / 12;

            $data = [
                'id_kelompok' => $id_kelompok[$x],
                'nm_aktiva' => $nm_aktiva[$x],
                'tgl' => $tgl[$x],
                'h_perolehan' => $h_perolehan[$x],
                'biaya_depresiasi' => $biaya_depresiasi,
                'admin' => Auth::user()->name,
            ];
            DB::table('aktiva')->insert($data);
        }

        return redirect()->route('aktiva')->with('sukses', 'Data berhasil ditambahkan');
    }

    public function print(Request $r)
    {
        $year =  date("Y", strtotime($r->tahun));
        $tahun1 =  date("$year-01-01");
        $tahun1_1 =  date("$year-12-t");

        $tahun2 =  date('Y-01-01', strtotime("-1 year", strtotime($tahun1)));
        $tahun2_1 =  date('Y-12-31', strtotime("-1 year", strtotime($tahun1)));
        $data = [
            'title' => 'Print Aktiva',
            'kelompok' => DB::table('kelompok_aktiva')->get(),
            'tahun1' => $tahun1,
            'tahun1_1' => $tahun1_1,
            'tahun2' => $tahun2,
            'tahun2_1' => $tahun2_1

        ];
        return view('aktiva.print_aktiva', $data);
    }
}
