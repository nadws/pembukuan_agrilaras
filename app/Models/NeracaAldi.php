<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class NeracaAldi extends Model
{
    use HasFactory;

    public static function GetKas($tgl1, $tgl2, $id_klasifikasi)
    {
        $result = DB::selectOne("SELECT 
        SUM(COALESCE(b.debit, 0) + COALESCE(c.debit, 0)) as debit,
        SUM(COALESCE(b.kredit, 0) - COALESCE(c.kredit, 0)) as kredit
            FROM akun as a
            LEFT JOIN (
                SELECT b.id_akun, SUM(b.debit) as debit, SUM(b.kredit) as kredit
                FROM jurnal as b
                WHERE b.id_buku NOT IN (5, 13) AND b.tgl BETWEEN ? AND ? AND b.penutup = 'T'
                GROUP BY b.id_akun
            ) as b ON b.id_akun = a.id_akun
            LEFT JOIN (
                SELECT c.id_akun, SUM(c.debit) as debit, SUM(c.kredit) as kredit
                FROM jurnal_saldo as c
                WHERE c.tgl BETWEEN ? AND ?
                GROUP BY c.id_akun
            ) as c ON c.id_akun = a.id_akun
            WHERE a.id_klasifikasi = ?;
        ", [$tgl1, $tgl2, $tgl1, $tgl2, $id_klasifikasi]);

        return $result;
    }

    public static function GetAkun($tgl1, $tgl2, $id_klasifikasi)
    {
        $result = DB::select("SELECT 
        a.id_akun, a.nm_akun, 
        (COALESCE(b.kredit, 0) + COALESCE(c.kredit, 0)) as kredit,
        (COALESCE(b.debit,0) + COALESCE(c.debit,0)) as debit
            FROM akun as a
            LEFT JOIN (
                SELECT b.id_akun, SUM(b.debit) as debit, SUM(b.kredit) as kredit
                FROM jurnal as b
                WHERE b.id_buku NOT IN (5, 13) AND b.tgl BETWEEN ? AND ? AND b.penutup = 'T'
                GROUP BY b.id_akun
            ) as b ON b.id_akun = a.id_akun
            LEFT JOIN (
                SELECT c.id_akun, SUM(c.debit) as debit, SUM(c.kredit) as kredit
                FROM jurnal_saldo as c
                WHERE c.tgl BETWEEN ? AND ?
                GROUP BY c.id_akun
            ) as c ON c.id_akun = a.id_akun
            WHERE a.id_klasifikasi = ?;
        ", [$tgl1, $tgl2, $tgl1, $tgl2, $id_klasifikasi]);

        return $result;
    }
    public static function GetKas3($tgl1, $tgl2)
    {
        $result = DB::selectOne("SELECT a.id_akun, a.nm_akun, b.kredit, b.debit, c.debit as debit_saldo, c.kredit as kredit_saldo
            FROM akun as a
            LEFT JOIN (
                SELECT b.id_akun, SUM(b.debit) as debit, SUM(b.kredit) as kredit
                FROM jurnal as b
                WHERE b.id_buku NOT IN (5, 13) AND b.tgl BETWEEN ? AND ? AND b.penutup = 'T'
                GROUP BY b.id_akun
            ) as b ON b.id_akun = a.id_akun
            LEFT JOIN (
                SELECT c.id_akun, SUM(c.debit) as debit, SUM(c.kredit) as kredit
                FROM jurnal_saldo as c
                WHERE c.tgl BETWEEN ? AND ?
                GROUP BY c.id_akun
            ) as c ON c.id_akun = a.id_akun
            WHERE a.id_klasifikasi in(8,10) and a.id_akun = '95';
        ", [$tgl1, $tgl2, $tgl1, $tgl2]);

        return $result;
    }
    public static function GetPeralatan($tgl2, $id_akun)
    {
        $result = DB::selectOne("SELECT a.id_akun, sum(a.debit) as debit
        FROM jurnal as a 
        WHERE a.tgl BETWEEN '2023-01-01' and ? and a.id_akun = ?;
        ", [$tgl2, $id_akun]);

        return $result;
    }

    public static function Getakumulasi($tgl1, $tgl2, $id_akun)
    {
        $result = DB::selectOne("SELECT a.id_akun, a.nm_akun, b.kredit, b.debit, c.debit as debit_saldo, c.kredit as kredit_saldo
            FROM akun as a
            LEFT JOIN (
                SELECT b.id_akun, SUM(b.debit) as debit, SUM(b.kredit) as kredit
                FROM jurnal as b
                WHERE  b.tgl BETWEEN ? AND ? AND b.penutup = 'T'
                GROUP BY b.id_akun
            ) as b ON b.id_akun = a.id_akun
            LEFT JOIN (
                SELECT c.id_akun, SUM(c.debit) as debit, SUM(c.kredit) as kredit
                FROM jurnal_saldo as c
                WHERE c.tgl BETWEEN ? AND ?
                GROUP BY c.id_akun
            ) as c ON c.id_akun = a.id_akun
            WHERE a.id_akun = ?;
        ", [$tgl1, $tgl2, $tgl1, $tgl2, $id_akun]);

        return $result;
    }
    public static function laba_berjalan_pendapatan($tgl1, $tgl2)
    {
        $result = DB::selectOne("SELECT a.id_akun, a.nm_akun, sum(b.kredit - b.debit) as pendapatan
        FROM akun as a 
        left join (
        SELECT b.id_akun , sum(b.debit) as debit , sum(b.kredit) as kredit
            FROM jurnal as b 
            where b.tgl BETWEEN ? and ? and b.penutup = 'T'
            GROUP by b.id_akun
        ) as b on b.id_akun = a.id_akun
        where a.iktisar ='Y' and a.id_klasifikasi ='4';
        ", [$tgl1, $tgl2]);

        return $result;
    }
    public static function laba_berjalan_biaya($tgl1, $tgl2)
    {
        $result = DB::selectOne("SELECT a.id_akun, a.nm_akun, sum(b.debit-b.kredit) as biaya
        FROM akun as a 
        left join (
        SELECT b.id_akun , sum(b.debit) as debit , sum(b.kredit) as kredit
            FROM jurnal as b 
            where b.tgl BETWEEN ? and ? and b.id_buku not in('5') and b.penutup = 'T'
            GROUP by b.id_akun
        ) as b on b.id_akun = a.id_akun
        where a.iktisar ='Y' and a.id_klasifikasi in (3,5);
        ", [$tgl1, $tgl2]);

        return $result;
    }
}
