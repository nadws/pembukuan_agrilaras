<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Penyetoran_telurController extends Controller
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
            $tgl = "$tahun" . "-" . "$bulan" . "-" . "01";

            $this->tgl1 = date('Y-m-01', strtotime($tgl));
            $this->tgl2 = date('Y-m-t', strtotime($tgl));
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
    public function index()
    {
        $tgl1 =  $this->tgl1;
        $tgl2 =  $this->tgl2;
        $data = [
            'title' => 'Penyetoran Telur',
            'tgl1' => $tgl1,
            'tgl2' => $tgl2,
            'invoice' => DB::select("SELECT a.id_jurnal, a.tgl, a.no_nota, b.nm_akun, a.ket, a.debit
            FROM jurnal as a 
            left join akun as b on b.id_akun = a.id_akun
            where a.id_buku = '6' and a.id_akun IN('64','25','82') and a.setor ='T' and a.debit != '0'
            group by a.no_nota
            order by a.tgl , a.no_nota ASC
            ")
        ];
        return view('penyetoran.index', $data);
    }

    public function perencanaan_setor_telur(Request $r)
    {
        $max = DB::table('setoran_telur')->latest('urutan')->first();

        if (empty($max->urutan)) {
            $nota_t = '1000';
        } else {
            $nota_t = $max->urutan + 1;
        }

        $data = [
            'title' => 'Perencanaan setoran',
            'id_jurnal' => $r->id_jurnal,
            'akun' => DB::table('akun')->whereIn('id_klasifikasi', ['1'])->get(),
            'nota' => $nota_t
        ];
        return view('penyetoran.perencanaan', $data);
    }

    public function save_perencanaan_telur(Request $r)
    {
        $max = DB::table('setoran_telur')->latest('urutan')->first();

        if (empty($max->urutan)) {
            $nota_t = '1000';
        } else {
            $nota_t = $max->urutan + 1;
        }
        for ($x = 0; $x < count($r->id_jurnal); $x++) {
            $data = [
                'nota_setor' => 'PET-' . $nota_t,
                'tgl' => $r->tgl,
                'id_jurnal' => $r->id_jurnal[$x],
                'no_nota_jurnal' => $r->no_nota_jurnal[$x],
                'nominal' => $r->nominal[$x],
                'urutan' => $nota_t,
                'id_akun' => $r->id_akun_pem[$x]
            ];
            DB::table('setoran_telur')->insert($data);

            DB::table('jurnal')->where('id_jurnal', $r->id_jurnal[$x])->update(['setor' => 'Y', 'nota_setor' => 'PET-' . $nota_t]);
        }

        DB::table('setoran_telur')->where('nota_setor', 'PET-' . $nota_t)->update(['selesai' => 'Y']);
        if (empty($r->id_akun)) {
            # code...
        } else {
            $max_akun = DB::table('jurnal')->latest('urutan')->where('id_akun', $r->id_akun1)->first();
            $akun = DB::table('akun')->where('id_akun', $r->id_akun1)->first();
            $urutan = empty($max_akun) ? '1001' : ($max_akun->urutan == 0 ? '1001' : $max_akun->urutan + 1);

            $data = [
                'tgl' => $r->tgl,
                'no_nota' => 'PET-' . $nota_t,
                'id_akun' => $r->id_akun1,
                'id_buku' => '7',
                'ket' => $r->ket,
                'debit' => 0,
                'kredit' => $r->total_setor,
                'admin' => Auth::user()->name,
                'no_urut' => $akun->inisial . '-' . $urutan,
                'urutan' => $urutan,
            ];
            DB::table('jurnal')->insert($data);

            $max_akun2 = DB::table('jurnal')->latest('urutan')->where('id_akun', $r->id_akun)->first();
            $akun2 = DB::table('akun')->where('id_akun', $r->id_akun)->first();
            $urutan2 = empty($max_akun2) ? '1001' : ($max_akun2->urutan == 0 ? '1001' : $max_akun2->urutan + 1);

            $data = [
                'tgl' => $r->tgl,
                'no_nota' => 'PET-' . $nota_t,
                'id_akun' => $r->id_akun,
                'id_buku' => '7',
                'ket' => $r->ket,
                'debit' => $r->total_setor,
                'kredit' => 0,
                'admin' => Auth::user()->name,
                'no_urut' => $akun2->inisial . '-' . $urutan2,
                'urutan' => $urutan2,
            ];
            DB::table('jurnal')->insert($data);
        }
        return redirect()->route('summary_buku_besar.detail', ['id_akun' => $r->id_akun, 'tgl1' => '2023-01-01', 'tgl2' => $r->tgl])->with('sukses', 'Data berhasil ditambahkan');
    }

    public function get_list_perencanaan()
    {
        $data =  [
            'invoice' => DB::select("SELECT a.tgl, a.nota_setor , b.nm_akun, sum(a.nominal) as nominal 
            FROM setoran_telur as a
            left join akun as b on b.id_akun = a.id_akun
            where a.selesai = 'T'
            group by a.nota_setor
            ")
        ];
        return view('penyetoran.list_perencanaan', $data);
    }
    public function get_perencanaan(Request $r)
    {
        $invoice = DB::table('setoran_telur')->where('nota_setor', $r->no_nota)->first();
        $data = [
            'invoice' => DB::select("SELECT c.tgl, a.no_nota_jurnal, b.nm_akun, c.ket, a.nominal
            FROM setoran_telur as a
            left join akun as b on b.id_akun = a.id_akun
            left join jurnal as c on c.id_jurnal = a.id_jurnal
            where a.nota_setor = '$r->no_nota'
            "),
            'akun' => DB::table('akun')->whereIn('id_klasifikasi', ['1', '7'])->where('id_akun', '!=', $invoice->id_akun)->get(),
            'no_nota' => $r->no_nota,
            'invo' => $invoice
        ];
        return view('penyetoran.get_perencanaan', $data);
    }

    public function save_setoran(Request $r)
    {
        DB::table('setoran_telur')->where('nota_setor', $r->no_nota)->update(['selesai' => 'Y']);

        if (empty($r->id_akun)) {
            # code...
        } else {
            $max_akun = DB::table('jurnal')->latest('urutan')->where('id_akun', $r->id_akun_kredit)->first();
            $akun = DB::table('akun')->where('id_akun', $r->id_akun_kredit)->first();
            $urutan = empty($max_akun) ? '1001' : ($max_akun->urutan == 0 ? '1001' : $max_akun->urutan + 1);

            $data = [
                'tgl' => $r->tgl,
                'no_nota' => $r->no_nota,
                'id_akun' => $r->id_akun_kredit,
                'id_buku' => '7',
                'ket' => $r->ket,
                'debit' => 0,
                'kredit' => $r->total_setor,
                'admin' => Auth::user()->name,
                'no_urut' => $akun->inisial . '-' . $urutan,
                'urutan' => $urutan,
            ];
            DB::table('jurnal')->insert($data);

            $max_akun2 = DB::table('jurnal')->latest('urutan')->where('id_akun', $r->id_akun)->first();
            $akun2 = DB::table('akun')->where('id_akun', $r->id_akun)->first();
            $urutan2 = empty($max_akun2) ? '1001' : ($max_akun2->urutan == 0 ? '1001' : $max_akun2->urutan + 1);

            $data = [
                'tgl' => $r->tgl,
                'no_nota' => $r->no_nota,
                'id_akun' => $r->id_akun,
                'id_buku' => '7',
                'ket' => $r->ket,
                'debit' => $r->total_setor,
                'kredit' => 0,
                'admin' => Auth::user()->name,
                'no_urut' => $akun2->inisial . '-' . $urutan2,
                'urutan' => $urutan2,
            ];
            DB::table('jurnal')->insert($data);
        }
        return redirect()->route('penyetoran_telur')->with('sukses', 'Data berhasil disetor');
    }

    public function print_setoran(Request $r)
    {
        $invoice = DB::table('setoran_telur')->where('nota_setor', $r->no_nota)->first();
        $data = [
            'invoice' => DB::select("SELECT c.tgl, a.no_nota_jurnal, b.nm_akun, c.ket, a.nominal
            FROM setoran_telur as a
            left join akun as b on b.id_akun = a.id_akun
            left join jurnal as c on c.id_jurnal = a.id_jurnal
            where a.nota_setor = '$r->no_nota'
            "),
            'akun' => DB::table('akun')->whereIn('id_klasifikasi', ['1', '7'])->where('id_akun', '!=', $invoice->id_akun)->get(),
            'no_nota' => $r->no_nota,
            'invo' => $invoice,
            'title' => 'Print Setoran'
        ];
        return view('penyetoran.print_perencanaan', $data);
    }

    public function delete_perencanaan(Request $r)
    {
        $invoice = DB::table('setoran_telur')->where('nota_setor', $r->no_nota)->get();
        foreach ($invoice as $i) {
            $data = [
                'setor' => 'T',
                'nota_setor' => ''
            ];
            DB::table('jurnal')->where('id_jurnal', $i->id_jurnal)->update($data);
        }
        DB::table('jurnal')->where('no_nota', $r->no_nota)->delete();
        DB::table('setoran_telur')->where('nota_setor', $r->no_nota)->delete();

        return redirect()->route('penyetoran_telur')->with('sukses', 'Data berhasil dihapus');
    }

    public function get_history_perencanaan(Request $r)
    {
        if (empty($r->tgl1)) {
            $tgl1 = date('Y-m-01');
            $tgl2 = date('Y-m-t');
        } else {
            $tgl1 = $r->tgl1;
            $tgl2 = $r->tgl2;
        }



        $data =  [
            'invoice' => DB::select("SELECT a.tgl, a.nota_setor , b.nm_akun, sum(a.nominal) as nominal , a.selesai
            FROM setoran_telur as a
            left join akun as b on b.id_akun = a.id_akun
            where a.tgl between '$tgl1' and '$tgl2'
            group by a.nota_setor
            "),
            'tgl1' => $tgl1,
            'tgl2' => $tgl2,
        ];
        return view('penyetoran.history_perencanaan', $data);
    }
}
