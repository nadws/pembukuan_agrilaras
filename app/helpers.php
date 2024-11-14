<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

if (!function_exists('tanggal')) {
    function tanggal($tgl)
    {
        $date = explode("-", $tgl);

        $bln  = $date[1];

        switch ($bln) {
            case '01':
                $bulan = "Januari";
                break;
            case '02':
                $bulan = "Februari";
                break;
            case '03':
                $bulan = "Maret";
                break;
            case '04':
                $bulan = "April";
                break;
            case '05':
                $bulan = "Mei";
                break;
            case '06':
                $bulan = "Juni";
                break;
            case '07':
                $bulan = "Juli";
                break;
            case '08':
                $bulan = "Agustus";
                break;
            case '09':
                $bulan = "September";
                break;
            case '10':
                $bulan = "Oktober";
                break;
            case '11':
                $bulan = "November";
                break;
            case '12':
                $bulan = "Desember";
                break;
        }
        $tanggal = $date[2];
        $tahun   = $date[0];

        $strTanggal = "$tanggal $bulan $tahun";
        return $strTanggal;
    }
}

if (!function_exists('kode')) {
    function kode($kode)
    {
        return str_pad($kode, 5, '0', STR_PAD_LEFT);
    }
}

if (!function_exists('buatNota')) {
    function buatNota($tbl, $kolom)
    {
        $max = DB::table($tbl)->latest($kolom)->first();
        return empty($max) ? 1000 : $max->$kolom + 1;
    }
}
if (!function_exists('sumBk')) {
    function sumBk($kategori, $data)
    {
        return array_sum(array_column($kategori, $data));
    }
}


class Nonaktif
{
    public static function edit($tbl, $kolom, $kolomValue, $data)
    {
        DB::table($tbl)->where($kolom, $kolomValue)->update([
            'nonaktif' => 'Y'
        ]);

        DB::table($tbl)->insert($data);
    }

    public static function delete($tbl, $kolom, $kolomValue)
    {
        DB::table($tbl)->where($kolom, $kolomValue)->update([
            'nonaktif' => 'Y'
        ]);
    }
}

class SettingHal
{

    public static function akses($halaman, $id_user)
    {
        return DB::selectOne("SELECT a.*, b.id_permission_page FROM permission_button
        AS
        a
        LEFT JOIN (
        SELECT b.id_user, b.id_permission_button, b.id_permission_page FROM permission_perpage AS b
        WHERE b.id_user ='$id_user' AND b.permission_id = '$halaman'
        ) AS b ON b.id_permission_button = a.id_permission_button WHERE b.id_user = '$id_user'");
    }

    public static function btnHal($whereId, $id_user)
    {
        return DB::table('permission_perpage as a')
            ->join('permission_button as b', 'b.id_permission_button', 'a.id_permission_button')
            ->where([['a.id_permission_button', $whereId], ['a.id_user', $id_user]])
            ->first();
    }

    public static function btnSetHal($halaman, $id_user, $jenis)
    {
        return DB::select("SELECT a.*, b.id_permission_page FROM permission_button AS
        a
        LEFT JOIN (
        SELECT b.id_permission_button, b.id_permission_page FROM permission_perpage AS b
        WHERE b.id_user ='$id_user'
        ) AS b ON b.id_permission_button = a.id_permission_button
        WHERE a.jenis = '$jenis' AND a.permission_id = '$halaman'");
    }
}
