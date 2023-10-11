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
        $result = DB::select("SELECT ak.id_akun,ak.nm_akun, a.debit , a.kredit, ak.kategori
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
}
