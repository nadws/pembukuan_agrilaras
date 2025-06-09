<?php

namespace App\Http\Controllers;

use App\Models\LaporanLayerModel;
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

        $tgl1 = date('Y-m-01', strtotime($tgl));

        $tgl_awal_harga = date("Y-m-d", strtotime($tgl . "-30 days"));



        $harga = DB::selectOne("SELECT b.nm_produk, a.tgl, sum(a.pcs / 1000) as pcs , sum(a.total_rp) as ttl_rupiah, a.admin
        FROM stok_produk_perencanaan as a 
        left join tb_produk_perencanaan as b on b.id_produk = a.id_pakan
        where a.h_opname = 'T' and a.tgl BETWEEN '$tgl_awal_harga' and '$tgl' and b.kategori = 'pakan' and a.pcs != 0;");





        $data = [
            'title' => 'Laporan Layer',
            'tgl' => $tgl,
            'tgl_sebelum' => $tgl_sebelumnya,
            'tgl_kemarin' => $tgl_kemarin,
            'harga' => $harga,
            'kandang' => LaporanLayerModel::getLaporanLayer($tgl, $tgl_sebelumnya, $tgl_kemarin, $tgl_minggu_sebelumnya, $tgl_minggu_kemaren, $tgl_awal_harga),
        ];
        return view('laporan.layer2', $data);
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

    function get_history_produk(Request $r)
    {
        if (empty($r->tgl1)) {
            $tgl1 = date('Y-m-01');
            $tgl2 = date('Y-m-d');
        } else {
            $tgl1 = $r->tgl1;
            $tgl2 = $r->tgl2;
        }

        $history = DB::table('tb_produk_perencanaan')->where('id_produk', $r->id_produk)->first();
        $kandang = DB::table('kandang')->where('id_kandang', $r->id_kandang)->first();

        $data = [
            'history' => $history,
            'id_kandang' => $r->id_kandang,
            'id_produk' => $r->id_produk,
            'kandang' => $kandang,
            'tgl1' => $tgl1,
            'tgl2' => $tgl2,
        ];

        return view('laporan.history_produk', $data);
    }
}
