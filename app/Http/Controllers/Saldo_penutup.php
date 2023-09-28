<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Saldo_penutup extends Controller
{
    protected $tgl1, $tgl2, $id_proyek, $period, $id_buku;
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

        $this->id_proyek = $r->id_proyek ?? 0;
        $this->id_buku = $r->id_buku ?? 2;
    }
    public function index(Request $r)
    {
        $tgl1 =  $this->tgl1;
        $tgl2 =  $this->tgl2;
        $data =  [
            'title' => 'Saldo Penutup',
            'akun' => DB::select("SELECT a.id_akun, a.kode_akun, a.nm_akun, b.debit, b.kredit, b.no_nota
            FROM akun as a 
            left join (
                SELECT b.id_akun, b.debit, b.kredit, b.no_nota
                FROM jurnal_saldo as b 
                where b.tgl between '$tgl1' and '$tgl2'
            ) as b on b.id_akun =  a.id_akun
            "),
            'tgl1' => $tgl1,
            'tgl2' => $tgl2


        ];
        return view('saldo_penutup.index', $data);
    }

    public function saveSaldopenutup(Request $r)
    {
        $id_akun = $r->id_akun;
        DB::table('jurnal_saldo')->where('tgl', $r->tgl)->delete();
        for ($i = 0; $i < count($id_akun); $i++) {
            $data = [
                'tgl' => $r->tgl,
                'id_akun' => $r->id_akun[$i],
                'debit' => $r->debit[$i],
                'kredit' => $r->kredit[$i],
                'tgl' => $r->tgl,
                'no_nota' => $r->no_nota[$i],
                'admin' => Auth::user()->name,
                'saldo' => 'Y',
                'penutup' => 'Y'
            ];
            DB::table('jurnal_saldo')->insert($data);
        }
        $bulan = date('m', strtotime($r->tgl));
        $tahun = date('Y', strtotime($r->tgl));
        return redirect()->route('saldo_penutup', ['period' => 'mounthly', 'bulan' => $bulan, 'tahun' => $tahun])->with('sukses', 'Data berhasil ditambahkan');
    }
}
