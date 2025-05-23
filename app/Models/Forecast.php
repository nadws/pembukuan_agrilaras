<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Forecast extends Model
{
    use HasFactory;

    public static function detailEggProduction()
    {
        $tgl_bulan_kemarin = date('Y-m-t', strtotime('-1 month'));
        return  DB::select("SELECT a.nm_kandang , (a.stok_awal - b.pop_hilang) as populasi, a.chick_in
        FROM kandang as a 
        left join (
            SELECT b.id_kandang, sum(b.mati + b.jual + b.afkir) as pop_hilang 
            FROM populasi as b
            where b.tgl <= '$tgl_bulan_kemarin' 
            group by b.id_kandang
        ) as b on b.id_kandang = a.id_kandang
        where a.selesai = 'T' order by a.nm_kandang asc");
    }
}
