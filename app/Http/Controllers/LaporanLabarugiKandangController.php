<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanLabarugiKandangController extends Controller
{
    public function index(Request $r)
    {
        $bulan = $r->bulan ?? date('m');
        $tahun = $r->tahun ?? date('Y');
        $tgl = date('Y-m-t', strtotime($tahun . '-' . $bulan . '-01'));
        $data = [
            'kandang' => DB::select("SELECT a.id_kandang, b.nm_kandang, sum(a.pcs) as pcs , sum(a.kg) as kg, (COALESCE(b.stok_awal,0) - COALESCE(c.mati,0) -COALESCE(c.jual,0) - COALESCE(c.afkir,0)) as ttl_ayam, (COALESCE(c.jual,0) + COALESCE(c.afkir,0)) as jual
            FROM stok_telur as a 
            left join kandang as b on b.id_kandang = a.id_kandang
            left join (
            	SELECT c.id_kandang, sum(c.mati) as mati, sum(c.jual) as jual, sum(c.afkir) as afkir
                FROM populasi as c
                where c.tgl <= '$tgl'
                group by c.id_kandang
            ) as c on c.id_kandang = a.id_kandang
            where Month(a.tgl) = '$bulan' and Year(a.tgl) = '$tahun' and a.pcs != 0 and a.id_gudang = '1' and a.id_kandang != '0'
            group by a.id_kandang
            order by b.nm_kandang ASC;"),
            'biaya_pokok' => DB::select("SELECT a.kode, b.nama, a.nm_departemen, sum(a.debit) as total
            FROM jurnal_accurate as a
            left join akun_accurate as b on b.kode = a.kode
            WHERE a.buku='1' and a.debit != 0 and Month(a.tgl) = '$bulan' and Year(a.tgl) = '$tahun' and a.nm_departemen is not null
            group by a.kode;"),

            'bulan' => $bulan,
            'tahun' => $tahun,
            'harga_telur' => DB::table('harga_telur')->whereMonth('tgl', $bulan)->whereYear('tgl', $tahun)->orderByDesc('tgl')->first(),
            'operasional' => DB::select("SELECT a.kode, b.nama, sum(a.debit) as debit
            FROM jurnal_accurate as a 
            left join akun_accurate as b on b.kode = a.kode
            where a.buku = '2' and MONTH(a.tgl) = '$bulan' and YEAR(a.tgl) = '$tahun'
            group by a.kode;"),
            'biaya_pokok2' => DB::select("SELECT a.kode, b.nama, sum(a.debit) as debit
            FROM jurnal_accurate as a 
            left join akun_accurate as b on b.kode = a.kode
            where a.buku = '1' and MONTH(a.tgl) = '$bulan' and YEAR(a.tgl) = '$tahun' and a.nm_departemen is null and a.debit != 0
            group by a.kode;"),
            'tgl' => $tgl,
            'bulan_array' => DB::table('bulan')->get(),
            'tahun_array' => DB::table('jurnal_accurate')
                ->selectRaw('YEAR(tgl) as tahun')
                ->distinct()
                ->orderBy('tahun', 'desc')
                ->get()


        ];
        return view('laporan-laba-rugi-kandang.index', $data);
    }
}
