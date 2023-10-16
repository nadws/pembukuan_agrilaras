<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BudgetController extends Controller
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
            $this->tgl1 = date('Y-m-d', strtotime("-6 days"));
            $this->tgl2 = date('Y-m-d');
        } elseif ($r->period == 'mounthly') {
            $bulan = $r->bulan;
            $tahun = $r->tahun;
            $tglawal = "$tahun" . "-" . "$bulan" . "-" . "01";
            $tglakhir = "$tahun" . "-" . "$bulan" . "-" . "01";

            $this->tgl1 = date('Y-m-01', strtotime($tglawal));
            $this->tgl2 = date('Y-m-t', strtotime($tglakhir));
        } elseif ($r->period == 'costume') {
            $this->tgl1 = $r->tgl1;
            $this->tgl2 = $r->tgl2;
        } elseif ($r->period == 'years') {
            $tahun = $r->tahunfilter;
            $tgl_awal = "$tahun" . "-" . "01" . "-" . "01";
            $tgl_akhir = "$tahun" . "-" . "12" . "-" . "01";

            $this->tgl1 = date('Y-m-01', strtotime($tgl_awal));
            $this->tgl2 = date('Y-m-t', strtotime($tgl_akhir));
        }
    }

    public function index(Request $r)
    {

        $tahun = $r->tahun ?? date('Y');
        $tglMundur1 = Carbon::now()->subMonths(2)->format('n');
        $tglMundur2 = Carbon::now()->format('n');

        $bulan1 =  $r->bulan1 ?? $tglMundur1;
        $bulan2 =  $r->bulan2 ?? $tglMundur2;

        $tgl1 =  date("$tahun-$bulan1-01");
        $tgl2 = date('Y-m-t', strtotime($tgl1));

        $biaya = DB::select("SELECT a.id_akun, a.nm_akun
        FROM akun as a 
        where a.id_klasifikasi in('3','6','11','12') ORDER BY a.nm_akun ASC");
        $data = [
            'title' => 'Budgeting',
            'tgl1' => $tgl1,
            'tgl2' => $tgl2,
            'tglMundur1' => $tglMundur1,
            'tglMundur2' => $tglMundur2,
            'tahun' => $tahun,
            'bulan1' => request()->get('bulan1') ?? $tglMundur1,
            'bulan2' => request()->get('bulan2') ?? $tglMundur2,
            'bulan' => DB::table('bulan')->get(),
            'bulanView' => DB::table('bulan')->whereBetween('bulan', [$bulan1, $bulan2])->get(),
            'biaya' => $biaya,

        ];
        return view('budget.index', $data);
    }

    public function halaman(Request $r)
    {
        $tgl1 = $r->tgl1 ?? date('Y-m-1');
        $tgl2 = $r->tgl2 ?? date('Y-m-t');



        $data = [];
        return view('budget.load_halaman', $data);
    }

    public function create(Request $r)
    {
        $cek = DB::table('budget')->where('tgl', date('Y-m-01'))->first();
        // if(!empty($cek)){
        //     DB::table('budget')->where('tgl', date('Y-m-01'))->update([
        //         'tgl_hapus' => now()
        //     ]);
        // }
        for ($i = 0; $i < count($r->id_akun); $i++) {
            if (!empty($r->budget[$i])) {
                $budget = str()->remove(',', $r->budget[$i]);
                DB::table('budget')->insert([
                    'id_akun' => $r->id_akun[$i],
                    'tgl' => date('Y-m-01'),
                    'rupiah' => $budget,
                    'admin' => auth()->user()->name
                ]);
            }
        }

        return redirect()->route('budget.index', [
            'bulan1' => $r->bulan1,
            'bulan2' => $r->bulan2,
            'tahun' => $r->tahun,
        ])->with('sukses', 'Data Berhasil ditambahkan');
    }
}
