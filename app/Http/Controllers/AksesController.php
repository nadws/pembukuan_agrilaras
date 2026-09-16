<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AksesController extends Controller
{
    public function index()
    {

        if ((string) auth()->user()->posisi_id === '1') {

            $data = [
                'title' => 'Permission Halaman',
                'permissionHalaman' => DB::table('permission')->get(),
                'permissionButton' => DB::table('permission_button as a')->join('permission as b', 'b.id_permission', 'a.permission_id')->get(),
            ];
            return view('permission_halaman.index', $data);
        } else {
            abort(403, 'akses tidak ada');
        }
    }

    public function detail_edit()
    {
        if ((string) auth()->user()->posisi_id === '1') {

            $data = [
                'title' => 'Permission Halaman',
                'permissionHalaman' => DB::table('navbar')->orderBy('urutan', 'ASC')->get(),
                'permissionButton' => DB::table('permission_button as a')->join('permission as b', 'b.id_permission', 'a.permission_id')->get(),
            ];
            return view('permission_halaman.navbar', $data);
        } else {
            abort(403, 'akses tidak ada');
        }
    }

    public function detail_get($id)
    {
        $detail = DB::table('navbar')->where('id_navbar', $id)->orderBy('urutan', 'ASC')->first();
        return response()->json($detail);
    }

    public function navbar_delete($id)
    {
        DB::table('navbar')->where('id_navbar', $id)->delete();
        return redirect()->route('akses.navbar')->with('sukses', 'Data Berhasil');
    }

    public function addMenu(Request $r)
    {
        if(!empty($r->navbar)) {
            if(!empty($r->navbar_edit)) {
                for ($i=0; $i < count($r->isi); $i++) { 
                    DB::table('navbar')->where('id_navbar', $r->id_navbar[$i])->update([
                        'urutan' => $r->urutan[$i],
                        'nama' => $r->nama[$i],
                        'route' => $r->route[$i],
                        'isi' => $r->isi[$i],
                    ]);
                }
            } else {
                for ($i=0; $i < count($r->isi); $i++) { 
                    DB::table('navbar')->insert([
                        'urutan' => $r->urutan[$i],
                        'nama' => $r->nama[$i],
                        'route' => $r->route[$i],
                        'isi' => $r->isi[$i],
                    ]);
                }
            }
            $rot = 'akses.navbar';
        } else {

            if (empty($r->detail)) {
                $id = DB::table('permission')->insertGetId([
                    'nm_permission' => $r->nm_permission,
                    'url' => $r->url,
                ]);
    
                for ($i = 0; $i < count($r->nm_button); $i++) {
                    DB::table('permission_button')->insert([
                        'permission_id' => $id,
                        'nm_permission_button' => $r->nm_button[$i],
                        'jenis' => $r->jenis[$i],
                    ]);
                }
            } else {
                if (!empty($r->nm_button_detail)) {
                    for ($i = 0; $i < count($r->nm_button_detail); $i++) {
                        DB::table('permission_button')->where('id_permission_button', $r->id_permission_button[$i])->update([
                            'permission_id' => $r->id_permission_gudang,
                            'nm_permission_button' => $r->nm_button_detail[$i],
                            'jenis' => $r->jenis[$i],
                        ]);
                    }
                }
    
                if (!empty($r->tambah_row)) {
                    for ($i = 0; $i < count($r->nm_button_row); $i++) {
                        DB::table('permission_button')->insert([
                            'permission_id' => $r->id_permission_gudang,
                            'nm_permission_button' => $r->nm_button_row[$i],
                            'jenis' => $r->jenis_row[$i],
                        ]);
                    }
                }
            }
        }


        return redirect()->route($rot ?? 'akses.index')->with('sukses', 'Data Berhasil');
    }

    public function detail($id)
    {
        $detail = DB::table('permission_button as a')->join('permission as b', 'a.permission_id', 'b.id_permission')->where('id_permission', $id)->get();

        return response()->json($detail);
    }

    public function editMenu(Request $r)
    {
    }

    public function save(Request $r)
    {
        $id_user = $r->id_user;
        $permission_id = $r->id_permission_gudang;
        DB::table('permission_perpage')->where('permission_id', $permission_id)->delete();
        if (!empty($id_user)) {
            for ($i = 0; $i < count($id_user); $i++) {
                $id_permission = "id_permission" . $id_user[$i];
                $id_permission = $r->$id_permission;
                if (empty($id_permission)) {
                    return redirect()->route('dashboard')->with('error', 'Permission Tidak Ada');
                }

                foreach ($id_permission as $b => $d) {
                    $data = [
                        'id_permission_button' => $d,
                        'id_user' => $id_user[$i],
                        'permission_id' => $permission_id
                    ];
                    DB::table('permission_perpage')->insert($data);
                }
            }
            $pesan = 'sukses';
        }

        return redirect()->route(
            !empty($r->id) ? $r->route :
                $r->route,
            $r->id
        )->with($pesan ?? 'error', "Permission " . strtoupper($pesan ?? 'error') . " di input");
    }
}
