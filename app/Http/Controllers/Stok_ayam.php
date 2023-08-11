<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Stok_ayam extends Controller
{
    public function index(Request $r)
    {
        $data = [
            'stok_ayam' => DB::selectOne("SELECT sum(a.debit - a.kredit) as saldo_kandang FROM stok_ayam as a where a.id_gudang = '1' and a.jenis = 'ayam'"),
            'stok_ayam_bjm' => DB::selectOne("SELECT sum(a.debit - a.kredit) as saldo_bjm FROM stok_ayam as a where a.id_gudang = '2' and a.jenis = 'ayam'"),
            'customer' => DB::table('customer')->get(),
            'history_ayam' => DB::table('stok_ayam')->where('jenis', 'ayam')->where('id_gudang', '2')->get(),
            'akun' => DB::table('akun')->whereIn('id_klasifikasi', ['1', '7'])->get(),


        ];
        return view("Stok_ayam.index", $data);
    }

    public function save_penjualan_ayam(Request $r)
    {
        $max = DB::table('invoice_ayam')->latest('urutan')->where('lokasi', 'alpa')->first();
        if (empty($max)) {
            $nota_t = '1000';
        } else {
            $nota_t = $max->urutan + 1;
        }
        $customer = DB::table('customer')->where('id_customer', $r->customer)->first();
        $urutan = empty($max_akun) ? '1001' : ($max_akun->urutan == 0 ? '1001' : $max_akun->urutan + 1);

        $max_customer = DB::table('invoice_ayam')->latest('urutan_customer')->where('id_customer', $r->customer)->first();

        if (empty($max_customer)) {
            $urutan_cus = '1';
        } else {
            $urutan_cus = $max_customer->urutan_customer + 1;
        }

        $data = [
            'tgl' => $r->tgl,
            'debit' => 0,
            'kredit' => $r->qty,
            'id_gudang' => '2',
            'admin' =>  auth()->user()->name,
            'jenis' => 'ayam',
            'no_nota' => 'PA-' . $nota_t,
            'transfer' => 'Y'
        ];
        DB::table('stok_ayam')->insert($data);

        $max = DB::table('invoice_ayam')->latest('urutan')->first();
        if (empty($max)) {
            $nota_t = '1000';
        } else {
            $nota_t = $max->urutan + 1;
        }
        $data = [
            'tgl' => $r->tgl,
            'no_nota' => 'PA-' . $nota_t,
            'id_customer' => $r->customer,
            'qty' => $r->qty,
            'h_satuan' => $r->h_satuan,
            'admin' =>  auth()->user()->name,
            'urutan' =>  $nota_t,
            'urutan_customer' => $urutan_cus,
            'lokasi' => 'alpa'
        ];
        DB::table('invoice_ayam')->insert($data);
        $nota =  'PA-' . $nota_t;
        $max_customer = DB::table('invoice_ayam')->latest('urutan_customer')->where('id_customer', $r->customer)->first();

        if (empty($max_customer)) {
            $urutan_cus = '1';
        } else {
            $urutan_cus = $max_customer->urutan_customer + 1;
        }
        $akun = DB::table('akun')->where('id_akun', '37')->first();

        $data = [
            'tgl' => $r->tgl,
            'no_nota' => 'PA' . $nota_t,
            'id_akun' => '37',
            'id_buku' => '6',
            'ket' => 'Penjualan Ayam ' . $customer->nm_customer . $urutan_cus,
            'debit' => 0,
            'kredit' => $r->ttl_rp,
            'admin' => Auth::user()->name,
            'no_urut' => $akun->inisial . '-' . $urutan,
            'urutan' => $urutan,
        ];
        DB::table('jurnal')->insert($data);

        for ($x = 0; $x < count($r->id_akun); $x++) {
            $max_akun2 = DB::table('jurnal')->latest('urutan')->where('id_akun', $r->id_akun[$x])->first();
            $akun2 = DB::table('akun')->where('id_akun', $r->id_akun[$x])->first();
            $urutan2 = empty($max_akun2) ? '1001' : ($max_akun2->urutan == 0 ? '1001' : $max_akun2->urutan + 1);
            $data = [
                'tgl' => $r->tgl,
                'no_nota' => 'PA' . $nota_t,
                'id_akun' => $r->id_akun[$x],
                'id_buku' => '6',
                'ket' => 'Penjualan Ayam ' . $customer->nm_customer . $urutan_cus,
                'debit' => $r->debit[$x],
                'kredit' => $r->kredit[$x],
                'admin' => Auth::user()->name,
                'no_urut' => $akun2->inisial . '-' . $urutan2,
                'urutan' => $urutan2,
            ];
            DB::table('jurnal')->insert($data);
        }
        return redirect()->route('history_ayam')->with('sukses', 'Data Berhasil Ditambahkan');
    }

    public function history_ayam(Request $r)
    {
        if (empty($r->tgl1)) {
            $tgl1 = date('Y-m-01');
            $tgl2 = date('Y-m-t');
        } else {
            $tgl1 = $r->tgl1;
            $tgl2 = $r->tgl2;
        }

        $data = [
            'title' => 'History Penjualan ayam',
            'invoice_ayam' => DB::select("SELECT * 
            FROM invoice_ayam as a left join customer as b on b.id_customer = a.id_customer 
            where a.lokasi = 'alpa' and a.tgl between '$tgl1' and '$tgl2'
            order by a.no_nota DESC
            "),
            'customer' => DB::table('customer')->get(),
            'tgl1' => $tgl1,
            'tgl2' => $tgl2,
            'stok_ayam_bjm' => DB::selectOne("SELECT sum(a.debit - a.kredit) as saldo_bjm FROM stok_ayam as a where a.id_gudang = '2' and a.jenis = 'ayam'"),
            'akun' => DB::table('akun')->whereIn('id_klasifikasi', ['1', '7'])->get(),
        ];
        return view("Stok_ayam.history", $data);
    }

    public function hapus_ayam(Request $r)
    {
        DB::table('invoice_ayam')->where('no_nota', $r->no_nota)->delete();
        DB::table('jurnal')->where('no_nota', $r->no_nota)->delete();
        return redirect()->route('history_ayam', ['tgl1' => $r->tgl1, 'tgl2' => $r->tgl2])->with('sukses', 'Data Berhasil Ditambahkan');
    }
}
