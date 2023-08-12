<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PenjualanAyamController extends Controller
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
        $penjualan = DB::select("SELECT *, sum(a.h_satuan * a.qty) as total, count(*) as ttl_produk  FROM `invoice_ayam` as a
        LEFT JOIN customer as b ON a.id_customer = b.id_customer
        WHERE a.lokasi = 'mtd'
        GROUP BY a.urutan");
        $data = [
            'title' => 'Penjualan Ayam',
            'penjualan' => $penjualan
        ];
        return view('penjualan_ayam.penjualan_ayam', $data);
    }

    public function setor(Request $r)
    {
        $data = [
            'title' => 'Penerimaan Uang Penjualan Ayam',
            'nota' => $r->no_nota,
            'akun' => DB::table('akun')->get(),
        ];
        return view('penjualan_ayam.setor', $data);
    }

    public function save_setor(Request $r)
    {
        $id_akun_penualan_ayam = 521;
        $max = DB::table('notas')->latest('nomor_nota')->where('id_buku', '6')->first();

        if (empty($max)) {
            $nota_t = '1000';
        } else {
            $nota_t = $max->nomor_nota + 1;
        }
        DB::table('notas')->insert(['nomor_nota' => $nota_t, 'id_buku' => '6']);

        for ($x = 0; $x < count($r->no_nota); $x++) {
            $max_akun = DB::table('jurnal')->latest('urutan')->where('id_akun', $id_akun_penualan_ayam)->first();
            $akun = DB::table('akun')->where('id_akun', $id_akun_penualan_ayam)->first();
            $urutan = empty($max_akun) ? '1001' : ($max_akun->urutan == 0 ? '1001' : $max_akun->urutan + 1);

            $data = [
                'tgl' => $r->tgl[$x],
                'no_nota' => 'PAMTD-' . $nota_t,
                'id_akun' => $id_akun_penualan_ayam,
                'id_buku' => '6',
                'ket' => 'Penjualan  ' . $r->no_nota[$x],
                'debit' => '0',
                'kredit' => $r->pembayaran[$x],
                'admin' => auth()->user()->name,
                'no_urut' => $akun->inisial . '-' . $urutan,
                'urutan' => $urutan,
            ];
            DB::table('jurnal')->insert($data);

            DB::table('invoice_ayam')->where('urutan', $r->urutan[$x])->update(['cek' => 'Y', 'admin_cek' => auth()->user()->name]);
        }

        for ($x = 0; $x < count($r->id_akun); $x++) {
            $max_akun = DB::table('jurnal')->latest('urutan')->where('id_akun', $r->id_akun[$x])->first();
            $akun = DB::table('akun')->where('id_akun', $r->id_akun[$x])->first();

            $urutan = empty($max_akun) ? '1001' : ($max_akun->urutan == 0 ? '1001' : $max_akun->urutan + 1);
            $data = [
                'tgl' => $r->tgl[$x],
                'no_nota' => 'PAMTD-' . $nota_t,
                'id_akun' => $r->id_akun[$x],
                'id_buku' => '6',
                'ket' => 'Penjualan ayam di Martadah',
                'debit' => $r->debit[$x],
                'kredit' => $r->kredit[$x],
                'admin' => auth()->user()->name,
                'no_urut' => $akun->inisial . '-' . $urutan,
                'urutan' => $urutan,
            ];
            DB::table('jurnal')->insert($data);
        }

        return redirect()->route('penjualan_ayam.index')->with('sukses', 'Data berhasil ditambahkan');
    }

    public function penyetoran(Request $r)
    {
        $tgl1 =  $this->tgl1;
        $tgl2 =  $this->tgl2;
        $data = [
            'title' => 'Penyetoran Ayam',
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
            where a.id_buku = '6' and a.id_akun IN('3','4','82') and a.setor ='T' and a.debit != '0' and c.id_akun in(521,522)
            group by a.no_nota
            order by a.tgl , a.no_nota ASC
            ")
        ];
        return view('penjualan_ayam.penyetoran', $data);
    }

    public function perencanaan_setor(Request $r)
    {
        $max = DB::table('setoran_ayam')->latest('urutan')->first();

        if (empty($max->urutan)) {
            $nota_t = '1000';
        } else {
            $nota_t = $max->urutan + 1;
        }

        $data = [
            'title' => 'Perencanaan Setoran Ayam',
            'id_jurnal' => $r->id_jurnal,
            'akun' => DB::table('akun')->whereIn('id_klasifikasi', ['1'])->get(),
            'nota' => $nota_t
        ];
        return view('penjualan_ayam.perencanaan', $data);
    }

    public function save_perencanaan(Request $r)
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
                'no_nota' => 'PET-' . $nota_t,
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
}
