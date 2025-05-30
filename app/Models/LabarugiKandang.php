<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class LabarugiKandang extends Model
{
    use HasFactory;

    public static function kandang($bulan, $tahun, $tgl_sebelum)
    {
        return DB::select("SELECT a.id_kandang, b.nm_kandang, d.pcs, d.kg,(COALESCE(b.stok_awal,0) - COALESCE(e.mati,0) -COALESCE(e.jual,0) - COALESCE(e.afkir,0)) as ttl_ayam, (COALESCE(e.jual,0) + COALESCE(e.afkir,0)) as jual
        FROM stok_produk_perencanaan as a
        left join kandang as b on b.id_kandang = a.id_kandang
        left join tb_produk_perencanaan as c on c.id_produk = a.id_pakan
        left join (
            SELECT d.id_kandang, sum(d.pcs) as pcs , sum(d.kg) as kg
            FROM stok_telur as d
            Where Month(d.tgl) = '$bulan' and Year(d.tgl) = '$tahun' and d.pcs != 0 and d.id_gudang = '1' and d.id_kandang != '0'
            group by d.id_kandang
        ) as d on d.id_kandang = a.id_kandang
        left join (
        SELECT e.id_kandang, sum(e.mati) as mati, sum(e.jual) as jual, sum(e.afkir) as afkir
        FROM populasi as e
        where e.tgl <= '$tgl_sebelum'
        group by e.id_kandang
        ) as e on e.id_kandang = a.id_kandang

        where Month(a.tgl) = '$bulan' and Year(a.tgl) = '$tahun' and c.kategori ='pakan' and a.id_kandang != '0'
        group by a.id_kandang
        order by b.nm_kandang ASC;");
    }
    public static function ayam1($bulan, $tahun, $kandang)
    {
        return DB::selectOne(
            "SELECT a.* FROM jurnal_accurate as a where a.kode = '400002' and a.nm_departemen = '$kandang' and a.nm_departemen is not null and MONTH(a.tgl) = '$bulan' and YEAR(a.tgl) = '$tahun';",
        );
    }
    public static function ayam2($bulan, $tahun)
    {
        return DB::selectOne(
            "SELECT a.* FROM jurnal_accurate as a where a.kode = '400002' and  a.nm_departemen is  null and MONTH(a.tgl) = '$bulan' and YEAR(a.tgl) = '$tahun';",
        );
    }
    public static function biaya_pokok($bulan, $tahun)
    {
        return DB::select("SELECT a.kode, b.nama, a.nm_departemen, sum(a.debit) as total
            FROM jurnal_accurate as a
            left join akun_accurate as b on b.kode = a.kode
            WHERE a.buku='1' and a.debit != 0 and Month(a.tgl) = '$bulan' and Year(a.tgl) = '$tahun' and a.nm_departemen is not null
            group by a.kode;");
    }
    public static function biaya_pokok2($bulan, $tahun)
    {
        return DB::select("SELECT a.kode, b.nama, sum(a.debit) as debit
            FROM jurnal_accurate as a 
            left join akun_accurate as b on b.kode = a.kode
            where a.buku = '1' and a.kode != '5101-01' and MONTH(a.tgl) = '$bulan' and YEAR(a.tgl) = '$tahun' and a.nm_departemen is null and a.debit != 0
            group by a.kode;");
    }
    public static function biaya_pokok3($bulan, $tahun)
    {
        return DB::select("SELECT a.kode, b.nama, sum(a.debit) as debit
            FROM jurnal_accurate as a 
            left join akun_accurate as b on b.kode = a.kode
            where a.buku = '1' and a.kode = '5101-01' and MONTH(a.tgl) = '$bulan' and YEAR(a.tgl) = '$tahun' and a.nm_departemen is null and a.debit != 0
            group by a.kode;");
    }
    public static function operasional($bulan, $tahun)
    {
        return DB::select("SELECT a.kode, b.nama, sum(a.debit) as debit
            FROM jurnal_accurate as a 
            left join akun_accurate as b on b.kode = a.kode
            where a.buku = '2' and MONTH(a.tgl) = '$bulan' and YEAR(a.tgl) = '$tahun'
            group by a.kode;");
    }
    public static function akumulasiKandang($kandang, $tipe)
    {
        return DB::select("SELECT b.nama, sum(a.ttl_rp) as ttl_rp 
            FROM laba_rugi_kandang as a
            left join akun_accurate as b on b.kode = a.kode
            where a.kandang_id = '$kandang' and b.tipe_akun = '$tipe'
            group by a.kode
            order by a.kode ASC ;
            ");
    }
}
