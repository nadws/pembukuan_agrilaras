<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CashIbuModel extends Model
{
    use HasFactory;

    public static function piutang($tgl_back)
    {
        return DB::select("SELECT a.id_akun,a.nm_akun, b.debit, b.kredit
        FROM akun as a
        left join (
        SELECT b.id_akun , sum(b.debit) as debit , sum(b.kredit) as kredit
        FROM jurnal as b
        where b.tgl BETWEEN '2020-01-01' and '$tgl_back' and  b.id_buku in('6','1')
        group by b.id_akun
        ) as b on b.id_akun = a.id_akun
        where a.id_akun in(SELECT t.id_akun FROM akuncash_ibu as t where t.kategori = '1');");
    }
    public static function penjualan($tgl1, $tgl2)
    {
        return DB::select("SELECT a.id_akun,a.nm_akun, b.debit, b.kredit
        FROM akun as a
        left join (
        SELECT b.id_akun, sum(b.debit) as debit , sum(b.kredit) as kredit
        FROM jurnal as b
        where b.tgl BETWEEN '$tgl1' and '$tgl2' and b.id_buku = '6'
        group by b.id_akun
        ) as b on b.id_akun = a.id_akun
        where a.id_akun in (SELECT t.id_akun FROM akuncash_ibu as t where t.kategori = '2');");
    }
    public static function uang($tgl1, $tgl2)
    {
        return DB::select("SELECT a.id_akun, a.nm_akun, b.debit, b.kredit
        FROM akun as a
        left join (
        SELECT b.id_akun, sum(b.debit) as debit , sum(b.kredit) as kredit
        FROM jurnal as b
        where b.tgl BETWEEN '$tgl1' and '$tgl2' and b.id_buku = '6'
        group by b.id_akun
        ) as b on b.id_akun = a.id_akun
        where a.id_akun in (SELECT t.id_akun FROM akuncash_ibu as t where t.kategori = '3');");
    }
    public static function piutang2($tgl2)
    {
        return DB::select("SELECT a.id_akun,a.nm_akun, b.debit, b.kredit
        FROM akun as a
        left join (
        SELECT b.id_akun, sum(b.debit) as debit , sum(b.kredit) as kredit
        FROM jurnal as b
        where b.tgl BETWEEN '2020-01-01' and '$tgl2' and b.id_buku in('6','1')
        group by b.id_akun
        ) as b on b.id_akun = a.id_akun
        where a.id_akun in (SELECT t.id_akun FROM akuncash_ibu as t where t.kategori = '4');");
    }
    public static function kerugian($tgl_back1, $tgl_back)
    {
        return DB::selectOne("SELECT a.id_akun,a.tgl, a.no_nota, sum(a.debit) AS debit, sum(a.kredit) AS kredit
        FROM jurnal AS a
        LEFT JOIN akun AS b ON b.id_akun = a.id_akun
        WHERE a.tgl BETWEEN '$tgl_back1' AND '$tgl_back' AND a.id_akun = 36");
    }
    public static function kerugianBulanIni($tgl1, $tgl2)
    {
        return DB::selectOne("SELECT a.id_akun,a.tgl, a.no_nota, sum(a.debit) AS debit, sum(a.kredit) AS kredit
        FROM jurnal AS a
        LEFT JOIN akun AS b ON b.id_akun = a.id_akun
        WHERE a.tgl BETWEEN '$tgl1' AND '$tgl2' AND a.id_akun = 36");
    }
    public static function biaya($tgl1, $tgl2)
    {
        return DB::select("SELECT a.id_akun, a.no_nota, c.nm_akun, sum(a.debit) as debit 
        FROM jurnal as a 
        left join ( SELECT b.no_nota , b.id_akun, c.nm_akun FROM jurnal as b 
        left join akun as c on c.id_akun = b.id_akun 
        where b.id_akun in (SELECT t.id_akun FROM akuncash_ibu as t where t.kategori in ('6')) and b.tgl BETWEEN '$tgl1' and '$tgl2' and b.kredit != 0 and b.id_buku in(2,10,12) 
        GROUP by b.no_nota ) as b on b.no_nota = a.no_nota 
        left join akun as c on c.id_akun = a.id_akun 
        where a.id_buku in(2,10,12) and a.debit != 0 and a.tgl BETWEEN '$tgl1' and '$tgl2' and b.id_akun is not null 
        group by a.id_akun");
    }
    public static function biaya_proyek($tgl1, $tgl2)
    {
        return DB::select("SELECT a.id_akun, a.no_nota, c.nm_akun, sum(a.debit) as debit , c.id_klasifikasi
        FROM jurnal as a 
        left join ( SELECT b.no_nota , b.id_akun, c.nm_akun FROM jurnal as b 
        left join akun as c on c.id_akun = b.id_akun 
        where b.id_akun in (SELECT t.id_akun FROM akuncash_ibu as t where t.kategori in ('7')) and b.tgl BETWEEN '$tgl1' and '$tgl2' and b.kredit != 0 and b.id_buku in(2,10,12) 
        GROUP by b.no_nota ) as b on b.no_nota = a.no_nota 
        left join akun as c on c.id_akun = a.id_akun 
        where a.id_buku in(2,10,12) and a.debit != 0 and a.tgl BETWEEN '$tgl1' and '$tgl2' and b.id_akun is not null 
        group by a.id_akun");
    }
    public static function biaya_admin($tgl1, $tgl2)
    {
        return DB::selectOne("SELECT sum(a.debit) as debit FROM jurnal as a where a.id_akun = '8' and a.tgl between
            '$tgl1' and '$tgl2' and a.id_buku = '6' ");
    }
    public static function hutang_herry($tgl1, $tgl2)
    {
        return DB::selectOne("SELECT a.id_akun, a.nm_akun, b.debit, b.kredit
        FROM akun as a 
        left join (
        SELECT a.id_akun, sum(a.kredit) as kredit , sum(a.debit) as debit
        FROM jurnal as a 
        where a.tgl BETWEEN '$tgl1' and '$tgl2' and a.id_akun = '19'
        ) as b on b.id_akun = a.id_akun
        where a.id_akun = '19';");
    }
    public static function bunga_bank($tgl1, $tgl2)
    {
        return DB::selectOne("SELECT b.tgl, b.id_akun, b.no_nota, b.debit, sum(b.kredit) as kredit
        FROM jurnal as b 
        left join akun as c on c.id_akun = b.id_akun
        where b.kredit != 0 and b.id_akun in('8','101') and b.id_buku in ('7') 
        and b.tgl BETWEEN '$tgl1' and '$tgl2'
       ");
    }
    public static function detail_proyek($id_akun, $tgl1, $tgl2)
    {
        return  DB::select("SELECT a.id_post_center, b.nm_post, sum(a.debit) as debit
        FROM jurnal as a 
        left join tb_post_center as b on b.id_post_center = a.id_post_center
        where a.id_akun ='$id_akun' and a.id_akun != '8' and a.tgl BETWEEN '$tgl1' and '$tgl2' and a.debit != '0'
        group by a.id_post_center;
    ");
    }
    public static function ttl_ayam($tgl2)
    {
        return  DB::select("SELECT a.id_kandang,  b.nm_kandang, b.stok_awal, c.mati,c.jual, c.afkir
        FROM tb_pakan_perencanaan as a 
        left join kandang as b on b.id_kandang = a.id_kandang
        left join (
            SELECT c.id_kandang , sum(c.mati) as mati, sum(c.jual) as jual, sum(c.afkir) as afkir
            FROM populasi as c 
            where c.tgl BETWEEN (SELECT MIN(d.tgl) FROM populasi as d WHERE d.id_kandang = c.id_kandang) and '$tgl2'
            group by c.id_kandang
        ) as c on c.id_kandang = a.id_kandang
        where a.tgl = '$tgl2'
        group by a.id_kandang;
        ");
    }
}
