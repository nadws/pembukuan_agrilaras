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

        $tahun = $r->tahun;
        $tglMundur1 = Carbon::now()->subMonths(2)->format('n');
        $tglMundur2 = Carbon::now()->format('n');

        $bulan1 =  $r->bulan1 ?? $tglMundur1;
        $bulan2 =  $r->bulan2 ?? $tglMundur2;

        $tgl1 =  date("$tahun-$bulan1-01");
        $tgl2 =  date("$tahun-$bulan2-t");

        $biaya = DB::select("SELECT a.id_akun, a.nm_akun, b.debit 
        FROM akun as a 
        LEFT JOIN ( SELECT a.id_akun, a.no_nota, c.nm_akun, sum(a.debit) as debit 
        FROM jurnal as a 
        left join ( SELECT b.no_nota , b.id_akun, c.nm_akun FROM jurnal as b 
        left join akun as c on c.id_akun = b.id_akun 
        where b.id_akun in (SELECT t.id_akun FROM akuncash_ibu as t where t.kategori in ('6','7')) and b.tgl BETWEEN '$tgl1' and '$tgl2' and b.kredit != 0 and b.id_buku in(2,10,12) 
        GROUP by b.no_nota ) as b on b.no_nota = a.no_nota 
        left join akun as c on c.id_akun = a.id_akun 
        where a.id_buku in(2,10,12) and a.debit != 0 and a.tgl BETWEEN '$tgl1' and '$tgl2' and b.id_akun is not null 
        group by a.id_akun ) AS b on b.id_akun = a.id_akun 
        where a.id_klasifikasi in('3','6','11','12')");

        $data = [
            'title' => 'Budgeting',
            'tgl1' => $tgl1,
            'tgl2' => $tgl2,
            'tglMundur1' => $tglMundur1,
            'tglMundur2' => $tglMundur2,
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
        dd($r->all());
        foreach($r->id_akun as $id_akun) {
            foreach($r->budget_perbulan[$id_akun] as $i => $d) {
                $budget = str()->remove(',', $d);
                DB::table('budget')->insert([
                    'id_akun' => $id_akun,
                    'tgl' => "2023-$i-01",
                    'rupiah' => $budget,
                    'admin' => auth()->user()->name
                ]);
            }
        }
        
        return redirect()->route('budget.index')->with('sukses', 'Data Berhasil ditambahkan');
    }
}
