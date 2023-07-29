<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Penjualan_martadah_alpaController extends Controller
{
    public function index(Request $r)
    {


        $data =  [
            'title' => 'Penjualan Agrilaras',
            'invoice' => DB::select("SELECT a.no_nota, a.tgl, a.tipe, a.admin, a.customer, b.nm_customer, sum(a.total_rp) as ttl_rp, a.status, a.cek, a.urutan_customer, a.admin
            FROM invoice_telur as a 
            left join customer as b on b.id_customer = a.id_customer
              where a.lokasi = 'mtd'
            group by a.no_nota
            order by a.urutan DESC
            ")

        ];
        return view('penjualan_martadh.index', $data);
    }

    public function detail_penjualan_mtd(Request $r)
    {
        $penjualan_mtd = DB::select("SELECT a.*, b.nm_telur FROM invoice_mtd as a 
        left join telur_produk as b on b.id_produk_telur = a.id_produk
        where a.no_nota = '$r->no_nota';");

        $penjualan_mtd_detail = DB::selectOne("SELECT a.*, b.nm_telur FROM invoice_mtd as a 
        left join telur_produk as b on b.id_produk_telur = a.id_produk
        where a.no_nota = '$r->no_nota';");

        $data = [
            'invoice' => $penjualan_mtd,
            'invoice2' => $penjualan_mtd_detail,
        ];

        return view('penjualan_martadh.detail', $data);
    }
}
