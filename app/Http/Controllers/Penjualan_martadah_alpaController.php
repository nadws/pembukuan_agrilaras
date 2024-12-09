<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Penjualan_martadah_alpaController extends Controller
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

        $invoice = DB::select("SELECT a.no_nota, a.tgl, a.tipe, a.admin, a.customer, b.nm_customer, sum(a.total_rp) as ttl_rp, a.status, a.cek, a.urutan_customer, a.admin
        FROM invoice_telur as a 
        left join customer as b on b.id_customer = a.id_customer2
          where a.lokasi = 'mtd' and a.tgl between '$tgl1' and '$tgl2'
        group by a.no_nota
        order by a.cek ASC 
        ");
        $ttlRp = 0;
        $ttlRpBelumDiCek = 0;
        foreach ($invoice as $v) {
            $ttlRp += $v->ttl_rp;
            if ($v->cek != 'Y') {
                $ttlRpBelumDiCek += $v->ttl_rp;
            }
        }
        $data =  [
            'title' => 'Penjualan Agrilaras',
            'invoice' => $invoice,
            'ttlRp' => $ttlRp,
            'ttlRpBelumDiCek' => $ttlRpBelumDiCek,
            'produk' => DB::table('telur_produk')->get(),

        ];
        return view('penjualan_martadh.index', $data);
    }

    public function detail_penjualan_mtd(Request $r)
    {
        $penjualan_mtd = DB::select("SELECT a.*, b.nm_telur FROM invoice_mtd as a 
        left join telur_produk as b on b.id_produk_telur = a.id_produk
        where a.no_nota = '$r->no_nota';");

        $penjualan_mtd_detail = DB::selectOne("SELECT a.*, b.nm_telur FROM invoice_mtd as a 
        left join telur_produk as b on b.id_produk_telur = a.id_produk
        where a.no_nota = '$r->no_nota';");

        $data = [
            'invoice' => $penjualan_mtd,
            'invoice2' => $penjualan_mtd_detail,
        ];

        return view('penjualan_martadh.detail', $data);
    }

    public function terima_invoice_mtd(Request $r)
    {
        $penjualan_mtd = DB::select("SELECT a.*, b.nm_telur FROM invoice_mtd as a 
        left join telur_produk as b on b.id_produk_telur = a.id_produk
        where a.no_nota = '$r->no_nota';");

        $penjualan_mtd_detail = DB::selectOne("SELECT a.*, b.nm_telur FROM invoice_mtd as a 
        left join telur_produk as b on b.id_produk_telur = a.id_produk
        where a.no_nota = '$r->no_nota';");

        $data = [
            'title' => 'Penerimaan Uang Martadah',
            'nota' => $r->no_nota,
            'akun' => DB::table('akun')->whereIn('id_klasifikasi', ['1', '7', '2'])->get(),
            'jurnal' => DB::select("SELECT *
            FROM jurnal as a
            WHERE a.no_nota = '$r->no_nota' and a.id_akun != '26'; "),
            'invoice' => $penjualan_mtd,
            'invoice2' => $penjualan_mtd_detail,
        ];
        return view('penjualan_martadh.penerimaan_uang', $data);
    }

    public function save_terima_invoice(Request $r)
    {
        DB::table('jurnal')->where('no_nota', $r->no_nota)->delete();
        DB::table('bayar_telur')->where('no_nota', $r->no_nota)->delete();

        $max = DB::table('notas')->latest('nomor_nota')->where('id_buku', '6')->first();

        if (empty($max)) {
            $nota_t = '1000';
        } else {
            $nota_t = $max->nomor_nota + 1;
        }
        DB::table('notas')->insert(['nomor_nota' => $nota_t, 'id_buku' => '2']);

        $max_akun = DB::table('jurnal')->latest('urutan')->where('id_akun', '26')->first();
        $akun = DB::table('akun')->where('id_akun', '26')->first();
        $urutan = empty($max_akun) ? '1001' : ($max_akun->urutan == 0 ? '1001' : $max_akun->urutan + 1);

        $data = [
            'tgl' => $r->tgl,
            'no_nota' => $r->no_nota,
            'id_akun' => '26',
            'id_buku' => '6',
            'ket' => 'Penjualan telur ' . $r->no_nota,
            'debit' => '0',
            'kredit' => $r->total_penjualan,
            'admin' => Auth::user()->name,
            'no_urut' => $akun->inisial . '-' . $urutan,
            'urutan' => $urutan,
        ];
        DB::table('jurnal')->insert($data);
        $data = [
            'tgl' => $r->tgl,
            'no_nota' => $r->no_nota,
            'debit' => 0,
            'kredit' => $r->total_penjualan,
        ];
        DB::table('bayar_telur')->insert($data);

        for ($x = 0; $x < count($r->id_akun); $x++) {
            $max_akun = DB::table('jurnal')->latest('urutan')->where('id_akun', $r->id_akun)->first();
            $akun = DB::table('akun')->where('id_akun', $r->id_akun)->first();

            $urutan = empty($max_akun) ? '1001' : ($max_akun->urutan == 0 ? '1001' : $max_akun->urutan + 1);
            $data = [
                'tgl' => $r->tgl,
                'no_nota' => $r->no_nota,
                'id_akun' => $r->id_akun[$x],
                'id_buku' => '6',
                'ket' => 'Penjualan telur di martadah',
                'debit' => $r->debit[$x],
                'kredit' => $r->kredit[$x],
                'admin' => Auth::user()->name,
                'no_urut' => $akun->inisial . '-' . $urutan,
                'urutan' => $urutan,
            ];
            DB::table('jurnal')->insert($data);
            if ($akun->id_klasifikasi == '7') {
                DB::table('invoice_telur')->where('no_nota',  $r->no_nota)->update(['status' => 'unpaid']);
            } else {
                $data = [
                    'tgl' => $r->tgl,
                    'no_nota' => $r->no_nota,
                    'debit' => $r->debit[$x],
                    'kredit' => 0,
                    'no_nota_piutang' => $r->no_nota
                ];
                DB::table('bayar_telur')->insert($data);
            }
        }

        if (empty($r->id_akun_sisa)) {
            # code...
        } else {
            $max_akun = DB::table('jurnal')->latest('urutan')->where('id_akun', $r->id_akun_sisa)->first();
            $akun = DB::table('akun')->where('id_akun', $r->id_akun_sisa)->first();

            $urutan = empty($max_akun) ? '1001' : ($max_akun->urutan == 0 ? '1001' : $max_akun->urutan + 1);

            if ($r->selisih < 0) {
                $data = [
                    'tgl' => $r->tgl,
                    'no_nota' => $r->no_nota,
                    'id_akun' => $r->id_akun_sisa,
                    'id_buku' => '6',
                    'ket' => 'Penjualan telur di martadah',
                    'debit' =>  $r->selisih * -1,
                    'kredit' => 0,
                    'admin' => Auth::user()->name,
                    'no_urut' => $akun->inisial . '-' . $urutan,
                    'urutan' => $urutan,
                ];
                DB::table('jurnal')->insert($data);
                $data = [
                    'tgl' => $r->tgl,
                    'no_nota' => $r->no_nota,
                    'debit' => 0,
                    'kredit' => $r->selisih * -1,
                    'no_nota_piutang' => $r->no_nota
                ];
                DB::table('bayar_telur')->insert($data);
            } else {
                $data = [
                    'tgl' => $r->tgl,
                    'no_nota' => $r->no_nota,
                    'id_akun' => $r->id_akun_sisa,
                    'id_buku' => '6',
                    'ket' => 'Penjualan telur di martadah',
                    'debit' => 0,
                    'kredit' => $r->selisih,
                    'admin' => Auth::user()->name,
                    'no_urut' => $akun->inisial . '-' . $urutan,
                    'urutan' => $urutan,
                ];
                DB::table('jurnal')->insert($data);
                $data = [
                    'tgl' => $r->tgl,
                    'no_nota' => $r->no_nota,
                    'debit' => $r->selisih,
                    'kredit' => 0,
                    'no_nota_piutang' => $r->no_nota
                ];
                DB::table('bayar_telur')->insert($data);
            }
        }

        DB::table('invoice_telur')->where('no_nota', $r->no_nota)->update(['cek' => 'Y', 'admin_cek' => Auth::user()->name]);



        $tgl1 = date('Y-m-01', strtotime($r->tgl));
        $tgl2 = date('Y-m-t', strtotime($r->tgl));
        return redirect()->route('penjualan_martadah_cek', ['period' => 'costume', 'tgl1' => $tgl1, 'tgl2' => $tgl2])->with('sukses', 'Data berhasil ditambahkan');
    }
}
