<?php

namespace App\Http\Controllers;

use App\Models\ProfitModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProfitController extends Controller
{
    protected $tgl1, $tgl2, $period;
    public function __construct(Request $r)
    {
        if (empty($r->period)) {
            $this->tgl1 = date('Y-m-01');
            $this->tgl2 = date('Y-m-t');
        } elseif ($r->period == 'daily') {
            $this->tgl1 = date('Y-m-d');
            $this->tgl2 = date('Y-m-d');
        } elseif ($r->period == 'weekly') {
            $this->tgl1 = date('Y-m-d', strtotime('-6 days'));
            $this->tgl2 = date('Y-m-d');
        } elseif ($r->period == 'mounthly') {
            $bulan = $r->bulan;
            $tahun = $r->tahun;
            $tglawal = "$tahun" . '-' . "$bulan" . '-' . '01';
            $tglakhir = "$tahun" . '-' . "$bulan" . '-' . '01';

            $this->tgl1 = date('Y-m-01', strtotime($tglawal));
            $this->tgl2 = date('Y-m-t', strtotime($tglakhir));
        } elseif ($r->period == 'costume') {
            $this->tgl1 = $r->tgl1;
            $this->tgl2 = $r->tgl2;
        } elseif ($r->period == 'years') {
            $tahun = $r->tahunfilter;
            $tgl_awal = "$tahun" . '-' . '01' . '-' . '01';
            $tgl_akhir = "$tahun" . '-' . '12' . '-' . '01';

            $this->tgl1 = date('Y-m-01', strtotime($tgl_awal));
            $this->tgl2 = date('Y-m-t', strtotime($tgl_akhir));
        }
    }
    public function index()
    {
        $data = [
            'title' => 'Profit and Loss',
            'tgl1' => $this->tgl1,
            'tgl2' => $this->tgl2,
        ];
        return view('profit.index', $data);
    }

    public function load(Request $r)
    {
        $tgl1 = $r->tgl1;
        $tgl2 = $r->tgl2;
        $akun = DB::table('akun')->get();


        $biaya_murni = ProfitModel::getData($tgl1, $tgl2);
        $biayaGantung = ProfitModel::getData2($tgl1, $tgl2);
        $biaya_penyesuaian = ProfitModel::getData3($tgl1, $tgl2);
        $biaya_disusutkan = ProfitModel::getData4($tgl1, $tgl2);
        $kg_butir = DB::table('rules_budget')->where('id_rules_budget', '1')->first();
        $rp_kg = DB::table('rules_budget')->where('id_rules_budget', '2')->first();

        $kg_per_butir =  $kg_butir->jumlah / 1000;
        $rp_kg = $rp_kg->jumlah;

        $month = date('m');
        $year = date('Y');

        $ttl_day = cal_days_in_month(CAL_GREGORIAN, $month, $year);

        $tgl1_ini = date('Y-m-01');
        $tgl1_filter = $tgl1;



        $tgl_now =  date('d');
        if ($tgl1_ini == $tgl1_filter) {
            $tgl_now =  date('d');
        } else {
            $tgl_now =  date('d', strtotime($tgl2));
        }
        $hari = $tgl_now;


        $estimasi_telur = ProfitModel::estimasi($tgl1, $kg_per_butir, $rp_kg, $hari);
        $estimasi_telur_bulan = ProfitModel::estimasi($tgl1, $kg_per_butir, $rp_kg, $ttl_day);

        $biaya_bkn_keluar = DB::select("SELECT a.id_akun, a.no_nota, c.nm_akun, sum(a.debit) as debit
        FROM jurnal as a
        left join (
        SELECT b.no_nota , b.id_akun, c.nm_akun
        FROM jurnal as b
        left join akun as c on c.id_akun = b.id_akun
        where b.id_akun not in (SELECT t.id_akun FROM akuncash_ibu as t where t.kategori = '6') and
        b.tgl BETWEEN '$tgl1' and '$tgl2' and b.kredit != 0
        GROUP by b.no_nota
        ) as b on b.no_nota = a.no_nota
        left join akun as c on c.id_akun = a.id_akun
        where a.id_buku not in(5,13) and a.debit != 0 and a.tgl BETWEEN '$tgl1' and '$tgl2' and b.id_akun is not
        null and c.id_klasifikasi in('3','6','11','12')
        group by a.id_akun;");

        $data = [
            'title' => 'Load Profit',
            'tgl1' => $tgl1,
            'tgl2' => $tgl2,
            'akun' => $akun,
            'biaya_murni' => $biaya_murni,
            'biayaGantung' => $biayaGantung,
            'biaya_penyesuaian' => $biaya_penyesuaian,
            'biaya_bkn_keluar' => $biaya_bkn_keluar,
            'estimasi_telur' => $estimasi_telur,
            'estimasi_telur_bulan' => $estimasi_telur_bulan,
            'biaya_disusutkan' => $biaya_disusutkan,
            'subKategori1' => DB::table('sub_kategori_cashflow')
                ->where('jenis', 1)
                ->orderBy('urutan', 'ASC')
                ->get(),
            'subKategori2' => DB::table('sub_kategori_cashflow')
                ->where('jenis', 2)
                ->orderBy('urutan', 'ASC')
                ->get(),
            'subKategori3' => DB::table('sub_kategori_cashflow')
                ->where('jenis', 3)
                ->orderBy('urutan', 'ASC')
                ->get(),
        ];
        return view('profit.load', $data);
    }

    public function modal(Request $r)
    {
        $akunProfit = DB::table('akunprofit as a')
            ->join('akun as b', 'a.id_akun', 'b.id_akun')
            ->where('a.kategori', $r->id_kategori)
            ->orderBy('a.urutan', 'ASC')
            ->get();

        $akun = DB::Select("SELECT * FROM akun as a");
        $data = [
            'akunProfit' => $akunProfit,
            'akun1' => $akun,
            'id_kategori' => $r->id_kategori
        ];
        return view('profit.modal', $data);
    }

    public function save_akun_profit(Request $r)
    {
        DB::table('akunprofit')->where('kategori', $r->kategori)->delete();
        for ($i = 0; $i < count($r->ceklis); $i++) {
            DB::table('akunprofit')->insert([
                'kategori' => $r->kategori,
                'id_akun' => $r->id_akun[$i],
                'urutan' => 1,
            ]);
        }
        return redirect()->route('controlflow')->with('sukses', 'Data Berhasil');
    }

    public function save_akun_profit_new(Request $r)
    {

        for ($i = 0; $i < count($r->id_akun); $i++) {
            if ($r->profit_loss[$i] == 'T') {
                # code...
            } else {
                DB::table('akunprofit')->insert([
                    'kategori' => $r->kategori,
                    'id_akun' => $r->id_akun[$i],
                    'urutan' => 1,
                ]);
                DB::table('akun')->where('id_akun', $r->id_akun[$i])->update(['profit_loss' => 'Y']);
            }
        }
        return redirect()->route('controlflow')->with('sukses', 'Data Berhasil');
    }

    public function delete(Request $r)
    {
        $getAkun = DB::table('akunprofit')->where('id_akunprofit', $r->id_profit);
        $id_akun = $getAkun->first()->id_akun;
        $getAkun->delete();

        DB::table('akun')->where('id_akun', $id_akun)->update(['profit_loss' => 'T']);
    }

    public function add(Request $r)
    {
        for ($i = 0; $i < count($r->id_akun); $i++) {
            DB::table('akunprofit')->insert([
                'urutan' => $r->urutan ?? 1,
                'id_akun' => $r->id_akun[$i],
                'kategori' => $r->kategori_id,
            ]);
            DB::table('akun')->where('id_akun', $r->id_akun[$i])->update(['profit_loss' => 'Y']);
        }
    }

    public function getQueryProfit($id_kategori, $jenis, $tgl1, $tgl2)
    {
        return DB::select("SELECT c.nm_akun, b.kredit, b.debit
        FROM profit_akun as a
        left join (
        SELECT b.id_akun, sum(b.debit) as debit, sum(b.kredit) as kredit
            FROM jurnal as b
            WHERE b.id_buku not in('1','5') and $jenis != 0 and b.penutup = 'T' and b.tgl between '$tgl1' and '$tgl2'
            group by b.id_akun
        ) as b on b.id_akun = a.id_akun
        left join akun as c on c.id_akun = a.id_akun
        where a.kategori_id = '$id_kategori';");
    }

    public function print(Request $r)
    {
        $tgl1 = $r->tgl1;
        $tgl2 = $r->tgl2;

        $data = [
            'title' => 'Profit and Loss',
            'tgl1' => $tgl1,
            'tgl2' => $tgl2,
            'subKategori1' => DB::table('sub_kategori_cashflow')
                ->where('jenis', 1)
                ->orderBy('urutan', 'ASC')
                ->get(),
            'subKategori2' => DB::table('sub_kategori_cashflow')
                ->where('jenis', 2)
                ->orderBy('urutan', 'ASC')
                ->get(),
        ];
        return view('profit.print', $data);
    }

    public function load_uraian(Request $r)
    {
        $data = [
            'subKategori' => DB::table('sub_kategori_cashflow')
                ->where('jenis', $r->jenis)
                ->orderBy('urutan', 'ASC')
                ->get(),
        ];
        return view('profit.load_uraian', $data);
    }

    public function save_subkategori(Request $r)
    {
        DB::table('sub_kategori_cashflow')->insert($r->all());
    }

    public function delete_subkategori(Request $r)
    {
        DB::table('sub_kategori_cashflow')
            ->where('id', $r->id)
            ->delete();
    }

    public function update(Request $r)
    {
        for ($i = 0; $i < count($r->id_edit); $i++) {
            DB::table('sub_kategori_cashflow')
                ->where('id', $r->id_edit[$i])
                ->update([
                    'sub_kategori' => $r->nm_kategori[$i],
                    'urutan' => $r->urutan[$i],
                ]);
        }
    }
    public function count_sisa(Request $r)
    {
        $sisa = DB::selectOne("SELECT COUNT(*) as sisa FROM akun as a
        LEFT JOIN profit_akun as b ON a.id_akun= b.id_akun
        WHERE b.id_akun IS null");

        $sisa2 = DB::selectOne("SELECT COUNT(*) as sisa FROM akun as a
        LEFT JOIN akun_neraca as b ON a.id_akun= b.id_akun
        WHERE b.id_akun IS null");

        echo $r->jenis == 'profit' ? $sisa->sisa : $sisa2->sisa;
    }
    public function view_akun()
    {
        $data = [
            'akun' => DB::Select("SELECT a.id_akun,a.nm_akun, b.id_akun as ada FROM akun as a
            LEFT JOIN profit_akun as b ON a.id_akun= b.id_akun"),
        ];

        return view('profit.view_akun', $data);
    }

    public function akunprofit(Request $r)
    {

        $akunPenjualan = DB::select("SELECT a.id_akun as akun_id, a.kode_akun , a.nm_akun, b.id_akun, a.profit_loss
        FROM akun as a
        left join akunprofit as b on b.id_akun = a.id_akun WHERE b.kategori = 1");

        $akunBiaya = DB::select("SELECT a.id_akun as akun_id, a.kode_akun , a.nm_akun, b.id_akun, a.profit_loss
        FROM akun as a
        left join akunprofit as b on b.id_akun = a.id_akun WHERE b.kategori = 4");
        $akunUangKeluar = DB::select("SELECT a.id_akun as akun_id, a.kode_akun , a.nm_akun, b.id_akun, a.profit_loss
        FROM akun as a
        left join akunprofit as b on b.id_akun = a.id_akun WHERE b.kategori = 9");
        $data = [
            'akunPenjualan' => $akunPenjualan,
            'akunBiaya' => $akunBiaya,
            'akunUangKeluar' => $akunUangKeluar,
            'allAkun' => DB::table('akun')->get(),
            'akun' => DB::select("SELECT a.id_akun as akun_id, a.kode_akun , a.nm_akun, b.id_akun, a.profit_loss
            FROM akun as a
            left join akunprofit as b on b.id_akun = a.id_akun;"),
        ];
        return view('profit.akunprofit', $data);
    }

    public function seleksi_akun_profit(Request $r)
    {
        // dd($r->all());
        for ($x = 0; $x < count($r->id_akun); $x++) {

            $data = [
                'profit_loss' => $r->profit_loss[$x]
            ];
            DB::table('akun')->where('id_akun', $r->id_akun[$x])->update($data);
        }
        return redirect()->route('controlflow')->with('sukses', 'Berhasil input akun');
    }

    function persen_pendapatan(Request $r)
    {
        $data = [
            'hd_persen' => DB::table('persen_budget_ayam')->get(),
            'kg_butir' => DB::table('rules_budget')->where('id_rules_budget', '1')->first(),
            'rp_kg' => DB::table('rules_budget')->where('id_rules_budget', '2')->first()
        ];
        return view('profit.hd_persen', $data);
    }

    function tambah_baris_budget_persen(Request $r)
    {
        $data =  [
            'count' => $r->count
        ];
        return view('profit.tbh_baris', $data);
    }

    function save_persen_pendapatan(Request $r)
    {
        DB::table('persen_budget_ayam')->truncate();
        for ($x = 0; $x < count($r->dari); $x++) {
            $data = [
                'umur_dari' => $r->dari[$x],
                'umur_sampai' => $r->sampai[$x],
                'persen' => $r->persen[$x],
            ];
            DB::table('persen_budget_ayam')->insert($data);
        }

        for ($x = 0; $x < count($r->id_rules_budget); $x++) {
            $data = [
                'jumlah' => $r->jumlah[$x]
            ];
            DB::table('rules_budget')->where('id_rules_budget', $r->id_rules_budget[$x])->update($data);
        }
    }

    function save_budget(Request $r)
    {
        DB::table('budget')->where('tgl', $r->tgl)->delete();
        for ($x = 0; $x < count($r->id_akun_budget); $x++) {
            $duit = str()->remove(',', $r->rupiah_budget[$x]);
            $data = [
                'id_akun' => $r->id_akun_budget[$x],
                'tgl' => $r->tgl,
                'rupiah' => $duit,
            ];
            DB::table('budget')->insert($data);
        }
    }
}
