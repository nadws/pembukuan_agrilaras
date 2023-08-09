<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Penjualan_martadah_alpaController extends Controller
{
    public function index(Request $r)
    {
        $data =  [
            'title' => 'Penjualan Agrilaras',
            'invoice' => DB::select("SELECT a.no_nota, a.tgl, a.tipe, a.admin, a.customer, b.nm_customer, sum(a.total_rp) as ttl_rp, a.status, a.cek, a.urutan_customer, a.admin
            FROM invoice_telur as a 
            left join customer as b on b.id_customer = a.id_customer
              where a.lokasi = 'mtd'
            group by a.no_nota
            order by a.cek ASC 
            "),
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
        $data = [
            'title' => 'Penerimaan Uang Martadah',
            'nota' => $r->no_nota,
            'akun' => DB::table('akun')->whereIn('id_klasifikasi', ['1', '7'])->get()
        ];
        return view('penjualan_martadh.penerimaan_uang', $data);
    }

    public function save_terima_invoice(Request $r)
    {
        $max = DB::table('notas')->latest('nomor_nota')->where('id_buku', '6')->first();

        if (empty($max)) {
            $nota_t = '1000';
        } else {
            $nota_t = $max->nomor_nota + 1;
        }
        DB::table('notas')->insert(['nomor_nota' => $nota_t, 'id_buku' => '2']);

        for ($x = 0; $x < count($r->no_nota); $x++) {
            $max_akun = DB::table('jurnal')->latest('urutan')->where('id_akun', '26')->first();
            $akun = DB::table('akun')->where('id_akun', '26')->first();
            $urutan = empty($max_akun) ? '1001' : ($max_akun->urutan == 0 ? '1001' : $max_akun->urutan + 1);

            $data = [
                'tgl' => $r->tgl[$x],
                'no_nota' => $r->no_nota[$x],
                'id_akun' => '26',
                'id_buku' => '6',
                'ket' => 'Penjualan telur ' . $r->no_nota[$x],
                'debit' => '0',
                'kredit' => $r->pembayaran[$x],
                'admin' => Auth::user()->name,
                'no_urut' => $akun->inisial . '-' . $urutan,
                'urutan' => $urutan,
            ];
            DB::table('jurnal')->insert($data);

            $max_akun = DB::table('jurnal')->latest('urutan')->where('id_akun', $r->id_akun)->first();
            $akun = DB::table('akun')->where('id_akun', $r->id_akun)->first();

            $urutan = empty($max_akun) ? '1001' : ($max_akun->urutan == 0 ? '1001' : $max_akun->urutan + 1);
            $data = [
                'tgl' => $r->tgl[$x],
                'no_nota' => $r->no_nota[$x],
                'id_akun' => $r->id_akun,
                'id_buku' => '6',
                'ket' => 'Penjualan telur di martadah',
                'debit' => $r->pembayaran[$x],
                'kredit' => 0,
                'admin' => Auth::user()->name,
                'no_urut' => $akun->inisial . '-' . $urutan,
                'urutan' => $urutan,
            ];
            DB::table('jurnal')->insert($data);

            $data = [
                'tgl' => $r->tgl[$x],
                'no_nota' => $r->no_nota[$x],
                'debit' => 0,
                'kredit' => $r->pembayaran[$x],
            ];
            DB::table('bayar_telur')->insert($data);
            if ($akun->id_klasifikasi == '7') {
                DB::table('invoice_telur')->where('no_nota',  $r->no_nota[$x])->update(['status' => 'unpaid']);
            } else {
                $data = [
                    'tgl' => $r->tgl[$x],
                    'no_nota' => $r->no_nota[$x],
                    'debit' => $r->pembayaran[$x],
                    'kredit' => 0,
                    'no_nota_piutang' => $r->no_nota[$x]
                ];
                DB::table('bayar_telur')->insert($data);
            }


            DB::table('invoice_telur')->where('no_nota', $r->no_nota[$x])->update(['cek' => 'Y', 'admin_cek' => Auth::user()->name]);
        }



        return redirect()->route('penjualan_martadah_cek')->with('sukses', 'Data berhasil ditambahkan');
    }
}
