<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Penjualan_umum_cekController extends Controller
{
    public function index(Request $r)
    {
        $penjualan = DB::select("SELECT *, sum(a.total_rp) as total, count(*) as ttl_produk  FROM `penjualan_agl` as a
        LEFT JOIN customer as b ON a.id_customer = b.id_customer
        WHERE a.lokasi = 'mtd'
        GROUP BY a.urutan");
        $data = [
            'title' => 'Penjualan Umum',
            'penjualan' => $penjualan,
        ];
        return view('penjualan_umum_cek.index', $data);
    }

    public function terima_invoice_umum_cek(Request $r)
    {
        $data = [
            'title' => 'Penerimaan Uang Martadah',
            'nota' => $r->no_nota,
            'akun' => DB::table('akun')->get(),
        ];
        return view('penjualan_umum_cek.penerimaan_uang', $data);
    }

    public function save_cek_umum_invoice(Request $r)
    {
        $max = DB::table('notas')->latest('nomor_nota')->where('id_buku', '6')->first();

        if (empty($max)) {
            $nota_t = '1000';
        } else {
            $nota_t = $max->nomor_nota + 1;
        }
        DB::table('notas')->insert(['nomor_nota' => $nota_t, 'id_buku' => '6']);

        for ($x = 0; $x < count($r->no_nota); $x++) {
            $max_akun = DB::table('jurnal')->latest('urutan')->where('id_akun', '84')->first();
            $akun = DB::table('akun')->where('id_akun', '84')->first();
            $urutan = empty($max_akun) ? '1001' : ($max_akun->urutan == 0 ? '1001' : $max_akun->urutan + 1);

            $data = [
                'tgl' => $r->tgl[$x],
                'no_nota' => 'PMLD-' . $nota_t,
                'id_akun' => '84',
                'id_buku' => '6',
                'ket' => 'Penjualan  ' . $r->no_nota[$x],
                'debit' => '0',
                'kredit' => $r->pembayaran[$x],
                'admin' => Auth::user()->name,
                'no_urut' => $akun->inisial . '-' . $urutan,
                'urutan' => $urutan,
            ];
            DB::table('jurnal')->insert($data);

            DB::table('penjualan_agl')->where('urutan', $r->urutan[$x])->update(['cek' => 'Y', 'admin_cek' => Auth::user()->name]);
        }

        for ($x = 0; $x < count($r->id_akun); $x++) {
            $max_akun = DB::table('jurnal')->latest('urutan')->where('id_akun', $r->id_akun[$x])->first();
            $akun = DB::table('akun')->where('id_akun', $r->id_akun[$x])->first();

            $urutan = empty($max_akun) ? '1001' : ($max_akun->urutan == 0 ? '1001' : $max_akun->urutan + 1);
            $data = [
                'tgl' => $r->tgl[$x],
                'no_nota' => 'PMLD-' . $nota_t,
                'id_akun' => $r->id_akun[$x],
                'id_buku' => '6',
                'ket' => 'Penjualan lain-lain di Martadah',
                'debit' => $r->debit[$x],
                'kredit' => $r->kredit[$x],
                'admin' => Auth::user()->name,
                'no_urut' => $akun->inisial . '-' . $urutan,
                'urutan' => $urutan,
            ];
            DB::table('jurnal')->insert($data);
        }

        return redirect()->route('penjualan_umum_cek')->with('sukses', 'Data berhasil ditambahkan');
    }
}
