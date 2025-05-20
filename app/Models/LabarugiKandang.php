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
        return DB::select("SELECT a.id_kandang, b.nm_kandang, sum(a.pcs) as pcs , sum(a.kg) as kg, (COALESCE(b.stok_awal,0) - COALESCE(c.mati,0) -COALESCE(c.jual,0) - COALESCE(c.afkir,0)) as ttl_ayam, (COALESCE(c.jual,0) + COALESCE(c.afkir,0)) as jual
            FROM stok_telur as a 
            left join kandang as b on b.id_kandang = a.id_kandang
            left join (
            	SELECT c.id_kandang, sum(c.mati) as mati, sum(c.jual) as jual, sum(c.afkir) as afkir
                FROM populasi as c
                where c.tgl <= '$tgl_sebelum'
                group by c.id_kandang
            ) as c on c.id_kandang = a.id_kandang
            where Month(a.tgl) = '$bulan' and Year(a.tgl) = '$tahun' and a.pcs != 0 and a.id_gudang = '1' and a.id_kandang != '0'
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
            where a.buku = '1' and MONTH(a.tgl) = '$bulan' and YEAR(a.tgl) = '$tahun' and a.nm_departemen is null and a.debit != 0
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
