<?php

namespace App\Http\Controllers;

use App\Models\Jurnal;
use App\Models\proyek;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Jurnal_aktivaController extends Controller
{
    public function add_balik_aktiva(Request $r)
    {
        $max = DB::table('notas')->latest('nomor_nota')->where('id_buku', '2')->first();

        if (empty($max)) {
            $nota_t = '1000';
        } else {
            $nota_t = $max->nomor_nota + 1;
        }
        $post = DB::select("SELECT * FROM tb_post_center as a where a.id_akun = 43 and a.nm_post not in(SELECT b.nm_aktiva FROM aktiva as b)");
        $data =  [
            'title' => 'Tambah Jurnal Umum',
            'max' => $nota_t,
            'proyek' => proyek::where('status', 'berjalan')->get(),
            'suplier' => DB::table('tb_suplier')->get(),
            'id_buku' => $r->id_buku,
            'akun_gantung' => DB::table('akun')->where('id_akun', 43)->first(),
            'akun_aktiva' => DB::table('akun')->where('id_akun', 9)->first(),
            'post' => $post

        ];

        return view('jurnal_pembalik_aktiva.add_aktiva', $data);
    }
    public function save_jurnal(Request $r)
    {
        $tgl = $r->tgl;
        // $no_nota = $r->no_nota;
        $id_akun = $r->id_akun;
        $keterangan = $r->keterangan;
        $debit = $r->debit;
        $kredit = $r->kredit;
        $id_proyek = $r->id_proyek;
        $id_suplier = $r->id_suplier;
        $no_urut = $r->no_urut;
        $id_post = $r->id_post;
        $id_buku = $r->id_buku;


        $max = DB::table('notas')->latest('nomor_nota')->where('id_buku', '2')->first();

        if (empty($max)) {
            $nota_t = '1000';
        } else {
            $nota_t = $max->nomor_nota + 1;
        }
        DB::table('notas')->insert(['nomor_nota' => $nota_t, 'id_buku' => '2']);

        for ($i = 0; $i < count($id_akun); $i++) {
            $max_akun = DB::table('jurnal')->latest('urutan')->where('id_akun', $id_akun[$i])->first();
            $akun = DB::table('akun')->where('id_akun', $id_akun[$i])->first();
            $urutan = empty($max_akun) ? '1001' : ($max_akun->urutan == 0 ? '1001' : $max_akun->urutan + 1);


            $data = [
                'tgl' => $tgl,
                'no_nota' => 'JU-' . $nota_t,
                'id_akun' => $id_akun[$i],
                'no_dokumen' => $no_urut[$i],
                'id_buku' => $id_buku,
                'ket' => $keterangan[$i],
                'debit' => $debit[$i],
                'kredit' => $kredit[$i],
                'admin' => Auth::user()->name,
                // 'no_dokumen' => $r->no_dokumen,
                'tgl_dokumen' => $r->tgl_dokumen,
                'id_proyek' => $id_proyek,
                'id_suplier' => $id_suplier,
                'no_urut' => $akun->inisial . '-' . $urutan,
                'urutan' => $urutan,
                'id_post_center' => $id_post
            ];
            Jurnal::create($data);
        }

        // $tgl1 = date('Y-m-01', strtotime($r->tgl));
        // $tgl2 = date('Y-m-t', strtotime($r->tgl));
        // return redirect()->route('jurnal', ['period' => 'costume', 'tgl1' => $tgl1, 'tgl2' => $tgl2, 'id_proyek' => 0, 'id_buku' => $id_buku])->with('sukses', 'Data berhasil ditambahkan');
        $nota_cek = 'JU-' . $nota_t;
        return redirect()->route('Cek_aktiva', ['no_nota' => $nota_cek])->with('sukses', 'Data berhasil ditambahkan');
    }

    public function Cek_aktiva(Request $r)
    {
        $data =  [
            'title' => 'Nota Pembalikan Aktiva Gantung',
            'no_nota' => $r->no_nota,
            'jurnal' => DB::select("SELECT * FROM jurnal as a where a.no_nota = '$r->no_nota'")
        ];

        return view('jurnal_pembalik_aktiva.cek_aktiva', $data);
    }
}
