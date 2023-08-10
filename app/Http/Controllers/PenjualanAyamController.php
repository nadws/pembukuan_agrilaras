<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PenjualanAyamController extends Controller
{
    public function index()
    {
        $penjualan = DB::select("SELECT *, sum(a.h_satuan * a.qty) as total, count(*) as ttl_produk  FROM `invoice_ayam` as a
        LEFT JOIN customer as b ON a.id_customer = b.id_customer
        WHERE a.lokasi = 'mtd'
        GROUP BY a.urutan");
        $data = [
            'title' => 'Penjualan Ayam',
            'penjualan' => $penjualan
        ];
        return view('penjualan_ayam.penjualan_ayam',$data);
    }

    public function setor(Request $r)
    {
        $data = [
            'title' => 'Penerimaan Uang Penjualan Ayam',
            'nota' => $r->no_nota,
            'akun' => DB::table('akun')->get(),
        ];
        return view('penjualan_ayam.setor',$data);
    }

    public function save_setor(Request $r)
    {
        $id_akun_penualan_ayam = 521;
        $max = DB::table('notas')->latest('nomor_nota')->where('id_buku', '6')->first();

        if (empty($max)) {
            $nota_t = '1000';
        } else {
            $nota_t = $max->nomor_nota + 1;
        }
        DB::table('notas')->insert(['nomor_nota' => $nota_t, 'id_buku' => '6']);

        for ($x = 0; $x < count($r->no_nota); $x++) {
            $max_akun = DB::table('jurnal')->latest('urutan')->where('id_akun', $id_akun_penualan_ayam)->first();
            $akun = DB::table('akun')->where('id_akun', $id_akun_penualan_ayam)->first();
            $urutan = empty($max_akun) ? '1001' : ($max_akun->urutan == 0 ? '1001' : $max_akun->urutan + 1);

            $data = [
                'tgl' => $r->tgl[$x],
                'no_nota' => 'PAMTD-' . $nota_t,
                'id_akun' => $id_akun_penualan_ayam,
                'id_buku' => '6',
                'ket' => 'Penjualan  ' . $r->no_nota[$x],
                'debit' => '0',
                'kredit' => $r->pembayaran[$x],
                'admin' => auth()->user()->name,
                'no_urut' => $akun->inisial . '-' . $urutan,
                'urutan' => $urutan,
            ];
            DB::table('jurnal')->insert($data);

            DB::table('invoice_ayam')->where('urutan', $r->urutan[$x])->update(['cek' => 'Y', 'admin_cek' => auth()->user()->name]);
        }

        for ($x = 0; $x < count($r->id_akun); $x++) {
            $max_akun = DB::table('jurnal')->latest('urutan')->where('id_akun', $r->id_akun[$x])->first();
            $akun = DB::table('akun')->where('id_akun', $r->id_akun[$x])->first();

            $urutan = empty($max_akun) ? '1001' : ($max_akun->urutan == 0 ? '1001' : $max_akun->urutan + 1);
            $data = [
                'tgl' => $r->tgl[$x],
                'no_nota' => 'PAMTD-' . $nota_t,
                'id_akun' => $r->id_akun[$x],
                'id_buku' => '6',
                'ket' => 'Penjualan ayam di Martadah',
                'debit' => $r->debit[$x],
                'kredit' => $r->kredit[$x],
                'admin' => auth()->user()->name,
                'no_urut' => $akun->inisial . '-' . $urutan,
                'urutan' => $urutan,
            ];
            DB::table('jurnal')->insert($data);
        }

        return redirect()->route('penjualan_ayam.index')->with('sukses', 'Data berhasil ditambahkan');
    }
}