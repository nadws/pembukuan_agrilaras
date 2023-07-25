<?php

namespace App\Http\Controllers;

use App\Models\Suplier;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Nonaktif;

class SuplierController extends Controller
{
    public function index()
    {
        $data = [
            'title' => 'Data Suplier',
            'suplier' => Suplier::where('nonaktif', 'T')->get()
        ];
        return view('suplier.suplier', $data);
    }

    public function create(Request $r)
    {
        if (!empty($r->file('img'))) {

            $file = $r->file('img');
            $fileDiterima = ['jpg', 'png', 'jpeg'];
            $cek = in_array($file->getClientOriginalExtension(), $fileDiterima);
            if ($cek) {
                $maxFileSize = 1024 * 1024; // 1MB
                if ($file instanceof UploadedFile && $file->getSize() > $maxFileSize) {
                    return redirect()->route('suplier.index')->with('error', 'File lebih dari 1MB');
                }
                $fileName = "S-$r->kd_produk" . $file->getClientOriginalName();
                $path = $file->move('upload/suplier', $fileName);
                Suplier::create([
                    'nm_suplier' => $r->nm_suplier,
                    'email' => $r->email,
                    'alamat' => $r->alamat,
                    'telepon' => $r->telepon,
                    'npwp' => $r->npwp,
                    'dokumen' => $fileName,
                    'admin' => auth()->user()->name,
                ]);

                return redirect()->route('suplier.index')->with('sukses', 'Data Berhasil Ditambahkan');
            } else {
                return redirect()->route('suplier.index')->with('error', 'File tidak didukung');
            }
        } else {
            Suplier::create([
                'nm_suplier' => $r->nm_suplier,
                'email' => $r->email,
                'alamat' => $r->alamat,
                'telepon' => $r->telepon,
                'npwp' => $r->npwp,
                'admin' => auth()->user()->name,
            ]);

            return redirect()->route('suplier.index')->with('sukses', 'Data Berhasil Ditambahkan');
        }
    }

    public function edit($id_suplier)
    {
        $data = [
            'suplier' => Suplier::where('id_suplier', $id_suplier)->first(),
            'id_suplier' => $id_suplier
        ];
        return view('suplier.edit', $data);
    }

    public function update(Request $r)
    {
        if (!empty($r->file('img'))) {
            $file = $r->file('img');
            $fileDiterima = ['jpg', 'png', 'jpeg'];
            $cek = in_array($file->getClientOriginalExtension(), $fileDiterima);
            if ($cek) {

                $maxFileSize = 1024 * 1024; // 1MB
                if ($file instanceof UploadedFile && $file->getSize() > $maxFileSize) {
                    return redirect()->route('suplier.index')->with('error', 'File lebih dari 1MB');
                }

                $fileName = "S-$r->kd_produk" . $file->getClientOriginalName();

                if ($fileName != $r->img_edit) {
                    unlink(public_path('/upload/suplier/' . $fileName));
                } else {
                    $path = $file->move('upload/suplier', $fileName);
                }
                $data = [
                    'nm_suplier' => $r->nm_suplier,
                    'email' => $r->email,
                    'alamat' => $r->alamat,
                    'telepon' => $r->telepon,
                    'npwp' => $r->npwp,
                    'dokumen' => $fileName,
                    'admin' => auth()->user()->name,
                ];

                Nonaktif::edit('tb_suplier', 'id_suplier', $r->id_suplier, $data);


                return redirect()->route('suplier.index')->with('sukses', 'Data Berhasil Diedit');
            } else {
                return redirect()->route('suplier.index')->with('error', 'File tidak didukung');
            }
        }

        $data = [
            'nm_suplier' => $r->nm_suplier,
            'email' => $r->email,
            'alamat' => $r->alamat,
            'telepon' => $r->telepon,
            'npwp' => $r->npwp,
            'dokumen' => $r->img_edit,
            'admin' => auth()->user()->name,
        ];
        Nonaktif::edit('tb_suplier', 'id_suplier', $r->id_suplier, $data);

        return redirect()->route('suplier.index')->with('sukses', 'Data Berhasil Diedit');
    }

    public function delete($id_suplier)
    {
        Nonaktif::delete('tb_suplier', 'id_suplier', $id_suplier);
        return redirect()->route('suplier.index')->with('sukses', 'Data Berhasil Dihapus');
    }
}
