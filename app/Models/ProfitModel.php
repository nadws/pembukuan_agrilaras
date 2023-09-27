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
                WHERE b.id_buku NOT IN (5, 13) AND b.debit != 0 AND b.tgl BETWEEN ? AND ? AND b.penutup = 'T'
                GROUP BY b.id_akun
            ) as b ON b.id_akun = a.id_akun
            LEFT JOIN (
                SELECT c.id_akun, SUM(c.debit) as debit, SUM(c.kredit) as kredit
                FROM jurnal_saldo as c
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
                WHERE b.id_buku NOT IN (5, 13) AND b.debit != 0 AND b.tgl BETWEEN ? AND ? AND b.penutup = 'T'
                GROUP BY b.id_akun
            ) as b ON b.id_akun = a.id_akun
            LEFT JOIN (
                SELECT c.id_akun, SUM(c.debit) as debit, SUM(c.kredit) as kredit
                FROM jurnal_saldo as c
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
                WHERE b.id_buku NOT IN (5, 13) AND b.debit != 0 AND b.tgl BETWEEN ? AND ? AND b.penutup = 'T'
                GROUP BY b.id_akun
            ) as b ON b.id_akun = a.id_akun
            LEFT JOIN (
                SELECT c.id_akun, SUM(c.debit) as debit, SUM(c.kredit) as kredit
                FROM jurnal_saldo as c
                WHERE c.tgl BETWEEN ? AND ?
                GROUP BY c.id_akun
            ) as c ON c.id_akun = a.id_akun
            WHERE a.id_klasifikasi = '5';
        ", [$tgl1, $tgl2, $tgl1, $tgl2]);

        return $result;
    }
}
