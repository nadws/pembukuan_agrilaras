<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Penjualan_umum_cekController extends Controller
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

    public function index(Request $r)
    {
        $penjualan = DB::select("SELECT *,a.id_customer as nm_customer, sum(a.total_rp) as total, count(*) as ttl_produk  FROM `penjualan_agl` as a
        LEFT JOIN customer as b ON a.id_customer = b.id_customer
        WHERE a.lokasi = 'mtd'
        GROUP BY a.urutan ORDER BY a.cek ASC");

        $ttlRp = 0;
        $ttlRpBelumDiCek = 0;
        foreach ($penjualan as $v) {
            $ttlRp += $v->total;
            if ($v->cek != 'Y') {
                $ttlRpBelumDiCek += $v->total;
            }
        }

        $data = [
            'title' => 'Penjualan Umum',
            'penjualan' => $penjualan,
            'ttlRp' => $ttlRp,
            'ttlRpBelumDiCek' => $ttlRpBelumDiCek,
        ];
        return view('penjualan_umum_cek.index', $data);
    }

    public function terima_invoice_umum_cek(Request $r)
    {
        $no_nota = $r->no_nota[0];
        $cekAdadiJurnal = DB::selectOne("SELECT *,b.id_akun as id_akun_lawan FROM `jurnal` as a
        LEFT JOIN (
            select no_nota, id_akun, debit,kredit FROM jurnal WHERE id_akun != '84' GROUP BY no_nota
        ) as b ON a.no_nota = b.no_nota
        WHERE a.ket LIKE '%PUM-$no_nota%';");
        // dd(empty($cekAdadiJurnal));
        $data = [
            'title' => 'Penerimaan Uang Martadah',
            'nota' => $r->no_nota,
            'jurnal' => $cekAdadiJurnal,
            'akun' => DB::table('akun')->get(),
        ];
        return view('penjualan_umum_cek.penerimaan_uang', $data);
    }

    public function save_cek_umum_invoice(Request $r)
    {
        $max = DB::table('notas')->latest('nomor_nota')->where('id_buku', '6')->first();
        if (empty($max)) {
            $nota_t = '1000';
        } else {
            $nota_t = $max->nomor_nota + 1;
        }
        DB::table('notas')->insert(['nomor_nota' => $nota_t, 'id_buku' => '6']);

        for ($x = 0; $x < count($r->no_nota); $x++) {
            $max_akun = DB::table('jurnal')->latest('urutan')->where('id_akun', '84')->first();
            $akun = DB::table('akun')->where('id_akun', '84')->first();
            $urutan = empty($max_akun) ? '1001' : ($max_akun->urutan == 0 ? '1001' : $max_akun->urutan + 1);

            $data = [
                'tgl' => $r->tgl[$x],
                'no_nota' => 'PMLD-' . $nota_t,
                'id_akun' => '84',
                'id_buku' => '6',
                'ket' => $r->no_nota[$x] . ':' . $r->nm_customer[$x],
                'debit' => '0',
                'kredit' => $r->pembayaran[$x],
                'admin' => Auth::user()->name,
                'no_urut' => $akun->inisial . '-' . $urutan,
                'urutan' => $urutan,
            ];
            DB::table('jurnal')->insert($data);

            DB::table('penjualan_agl')->where('urutan', $r->urutan[$x])->update(['cek' => 'Y', 'admin_cek' => Auth::user()->name]);
        }

        for ($x = 0; $x < count($r->id_akun); $x++) {
            $max_akun = DB::table('jurnal')->latest('urutan')->where('id_akun', $r->id_akun[$x])->first();
            $akun = DB::table('akun')->where('id_akun', $r->id_akun[$x])->first();

            $urutan = empty($max_akun) ? '1001' : ($max_akun->urutan == 0 ? '1001' : $max_akun->urutan + 1);
            $data = [
                'tgl' => $r->tgl[$x],
                'no_nota' => 'PMLD-' . $nota_t,
                'id_akun' => $r->id_akun[$x],
                'id_buku' => '6',
                'ket' => 'Penjualan lain-lain di Martadah',
                'debit' => $r->debit[$x],
                'kredit' => $r->kredit[$x],
                'admin' => Auth::user()->name,
                'no_urut' => $akun->inisial . '-' . $urutan,
                'urutan' => $urutan,
            ];
            DB::table('jurnal')->insert($data);
        }

        return redirect()->route('penjualan_umum_cek')->with('sukses', 'Data berhasil ditambahkan');
    }

    public function penyetoran(Request $r)
    {
        $tgl1 =  $this->tgl1;
        $tgl2 =  $this->tgl2;
        $data = [
            'title' => 'Penyetoran Umum',
            'tgl1' => $tgl1,
            'tgl2' => $tgl2,
            'invoice' => DB::select("SELECT a.id_jurnal, a.tgl, a.no_nota, b.nm_akun, a.ket, a.debit, c.id_akun
            FROM jurnal as a 
            left join akun as b on b.id_akun = a.id_akun
            LEFT JOIN (
            	SELECT c.id_akun, c.no_nota
                FROM jurnal as c
                where c.kredit != '0' and c.id_buku ='6'
            ) as c on c.no_nota = a.no_nota
            where a.id_buku = '6' and a.id_akun IN('64','25','82') and a.setor ='T' and a.debit != '0' and c.id_akun in(84)
            group by a.no_nota
            order by a.tgl , a.no_nota ASC
            ")
        ];
        return view('penjualan_umum_cek.penyetoran', $data);
    }

    public function perencanaan_setor(Request $r)
    {
        $max = DB::table('setoran_umum')->latest('urutan')->first();

        if (empty($max->urutan)) {
            $nota_t = '1000';
        } else {
            $nota_t = $max->urutan + 1;
        }

        $data = [
            'title' => 'Perencanaan Setoran Umum',
            'id_jurnal' => $r->id_jurnal,
            'akun' => DB::table('akun')->whereIn('id_klasifikasi', ['1'])->get(),
            'nota' => $nota_t
        ];
        return view('penjualan_umum_cek.perencanaan', $data);
    }

    public function save_perencanaan(Request $r)
    {
        $max = DB::table('setoran_umum')->latest('urutan')->first();

        if (empty($max->urutan)) {
            $nota_t = '1000';
        } else {
            $nota_t = $max->urutan + 1;
        }
        for ($x = 0; $x < count($r->id_jurnal); $x++) {
            $data = [
                'nota_setor' => 'PEUMUM-' . $nota_t,
                'tgl' => $r->tgl,
                'id_jurnal' => $r->id_jurnal[$x],
                'no_nota_jurnal' => $r->no_nota_jurnal[$x],
                'nominal' => $r->nominal[$x],
                'urutan' => $nota_t,
                'id_akun' => $r->id_akun_pem[$x]
            ];
            DB::table('setoran_umum')->insert($data);

            DB::table('jurnal')->where('id_jurnal', $r->id_jurnal[$x])->update(['setor' => 'Y', 'nota_setor' => 'PEUMUM-' . $nota_t]);
        }

        DB::table('setoran_umum')->where('nota_setor', 'PEUMUM-' . $nota_t)->update(['selesai' => 'Y']);
        if (!empty($r->id_akun)) {
            $max_akun = DB::table('jurnal')->latest('urutan')->where('id_akun', $r->id_akun1)->first();
            $akun = DB::table('akun')->where('id_akun', $r->id_akun1)->first();
            $urutan = empty($max_akun) ? '1001' : ($max_akun->urutan == 0 ? '1001' : $max_akun->urutan + 1);

            $data = [
                'tgl' => $r->tgl,
                'no_nota' => 'PEUMUM-' . $nota_t,
                'id_akun' => $r->id_akun1,
                'id_buku' => '7',
                'ket' => $r->ket,
                'debit' => 0,
                'kredit' => $r->total_setor,
                'admin' => auth()->user()->name,
                'no_urut' => $akun->inisial . '-' . $urutan,
                'urutan' => $urutan,
            ];
            DB::table('jurnal')->insert($data);

            $max_akun2 = DB::table('jurnal')->latest('urutan')->where('id_akun', $r->id_akun)->first();
            $akun2 = DB::table('akun')->where('id_akun', $r->id_akun)->first();
            $urutan2 = empty($max_akun2) ? '1001' : ($max_akun2->urutan == 0 ? '1001' : $max_akun2->urutan + 1);

            $data = [
                'tgl' => $r->tgl,
                'no_nota' => 'PEUMUM-' . $nota_t,
                'id_akun' => $r->id_akun,
                'id_buku' => '7',
                'ket' => $r->ket,
                'debit' => $r->total_setor,
                'kredit' => 0,
                'admin' => auth()->user()->name,
                'no_urut' => $akun2->inisial . '-' . $urutan2,
                'urutan' => $urutan2,
            ];
            DB::table('jurnal')->insert($data);
        }
        return redirect()->route('summary_buku_besar.detail', ['id_akun' => $r->id_akun, 'tgl1' => '2023-01-01', 'tgl2' => $r->tgl])->with('sukses', 'Data berhasil ditambahkan');
    }

    public function get_history_perencanaan(Request $r)
    {
        $tgl1 = $r->tgl1 ?? date('Y-m-01');
        $tgl2 = $r->tgl2 ?? date('Y-m-t');

        $data =  [
            'invoice' => DB::select("SELECT a.tgl, a.nota_setor , b.nm_akun, sum(a.nominal) as nominal , a.selesai
            FROM setoran_umum as a
            left join akun as b on b.id_akun = a.id_akun
            where a.tgl between '$tgl1' and '$tgl2'
            group by a.nota_setor
            "),
            'tgl1' => $tgl1,
            'tgl2' => $tgl2,
        ];
        return view('penjualan_umum_cek.history_perencanaan', $data);
    }

    public function print_setoran(Request $r)
    {
        $invoice = DB::table('setoran_umum')->where('nota_setor', $r->no_nota)->first();
        $data = [
            'invoice' => DB::select("SELECT c.tgl, a.no_nota_jurnal, b.nm_akun, c.nm_customer, a.nominal
            FROM setoran_umum as a
            left join akun as b on b.id_akun = a.id_akun
            LEFT JOIN (SELECT no_nota,SUBSTRING_INDEX(ket, ':', -1) AS nm_customer,tgl FROM `jurnal` WHERE debit = 0 GROUP BY no_nota) as c ON a.no_nota_jurnal = c.no_nota
            where a.nota_setor = '$r->no_nota'
            group by a.no_nota_jurnal;
            "),
            'akun' => DB::table('akun')->whereIn('id_klasifikasi', ['1', '7'])->where('id_akun', '!=', $invoice->id_akun)->get(),
            'no_nota' => $r->no_nota,
            'invo' => $invoice,
            'title' => 'Print Setoran Umum'
        ];
        return view('penjualan_umum_cek.print_perencanaan', $data);
    }

    public function delete_perencanaan(Request $r)
    {
        $invoice = DB::table('setoran_umum')->where('nota_setor', $r->no_nota)->get();
        foreach ($invoice as $i) {
            $data = [
                'setor' => 'T',
                'nota_setor' => ''
            ];
            DB::table('jurnal')->where('id_jurnal', $i->id_jurnal)->update($data);
        }
        DB::table('jurnal')->where('no_nota', $r->no_nota)->delete();
        DB::table('setoran_umum')->where('nota_setor', $r->no_nota)->delete();

        return redirect()->route('penyetoran_penjualan_umum')->with('sukses', 'Data berhasil dihapus');
    }
}
