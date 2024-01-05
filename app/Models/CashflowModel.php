<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CashflowModel extends Model
{
    use HasFactory;
    public static function uangBiaya($tgl1, $tgl2, $id_kategori)
    {
        $result = DB::select("SELECT ak.id_akun,ak.nm_akun, a.debit , a.kredit
        FROM akun as ak
        
        left join (
        SELECT a.id_akun , sum(a.debit) as debit , sum(a.kredit) as kredit
            FROM jurnal as a 
            left join (
                SELECT j.no_nota, j.id_akun
                FROM jurnal as j
                LEFT JOIN akun as b ON b.id_akun = j.id_akun
                WHERE j.debit != '0'
                GROUP BY j.no_nota
            ) d ON a.no_nota = d.no_nota AND d.id_akun != a.id_akun
            WHERE  a.tgl between ? and ?  and a.id_buku in ('2','12','10')
             group by a.id_akun
        ) as a on a.id_akun = ak.id_akun
        left join akuncash_ibu as acb on acb.id_akun = ak.id_akun and acb.kategori = ?
        WHERE ak.id_akun in (SELECT t.id_akun FROM akuncash_ibu as t where t.kategori = ?)
        order by acb.urutan ASC
        ", [$tgl1, $tgl2, $id_kategori, $id_kategori]);

        return $result;
    }
    public static function uangBiayabalance($tgl2, $id_kategori)
    {
        $result = DB::select("SELECT ak.id_akun,ak.nm_akun, a.debit , a.kredit
        FROM akun as ak
        
        left join (
        SELECT a.id_akun , sum(a.debit) as debit , sum(a.kredit) as kredit
            FROM jurnal as a 
            left join (
                SELECT j.no_nota, j.id_akun
                FROM jurnal as j
                LEFT JOIN akun as b ON b.id_akun = j.id_akun
                WHERE j.debit != '0'
                GROUP BY j.no_nota
            ) d ON a.no_nota = d.no_nota AND d.id_akun != a.id_akun
            WHERE  a.tgl between '2022-12-01' and ? 
             group by a.id_akun
        ) as a on a.id_akun = ak.id_akun
        left join akuncash_ibu as acb on acb.id_akun = ak.id_akun and acb.kategori = ?
        WHERE ak.id_akun in (SELECT t.id_akun FROM akuncash_ibu as t where t.kategori = ?)
        order by acb.urutan ASC
        ", [$tgl2, $id_kategori, $id_kategori]);

        return $result;
    }

    public static function cashflow_pendapatan_setahun($tahun)
    {
        $result = DB::select("SELECT a.id_akun, a.nm_akun, b.debit, b.kredit, b.bulan, b.tahun
        FROM akun as a
        left join (
        SELECT b.id_akun, sum(b.debit) as debit , sum(b.kredit) as kredit,MONTH(b.tgl) as bulan,YEAR(b.tgl) as tahun
        FROM jurnal as b
        where Year(b.tgl) = ? and b.id_buku = '6'
        group by b.id_akun , MONTH(b.tgl), YEAR(b.tgl)
        ) as b on b.id_akun = a.id_akun
        where a.id_akun in (SELECT t.id_akun FROM akuncash_ibu as t where t.kategori = '3');
        ", [$tahun]);
        return $result;
    }
    public static function cashflow_uangmasuk_setahun($id_akun1, $id_akun2, $tahun, $id_buku)
    {
        $id_akun1_values = implode(",", $id_akun1);
        $id_akun2_values = implode(",", $id_akun2);
        $id_buku_values = implode(",", $id_buku);

        $result = DB::select("SELECT a.id_jurnal, a.id_akun, a.tgl, c.nm_akun, sum(a.debit) as debit, b.no_nota, MONTH(a.tgl) as bulan , YEAR(a.tgl) as tahun
        FROM akun as c 
        left join jurnal as a on a.id_akun = c.id_akun
        LEFT JOIN (
        SELECT b.tgl, b.id_akun, b.no_nota
            FROM jurnal as b 
            left join akun as c on c.id_akun = b.id_akun
            where b.kredit != 0 and b.id_akun in( {$id_akun2_values} )
            GROUP by b.no_nota
        ) as b on b.no_nota = a.no_nota
        where Year(a.tgl) = ?  and a.id_akun not in({$id_akun1_values}) and a.debit != 0 and a.id_buku in({$id_buku_values}) and b.no_nota is not null
        group by a.id_akun, MONTH(a.tgl), YEAR(a.tgl);
        ", [$tahun]);
        return $result;
    }
    public static function cashflow_bayar_uangmasuk_setahun($id_akun1, $id_akun2, $tahun, $id_buku)
    {
        $id_akun1_values = implode(",", $id_akun1);
        $id_akun2_values = implode(",", $id_akun2);
        $id_buku_values = implode(",", $id_buku);

        $result = DB::select("SELECT a.id_jurnal, a.id_akun, a.tgl, c.nm_akun, sum(a.debit) as debit, sum(a.kredit) as kredit, b.no_nota, MONTH(a.tgl) as bulan , YEAR(a.tgl) as tahun
        FROM jurnal as a 
        left join akun as c on c.id_akun = a.id_akun
        LEFT JOIN (
        SELECT b.tgl, b.id_akun, b.no_nota
            FROM jurnal as b 
            left join akun as c on c.id_akun = b.id_akun
            where b.debit != 0 and b.id_akun in( {$id_akun2_values} )
            GROUP by b.no_nota
        ) as b on b.no_nota = a.no_nota
        where Year(a.tgl) = ?  and a.id_akun not in({$id_akun1_values}) and a.kredit != 0 and a.id_buku in({$id_buku_values}) and b.no_nota is not null
        group by a.id_akun, MONTH(a.tgl), YEAR(a.tgl);
        ", [$tahun]);
        return $result;
    }
    public static function cashflow_hutang_setahun($tahun)
    {
        $result = DB::select("SELECT a.id_akun, a.nm_akun, b.debit, b.kredit, b.bulan, b.tahun
        FROM akun as a
        left join (
        SELECT b.id_akun, sum(b.debit) as debit , sum(b.kredit) as kredit,MONTH(b.tgl) as bulan,YEAR(b.tgl) as tahun
        FROM jurnal as b
        where Year(b.tgl) = ? and b.id_buku = '7'
        group by b.id_akun , MONTH(b.tgl), YEAR(b.tgl)
        ) as b on b.id_akun = a.id_akun
        where a.id_akun = 19;
        ", [$tahun]);
        return $result;
    }
    public static function cashflow_uang_cost($tahun, $id_kategori)
    {
        $result = DB::select("SELECT a.id_akun, a.no_nota, c.nm_akun, sum(a.debit) as debit ,MONTH(a.tgl) as bulan, YEAR(a.tgl) as tahun
        FROM jurnal as a 
        left join ( SELECT b.no_nota , b.id_akun, c.nm_akun FROM jurnal as b 
        left join akun as c on c.id_akun = b.id_akun 
        where b.id_akun in (SELECT t.id_akun FROM akuncash_ibu as t where t.kategori in (?)) and Year(b.tgl) = ? and b.kredit != 0 and b.id_buku in(2,10,12) 
        GROUP by b.no_nota ) as b on b.no_nota = a.no_nota 
        left join akun as c on c.id_akun = a.id_akun 
        where a.id_buku in(2,10,12) and a.debit != 0 and Year(a.tgl) = ? and b.id_akun is not null 
        group by a.id_akun,MONTH(a.tgl), YEAR(a.tgl);", [$id_kategori, $tahun, $tahun]);
        return $result;
    }
    public static function cashflow_uang_masuk($tahun, $id_akun1, $id_buku)
    {
        $id_akun1_values = implode(",", $id_akun1);
        $id_buku = implode(",", $id_buku);

        $result = DB::select("SELECT a.id_akun, b.nm_akun, sum(a.debit) as debit, month(a.tgl) as bulan, YEAR(a.tgl) as tahun
        FROM jurnal as a 
        left join akun as b on b.id_akun = a.id_akun
        where a.id_akun in ({$id_akun1_values}) and a.id_buku in ({$id_buku}) and YEAR(a.tgl) = ?
        GROUP by a.id_akun, Month(a.tgl), YEAR(a.tgl);
        ", [$tahun]);
        return $result;
    }
}
