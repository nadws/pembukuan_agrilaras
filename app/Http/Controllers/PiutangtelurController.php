<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PiutangtelurController extends Controller
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
        $tgl1 =  $this->tgl1;
        $tgl2 =  $this->tgl2;

        if (empty($r->kategori)) {
            $kategori = 'All';
        } else {
            $kategori = $r->kategori;
        }

        if ($kategori == 'All') {
            $invoice = DB::select("SELECT a.no_nota, a.tgl, a.tipe, a.admin, b.nm_customer, sum(a.total_rp) as ttl_rp, a.status, c.paid , a.urutan_customer, c.bayar, a.customer, a.id_customer
            FROM invoice_telur as a 
            left join customer as b on b.id_customer = a.id_customer
            left join (
                SELECT c.no_nota, sum(c.kredit -  c.debit) as paid, sum(c.debit) as bayar
                FROM bayar_telur as c
                group by c.no_nota
            ) as c on c.no_nota = a.no_nota
            where  a.status = 'unpaid'
            group by a.no_nota
            order by a.urutan DESC
            ");
        } elseif ($kategori == 'Unpaid') {
            $invoice = DB::select("SELECT a.no_nota, a.tgl, a.tipe, a.admin, b.nm_customer, sum(a.total_rp) as ttl_rp, a.status, c.paid , a.urutan_customer, c.bayar, a.customer, a.id_customer
            FROM invoice_telur as a 
            left join customer as b on b.id_customer = a.id_customer
            left join (
                SELECT c.no_nota, sum(c.kredit -  c.debit) as paid, sum(c.debit) as bayar
                FROM bayar_telur as c
                group by c.no_nota
            ) as c on c.no_nota = a.no_nota
            where  a.status = 'unpaid' and c.paid != '0'
            group by a.no_nota
            order by a.urutan DESC
            ");
        } elseif ($kategori == 'Paid') {
            $invoice = DB::select("SELECT a.no_nota, a.tgl, a.tipe, a.admin, b.nm_customer, sum(a.total_rp) as ttl_rp, a.status, c.paid , a.urutan_customer, c.bayar, a.customer, a.id_customer
            FROM invoice_telur as a 
            left join customer as b on b.id_customer = a.id_customer
            left join (
                SELECT c.no_nota, sum(c.kredit -  c.debit) as paid, sum(c.debit) as bayar
                FROM bayar_telur as c
                group by c.no_nota
            ) as c on c.no_nota = a.no_nota
            where  a.status = 'unpaid' and c.paid = '0'
            group by a.no_nota
            order by a.urutan DESC
            ");
        }



        $data =  [
            'title' => 'Piutang Telur',
            'tgl1' => $tgl1,
            'tgl2' => $tgl2,
            'invoice' => $invoice,
            'kategori' => $kategori

        ];
        return view('piutang_agl.index', $data);
    }

    public function bayar_piutang_telur(Request $r)
    {
        $max = DB::table('bayar_telur')->latest('urutan_piutang')->first();

        if ($max->urutan_piutang == '0') {
            $nota_t = '1000';
        } else {
            $nota_t = $max->urutan_piutang + 1;
        }
        $data = [
            'title' => 'Bayar Piutang Telur',
            'no_nota' => $r->no_nota,
            'akun' => DB::table('akun')->whereIn('id_klasifikasi', ['1', '2'])->get(),
            'nota' => $nota_t
        ];
        return view('piutang_agl.bayar', $data);
    }
    public function save_bayar_piutang(Request $r)
    {
        try {
            DB::beginTransaction();

            $max = DB::table('bayar_telur')->latest('urutan_piutang')->first();
            $nota_t = empty($max) || $max->urutan_piutang == '0' ? '1000' : $max->urutan_piutang + 1;

            for ($x = 0; $x < count($r->no_nota); $x++) {
                $bayar = DB::table('bayar_telur')->where('no_nota', $r->no_nota[$x])->where('kredit', '!=', '0')->first();

                if ($bayar && strtotime($r->tgl) < strtotime($bayar->tgl)) {
                    throw new \Exception("Tanggal yang diinput tidak boleh kurang dari tanggal di nota.");
                }
                $data = [
                    'urutan_piutang' => $nota_t,
                    'no_nota_piutang' => 'PT' . $nota_t,
                    'tgl' => $r->tgl,
                    'no_nota' => $r->no_nota[$x],
                    'debit' => $r->pembayaran[$x],
                    'kredit' => '0',
                    'admin' => Auth::user()->name,
                    'piutang' => 'Y'
                ];
                DB::table('bayar_telur')->insert($data);
            }

            $max_akun = DB::table('jurnal')->latest('urutan')->where('id_akun', '23')->first();
            $akun = DB::table('akun')->where('id_akun', '23')->first();

            $urutan = empty($max_akun) ? '1001' : ($max_akun->urutan == 0 ? '1001' : $max_akun->urutan + 1);
            $data = [
                'tgl' => $r->tgl,
                'no_nota' => 'PT' . $nota_t,
                'id_akun' => '23',
                'id_buku' => '6',
                'ket' => 'Pelunasan piutang ' . $r->ket,
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
                    'no_nota' => 'PT' . $nota_t,
                    'id_akun' => $r->id_akun[$x],
                    'id_buku' => '6',
                    'ket' => 'Pelunasan piutang ' . $r->ket,
                    'debit' => $r->debit[$x],
                    'kredit' => $r->kredit[$x],
                    'admin' => Auth::user()->name,
                    'no_urut' => $akun2->inisial . '-' . $urutan2,
                    'urutan' => $urutan2,
                ];
                DB::table('jurnal')->insert($data);
            }

            DB::commit();
            return redirect()->route('piutang_telur')->with('sukses', 'Data berhasil ditambahkan');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('piutang_telur')->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function get_pembayaranpiutang_telur(Request $r)
    {
        $piutang = DB::select("SELECT a.tgl, a.no_nota_piutang, a.no_nota, a.debit, a.admin
        FROM bayar_telur as a
        where a.no_nota = '$r->no_nota' and a.debit != '0';");

        $data = [
            'piutang' => $piutang
        ];
        return view('piutang_agl.get_bayar', $data);
    }

    public function edit_piutang(Request $r)
    {
        $data = [
            'nota' => $r->no_nota,
            'title' => 'Edit Pembayaran Piutang',
            'head' => DB::table('bayar_telur')->where('no_nota_piutang', $r->no_nota)->first(),
            'invoice' => DB::select("SELECT a.no_nota_piutang, c.nm_customer, b.tgl, d.debit_total,  a.debit, b.total_rp,b.no_nota
            FROM bayar_telur as a 
            left join invoice_telur as b on b.no_nota = a.no_nota
            left join customer as c on c.id_customer = b.id_customer
            left join (
               SELECT d.no_nota, sum(d.debit) as debit_total
               FROM bayar_telur as d 
                group by d.no_nota
            ) as d on d.no_nota = a.no_nota
            where a.no_nota_piutang = '$r->no_nota';"),
            'akun' => DB::table('akun')->whereIn('id_klasifikasi', ['1', '2', '4'])->get(),
            'jurnal' => DB::select("SELECT * FROM jurnal as a where a.no_nota = '$r->no_nota' and a.id_akun != '23'"),
            'jurnal2' => DB::selectOne("SELECT * FROM jurnal as a where a.no_nota = '$r->no_nota' and a.id_akun = '23'"),
        ];
        return view('piutang_agl.edit_get_bayar', $data);
    }

    public function edit_bayar_piutang(Request $r)
    {
        DB::table('jurnal')->where('no_nota', $r->no_nota_piutang)->delete();
        DB::table('bayar_telur')->where('no_nota_piutang', $r->no_nota_piutang)->delete();

        for ($x = 0; $x < count($r->no_nota); $x++) {
            $data = [
                'urutan_piutang' => $r->urutan_piutang,
                'no_nota_piutang' => $r->no_nota_piutang,
                'tgl' => $r->tgl,
                'no_nota' => $r->no_nota[$x],
                'debit' => $r->pembayaran[$x],
                'kredit' => '0',
                'admin' => Auth::user()->name,
                'piutang' => 'Y'
            ];
            DB::table('bayar_telur')->insert($data);
        }
        $max_akun = DB::table('jurnal')->latest('urutan')->where('id_akun', '23')->first();
        $akun = DB::table('akun')->where('id_akun', '23')->first();

        $urutan = empty($max_akun) ? '1001' : ($max_akun->urutan == 0 ? '1001' : $max_akun->urutan + 1);
        $data = [
            'tgl' => $r->tgl,
            'no_nota' => $r->no_nota_piutang,
            'id_akun' => '23',
            'id_buku' => '6',
            'ket' => 'Pelunasan piutang ' . $r->ket,
            'debit' => 0,
            'kredit' => $r->total_penjualan,
            'admin' => Auth::user()->name,
            'no_urut' => $r->no_urut_penjualan,
            'urutan' => $r->urutan_penjualan,
        ];
        DB::table('jurnal')->insert($data);

        for ($x = 0; $x < count($r->id_akun); $x++) {
            $max_akun2 = DB::table('jurnal')->latest('urutan')->where('id_akun', $r->id_akun[$x])->first();
            $akun2 = DB::table('akun')->where('id_akun', $r->id_akun[$x])->first();
            $urutan2 = empty($max_akun2) ? '1001' : ($max_akun2->urutan == 0 ? '1001' : $max_akun2->urutan + 1);

            if ($r->id_akun2[$x] == $r->id_akun[$x]) {
                $uruan_jurnal = $r->urutan_jurnal[$x];
            } else {
                $uruan_jurnal = $urutan2;
            }
            $data = [
                'tgl' => $r->tgl,
                'no_nota' => $r->no_nota_piutang,
                'id_akun' => $r->id_akun[$x],
                'id_buku' => '6',
                'ket' => 'Pelunasan piutang ' . $r->ket,
                'debit' => $r->debit[$x],
                'kredit' => $r->kredit[$x],
                'admin' => Auth::user()->name,
                'no_urut' => $akun2->inisial . '-' . $uruan_jurnal,
                'urutan' => $uruan_jurnal,
            ];
            DB::table('jurnal')->insert($data);
        }
        return redirect()->route('piutang_telur')->with('sukses', 'Data berhasil ditambahkan');
    }
}
