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
        $biayabeliasset = ProfitModel::beliasset($tgl2);
        $biaya_penyesuaian = ProfitModel::getData3($tgl1, $tgl2);
        $biaya_disusutkan = ProfitModel::getData4($tgl1, $tgl2);
        $aktiva = ProfitModel::aktiva($tgl2);
        $peralatan = ProfitModel::peralatan($tgl2);
        $aktiva_depresiasi = ProfitModel::asset_depresiasi($tgl2, '9');
        $biaya_aktiva_depresiasi = ProfitModel::asset_depresiasi($tgl2, '52');
        $peralatan_depresiasi = ProfitModel::asset_depresiasi($tgl2, '16');
        $biaya_peralatan_depresiasi = ProfitModel::asset_depresiasi($tgl2, '59');
        $kg_butir = DB::table('rules_budget')->where('id_rules_budget', '1')->first();
        $rp_kg = DB::table('rules_budget')->where('id_rules_budget', '2')->first();

        $kg_per_butir =  $kg_butir->jumlah / 1000;
        $rp_kg = $rp_kg->jumlah;

        $month = date('m', strtotime($tgl2));
        $year = date('Y', strtotime($tgl2));

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
        where b.id_akun not in (SELECT t.id_akun FROM akuncash_ibu as t where t.kategori in( '6','7')) and
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
            'biayabeliasset' => $biayabeliasset,
            'aktiva' => $aktiva,
            'peralatan' => $peralatan,
            'aktiva_depresiasi' => $aktiva_depresiasi,
            'biaya_aktiva_depresiasi' => $biaya_aktiva_depresiasi,
            'peralatan_depresiasi' => $peralatan_depresiasi,
            'biaya_peralatan_depresiasi' => $biaya_peralatan_depresiasi,

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
        DB::table('budget')->truncate();
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


    function profit_setahun(Request $r)
    {

        // $cari_tahun = DB::selectOne("SELECT YEAR(max(a.tgl)) as tahun FROM jurnal as a where a.id_akun = '26' and YEAR(a.tgl) != 0");
        $cari_tahun = 2024;
        if (empty($r->tahun)) {
            $tahun = $cari_tahun;
        } else {
            $tahun = $r->tahun;
        }
        $pendapatan = ProfitModel::pendapatan_setahun($tahun, '4');


        $biaya = ProfitModel::pendapatan_setahun($tahun, '3');
        $biaya_penyesuaian = ProfitModel::biaya_penyesuaian_setahun($tahun);
        $biaya_disusutkan = ProfitModel::biaya_disusutkan_setahun($tahun);
        $biaya_beli_asset = ProfitModel::biaya_beli_asset($tahun);
        // $saldopullet = ProfitModel::saldo_pullet($tahun);
        $saldopullet2 = ProfitModel::saldo_pullet2($tahun);



        $data = [];
        foreach ($pendapatan as $transaction) {

            $month = date('F', strtotime("{$transaction->tahun}-{$transaction->bulan}-01"));

            // Ubah bulan dan tahun menjadi format yang benar
            $nominal = $transaction->kredit; // Menghitung nominal

            // Menambahkan data akun dan nominal ke struktur data
            if (!isset($data[$transaction->id_akun])) {
                $data[$transaction->id_akun] = [
                    'January' => 0,
                    'February' => 0,
                    'March' => 0,
                    'April' => 0,
                    'May' => 0,
                    'June' => 0,
                    'July' => 0,
                    'August' => 0,
                    'September' => 0,
                    'October' => 0,
                    'November' => 0,
                    'December' => 0,
                ];
            }
            // Menambahkan data nominal ke struktur data
            $data[$transaction->id_akun][$month] = $nominal;
        }

        $data2 = [];
        foreach ($biaya as $transaction2) {

            $month = date('F', strtotime("{$transaction2->tahun}-{$transaction2->bulan}-01")); // Ubah bulan dan tahun menjadi format yang benar
            $nominal = $transaction2->debit  - $transaction2->kredit; // Menghitung nominal

            // Menambahkan data akun dan nominal ke struktur data
            if (!isset($data2[$transaction2->id_akun])) {
                $data2[$transaction2->id_akun] = [
                    'January' => 0,
                    'February' => 0,
                    'March' => 0,
                    'April' => 0,
                    'May' => 0,
                    'June' => 0,
                    'July' => 0,
                    'August' => 0,
                    'September' => 0,
                    'October' => 0,
                    'November' => 0,
                    'December' => 0,
                ];
            }
            // Menambahkan data nominal ke struktur data
            $data2[$transaction2->id_akun][$month] = $nominal;
        }
        $data3 = [];
        foreach ($biaya_penyesuaian as $transaction3) {
            $month = date('F', strtotime("{$transaction3->tahun}-{$transaction3->bulan}-01")); // Ubah bulan dan tahun menjadi format yang benar
            $nominal = $transaction3->debit  - $transaction3->kredit; // Menghitung nominal

            // Menambahkan data akun dan nominal ke struktur data
            if (!isset($data3[$transaction3->id_akun])) {
                $data3[$transaction3->id_akun] = [
                    'January' => 0,
                    'February' => 0,
                    'March' => 0,
                    'April' => 0,
                    'May' => 0,
                    'June' => 0,
                    'July' => 0,
                    'August' => 0,
                    'September' => 0,
                    'October' => 0,
                    'November' => 0,
                    'December' => 0,
                ];
            }
            // Menambahkan data nominal ke struktur data
            $data3[$transaction3->id_akun][$month] = $nominal;
        }
        $data4 = [];
        foreach ($biaya_disusutkan as $b) {
            $month = date('F', strtotime("{$b->tahun}-{$b->bulan}-01")); // Ubah bulan dan tahun menjadi format yang benar
            $nominal = $b->debit; // Menghitung nominal

            // Menambahkan data akun dan nominal ke struktur data
            if (!isset($data4[$b->id_akun])) {
                $data4[$b->id_akun] = [
                    'January' => 0,
                    'February' => 0,
                    'March' => 0,
                    'April' => 0,
                    'May' => 0,
                    'June' => 0,
                    'July' => 0,
                    'August' => 0,
                    'September' => 0,
                    'October' => 0,
                    'November' => 0,
                    'December' => 0,
                ];
            }
            // Menambahkan data nominal ke struktur data
            $data4[$b->id_akun][$month] = $nominal;
        }
        $data5 = [];
        foreach ($biaya_beli_asset as $b) {
            $month = date('F', strtotime("{$b->tahun}-{$b->bulan}-01")); // Ubah bulan dan tahun menjadi format yang benar
            $nominal = $b->debit + $b->debit_saldo; // Menghitung nominal

            // Menambahkan data akun dan nominal ke struktur data
            if (!isset($data5[$b->id_akun])) {
                $data5[$b->id_akun] = [
                    'January' => 0,
                    'February' => 0,
                    'March' => 0,
                    'April' => 0,
                    'May' => 0,
                    'June' => 0,
                    'July' => 0,
                    'August' => 0,
                    'September' => 0,
                    'October' => 0,
                    'November' => 0,
                    'December' => 0,
                ];
            }
            // Menambahkan data nominal ke struktur data
            $data5[$b->id_akun][$month] = $nominal;
        }
        // $data6 = [];
        // foreach ($saldopullet as $b) {
        //     $month = date('F', strtotime("{$b->tahun}-{$b->bulan}-01")); // Ubah bulan dan tahun menjadi format yang benar
        //     $nominal = $b->debit; // Menghitung nominal

        //     // Menambahkan data akun dan nominal ke struktur data
        //     if (!isset($data6[$b->id_aktiva])) {
        //         $data6[$b->id_aktiva] = [
        //             'January' => 0,
        //             'February' => 0,
        //             'March' => 0,
        //             'April' => 0,
        //             'May' => 0,
        //             'June' => 0,
        //             'July' => 0,
        //             'August' => 0,
        //             'September' => 0,
        //             'October' => 0,
        //             'November' => 0,
        //             'December' => 0,
        //         ];
        //     }
        //     // Menambahkan data nominal ke struktur data
        //     $data6[$b->id_aktiva][$month] = $nominal;
        // }
        $data7 = [];
        foreach ($saldopullet2 as $b) {
            $month = date('F', strtotime("{$b->tahun}-{$b->bulan}-01")); // Ubah bulan dan tahun menjadi format yang benar
            $nominal = $b->debit; // Menghitung nominal

            // Menambahkan data akun dan nominal ke struktur data
            if (!isset($data7[$b->id_kandang])) {
                $data7[$b->id_kandang] = [
                    'January' => 0,
                    'February' => 0,
                    'March' => 0,
                    'April' => 0,
                    'May' => 0,
                    'June' => 0,
                    'July' => 0,
                    'August' => 0,
                    'September' => 0,
                    'October' => 0,
                    'November' => 0,
                    'December' => 0,
                ];
            }
            // Menambahkan data nominal ke struktur data
            $data7[$b->id_kandang][$month] = $nominal;
        }
        $datas = [
            'title' => 'Profit Setahun',
            'tahun' => DB::select("SELECT YEAR(a.tgl) as tahun FROM jurnal as a where a.id_akun = '26' and YEAR(a.tgl) != 0 group by YEAR(a.tgl);"),
            'thn' => $tahun,

        ];

        return view('profit.profit_setahun', compact('data', 'data2', 'data3', 'data4', 'data5',  'data7'), $datas);
    }

    public function get_depresiasi(Request $r)
    {
        if ($r->akun == '51') {
            $depresiasi_ak = DB::select("SELECT b.nm_aktiva, b.tgl as tgl_perolehan,  c.nm_kelompok, c.umur,  a.tgl, a.b_penyusutan
        FROM depresiasi_aktiva as a
        left join aktiva as b on b.id_aktiva = a.id_aktiva
        left join kelompok_aktiva as c on c.id_kelompok = b.id_kelompok
        where a.tgl between '$r->tgl1' and '$r->tgl2'");
        } else {
            $depresiasi_ak = DB::select("SELECT b.nm_aktiva, b.tgl, c.nm_kelompok, c.umur, c.periode, a.b_penyusutan
            FROM depresiasi_peralatan as a
            left join peralatan as b on b.id_aktiva = a.id_aktiva
            left join kelompok_peralatan as c on c.id_kelompok = b.id_kelompok
            where a.tgl between '$r->tgl1' and '$r->tgl2';");
        }
        $data = [
            'depaktiva' => $depresiasi_ak
        ];

        if ($r->akun == '51') {
            return view('profit.depresiasi_aktv', $data);
        } else {
            return view('profit.depresiasi_peralatan', $data);
        }
    }
    // public function get_depresiasi_peralatan(Request $r)
    // {
    //     $depresiasi_ak = DB::select("SELECT b.nm_aktiva, b.tgl, c.nm_kelompok, c.umur, c.periode, a.b_penyusutan
    //     FROM depresiasi_peralatan as a
    //     left join peralatan as b on b.id_aktiva = a.id_aktiva
    //     left join kelompok_peralatan as c on c.id_kelompok = b.id_kelompok
    //     where a.tgl between '$r->tgl1' and '$r->tgl2';");


    //     $data = [
    //         'depaktiva' => $depresiasi_ak
    //     ];

    //     return view('profit.depresiasi_peralatan', $data);
    // }

    public function getPopulasi(Request $r)
    {
        $data = [
            'title' => 'Populasi',
            'kandang' => DB::table('kandang')->where('id_kandang', $r->id_kandang)->first(),
            'populasi' => DB::select("SELECT a.id_kandang, a.tgl, b.nm_kandang, b.rupiah, b.stok_awal, MONTH(a.tgl) as bulan, YEAR(a.tgl) as tahun, sum(a.mati) as death, sum(a.jual) as jual, sum(a.afkir) as afkir
            FROM populasi as a 
            left join kandang as b on b.id_kandang = a.id_kandang
            where  MONTH(a.tgl) = '$r->bulan' and YEAR(a.tgl) = '$r->tahun' and a.id_kandang = '$r->id_kandang'
            group by a.tgl;"),
        ];
        return view('profit.populasi', $data);
    }
}
