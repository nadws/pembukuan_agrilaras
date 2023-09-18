<?php

namespace App\Http\Controllers;

use App\Exports\AkunExport;
use App\Models\Akun;
use App\Models\PostCenter;
use App\Models\SubklasifikasiAkun;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Nonaktif;
use SettingHal;

class AkunController extends Controller
{
    public function index()
    {
        $id_user = auth()->user()->id;
        $data =  [
            'title' => 'Daftar Akun',
            'akun' => DB::table('akun as a')->join('subklasifikasi_akun as b', 'a.id_klasifikasi', 'b.id_subklasifikasi_akun')->where('a.nonaktif', 'T')->orderBy('a.id_akun', 'DESC')->get(),
            'subklasifikasi' => SubklasifikasiAkun::all(),

            'user' => User::where('posisi_id', 1)->get(),
            'halaman' => 4,
            'create' => SettingHal::btnHal(18, $id_user),
            'export' => SettingHal::btnHal(19, $id_user),
            'edit' => SettingHal::btnHal(20, $id_user),
            'subAkun' => SettingHal::btnHal(21, $id_user),
        ];
        return view('Akun.index', $data);
    }

    public function create(Request $r)
    {
        $data = [
            'id_klasifikasi' => $r->id_klasifikasi,
            'kode_akun' => $r->kode_akun,
            'nm_akun' => $r->nm_akun,
            'inisial' => strtoupper($r->inisial),
            'iktisar' => $r->iktisar,
        ];
        Akun::create($data);
        return redirect()->route('akun');
    }

    public function get_kode(Request $r)
    {
        $id_sub = $r->id_sub;

        $kode =  SubklasifikasiAkun::where('id_subklasifikasi_akun', $id_sub)->first();
        $max = Akun::where('id_klasifikasi', $id_sub)->latest('kode_akun')->first();

        if (empty($max->kode_akun)) {
            $kodemax = '1';
        } else {
            $kodemax = $max->kode_akun + 1;
        }
        $data = [
            'kode' => "$kode->kode_sub-$kodemax",
            'kode_max' => $kodemax
        ];
        return $data;
    }

    public function get_edit_akun($id_akun)
    {
        $data = [
            'akun' => DB::table("akun as a")
                ->join('subklasifikasi_akun as b', 'a.id_klasifikasi', 'b.id_subklasifikasi_akun')
                ->where('id_akun', $id_akun)
                ->orderBy('a.id_akun', 'DESC')
                ->first(),
            'subklasifikasi' => SubklasifikasiAkun::all()
        ];
        return view('Akun.getEdit', $data);
    }

    public function update(Request $r)
    {
        $data = [
            'id_klasifikasi' => $r->id_klasifikasi,
            'kode_akun' => $r->kode_akun,
            'nm_akun' => $r->nm_akun,
            'inisial' => $r->inisial,
            'iktisar' => $r->iktisar,
        ];
        DB::table('akun')->where('id_akun', $r->id_akun)->update($data);
        // Nonaktif::edit('akun', 'id_akun', $r->id_akun, $data);
        return redirect()->route('akun');
    }

    public function load_sub_akun($id_akun)
    {
        $data = [
            'detail' => PostCenter::where('id_akun', $id_akun)->get(),
            'id_akun' => $id_akun,
        ];
        return view('Akun.sub_akun', $data);
    }

    public function add_sub(Request $r)
    {
        PostCenter::insert([
            'id_akun' => $r->id_akun,
            'nm_post' => $r->nm_post,
        ]);
    }

    public function remove_sub(Request $r)
    {
        PostCenter::where('id_post_center', $r->id)->update(['nonaktif', 'T']);
    }

    public function export_akun(Request $r)
    {
        $tbl = DB::table('akun as a')
                ->join('subklasifikasi_akun as b', 'b.id_subklasifikasi_akun', 'a.id_klasifikasi')
                ->where('a.nonaktif', 'T')->get();
        $tblKlasifikasi = DB::table('subklasifikasi_akun')->get();

        $totalrow = count($tbl) + 1;
        $totalrow2 = count($tblKlasifikasi) + 1;

        $data = [
            'tbl1' => $tbl,
            'row1' => $totalrow,
            'tbl2' => $tblKlasifikasi,
            'row2' => $totalrow2,
        ];

        return Excel::download(new AkunExport($data), 'Export Akun.xlsx');
    }

    public function importAkun(Request $r)
    {
        $file = $r->file('file');
        $fileDiterima = ['xls', 'xlsx'];
        $cek = in_array($file->getClientOriginalExtension(), $fileDiterima);
        if ($cek) {
            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx;
            $spreadsheet = $reader->load($file);
            $sheet = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

            $numrow = 3;
            foreach ($sheet as $row) {
                if ($row['B'] == '' && $row['D'] == '') {
                    continue;
                }
                if ($numrow > 2) {
                    if ($row['A'] == '') {
                        DB::table('akun')->insert([
                            'kode_akun' => $row['B'],
                            'inisial' => $row['C'],
                            'nm_akun' => $row['D'],
                            'id_klasifikasi' => $row['E'],
                            'iktisar' => $row['G'],
                        ]);
                    } else {
                        DB::table('akun')->where('id_akun', $row['A'])->update([
                            'kode_akun' => $row['B'],
                            'inisial' => $row['C'],
                            'nm_akun' => $row['D'],
                            'id_klasifikasi' => $row['E'],
                            'iktisar' => $row['G'],
                        ]);
                    }
                }
            }
            return redirect()->route('akun')->with('sukses', 'Berhasil Import Data');
        } else {
            return redirect()->route('akun')->with('error', 'File tidak didukung');
        }
    }
}
