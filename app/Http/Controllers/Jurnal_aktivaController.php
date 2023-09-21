<?php

namespace App\Http\Controllers;

use App\Models\Gudang;
use App\Models\Jurnal;
use App\Models\Produk;
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
        if (empty($r->kategori)) {
            $kategori =  'aktiva';
        } else {
            $kategori =  $r->kategori;
        }

        if ($kategori == 'aktiva') {
            $akun_gantung = DB::table('akun')->where('id_akun', 43)->first();
            $akun_aktiva = DB::table('akun')->where('id_akun', 9)->first();
            $post = DB::select("SELECT * FROM tb_post_center as a where a.id_akun = '$akun_gantung->id_akun' and a.nm_post not in(SELECT b.nm_aktiva FROM aktiva as b)");
        } else if ($kategori == 'peralatan') {
            $akun_gantung = DB::table('akun')->whereIn('id_akun', [61, 76])->get();
            $akun_aktiva = DB::table('akun')->where('id_akun', 16)->first();
            $post = 'peralatan';
        } else if ($kategori == 'pullet') {
            $akun_gantung = DB::table('akun')->where('id_akun', 76)->first();
            $akun_aktiva = DB::table('akun')->where('id_akun', 75)->first();
            $post = DB::select("SELECT * FROM tb_post_center as a where a.id_akun = '$akun_gantung->id_akun' and a.nm_post not in(SELECT b.nm_aktiva FROM peralatan as b)");
        } else {
            $akun_gantung = DB::table('akun')->where('id_akun', 60)->first();
            $akun_aktiva = DB::table('akun')->where('id_akun', 30)->first();
            $post = DB::select("SELECT * FROM tb_post_center as a where a.id_akun = '$akun_gantung->id_akun' and a.nm_post not in(SELECT b.nm_produk FROM tb_produk as b)");
        }

        $data =  [
            'title' => 'Tambah Jurnal Pembalik Aktiva Gantung',
            'max' => $nota_t,
            'proyek' => proyek::where('status', 'berjalan')->get(),
            'suplier' => DB::table('tb_suplier')->get(),
            'id_buku' => $r->id_buku,
            'akun_gantung' => $akun_gantung,
            'akun_aktiva' => $akun_aktiva,
            'post' => $post,
            'kategori' => $kategori

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

        return redirect()->route('Cek_aktiva', ['no_nota' => $nota_cek, 'kategori' => $r->kategori])->with('sukses', 'Data berhasil ditambahkan');
    }

    public function Cek_aktiva(Request $r)
    {
        if ($r->kategori == 'aktiva') {
            $kelompok = DB::table('kelompok_aktiva')->get();
        } else if ($r->kategori == 'peralatan') {
            $kelompok = DB::table('kelompok_peralatan')->get();
        } else {
            $kelompok = 0;
        }

        $data =  [
            'title' => 'Cek Nota',
            'no_nota' => $r->no_nota,
            'gudang' => Gudang::where('kategori_id', 1)->get(),
            'jurnal' => Jurnal::where('no_nota', $r->no_nota)->get(),
            'head_jurnal' => DB::selectOne("SELECT a.ket,c.nm_suplier, a.tgl, b.nm_proyek, a.id_proyek, a.no_dokumen,a.tgl_dokumen, a.no_nota, sum(a.debit) as debit , sum(a.kredit) as kredit , d.nm_post
            FROM jurnal as a 
            left join proyek as b on b.id_proyek = a.id_proyek
            left join tb_suplier as c on c.id_suplier = a.id_suplier
            left join tb_post_center as d on d.id_post_center = a.id_post_center
            where a.no_nota = '$r->no_nota'"),
            'kelompok' => $kelompok,
            'kategori' => $r->kategori,
            'pembelian' => $r->pembelian ?? '',
            'satuan' => DB::table('tb_satuan')->get()
        ];

        return view('jurnal_pembalik_aktiva.cek_aktiva', $data);
    }

    public function save_atk(Request $r)
    {
        $no_nota = "INV" . strtoupper(str()->random(4));
        $kd_produk = Produk::latest('kd_produk')->first();
        $kd_produk = $kd_produk->kd_produk + 1;
        $data = [
            'nm_produk' => $r->nm_atk,
            'kd_produk' => $kd_produk,
            'kontrol_stok' => 'Y',
            'kategori_id' => '1',
            'gudang_id' => $r->id_gudang,
            'satuan_id' => $r->id_satuan,
            'departemen_id' => '1',
            'tgl' => $r->tgl,
            'admin' => auth()->user()->name

        ];
        $insertedProductId = DB::table('tb_produk')->insertGetId($data);
        $data = [
            'id_produk' => $insertedProductId,
            'no_nota' => $no_nota,
            'tgl' => $r->tgl,
            'jenis' => 'selesai',
            'status' => 'masuk',
            'jml_sebelumnya' => '0',
            'jml_sesudahnya' => $r->stok,
            'debit' => $r->stok,
            'kredit' => '0',
            'rp_satuan' => $r->total_rp,
            'gudang_id' => $r->id_gudang,
            'kategori_id' => '1',
            'admin' => auth()->user()->name
        ];
        DB::table('tb_stok_produk')->insert($data);
        return redirect()->route('produk.index')->with('sukses', 'Data Berhasil Ditambahkan');
    }

    public function get_post_pembalikan(Request $r)
    {
        $id_akun = $r->id_akun;
        // $post = DB::table('tb_post_center')->where('id_akun', $id_akun)->get();
        $post = DB::select("SELECT * FROM tb_post_center as a where a.id_akun = '$id_akun' and a.nm_post not in(SELECT b.nm_aktiva FROM peralatan as b)");

        echo "<option value=''>Pilih sub akun</option>";
        foreach ($post as $k) {
            echo "<option value='" . $k->id_post_center  . "'>" . $k->nm_post . "</option>";
        }
    }
}
