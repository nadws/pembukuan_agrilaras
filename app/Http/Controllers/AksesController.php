<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AksesController extends Controller
{
    public function index()
    {

        if ((string) auth()->user()->posisi_id === '1') {

            $roles = DB::table('tb_posisi')->orderBy('id_posisi')->get()->map(function ($role) {
                $role->jumlah_user = DB::table('users')->where('posisi_id', (string) $role->id_posisi)->count();
                $role->jumlah_akses = DB::table('permission_role')->where('posisi_id', $role->id_posisi)->count();
                return $role;
            });
            $permissions = DB::table('permission')->orderBy('id_permission')->get();
            $buttons = DB::table('permission_button')->orderBy('permission_id')->orderBy('id_permission_button')->get();
            $grants = DB::table('permission_role')->get()->groupBy('posisi_id')
                ->map(fn ($rows) => $rows->pluck('id_permission_button')->map(fn ($id) => (int) $id)->flip());

            $data = [
                'title' => 'Role & Access',
                'roles' => $roles,
                'permissionHalaman' => $permissions,
                'permissionButton' => $buttons,
                'grants' => $grants,
            ];
            return view('permission_halaman.index', $data);
        } else {
            abort(403, 'akses tidak ada');
        }
    }

    public function storeRole(Request $r)
    {
        abort_unless((string) auth()->user()->posisi_id === '1', 403, 'akses tidak ada');
        $validated = $r->validate(['nm_posisi' => ['required', 'string', 'max:100']]);
        DB::table('tb_posisi')->insert([
            'nm_posisi' => trim($validated['nm_posisi']),
            'created_at' => now(), 'updated_at' => now(),
        ]);

        return redirect()->route('akses.index')->with('sukses', 'Role berhasil ditambahkan.');
    }

    public function updateRole(Request $r, int $id)
    {
        abort_unless((string) auth()->user()->posisi_id === '1', 403, 'akses tidak ada');
        $validated = $r->validate(['nm_posisi' => ['required', 'string', 'max:100']]);
        DB::table('tb_posisi')->where('id_posisi', $id)->update([
            'nm_posisi' => trim($validated['nm_posisi']),
            'updated_at' => now(),
        ]);

        return redirect()->route('akses.index')->with('sukses', 'Role berhasil diubah.');
    }

    public function destroyRole(int $id)
    {
        abort_unless((string) auth()->user()->posisi_id === '1', 403, 'akses tidak ada');
        $dipakai = DB::table('users')->where('posisi_id', (string) $id)->count();
        abort_if($dipakai > 0, 422, 'Role masih dipakai ' . $dipakai . ' user, pindahkan dulu usernya.');

        DB::transaction(function () use ($id) {
            DB::table('permission_role')->where('posisi_id', $id)->delete();
            DB::table('tb_posisi')->where('id_posisi', $id)->delete();
        });

        return redirect()->route('akses.index')->with('sukses', 'Role berhasil dihapus.');
    }

    public function saveMatrix(Request $r)
    {
        abort_unless((string) auth()->user()->posisi_id === '1', 403, 'akses tidak ada');
        $validated = $r->validate([
            'akses' => ['nullable', 'array'],
            'akses.*' => ['array'],
            'akses.*.*' => ['integer', 'exists:permission_button,id_permission_button'],
        ]);

        $validPosisi = DB::table('tb_posisi')->pluck('id_posisi')->map(fn ($id) => (int) $id);

        DB::transaction(function () use ($validated, $validPosisi) {
            foreach ($validPosisi as $posisiId) {
                $buttonIds = collect($validated['akses'][$posisiId] ?? [])
                    ->map(fn ($id) => (int) $id)->unique()->values();
                DB::table('permission_role')->where('posisi_id', $posisiId)->delete();
                foreach ($buttonIds->chunk(200) as $chunk) {
                    DB::table('permission_role')->insert($chunk->map(fn ($buttonId) => [
                        'posisi_id' => $posisiId,
                        'id_permission_button' => $buttonId,
                        'created_at' => now(), 'updated_at' => now(),
                    ])->all());
                }
            }
        });

        return redirect()->route('akses.index')->with('sukses', 'Matriks akses per role berhasil disimpan.');
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

    public function saveRolePage(Request $r)
    {
        abort_unless((string) auth()->user()->posisi_id === '1', 403, 'akses tidak ada');
        $validated = $r->validate([
            'route' => ['required', 'string', 'max:150'],
            'id' => ['nullable'],
            'permission_id' => ['required', 'integer', 'exists:permission,id_permission'],
            'akses' => ['nullable', 'array'],
            'akses.*' => ['array'],
            'akses.*.*' => ['integer', 'exists:permission_button,id_permission_button'],
        ]);

        $buttonIds = DB::table('permission_button')
            ->where('permission_id', $validated['permission_id'])
            ->pluck('id_permission_button')->map(fn ($id) => (int) $id);
        $roles = DB::table('tb_posisi')->pluck('id_posisi')->map(fn ($id) => (int) $id);

        DB::transaction(function () use ($validated, $buttonIds, $roles) {
            DB::table('permission_role')
                ->whereIn('posisi_id', $roles)
                ->whereIn('id_permission_button', $buttonIds)
                ->delete();
            foreach ($roles as $posisiId) {
                $chosen = collect($validated['akses'][$posisiId] ?? [])
                    ->map(fn ($id) => (int) $id)
                    ->intersect($buttonIds)->unique()->values();
                foreach ($chosen as $buttonId) {
                    DB::table('permission_role')->insert([
                        'posisi_id' => $posisiId,
                        'id_permission_button' => $buttonId,
                        'created_at' => now(), 'updated_at' => now(),
                    ]);
                }
            }
        });

        return redirect()->route($validated['route'], $validated['id'] ?? [])->with('sukses', 'Akses role berhasil disimpan.');
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
