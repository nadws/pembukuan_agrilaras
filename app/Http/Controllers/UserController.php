<?php

namespace App\Http\Controllers;

use App\Models\Posisi;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        $id_user = auth()->id();
        $data = [
            'title' => 'Data User',
            'user' => User::with('posisi')->where('nonaktif', 'T')->get(),
            'posisi' => Posisi::all(),
            'tambah' => \SettingHal::btnHal(108, $id_user),
            'edit' => \SettingHal::btnHal(109, $id_user),
            'hapus' => \SettingHal::btnHal(110, $id_user),
        ];
        return view('user.user', $data);
    }

    public function create(Request $r)
    {
        $validated = $r->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'posisi_id' => ['required'],
            'password' => ['required', 'string', 'min:4'],
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'posisi_id' => $validated['posisi_id'],
            'password' => bcrypt($validated['password']),
            'nonaktif' => 'T',
        ]);

        return redirect()->route('user.index')->with('sukses', 'Data Berhasil Dibuat');
    }

    public function edit(Request $r)
    {
        $user = User::with('posisi')->findOrFail($r->id);
        $posisi = Posisi::all();

        return view('user.edit', compact('user', 'posisi'));
    }

    public function update(Request $r)
    {
        $validated = $r->validate([
            'id_user' => ['required', 'exists:users,id'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $r->id_user . ',id'],
            'posisi_id' => ['required'],
            'password' => ['nullable', 'string', 'min:4'],
        ]);

        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'posisi_id' => $validated['posisi_id'],
        ];
        if (!empty($validated['password'])) {
            $data['password'] = bcrypt($validated['password']);
        }

        User::where('id', $validated['id_user'])->update($data);

        return redirect()->route('user.index')->with('sukses', 'Data Berhasil Diubah');
    }

    public function delete(Request $r)
    {
        User::find($r->id_user)->delete();
        return redirect()->route('user.index')->with('sukses', 'Data Berhasil Dihapus');
    }
}
