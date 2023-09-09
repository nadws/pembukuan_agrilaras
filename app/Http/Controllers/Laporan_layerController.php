<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Laporan_layerController extends Controller
{
    public function index(Request $r)
    {
        if (empty($r->tgl)) {
            $tgl = date("Y-m-d", strtotime("-1 day"));
        } else {
            $tgl = $r->tgl;
        }
        $tgl_sebelumnya = date("Y-m-d", strtotime($tgl . " -6 days"));
        $tgl_kemarin = date("Y-m-d", strtotime($tgl . " -1 days"));

        $tgl_minggu_kemaren = date("Y-m-d", strtotime($tgl_sebelumnya . " -1 days"));
        $tgl_minggu_sebelumnya = date("Y-m-d", strtotime($tgl_minggu_kemaren . " -6 days"));



        $data = [
            'title' => 'Laporan Layer',
            'tgl' => $tgl,
            'kandang' => DB::select("SELECT a.chick_in, a.chick_out, a.nm_kandang  , FLOOR(DATEDIFF('$tgl', a.chick_in) / 7) AS mgg , DATEDIFF('$tgl', a.chick_in) AS hari, a.stok_awal, b.pop_kurang, c.mati, c.jual, d.kg_pakan, e.feed, f.kg_pakan_week, g.feed as feed_past, e.berat as berat_badan , h.pcs, i.pcs_past, j.kuml_pcs, h.kg, i.kg_past,j.kuml_kg, g.telur,k.pcs_telur_week,k.kg_telur_week,l.kg_pakan_kuml, m.rp_vitamin, n.kuml_rp_vitamin,o.pop_kurang_past, e.berat_telur as t_peforma, p.jlh_hari, q.jlh_hari_past, r.pcs_telur_week_past, q.kg_pp_week,p.kg_p_week, s.kum_ttl_rp_vaksin,t.ttl_rp_vaksin, e.telur as p_hd, u.pcs as pcs_satu_minggu, u.kg as kg_satu_minggu, v.pcs as pcs_minggu_sebelumnya , v.kg as kg_minggu_sebelumnya, w.mati_week , w.jual_week, DATE_ADD( a.chick_in, INTERVAL 85 WEEK) AS tgl_setelah_85_minggu
            FROM kandang as a 


          

            -- Populasi --
            left join(SELECT b.id_kandang, sum(b.mati+b.jual) as pop_kurang 
            FROM populasi as b 
            where b.tgl between '2020-01-01' and '$tgl'
            group by b.id_kandang ) as b on b.id_kandang = a.id_kandang

            left join(SELECT b.id_kandang, sum(b.mati+b.jual) as pop_kurang_past
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

            left join peformance as e on e.id_strain = a.id_strain and e.umur = FLOOR(DATEDIFF('$tgl', a.chick_in) / 7)

            left join (
                SELECT d.id_kandang, sum(d.pcs_kredit) as kg_pakan_week
                FROM stok_produk_perencanaan as d 
                where d.tgl between '$tgl_sebelumnya' and '$tgl'
                group by d.id_kandang
            ) as f on f.id_kandang = a.id_kandang

            left join peformance as g on g.id_strain = a.id_strain and g.umur = FLOOR(DATEDIFF('$tgl', a.chick_in) / 7) -1
            -- Pakan --

            -- Data telur --
            left join (SELECT h.id_kandang , sum(h.pcs) as pcs, sum(h.kg) as kg FROM stok_telur as h  where h.tgl = '$tgl' group by h.id_kandang) as h on h.id_kandang = a.id_kandang

            left join (SELECT h.id_kandang , sum(h.pcs) as pcs_past, sum(h.kg) as kg_past FROM stok_telur as h  where h.tgl = '$tgl_kemarin' group by h.id_kandang) as i on i.id_kandang = a.id_kandang

            left join (SELECT h.id_kandang , sum(h.pcs) as kuml_pcs, sum(h.kg) as kuml_kg FROM stok_telur as h  where h.tgl between '2020-01-01' and '$tgl' group by h.id_kandang) as j on j.id_kandang = a.id_kandang

            left join (
                SELECT a.id_kandang, sum(a.pcs) as pcs_telur_week, sum(a.kg) as kg_telur_week, FLOOR(DATEDIFF(a.tgl, b.chick_in) / 7) AS mgg_hd_week
                FROM stok_telur as a 
                left JOIN kandang as b on b.id_kandang = a.id_kandang
                where a.tgl between '2020-01-01' and '$tgl'
                group by FLOOR(DATEDIFF(a.tgl, b.chick_in) / 7), a.id_kandang
            ) as k on k.id_kandang = a.id_kandang and k.mgg_hd_week = FLOOR(DATEDIFF('$tgl', a.chick_in) / 7)

            left join (
                SELECT d.id_kandang, sum(d.pcs_kredit) as kg_pakan_kuml
                FROM stok_produk_perencanaan as d 
                where d.tgl between '2020-01-01' and '$tgl'
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
                SELECT d.id_kandang, sum(d.total_rp) as kuml_rp_vitamin
                FROM stok_produk_perencanaan as d 
                left join tb_produk_perencanaan as e on e.id_produk = d.id_pakan
                where d.tgl between '2020-01-01' and '$tgl' and e.kategori in('obat_pakan','obat_air') and d.pcs_kredit != '0'
                group by d.id_kandang
            ) as n on n.id_kandang = a.id_kandang


            left join (
                SELECT o.id_kandang,  sum(o.kg_p_week) as kg_p_week, count(o.id_kandang) as jlh_hari, FLOOR(DATEDIFF(o.tgl, p.chick_in) / 7) AS mgg_hd
                FROM 
                ( SELECT o.id_kandang, o.tgl,sum(o.pcs_kredit) as kg_p_week
                FROM stok_produk_perencanaan as o
                left join tb_produk_perencanaan as q on q.id_produk = o.id_pakan
                where q.kategori = 'pakan' and o.tgl between '2020-01-01' and '$tgl'
                group by o.tgl , o.id_kandang
                ) as o
                left join kandang as p on p.id_kandang = o.id_kandang
                group by FLOOR(DATEDIFF(o.tgl, p.chick_in) / 7), p.id_kandang
            ) as p on p.id_kandang = a.id_kandang and p.mgg_hd = FLOOR(DATEDIFF('$tgl', a.chick_in) / 7)

            left join (
                SELECT o.id_kandang, sum(o.kg_p_week) as kg_pp_week, count(o.id_kandang) as jlh_hari_past, FLOOR(DATEDIFF(o.tgl, p.chick_in) / 7) AS mgg_hd_past
                FROM 
                ( SELECT o.id_kandang, o.tgl,sum(o.pcs_kredit) as kg_p_week
                FROM stok_produk_perencanaan as o
                left join tb_produk_perencanaan as q on q.id_produk = o.id_pakan
                where q.kategori = 'pakan'
                group by o.tgl , o.id_kandang
                ) as o
                left join kandang as p on p.id_kandang = o.id_kandang
                group by FLOOR(DATEDIFF(o.tgl, p.chick_in) / 7), p.id_kandang
            ) as q on q.id_kandang = a.id_kandang and q.mgg_hd_past = FLOOR(DATEDIFF('$tgl', a.chick_in) / 7)-1

            left join (
                SELECT a.id_kandang, sum(a.pcs) as pcs_telur_week_past, sum(a.kg) as kg_telur_week_past, FLOOR(DATEDIFF(a.tgl, b.chick_in) / 7) AS mgg_hd_week_past
                FROM stok_telur as a 
                left JOIN kandang as b on b.id_kandang = a.id_kandang
                group by FLOOR(DATEDIFF(a.tgl, b.chick_in) / 7), a.id_kandang
            ) as r on r.id_kandang = a.id_kandang and r.mgg_hd_week_past = FLOOR(DATEDIFF('$tgl', a.chick_in) / 7)-1
            
            left join (
                SELECT s.id_kandang , sum(s.ttl_rp) as kum_ttl_rp_vaksin
                FROM tb_vaksin_perencanaan as s
                group by s.id_kandang
            ) as s on s.id_kandang = a.id_kandang

            left join (
                SELECT s.id_kandang , sum(s.ttl_rp) as ttl_rp_vaksin,FLOOR(DATEDIFF(s.tgl, b.chick_in) / 7) AS mgg_vaksin
                FROM tb_vaksin_perencanaan as s
                left JOIN kandang as b on b.id_kandang = s.id_kandang
                group by s.id_kandang,FLOOR(DATEDIFF(s.tgl, b.chick_in) / 7)
            ) as t on t.id_kandang = a.id_kandang and t.mgg_vaksin = FLOOR(DATEDIFF('$tgl', a.chick_in) / 7)

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




            -- Data telur --


            where a.selesai = 'T'
            order by a.id_kandang ASC
            ")
        ];
        return view('laporan.layer', $data);
    }

    public function rumus_layer(Request $r)
    {
        if ($r->rumus == 'butir_today') {
            echo "<b>Butir Today - Yesterday =</b> <em >telur sekarang perbutir - telur kemarin perbutir</em>";
        }
        if ($r->rumus == 'hh') {
            echo "<b>Hen House =</b> <em >(Jumlah telur hari ini/Jumlah ayam awal)  x 100%</em>";
        }
        if ($r->rumus == 'hhkum') {
            echo "<b>Hen House Komulatif =</b> <em >(Jumlah telur dari awal sampai hari ini/Jumlah ayam awal)  x 100%</em>";
        }
        if ($r->rumus == 'kg_today') {
            echo "<b>Kg Today - Yesterday =</b> <em >telur sekarang kg - telur kemarin kg</em> <br><br>";
            echo "<b>Note =</b> <em >Jika minus maka akan merah</em>";
        }
        if ($r->rumus == 'hh_kg') {
            echo "<b>Hen House Kg =</b> <em >(Jumlah telur hari ini (kg)/Jumlah ayam awal)  x 100%</em>";
        }
        if ($r->rumus == 'hh_kgkum') {
            echo "<b>Hen House Komulatif Kg =</b> <em >(Jumlah telur dari awal sampai hari ini(kg)/Jumlah ayam awal)  x 100%</em>";
        }
        if ($r->rumus == 'gr_butir') {
            echo "<b>Gram Perbutir =</b> <em >(Jumlah telur hari ini (gr) - (jumlah pcs hari ini / 180)) / jumlah pcs hari ini)</em>";
        }
        if ($r->rumus == 'hd_day') {
            echo "<b>HD perday =</b> <em >(Jumlah telur hari ini/Jumlah ayam akhir) x 100%</em><br><br>";
            echo "<b>HH perday =</b> <em >(Jumlah telur hari ini/Jumlah ayam awal) x 100%</em><br><br>";
        }
        if ($r->rumus == 'hd_past') {
            echo "<b>HD past =</b> <em >(Jumlah telur kemarin/Jumlah ayam akhir kemarin) x 100%</em>";
        }
        if ($r->rumus == 'hd_week') {
            echo "<b>HD Week =</b> <em >(PCS Telur minggu ini/Jumlah ayam akhir minggu ini) x 100</em> <br><br>";
            echo "<b>HD Past Week =</b> <em >(PCS Telur minggu lalu/Jumlah ayam akhir minggu lalu) x 100</em>";
        }
        if ($r->rumus == 'fcr_week') {
            echo "<b>FCR week =</b> <em >Jumlah pakan minggu ini (kg)/(Jumlah telur minggu ini (kg) - (pcs telur minggu ini / 180))</em> <br><br>";
            echo "<b>FCR week + =</b> <em >(Jumlah pakan minggu ini (kg) + (Rupiah vitamin minggu ini /7000))/(Jumlah telur minggu ini (kg) - (pcs telur minggu ini / 180))</em> <br><br>";
            echo "<b>Note :</b> Jika Fcr diatas 2.2 maka kolom berwarna merah";
        }
        if ($r->rumus == 'fcrplus_week') {
            echo "<b>FCR+ week =</b> <em >(Jumlah pakan yang diberikan selama 1 minggu (kg) + (total rupiah vaksin & vitamin / 7000))/(Jumlah telur selama 1 minggu (kg) - (pcs telur selama 1 minggu / 180))</em>";
        }
        if ($r->rumus == 'd_c') {
            echo "<b>Note :</b> Jika mati lebih dari 3 maka kolom berwarna merah";
        }
        if ($r->rumus == 'mgg') {
            echo "<b>Note :</b> Jika Minggu mencapai 80 minggu atau lebih  maka kolom berwarna merah";
        }
        if ($r->rumus == 'butir') {
            echo "<b>Butir =</b> <em >telur sekarang pcs - telur kemarin pcs</em><br><br>";
            echo "<b>Note =</b> <em >Jika minus maka akan merah</em>";
        }
    }
}
