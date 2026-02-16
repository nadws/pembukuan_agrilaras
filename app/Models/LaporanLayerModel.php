<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class LaporanLayerModel extends Model
{
    use HasFactory;

    public static function getLaporanLayer($tgl, $tgl_sebelumnya, $tgl_kemarin, $tgl_minggu_sebelumnya, $tgl_minggu_kemaren)
    {
        return DB::select("SELECT  a.nm_kandang ,a.chick_in,a.chick_out,CEIL(DATEDIFF('$tgl', a.chick_in) / 7) AS mgg ,aa.ttl_gjl,CEIL(DATEDIFF(a.chick_out, a.chick_in) / 7) AS mgg_afkir, c.mati, (c.jual + c.afkir) as jual, d.kg_pakan, w.mati_week , w.jual_week,a.stok_awal,b.pop_kurang,h.pcs,h.kg,e.berat_telur as t_peforma,i.kg_past,i.pcs_past,a.id_kandang,e.telur as p_hd,k.pcs_telur_week,p.jlh_hari,r.pcs_telur_week_past,q.jlh_hari_past,o.pop_kurang_past,p.kg_p_week,k.kg_telur_week,m.rp_vitamin,t.ttl_rp_vaksin,z.rp_vitamin_week,ac.kg_telur_past_week,ac.pcs_telur_past_week,ae.kum_ttl_rp_past_vaksin,ad.rp_vitamin_past_week,l.kg_pakan_kuml,j.kuml_kg,j.kuml_pcs,al.rata,n.kuml_rp_vitamin,s.kum_ttl_rp_vaksin,a.rupiah,af.ttl_rupiah_hrga,af.pcs_hrga,ag.kuml_kg_kemarin,ag.kuml_pcs_kemarin,am.rata as rata_kemarin,ai.kg_pakan_kuml_kemarin,aj.kuml_rp_vitamin_kemarin,ak.kum_ttl_rp_vaksin_kemarin,u.pcs as pcs_satu_minggu, u.kg as kg_satu_minggu, v.pcs as pcs_minggu_sebelumnya , v.kg as kg_minggu_sebelumnya,ab.kg_p_past_week
        FROM kandang as a 
        -- Populasi --
        left join(SELECT b.id_kandang, sum(b.mati+b.jual+b.afkir) as pop_kurang 
        FROM populasi as b 
        where b.tgl between '2020-01-01' and '$tgl'
        group by b.id_kandang ) as b on b.id_kandang = a.id_kandang

        left join(SELECT b.id_kandang, sum(b.mati+b.jual+b.afkir) as pop_kurang_past
        FROM populasi as b 
        where b.tgl between '2020-01-01' and '$tgl_sebelumnya'
        group by b.id_kandang ) as o on o.id_kandang = a.id_kandang
        
        left join populasi as c on c.id_kandang = a.id_kandang and c.tgl = '$tgl'
        -- Populasi --

        -- Pakan --
        left join (
            SELECT d.id_kandang, sum(d.pcs_kredit) as kg_pakan
            FROM stok_produk_perencanaan as d 
            left join tb_produk_perencanaan as e on e.id_produk = d.id_pakan
            where d.tgl = '$tgl' and e.kategori = 'pakan'
            group by d.id_kandang
        ) as d on d.id_kandang = a.id_kandang

        left join peformance as e on e.id_strain = a.id_strain and e.umur = CEIL(DATEDIFF('$tgl', a.chick_in) / 7)

        

        
        -- Pakan --

        -- Data telur --
        left join (SELECT h.id_kandang , sum(h.pcs) as pcs, sum(h.kg) as kg FROM stok_telur as h  where h.tgl = '$tgl' group by h.id_kandang) as h on h.id_kandang = a.id_kandang

        left join (SELECT h.id_kandang , sum(h.pcs) as pcs_past, sum(h.kg) as kg_past FROM stok_telur as h  where h.tgl = '$tgl_kemarin' group by h.id_kandang) as i on i.id_kandang = a.id_kandang

        left join (SELECT h.id_kandang , count(h.id_stok_telur) as count_bagi, sum(h.pcs) as kuml_pcs, sum(h.kg) as kuml_kg FROM stok_telur as h  where h.tgl between '2020-01-01' and '$tgl' and h.pcs != 0 group by h.id_kandang) as j on j.id_kandang = a.id_kandang

        left join (
            SELECT a.id_kandang, sum(a.pcs) as pcs_telur_week, sum(a.kg) as kg_telur_week, CEIL(DATEDIFF(a.tgl, b.chick_in) / 7) AS mgg_hd_week
            FROM stok_telur as a 
            left JOIN kandang as b on b.id_kandang = a.id_kandang
            where a.tgl between '2020-01-01' and '$tgl'
            group by CEIL(DATEDIFF(a.tgl, b.chick_in) / 7), a.id_kandang
        ) as k on k.id_kandang = a.id_kandang and k.mgg_hd_week = CEIL(DATEDIFF('$tgl', a.chick_in) / 7)

        left join (
            SELECT d.id_kandang, sum(d.pcs_kredit) as kg_pakan_kuml
            FROM stok_produk_perencanaan as d 
            left join tb_produk_perencanaan as b on b.id_produk = d.id_pakan
            where d.tgl between '2020-01-01' and '$tgl' and b.kategori = 'pakan'
            group by d.id_kandang
        ) as l on l.id_kandang = a.id_kandang

        left join (
            SELECT d.id_kandang, sum(d.total_rp) as rp_vitamin
            FROM stok_produk_perencanaan as d 
            left join tb_produk_perencanaan as e on e.id_produk = d.id_pakan
            where d.tgl = '$tgl' and e.kategori in('obat_pakan','obat_air') and d.pcs_kredit != '0'
            group by d.id_kandang
        ) as m on m.id_kandang = a.id_kandang

        left join (
            SELECT d.id_kandang, sum(d.total_rp) as rp_vitamin_week
            FROM stok_produk_perencanaan as d 
            left join tb_produk_perencanaan as e on e.id_produk = d.id_pakan
            where d.tgl between '$tgl_sebelumnya' and '$tgl' and e.kategori in('obat_pakan','obat_air') and d.pcs_kredit != '0'
            group by d.id_kandang
        ) as z on z.id_kandang = a.id_kandang

        left join (
            SELECT d.id_kandang, sum(d.total_rp) as kuml_rp_vitamin
            FROM stok_produk_perencanaan as d 
            left join tb_produk_perencanaan as e on e.id_produk = d.id_pakan
            where d.tgl between '2020-01-01' and '$tgl' and e.kategori in('obat_pakan','obat_air') and d.pcs_kredit != '0'
            group by d.id_kandang
        ) as n on n.id_kandang = a.id_kandang


        left join (
            SELECT o.id_kandang,  sum(o.kg_p_week) as kg_p_week, count(o.id_kandang) as jlh_hari, CEIL(DATEDIFF(o.tgl, p.chick_in) / 7) AS mgg_hd
            FROM 
            ( SELECT o.id_kandang, o.tgl,sum(o.pcs_kredit) as kg_p_week
            FROM stok_produk_perencanaan as o
            left join tb_produk_perencanaan as q on q.id_produk = o.id_pakan
            where q.kategori = 'pakan' and o.tgl between '2020-01-01' and '$tgl'
            group by o.tgl , o.id_kandang
            ) as o
            left join kandang as p on p.id_kandang = o.id_kandang
            group by CEIL(DATEDIFF(o.tgl, p.chick_in) / 7), p.id_kandang
        ) as p on p.id_kandang = a.id_kandang and p.mgg_hd = CEIL(DATEDIFF('$tgl', a.chick_in) / 7)

        left join (
            SELECT o.id_kandang, sum(o.kg_p_week) as kg_pp_week, count(o.id_kandang) as jlh_hari_past, CEIL(DATEDIFF(o.tgl, p.chick_in) / 7) AS mgg_hd_past
            FROM 
            ( SELECT o.id_kandang, o.tgl,sum(o.pcs_kredit) as kg_p_week
            FROM stok_produk_perencanaan as o
            left join tb_produk_perencanaan as q on q.id_produk = o.id_pakan
            where q.kategori = 'pakan'
            group by o.tgl , o.id_kandang
            ) as o
            left join kandang as p on p.id_kandang = o.id_kandang
            group by CEIL(DATEDIFF(o.tgl, p.chick_in) / 7), p.id_kandang
        ) as q on q.id_kandang = a.id_kandang and q.mgg_hd_past = CEIL(DATEDIFF('$tgl', a.chick_in) / 7)-1

        left join (
            SELECT a.id_kandang, sum(a.pcs) as pcs_telur_week_past, sum(a.kg) as kg_telur_week_past, CEIL(DATEDIFF(a.tgl, b.chick_in) / 7) AS mgg_hd_week_past
            FROM stok_telur as a 
            left JOIN kandang as b on b.id_kandang = a.id_kandang
            group by CEIL(DATEDIFF(a.tgl, b.chick_in) / 7), a.id_kandang
        ) as r on r.id_kandang = a.id_kandang and r.mgg_hd_week_past = CEIL(DATEDIFF('$tgl', a.chick_in) / 7)-1
        
        left join (
            SELECT s.id_kandang , sum(s.ttl_rp) as kum_ttl_rp_vaksin, s.tgl
            FROM tb_vaksin_perencanaan as s
            group by s.id_kandang
        ) as s on s.id_kandang = a.id_kandang 

        left join (
            SELECT s.id_kandang , sum(s.ttl_rp) as ttl_rp_vaksin,CEIL(DATEDIFF(s.tgl, b.chick_in) / 7) AS mgg_vaksin
            FROM tb_vaksin_perencanaan as s
            left JOIN kandang as b on b.id_kandang = s.id_kandang
            group by s.id_kandang,CEIL(DATEDIFF(s.tgl, b.chick_in) / 7)
        ) as t on t.id_kandang = a.id_kandang and t.mgg_vaksin = CEIL(DATEDIFF('$tgl', a.chick_in) / 7)

        left join (
            SELECT h.id_kandang , sum(h.pcs) as pcs, sum(h.kg) as kg 
            FROM stok_telur as h  where h.tgl between '$tgl_sebelumnya' and '$tgl' 
        group by h.id_kandang) as u on u.id_kandang = a.id_kandang

        left join (
            SELECT h.id_kandang , sum(h.pcs) as pcs, sum(h.kg) as kg 
            FROM stok_telur as h  where h.tgl between '$tgl_minggu_sebelumnya' and '$tgl_minggu_kemaren' 
        group by h.id_kandang) as v on v.id_kandang = a.id_kandang


        left join (
            SELECT w.id_kandang , sum(w.mati) as mati_week , sum(w.jual) as jual_week
                FROM populasi as w 
                where w.tgl between '$tgl_sebelumnya' and '$tgl'
            group by w.id_kandang
        ) as w on w.id_kandang = a.id_kandang

        

        

        left join (
            SELECT a.id_kandang, a.nm_kandang, count(b.total) as ttl_gjl
            FROM kandang as a 
            left join (
            SELECT a.id_kandang, count(a.id_kandang) as total
            FROM stok_produk_perencanaan as a 
            left join tb_produk_perencanaan as b on a.id_pakan = b.id_produk
            where b.kategori = 'pakan' and a.pcs_kredit != 0 and a.tgl between '2020-01-01' and '$tgl'
            group by a.tgl,  a.id_kandang
            ) as b on b.id_kandang = a.id_kandang
            GROUP by a.id_kandang
        ) as aa on aa.id_kandang = a.id_kandang

        left join (
            SELECT o.id_kandang,  sum(o.kg_p_week) as kg_p_past_week, count(o.id_kandang) as jlh_hari, CEIL(DATEDIFF(o.tgl, p.chick_in) / 7) AS mgg_hd
            FROM 
            ( SELECT o.id_kandang, o.tgl,sum(o.pcs_kredit) as kg_p_week
            FROM stok_produk_perencanaan as o
            left join tb_produk_perencanaan as q on q.id_produk = o.id_pakan
            where q.kategori = 'pakan' and o.tgl between '2020-01-01' and '$tgl'
            group by o.tgl , o.id_kandang
            ) as o
            left join kandang as p on p.id_kandang = o.id_kandang
            group by CEIL(DATEDIFF(o.tgl, p.chick_in) / 7), p.id_kandang
        ) as ab on ab.id_kandang = a.id_kandang and ab.mgg_hd = CEIL(DATEDIFF('$tgl_sebelumnya', a.chick_in) / 7)

        left join (
            SELECT a.id_kandang, sum(a.pcs) as pcs_telur_past_week, sum(a.kg) as kg_telur_past_week, CEIL(DATEDIFF(a.tgl, b.chick_in) / 7) AS mgg_hd_week
            FROM stok_telur as a 
            left JOIN kandang as b on b.id_kandang = a.id_kandang
            where a.tgl between '2020-01-01' and '$tgl'
            group by CEIL(DATEDIFF(a.tgl, b.chick_in) / 7), a.id_kandang
        ) as ac on ac.id_kandang = a.id_kandang and ac.mgg_hd_week = CEIL(DATEDIFF('$tgl_sebelumnya', a.chick_in) / 7)

        left join (
            SELECT o.id_kandang,  sum(o.ttl_rp) as rp_vitamin_past_week, count(o.id_kandang) as jlh_hari, CEIL(DATEDIFF(o.tgl, p.chick_in) / 7) AS mgg_hd
            FROM 
            ( SELECT o.id_kandang, o.tgl,sum(o.total_rp) as ttl_rp
            FROM stok_produk_perencanaan as o
            left join tb_produk_perencanaan as q on q.id_produk = o.id_pakan
            where q.kategori in('obat_pakan','obat_air') and o.tgl between '2020-01-01' and '2024-11-11'
            group by o.tgl , o.id_kandang
            ) as o
            left join kandang as p on p.id_kandang = o.id_kandang
            group by CEIL(DATEDIFF(o.tgl, p.chick_in) / 7), p.id_kandang
        ) as ad on d.id_kandang = a.id_kandang and ad.mgg_hd = CEIL(DATEDIFF('$tgl_sebelumnya', a.chick_in) / 7)

        left join (
            SELECT s.id_kandang , sum(s.ttl_rp) as kum_ttl_rp_past_vaksin, CEIL(DATEDIFF(s.tgl, p.chick_in) / 7) AS mgg_hd
            FROM tb_vaksin_perencanaan as s
            left join kandang as p on p.id_kandang = s.id_kandang
           
            group by CEIL(DATEDIFF(s.tgl, p.chick_in) / 7), s.id_kandang
        ) as ae on ae.id_kandang = a.id_kandang and ae.mgg_hd = CEIL(DATEDIFF('$tgl_sebelumnya', a.chick_in) / 7)

        left join (
            SELECT a.id_kandang, b.nm_produk, a.tgl, sum(a.pcs_kredit / 1000) as pcs_hrga , sum(a.total_rp) as ttl_rupiah_hrga, a.admin
            FROM stok_produk_perencanaan as a 
            left join tb_produk_perencanaan as b on b.id_produk = a.id_pakan
            where a.h_opname = 'T' and a.tgl BETWEEN '2020-01-01' and '$tgl' and b.kategori = 'pakan' 
            group by a.id_kandang
        ) as af on af.id_kandang = a.id_kandang

        left join (SELECT h.id_kandang , count(h.id_stok_telur) as count_bagi, sum(h.pcs) as kuml_pcs_kemarin, sum(h.kg) as kuml_kg_kemarin FROM stok_telur as h  where h.tgl between '2020-01-01' and '$tgl_kemarin' and h.pcs != 0 group by h.id_kandang) as ag on ag.id_kandang = a.id_kandang

        

        left join (
            SELECT d.id_kandang, sum(d.pcs_kredit) as kg_pakan_kuml_kemarin
            FROM stok_produk_perencanaan as d 
            left join tb_produk_perencanaan as b on b.id_produk = d.id_pakan
            where d.tgl between '2020-01-01' and '$tgl_kemarin' and b.kategori = 'pakan'
            group by d.id_kandang
        ) as ai on ai.id_kandang = a.id_kandang

        left join (
            SELECT d.id_kandang, sum(d.total_rp) as kuml_rp_vitamin_kemarin
            FROM stok_produk_perencanaan as d 
            left join tb_produk_perencanaan as e on e.id_produk = d.id_pakan
            where d.tgl between '2020-01-01' and '$tgl_kemarin' and e.kategori in('obat_pakan','obat_air') and d.pcs_kredit != '0'
            group by d.id_kandang
        ) as aj on aj.id_kandang = a.id_kandang

        left join (
            SELECT s.id_kandang , sum(s.ttl_rp) as kum_ttl_rp_vaksin_kemarin, s.tgl
            FROM tb_vaksin_perencanaan as s
            where s.tgl between '2020-01-01' and '$tgl_kemarin'
            group by s.id_kandang
        ) as ak on ak.id_kandang = a.id_kandang 

        left join (
            SELECT a.id_kandang, b.nm_kandang,  sum(a.kg) as kg, sum(a.ttl_rp) as ttl_rp, (sum(a.ttl_rp) / sum(a.kg)) as rata
            FROM (
                SELECT a.id_kandang, a.tgl, sum(a.pcs) as pcs, (sum(a.kg) - (sum(a.pcs) / 180)) as kg , b.rata, 
                ((sum(a.kg) - (sum(a.pcs) / 180)) * b.rata) as ttl_rp
                FROM stok_telur as a
                left join (
                    SELECT a.tgl, (sum(a.ttl_rp) / sum(a.kg)) as rata
                    FROM ( 
                        SELECT a.tgl, sum(a.kg_jual) as kg , sum(a.total_rp) as ttl_rp, 'kg' as tipe 
                        FROM invoice_telur as a where a.lokasi != 'opname' and a.tipe = 'kg' group by a.tgl 
                        UNION ALL 
                        SELECT a.tgl, sum(a.pcs * 0.06) as kg , sum(a.total_rp) as ttl_rp, 'pcs' as tipe 
                        FROM invoice_telur as a 
                        where a.lokasi != 'opname' and a.tipe = 'pcs' group by a.tgl 
                    ) as a group by a.tgl
                
            ) as b on b.tgl = a.tgl
                where a.id_kandang != 0 and a.tgl between '2020-01-01' and '$tgl'
            group by a.tgl, a.id_kandang
            ) as a
            left join kandang as b on b.id_kandang = a.id_kandang
            group by a.id_kandang
        ) as al on al.id_kandang = a.id_kandang
        left join (
            SELECT a.id_kandang, b.nm_kandang,  sum(a.kg) as kg, sum(a.ttl_rp) as ttl_rp, (sum(a.ttl_rp) / sum(a.kg)) as rata
            FROM (
                SELECT a.id_kandang, a.tgl, sum(a.pcs) as pcs, (sum(a.kg) - (sum(a.pcs) / 180)) as kg , b.rata, 
                ((sum(a.kg) - (sum(a.pcs) / 180)) * b.rata) as ttl_rp
                FROM stok_telur as a
                left join (
                    SELECT a.tgl, (sum(a.ttl_rp) / sum(a.kg)) as rata
                    FROM ( 
                        SELECT a.tgl, sum(a.kg_jual) as kg , sum(a.total_rp) as ttl_rp, 'kg' as tipe 
                        FROM invoice_telur as a where a.lokasi != 'opname' and a.tipe = 'kg' group by a.tgl 
                        UNION ALL 
                        SELECT a.tgl, sum(a.pcs * 0.06) as kg , sum(a.total_rp) as ttl_rp, 'pcs' as tipe 
                        FROM invoice_telur as a 
                        where a.lokasi != 'opname' and a.tipe = 'pcs' group by a.tgl 
                    ) as a group by a.tgl
                
            ) as b on b.tgl = a.tgl
                where a.id_kandang != 0 and a.tgl between '2020-01-01' and '$tgl_kemarin'
            group by a.tgl, a.id_kandang
            ) as a
            left join kandang as b on b.id_kandang = a.id_kandang
            group by a.id_kandang
        ) as am on am.id_kandang = a.id_kandang

        




        where a.selesai = 'T'
        group by a.id_kandang
        order by a.nm_kandang ASC
        ");
    }

    public static function rataRataTelur($idkandang)
    {
        $tgl_periode = DB::selectOne("SELECT min(a.tgl) as tgl_awal , max(a.tgl) as tgl_akhir FROM tb_pakan_perencanaan as a WHERE a.id_kandang ='$idkandang';");

        $hasil = DB::selectOne("SELECT sum(a.total_rp) as ttl_rp, sum(a.kg_jual) as kg_jual FROM invoice_telur as a where a.tgl BETWEEN '$tgl_periode->tgl_awal' and '$tgl_periode->tgl_akhir' and a.tipe ='kg';");

        return $hasil;
    }
    public static function rataRataAyam($idkandang)
    {
        $tgl_periode = DB::selectOne("SELECT min(a.tgl) as tgl_awal , max(a.tgl) as tgl_akhir FROM tb_pakan_perencanaan as a WHERE a.id_kandang ='$idkandang';");

        $hasil = DB::selectOne("SELECT sum(`qty`) jumlah, sum(`qty`*`h_satuan`) total_harga FROM `invoice_ayam` WHERE `tgl`BETWEEN '$tgl_periode->tgl_awal' and '$tgl_periode->tgl_akhir';");

        return $hasil;
    }
    public static function biayaOperasional($idkandang)
    {
        $tgl_periode = DB::selectOne("SELECT 
            MIN(a.tgl) AS tgl_awal,
            MAX(a.tgl) AS tgl_akhir
        FROM tb_pakan_perencanaan a
        WHERE a.id_kandang = '$idkandang'
    ");

        $tgl_akhir = date('Y-m-t', strtotime($tgl_periode->tgl_akhir));



        $hasil = DB::selectOne("SELECT SUM(a.debit) AS debit
        FROM jurnal_accurate a
        LEFT JOIN akun_accurate b ON b.kode = a.kode
        WHERE a.tgl  BETWEEN '2025-02-01' and '$tgl_akhir' AND a.buku = '2'
    
    ");

        return $hasil;
    }


    public static function populasi_periode($idkandang)
    {
        $tgl_periode = DB::selectOne("SELECT min(a.tgl) as tgl_awal , max(a.tgl) as tgl_akhir FROM tb_pakan_perencanaan as a WHERE a.id_kandang ='$idkandang';");

        $hasil = DB::select("SELECT b.nm_kandang, b.stok_awal, sum(a.mati) as mati, sum(a.jual) as jual, sum(a.afkir) as afkir, b.rupiah FROM populasi as a left join kandang as b on b.id_kandang = a.id_kandang where a.tgl BETWEEN '$tgl_periode->tgl_awal' and '$tgl_periode->tgl_akhir' group by a.id_kandang;");

        return $hasil;
    }
    public static function jurnal_periode($idkandang)
    {
        $tgl_periode = DB::selectOne("SELECT min(a.tgl) as tgl_awal , max(a.tgl) as tgl_akhir FROM tb_pakan_perencanaan as a WHERE a.id_kandang ='$idkandang';");

        $hasil = DB::selectOne("SELECT sum(a.debit) as debit
        FROM jurnal as a
        left join akun as b on b.id_akun = a.id_akun
        where a.tgl BETWEEN '$tgl_periode->tgl_awal' and '2025-01-31' and a.id_akun in (112,98,70,91,2,14,15,18,20,21,33,36,42,3,44,45,55,4,5,8,10,11,12,51,58);");

        return $hasil;
    }
    public static function jurnal_periode_detail($idkandang)
    {
        $tgl_periode = DB::selectOne("SELECT min(a.tgl) as tgl_awal , max(a.tgl) as tgl_akhir FROM tb_pakan_perencanaan as a WHERE a.id_kandang ='$idkandang';");

        $hasil = DB::select("SELECT b.nm_akun, sum(a.debit) as debit FROM jurnal as a left join akun as b on b.id_akun = a.id_akun where a.tgl BETWEEN '$tgl_periode->tgl_awal' and '2025-01-31' and a.id_akun in (112,98,70,91,2,14,15,18,20,21,33,36,42,3,44,45,55,4,5,8,10,11,12,51,58) group by a.id_akun;");

        return $hasil;
    }
}
