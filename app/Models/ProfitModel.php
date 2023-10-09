<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ProfitModel extends Model
{
    use HasFactory;
    public static function getData($tgl1, $tgl2)
    {
        $result = DB::select("SELECT a.id_akun, a.nm_akun, b.kredit, b.debit, c.debit as debit_saldo, c.kredit as kredit_saldo
            FROM akun as a
            LEFT JOIN (
                SELECT b.id_akun, SUM(b.debit) as debit, SUM(b.kredit) as kredit
                FROM jurnal as b
                WHERE b.id_buku NOT IN (5, 13) AND  b.tgl BETWEEN ? AND ? AND b.penutup = 'T'
                GROUP BY b.id_akun
            ) as b ON b.id_akun = a.id_akun
            LEFT JOIN (
                SELECT c.id_akun, SUM(c.debit) as debit, SUM(c.kredit) as kredit
                FROM jurnal_saldo_sebelum_penutup as c
                WHERE c.tgl BETWEEN ? AND ?
                GROUP BY c.id_akun
            ) as c ON c.id_akun = a.id_akun
            WHERE a.id_klasifikasi = '3';
        ", [$tgl1, $tgl2, $tgl1, $tgl2]);

        return $result;
    }
    public static function getData2($tgl1, $tgl2)
    {
        $result = DB::select("SELECT a.id_akun, a.nm_akun, b.kredit, b.debit, c.debit as debit_saldo, c.kredit as kredit_saldo
            FROM akun as a
            LEFT JOIN (
                SELECT b.id_akun, SUM(b.debit) as debit, SUM(b.kredit) as kredit
                FROM jurnal as b
                WHERE b.id_buku NOT IN (5, 13) AND  b.tgl BETWEEN ? AND ? AND b.penutup = 'T'
                GROUP BY b.id_akun
            ) as b ON b.id_akun = a.id_akun
            LEFT JOIN (
                SELECT c.id_akun, SUM(c.debit) as debit, SUM(c.kredit) as kredit
                FROM jurnal_saldo_sebelum_penutup as c
                WHERE c.tgl BETWEEN ? AND ?
                GROUP BY c.id_akun
            ) as c ON c.id_akun = a.id_akun
            WHERE a.id_klasifikasi in('6','11','12');
        ", [$tgl1, $tgl2, $tgl1, $tgl2]);

        return $result;
    }
    public static function getData3($tgl1, $tgl2)
    {
        $result = DB::select("SELECT a.id_akun, a.nm_akun, b.kredit, b.debit, c.debit as debit_saldo, c.kredit as kredit_saldo
            FROM akun as a
            LEFT JOIN (
                SELECT b.id_akun, SUM(b.debit) as debit, SUM(b.kredit) as kredit
                FROM jurnal as b
                WHERE b.id_buku NOT IN (5, 13)  AND b.tgl BETWEEN ? AND ? AND b.penutup = 'T'
                GROUP BY b.id_akun
            ) as b ON b.id_akun = a.id_akun
            LEFT JOIN (
                SELECT c.id_akun, SUM(c.debit) as debit, SUM(c.kredit) as kredit
                FROM jurnal_saldo_sebelum_penutup as c
                WHERE c.tgl BETWEEN ? AND ?
                GROUP BY c.id_akun
            ) as c ON c.id_akun = a.id_akun
            WHERE a.id_klasifikasi = '5' and a.id_akun not in(51,58);
        ", [$tgl1, $tgl2, $tgl1, $tgl2]);

        return $result;
    }
    public static function getData4($tgl1, $tgl2)
    {
        $result = DB::select("SELECT a.id_akun, a.nm_akun, b.kredit, b.debit, c.debit as debit_saldo, c.kredit as kredit_saldo
            FROM akun as a
            LEFT JOIN (
                SELECT b.id_akun, SUM(b.debit) as debit, SUM(b.kredit) as kredit
                FROM jurnal as b
                WHERE b.id_buku NOT IN (5, 13)  AND b.tgl BETWEEN ? AND ? AND b.penutup = 'T'
                GROUP BY b.id_akun
            ) as b ON b.id_akun = a.id_akun
            LEFT JOIN (
                SELECT c.id_akun, SUM(c.debit) as debit, SUM(c.kredit) as kredit
                FROM jurnal_saldo_sebelum_penutup as c
                WHERE c.tgl BETWEEN ? AND ?
                GROUP BY c.id_akun
            ) as c ON c.id_akun = a.id_akun
            WHERE a.id_akun in(51,58);
        ", [$tgl1, $tgl2, $tgl1, $tgl2]);

        return $result;
    }
    public static function estimasi($tgl1, $kg_per_butir, $rp_kg, $hari)
    {
        $result = DB::selectOne("SELECT a.nm_kandang, FLOOR(DATEDIFF('2023-09-01', a.chick_in) / 7) AS mgg, b.persen, (a.stok_awal - c.mati - c.jual) as populasi, (((a.stok_awal - c.mati - c.jual) * b.persen) / 100) as pcs, ((((a.stok_awal - c.mati - c.jual) * b.persen) / 100) * 0.064) as kg , sum((((((a.stok_awal - c.mati - c.jual) * b.persen) / 100) * ? ) * ?) * ?) as estimasi

        FROM kandang as a
        
        left join persen_budget_ayam as b on  FLOOR(DATEDIFF(?, a.chick_in) / 7) BETWEEN b.umur_dari and b.umur_sampai
        
        left join (
        SELECT c.id_kandang, sum(c.mati) as mati , sum(c.jual) as jual
            FROM populasi as c 
            where c.tgl BETWEEN '2020-01-01' and ?
            group by c.id_kandang
        ) as c on c.id_kandang = a.id_kandang
        
        where a.selesai = 'T';
        ", [$kg_per_butir,  $rp_kg, $hari, $tgl1,  $tgl1]);

        return $result;
    }
    public static function aktiva($tgl2)
    {
        $result = DB::selectOne("SELECT a.h_perolehan, sum(a.biaya_depresiasi) as biaya, c.beban 
        FROM aktiva as a 
        left join kelompok_aktiva as b on b.id_kelompok = a.id_kelompok 
        left join( 
            SELECT sum(c.b_penyusutan) as beban , c.id_aktiva 
                  FROM depresiasi_aktiva as 
                  c group by c.id_aktiva 
        ) as c on c.id_aktiva = a.id_aktiva
        where a.tgl between '2017-01-01' and ? and (a.h_perolehan - c.beban) > 0 
        order by a.tgl ASC;
        ", [$tgl2]);

        return $result;
    }
    public static function peralatan($tgl2)
    {
        $result = DB::select("SELECT a.*, c.beban FROM peralatan as a 
        left join kelompok_peralatan as b on b.id_kelompok = a.id_kelompok
        left join(
        SELECT sum(c.b_penyusutan) as beban , c.id_aktiva
            FROM depresiasi_peralatan as c
            group by c.id_aktiva
        ) as c on c.id_aktiva = a.id_aktiva
        where a.tgl between '2017-01-01' and ? 
        order by a.tgl ASC
        ", [$tgl2]);

        return $result;
    }
}
