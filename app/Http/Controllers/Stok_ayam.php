<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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
            'akun' => DB::table('akun')->whereIn('id_klasifikasi', ['1', '7'])->get()
        ];
        return view("Stok_ayam.index", $data);
    }
}
