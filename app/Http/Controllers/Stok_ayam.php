<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Stok_ayam extends Controller
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
        $data = [
            'stok_ayam' => DB::selectOne("SELECT sum(a.debit - a.kredit) as saldo_kandang FROM stok_ayam as a where a.id_gudang = '1' and a.jenis = 'ayam'"),
            'stok_ayam_bjm' => DB::selectOne("SELECT sum(a.debit - a.kredit) as saldo_bjm FROM stok_ayam as a where a.id_gudang = '2' and a.jenis = 'ayam'"),
            'customer' => DB::table('customer')->get(),
            'history_ayam' => DB::table('stok_ayam')->where('jenis', 'ayam')->where('id_gudang', '2')->get(),
            'akun' => DB::table('akun')->whereIn('id_klasifikasi', ['1', '2', '7'])->get(),
            'kandang' => DB::table('kandang')->where('selesai', 'T')->orderBy('nm_kandang', 'ASC')->get()
        ];
        return view("Stok_ayam.index", $data);
    }

    public function save_penjualan_ayam(Request $r)
    {
        $max = DB::table('invoice_ayam')->latest('urutan')->where('lokasi', 'alpa')->first();
        if (empty($max)) {
            $nota_t = '1000';
        } else {
            $nota_t = $max->urutan + 1;
        }
        $customer = DB::table('customer')->where('id_customer', $r->customer)->first();
        $urutan = empty($max_akun) ? '1001' : ($max_akun->urutan == 0 ? '1001' : $max_akun->urutan + 1);

        $max_customer = DB::table('invoice_ayam')->latest('urutan_customer')->where('id_customer', $r->customer)->first();

        if (empty($max_customer)) {
            $urutan_cus = '1';
        } else {
            $urutan_cus = $max_customer->urutan_customer + 1;
        }

        $data = [
            'tgl' => $r->tgl,
            'debit' => 0,
            'kredit' => $r->qty,
            'id_gudang' => '2',
            'admin' =>  auth()->user()->name,
            'jenis' => 'ayam',
            'no_nota' => 'PA-' . $nota_t,
            'transfer' => 'Y'
        ];
        DB::table('stok_ayam')->insert($data);

        $max = DB::table('invoice_ayam')->latest('urutan')->first();
        if (empty($max)) {
            $nota_t = '1000';
        } else {
            $nota_t = $max->urutan + 1;
        }
        $data = [
            'tgl' => $r->tgl,
            'no_nota' => 'PA-' . $nota_t,
            'id_customer' => $r->customer,
            'id_customer2' => $r->id_customer2,
            'qty' => $r->qty,
            'h_satuan' => $r->h_satuan,
            'admin' =>  auth()->user()->name,
            'urutan' =>  $nota_t,
            'urutan_customer' => $urutan_cus,
            'lokasi' => 'alpa',
            'id_kandang' => $r->id_kandang
        ];
        DB::table('invoice_ayam')->insert($data);
        $nota =  'PA-' . $nota_t;
        $max_customer = DB::table('invoice_ayam')->latest('urutan_customer')->where('id_customer', $r->customer)->first();

        if (empty($max_customer)) {
            $urutan_cus = '1';
        } else {
            $urutan_cus = $max_customer->urutan_customer + 1;
        }
        $akun = DB::table('akun')->where('id_akun', '37')->first();

        $data = [
            'tgl' => $r->tgl,
            'no_nota' => 'PA-' . $nota_t,
            'id_akun' => '37',
            'id_buku' => '6',
            'ket' => 'Penjualan Ayam ' . $customer->nm_customer . $urutan_cus,
            'debit' => 0,
            'kredit' => $r->ttl_rp,
            'admin' => Auth::user()->name,
            'no_urut' => $akun->inisial . '-' . $urutan,
            'urutan' => $urutan,
        ];
        DB::table('jurnal')->insert($data);

        $data = [
            'tgl' => $r->tgl,
            'no_nota' => 'PA-' . $nota_t,
            'debit' => '0',
            'kredit' => $r->ttl_rp,
            'no_nota_piutang' => 'PA-' . $nota_t,
            'admin' => Auth::user()->name,
        ];
        DB::table('bayar_ayam')->insert($data);

        for ($x = 0; $x < count($r->id_akun); $x++) {
            $max_akun2 = DB::table('jurnal')->latest('urutan')->where('id_akun', $r->id_akun[$x])->first();
            $akun2 = DB::table('akun')->where('id_akun', $r->id_akun[$x])->first();
            $urutan2 = empty($max_akun2) ? '1001' : ($max_akun2->urutan == 0 ? '1001' : $max_akun2->urutan + 1);
            $data = [
                'tgl' => $r->tgl,
                'no_nota' => 'PA-' . $nota_t,
                'id_akun' => $r->id_akun[$x],
                'id_buku' => '6',
                'ket' => 'Penjualan Ayam ' . $customer->nm_customer . $urutan_cus,
                'debit' => $r->debit[$x],
                'kredit' => $r->kredit[$x],
                'admin' => Auth::user()->name,
                'no_urut' => $akun2->inisial . '-' . $urutan2,
                'urutan' => $urutan2,
            ];
            DB::table('jurnal')->insert($data);



            if ($akun2->id_akun == '66') {
                // $nota = 'PA' . $nota_t;
                // DB::table('invoice_ayam')->where('no_nota', $nota)->update(['status' => 'unpaid']);
            } else {
                $data = [
                    'tgl' => $r->tgl,
                    'no_nota' => 'PA-' . $nota_t,
                    'debit' => $r->debit[$x],
                    'kredit' => $r->kredit[$x],
                    'no_nota_piutang' => 'PA-' . $nota_t,
                    'admin' => Auth::user()->name,
                ];
                DB::table('bayar_ayam')->insert($data);
            }
        }
        return redirect()->route('history_ayam')->with('sukses', 'Data Berhasil Ditambahkan');
    }

    public function edit_save_penjualan_ayam(Request $r)
    {

        DB::table('stok_ayam')->where('no_nota', $r->no_nota)->delete();
        DB::table('invoice_ayam')->where('no_nota', $r->no_nota)->delete();
        DB::table('jurnal')->where('no_nota', $r->no_nota)->delete();
        DB::table('bayar_ayam')->where('no_nota', $r->no_nota)->delete();

        $max = DB::table('invoice_ayam')->latest('urutan')->where('lokasi', 'alpa')->first();
        if (empty($max)) {
            $nota_t = '1000';
        } else {
            $nota_t = $max->urutan + 1;
        }
        $customer = DB::table('customer')->where('id_customer', $r->customer)->first();
        $urutan = empty($max_akun) ? '1001' : ($max_akun->urutan == 0 ? '1001' : $max_akun->urutan + 1);

        $max_customer = DB::table('invoice_ayam')->latest('urutan_customer')->where('id_customer', $r->customer)->first();

        if (empty($max_customer)) {
            $urutan_cus = '1';
        } else {
            $urutan_cus = $max_customer->urutan_customer + 1;
        }

        $data = [
            'tgl' => $r->tgl,
            'debit' => 0,
            'kredit' => $r->qty,
            'id_gudang' => '2',
            'admin' =>  auth()->user()->name,
            'jenis' => 'ayam',
            'no_nota' => $r->no_nota,
            'transfer' => 'Y'
        ];
        DB::table('stok_ayam')->insert($data);

        $max = DB::table('invoice_ayam')->latest('urutan')->first();
        if (empty($max)) {
            $nota_t = '1000';
        } else {
            $nota_t = $max->urutan + 1;
        }
        $data = [
            'tgl' => $r->tgl,
            'no_nota' => $r->no_nota,
            'id_customer' => $r->customer,
            'id_customer2' => $r->id_customer2,
            'qty' => $r->qty,
            'h_satuan' => $r->h_satuan,
            'admin' =>  auth()->user()->name,
            'urutan' =>  $r->urutan,
            'urutan_customer' => $r->urutan_customer,
            'lokasi' => 'alpa'
        ];
        DB::table('invoice_ayam')->insert($data);
        $nota =  $r->no_nota;
        $max_customer = DB::table('invoice_ayam')->latest('urutan_customer')->where('id_customer', $r->customer)->first();

        if (empty($max_customer)) {
            $urutan_cus = '1';
        } else {
            $urutan_cus = $max_customer->urutan_customer + 1;
        }
        $akun = DB::table('akun')->where('id_akun', '37')->first();

        $data = [
            'tgl' => $r->tgl,
            'no_nota' => $r->no_nota,
            'id_akun' => '37',
            'id_buku' => '6',
            'ket' => 'Penjualan Ayam ' . $customer->nm_customer . $urutan_cus,
            'debit' => 0,
            'kredit' => $r->ttl_rp,
            'admin' => Auth::user()->name,
            'no_urut' => $akun->inisial . '-' . $urutan,
            'urutan' => $urutan,
        ];
        DB::table('jurnal')->insert($data);

        $data = [
            'tgl' => $r->tgl,
            'no_nota' => $r->no_nota,
            'debit' => '0',
            'kredit' => $r->ttl_rp,
            'no_nota_piutang' => $r->no_nota,
            'admin' => Auth::user()->name,
        ];
        DB::table('bayar_ayam')->insert($data);

        for ($x = 0; $x < count($r->id_akun); $x++) {
            $max_akun2 = DB::table('jurnal')->latest('urutan')->where('id_akun', $r->id_akun[$x])->first();
            $akun2 = DB::table('akun')->where('id_akun', $r->id_akun[$x])->first();
            $urutan2 = empty($max_akun2) ? '1001' : ($max_akun2->urutan == 0 ? '1001' : $max_akun2->urutan + 1);
            $data = [
                'tgl' => $r->tgl,
                'no_nota' => $r->no_nota,
                'id_akun' => $r->id_akun[$x],
                'id_buku' => '6',
                'ket' => 'Penjualan Ayam ' . $customer->nm_customer . $urutan_cus,
                'debit' => $r->debit[$x],
                'kredit' => $r->kredit[$x],
                'admin' => Auth::user()->name,
                'no_urut' => $akun2->inisial . '-' . $urutan2,
                'urutan' => $urutan2,
            ];
            DB::table('jurnal')->insert($data);



            if ($akun2->id_akun == '66') {
                // $nota = 'PA' . $nota_t;
                // DB::table('invoice_ayam')->where('no_nota', $nota)->update(['status' => 'unpaid']);
            } else {
                $data = [
                    'tgl' => $r->tgl,
                    'no_nota' => $r->no_nota,
                    'debit' => $r->debit[$x],
                    'kredit' => $r->kredit[$x],
                    'no_nota_piutang' => $r->no_nota,
                    'admin' => Auth::user()->name,
                ];
                DB::table('bayar_ayam')->insert($data);
            }
        }
        return redirect()->route('history_ayam')->with('sukses', 'Data Berhasil Ditambahkan');
    }

    function edit_ayam(Request $r)
    {
        $invoice =  DB::table('invoice_ayam')->where('no_nota', $r->no_nota)->first();
        $jurnal = DB::table('jurnal')->where('no_nota', $r->no_nota)->where('id_akun', '!=', '37')->get();

        $data = [
            'invoice' => $invoice,
            'no_nota' => $r->no_nota,
            'jurnal' => $jurnal,
            'akun' => DB::table('akun')->whereIn('id_klasifikasi', ['1', '2', '7'])->get(),
            'customer' => DB::table('customer')->get(),
            'stok_ayam' => DB::selectOne("SELECT sum(a.debit - a.kredit) as saldo_kandang FROM stok_ayam as a where a.id_gudang = '1' and a.jenis = 'ayam'"),
            'stok_ayam_bjm' => DB::selectOne("SELECT sum(a.debit - a.kredit) as saldo_bjm FROM stok_ayam as a where a.id_gudang = '2' and a.jenis = 'ayam'"),
        ];
        return view("Stok_ayam.edit", $data);
    }

    public function history_ayam(Request $r)
    {
        $tgl1 =  $this->tgl1;
        $tgl2 =  $this->tgl2;

        $data = [
            'title' => 'History Penjualan ayam',
            'invoice_ayam' => DB::select("SELECT a.*, b.* , c.total_bayar
            FROM invoice_ayam as a 
            left join customer as b on b.id_customer = a.id_customer 
            left join (
                SELECT c.no_nota, sum(c.kredit - c.debit) as total_bayar
                FROM bayar_ayam as c 
                group by c.no_nota
            ) as c on c.no_nota = a.no_nota
            where a.lokasi = 'alpa' and a.tgl between '$tgl1' and '$tgl2'
            order by a.no_nota DESC
            "),
            'customer' => DB::table('customer')->get(),
            'tgl1' => $tgl1,
            'tgl2' => $tgl2,
            'stok_ayam_bjm' => DB::selectOne("SELECT sum(a.debit - a.kredit) as saldo_bjm FROM stok_ayam as a where a.id_gudang = '2' and a.jenis = 'ayam'"),
            'akun' => DB::table('akun')->whereIn('id_klasifikasi', ['1', '2'])->get(),
        ];
        return view("Stok_ayam.history", $data);
    }
    public function piutang_ayam(Request $r)
    {
        if (empty($r->tgl1)) {
            $tgl1 = date('Y-m-01');
            $tgl2 = date('Y-m-t');
        } else {
            $tgl1 = $r->tgl1;
            $tgl2 = $r->tgl2;
        }

        $data = [
            'title' => 'Piutang Penjualan ayam',
            'invoice_ayam' => DB::select("SELECT a.*, b.* , c.total_bayar
            FROM invoice_ayam as a 
            left join customer as b on b.id_customer = a.id_customer 
            left join (
                SELECT c.no_nota, sum(c.kredit - c.debit) as total_bayar
                FROM bayar_ayam as c 
                group by c.no_nota
            ) as c on c.no_nota = a.no_nota
            where a.lokasi in('alpa','mtd')  and c.total_bayar != 0
            order by a.no_nota DESC
            "),
            'customer' => DB::table('customer')->get(),
            'tgl1' => $tgl1,
            'tgl2' => $tgl2,
            'stok_ayam_bjm' => DB::selectOne("SELECT sum(a.debit - a.kredit) as saldo_bjm FROM stok_ayam as a where a.id_gudang = '2' and a.jenis = 'ayam'"),
            'akun' => DB::table('akun')->whereIn('id_klasifikasi', ['1', '2', '7'])->get(),
        ];
        return view("Stok_ayam.piutang_ayam", $data);
    }

    public function bayar_piutang(Request $r)
    {
        $max = DB::table('bayar_ayam')->latest('urutan_piutang')->first();

        if ($max->urutan_piutang == '0') {
            $nota_t = '1000';
        } else {
            $nota_t = $max->urutan_piutang + 1;
        }
        $data = [
            'title' => 'Bayar Piutang Telur',
            'no_nota' => $r->no_nota,
            'akun' => DB::table('akun')->whereIn('id_klasifikasi', ['1', '2', '7'])->get(),
            'nota' => $nota_t
        ];
        return view('Stok_ayam.bayar', $data);
    }

    public function hapus_ayam(Request $r)
    {
        DB::table('invoice_ayam')->where('no_nota', $r->no_nota)->delete();
        DB::table('jurnal')->where('no_nota', $r->no_nota)->delete();
        DB::table('stok_ayam')->where('no_nota', $r->no_nota)->delete();
        return redirect()->route('history_ayam', ['tgl1' => $r->tgl1, 'tgl2' => $r->tgl2])->with('sukses', 'Data Berhasil Ditambahkan');
    }

    public function save_bayar_piutang(Request $r)
    {
        $max = DB::table('bayar_ayam')->latest('urutan_piutang')->first();

        if ($max->urutan_piutang == '0') {
            $nota_t = '1000';
        } else {
            $nota_t = $max->urutan_piutang + 1;
        }

        for ($x = 0; $x < count($r->no_nota); $x++) {
            $data = [
                'urutan_piutang' => $nota_t,
                'no_nota_piutang' => 'PIA' . $nota_t,
                'tgl' => $r->tgl,
                'no_nota' => $r->no_nota[$x],
                'debit' => $r->pembayaran[$x],
                'kredit' => '0',
                'admin' => Auth::user()->name,
            ];
            DB::table('bayar_ayam')->insert($data);
        }
        $max_akun = DB::table('jurnal')->latest('urutan')->where('id_akun', '66')->first();
        $akun = DB::table('akun')->where('id_akun', '66')->first();

        $urutan = empty($max_akun) ? '1001' : ($max_akun->urutan == 0 ? '1001' : $max_akun->urutan + 1);
        $data = [
            'tgl' => $r->tgl,
            'no_nota' => 'PIA' . $nota_t,
            'id_akun' => '66',
            'id_buku' => '6',
            'ket' => 'Pelunasan piutang ayam ' . $r->ket,
            'debit' => 0,
            'kredit' => $r->total_penjualan,
            'admin' => Auth::user()->name,
            'no_urut' => $akun->inisial . '-' . $urutan,
            'urutan' => $urutan,
        ];
        DB::table('jurnal')->insert($data);

        for ($x = 0; $x < count($r->id_akun); $x++) {
            $max_akun2 = DB::table('jurnal')->latest('urutan')->where('id_akun', $r->id_akun[$x])->first();
            $akun2 = DB::table('akun')->where('id_akun', $r->id_akun[$x])->first();
            $urutan2 = empty($max_akun2) ? '1001' : ($max_akun2->urutan == 0 ? '1001' : $max_akun2->urutan + 1);
            $data = [
                'tgl' => $r->tgl,
                'no_nota' => 'PIA' . $nota_t,
                'id_akun' => $r->id_akun[$x],
                'id_buku' => '6',
                'ket' => 'Pelunasan piutang ayam ' . $r->ket,
                'debit' => $r->debit[$x],
                'kredit' => $r->kredit[$x],
                'admin' => Auth::user()->name,
                'no_urut' => $akun2->inisial . '-' . $urutan2,
                'urutan' => $urutan2,
            ];
            DB::table('jurnal')->insert($data);
        }
        return redirect()->route('piutang_ayam')->with('sukses', 'Data berhasil ditambahkan');
    }
}
