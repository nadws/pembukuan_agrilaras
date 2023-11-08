<?php

namespace App\Http\Controllers;

use App\Models\Akun;
use App\Models\Jurnal;
use App\Models\Produk;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use SettingHal;

class PenjualanUmumController extends Controller
{
    protected $produk;
    public $akunPenjualan = '84';
    public $akunPiutangDagang = '12';

    protected $tgl1, $tgl2, $id_proyek, $period, $id_buku;

    public function __construct(Request $r)
    {
        $this->produk = Produk::with('satuan')->where([['kontrol_stok', 'Y'], ['kategori_id', 3]])->get();

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

    public function index()
    {
        $tgl1 = $this->tgl1;
        $tgl2 = $this->tgl2;
        $id_user = auth()->user()->id;
        $penjualan = DB::select("SELECT *, sum(a.total_rp) as total, count(*) as ttl_produk  FROM `penjualan_agl` as a
        LEFT JOIN customer as b ON a.id_customer = b.id_customer
        WHERE a.tgl BETWEEN '$tgl1' AND '$tgl2' AND a.lokasi = 'alpa' 
        GROUP BY a.urutan ORDER BY a.urutan DESC");



        $data = [
            'title' => 'Penjualan Umum',
            'penjualan' => $penjualan,
            'tgl1' => $tgl1,
            'tgl2' => $tgl2,

            'user' => User::where('posisi_id', 1)->get(),
            'halaman' => 17,
            'create' => SettingHal::btnHal(71, $id_user),
            'edit' => SettingHal::btnHal(74, $id_user),
            'detail' => SettingHal::btnHal(75, $id_user),
            'delete' => SettingHal::btnHal(76, $id_user),
        ];
        return view('penjualan2.penjualan', $data);
    }

    public function add()
    {
        $nota = buatNota('penjualan_agl', 'urutan');
        $data = [
            'title' => 'Tambah Penjualan Umum',
            'customer' => DB::table('customer')->get(),
            'produk' => $this->produk,
            'akun' => DB::table('akun')->whereIn('id_klasifikasi', ['1', '7', '2'])->get(),
            'satuan' => DB::table('tb_satuan')->get(),
            'no_nota' => $nota
        ];
        return view('penjualan2.add', $data);
    }

    public function selectPelanggan()
    {
        $selectOptions = "<select required name='id_customer' class='form-control select2-pelanggan' id=''>";
        $selectOptions .= "
        <option value=''>- Pilih Customer -</option>
        <option value='tambah'>+ Customer</option>
        ";

        $customer = DB::table('customer')->get(); // Ganti dengan model dan cara mengambil data customer yang sesuai
        foreach ($customer as $d) {
            $selectOptions .= "<option value='{$d->id_customer}'>{$d->nm_customer}</option>";
        }

        $selectOptions .= "</select>";

        return $selectOptions;
    }

    public function tbhCustomer(Request $r)
    {
        DB::table('customer')->insert([
            'nm_customer' => $r->nama
        ]);
        return 'berhasil';
    }

    public function selectProduk()
    {
        $selectOptions = "<select required name='id_produk[]' class='form-control select2-produk produk-change' id=''>";
        $selectOptions .= "
        <option value=''>- Pilih Produk -</option>
        <option value='tambah'>+ Produk</option>
        ";

        $produk = $this->produk; // Ganti dengan model dan cara mengambil data produk yang sesuai
        foreach ($produk as $d) {
            $selectOptions .= "<option value='{$d->id_produk}'>{$d->nm_produk} (".strtoupper($d->satuan->nm_satuan).")</option>";
        }

        $selectOptions .= "</select>";

        return $selectOptions;
    }

    public function tbhProduk(Request $r)
    {
        DB::table('tb_produk')->insert([
            'kd_produk' => 1,
            'nm_produk' => $r->nama,
            'satuan_id' => $r->id_satuan,
            'kontrol_stok' => 'Y',
            'departemen_id' => 1,
            'kategori_id' => 3,
            'admin' => auth()->user()->name,
            'tgl' => date('Y-m-d')
        ]);
    }

    public function tbh_add(Request $r)
    {
        $data = [
            'count' => $r->count,
            'produk' => $this->produk,
        ];
        return view('penjualan2.tbh_add', $data);
    }

    public function tbh_pembayaran(Request $r)
    {
        $data = [
            'count' => $r->count,
            'akun' => Akun::all()
        ];
        return view('penjualan2.tbh_pembayaran', $data);
    }

    public function store(Request $r)
    {
        $produkNames = DB::table('tb_produk')->whereIn('id_produk', $r->id_produk)->pluck('nm_produk');
        $produkNames = implode(', ', $produkNames->toArray());
        $nm_customer = DB::table('customer')->where('id_customer', $r->id_customer)->first()->nm_customer;
        $ttlDebit = 0;

        for ($i = 0; $i < count($r->akun_pembayaran); $i++) {
            $ttlDebit += $r->debit[$i] ?? 0 - $r->kredit[$i] ?? 0;
        }
        $max_akun2 = DB::table('jurnal')->latest('urutan')->where('id_akun', $this->akunPenjualan)->first();
        $akun2 = DB::table('akun')->where('id_akun', $this->akunPenjualan)->first();
        $urutan2 = empty($max_akun2) ? '1001' : ($max_akun2->urutan == 0 ? '1001' : $max_akun2->urutan + 1);


        $dataK = [
            'tgl' => $r->tgl,
            'no_nota' => 'PUM-' . $r->no_nota,
            'id_akun' => $this->akunPenjualan,
            'id_buku' => '6',
            'ket' => 'Penjualan Umum Alpa : '.$produkNames,
            'no_urut' => $akun2->inisial . '-' . $urutan2,
            'urutan' => $urutan2,
            'kredit' => $ttlDebit,
            'debit' => 0,
            'admin' => auth()->user()->name,
        ];
        $penjualan = Jurnal::create($dataK);

        for ($i = 0; $i < count($r->akun_pembayaran); $i++) {
            $id_akun = $r->akun_pembayaran[$i];

            $max_akun = DB::table('jurnal')->latest('urutan')->where('id_akun', $id_akun)->first();
            $akun = DB::table('akun')->where('id_akun', $id_akun)->first();
            $urutan = empty($max_akun) ? '1001' : ($max_akun->urutan == 0 ? '1001' : $max_akun->urutan + 1);

            Jurnal::create([
                'tgl' => $r->tgl,
                'id_akun' => $id_akun,
                'id_buku' => 6,
                'no_nota' => 'PUM-' . $r->no_nota,
                'ket' => "Penjualan $nm_customer $produkNames",
                'debit' => $r->debit[$i] ?? 0,
                'kredit' => $r->kredit[$i] ?? 0,
                'no_urut' => $akun->inisial . '-' . $urutan,
                'urutan' => $urutan2,
                'admin' => auth()->user()->name,
            ]);


            if ($akun->id_klasifikasi == '7') {
            } else {
                $data = [
                    'tgl' => $r->tgl,
                    'no_nota' => $r->no_nota,
                    'debit' => $r->debit[$i],
                    'kredit' => $r->kredit[$i],
                    'no_nota_piutang' => 'PUM-' . $r->no_nota
                ];
                DB::table('bayar_umum')->insert($data);
            }
        }
        $data = [
            'tgl' => $r->tgl,
            'no_nota' => $r->no_nota,
            'debit' => 0,
            'kredit' => $ttlDebit,
        ];
        DB::table('bayar_umum')->insert($data);

        for ($i = 0; $i < count($r->id_produk); $i++) {
            DB::table('penjualan_agl')->insert([
                'urutan' => $r->no_nota,
                'nota_manual' => $r->nota_manual,
                'tgl' => $r->tgl,
                'kode' => 'PUM',
                'id_customer' => $r->id_customer,
                'driver' => $r->driver,
                'id_produk' => $r->id_produk[$i],
                'qty' => $r->qty[$i],
                'rp_satuan' => $r->rp_satuan[$i],
                'total_rp' => $r->total_rp[$i],
                'ket' => $r->ket,
                'lokasi' => 'alpa',
                'id_jurnal' => $penjualan->id,
                'admin' => auth()->user()->name
            ]);
        }

        return redirect()->route('penjualan2.index')->with('sukses', 'Data Berhasil Ditambahkan');
    }

    public function edit(Request $r)
    {
        $penjualan = DB::selectOne("SELECT *, sum(a.total_rp) as total, count(*) as ttl_produk  FROM `penjualan_agl` as a
        LEFT JOIN customer as b ON a.id_customer = b.id_customer
        WHERE a.urutan = '$r->urutan' ");
        $data = [
            'title' => 'Edit Penjualan Umum',
            'customer' => DB::table('customer')->get(),
            'produk' => $this->produk,
            'getProduk' => DB::table('penjualan_agl as a')
                ->join('tb_produk as b', 'a.id_produk', 'b.id_produk')
                ->where('urutan', $r->urutan)
                ->get(),
            'getPenjualan' => $penjualan,
            'getPembayaran' => DB::table('jurnal')->where([['no_nota', 'PUM-' . $r->urutan], ['id_akun', '!=', $this->akunPenjualan]])->get(),
            'akun' => Akun::all(),
            'no_nota' => $penjualan->urutan
        ];
        return view('penjualan2.edit', $data);
    }

    public function update(Request $r)
    {

        DB::table('jurnal')->where('no_nota', 'PUM-' . $r->no_nota)->delete();
        DB::table('penjualan_agl')->where('urutan', $r->no_nota)->delete();
        DB::table('bayar_umum')->where('no_nota', $r->no_nota)->delete();


        $max_akun2 = DB::table('jurnal')->latest('urutan')->where('id_akun', $this->akunPenjualan)->first();
        $akun2 = DB::table('akun')->where('id_akun', $this->akunPenjualan)->first();
        $urutan2 = empty($max_akun2) ? '1001' : ($max_akun2->urutan == 0 ? '1001' : $max_akun2->urutan + 1);

        $ttlDebit = 0;

        for ($i = 0; $i < count($r->akun_pembayaran); $i++) {
            $ttlDebit += $r->debit[$i] ?? 0 - $r->kredit[$i] ?? 0;
        }

        $dataK = [
            'tgl' => $r->tgl,
            'no_nota' => 'PUM-' . $r->no_nota,
            'id_akun' => $this->akunPenjualan,
            'ket' => 'PUM-' . $r->no_nota,
            'no_urut' => $akun2->inisial . '-' . $urutan2,
            'urutan' => $urutan2,
            'kredit' => $ttlDebit,
            'debit' => 0,
            'id_buku' => '6',
            'admin' => auth()->user()->name,
        ];
        $penjualan = Jurnal::create($dataK);

        $data = [
            'tgl' => $r->tgl,
            'no_nota' => $r->no_nota,
            'debit' => 0,
            'kredit' => $ttlDebit,
        ];
        DB::table('bayar_umum')->insert($data);

        for ($i = 0; $i < count($r->akun_pembayaran); $i++) {
            $ttlDebit += $r->debit[$i] ?? 0 - $r->kredit[$i] ?? 0;

            $id_akun = $r->akun_pembayaran[$i];

            $max_akun = DB::table('jurnal')->latest('urutan')->where('id_akun', $id_akun)->first();
            $akun = DB::table('akun')->where('id_akun', $id_akun)->first();
            $urutan = empty($max_akun) ? '1001' : ($max_akun->urutan == 0 ? '1001' : $max_akun->urutan + 1);

            Jurnal::create([
                'tgl' => $r->tgl,
                'id_akun' => $id_akun,
                'id_buku' => '6',
                'no_nota' => 'PUM-' . $r->no_nota,
                'ket' => 'PUM-' . $r->no_nota,
                'debit' => $r->debit[$i] ?? 0,
                'kredit' => $r->kredit[$i] ?? 0,
                'no_urut' => $akun->inisial . '-' . $urutan,
                'urutan' => $urutan,
                'admin' => auth()->user()->name,
            ]);
            if ($id_akun == $this->akunPiutangDagang) {
                DB::table('invoice_agl')->insert([
                    'no_penjualan' => $r->nota_manual,
                    'no_nota' => 'PUM-' . $r->no_nota,
                    'tgl' => $r->tgl,
                    'ket' => $r->ket,
                    'total_rp' => $ttlDebit,
                    'status' => 'unpaid',
                    'admin' => auth()->user()->name
                ]);
            }
            if ($akun->id_klasifikasi == '7') {
            } else {
                $data = [
                    'tgl' => $r->tgl,
                    'no_nota' => $r->no_nota,
                    'debit' => $r->debit[$i],
                    'kredit' => $r->kredit[$i],
                    'no_nota_piutang' => 'PUM-' . $r->no_nota
                ];
                DB::table('bayar_umum')->insert($data);
            }
        }



        for ($i = 0; $i < count($r->id_produk); $i++) {
            DB::table('penjualan_agl')->insert([
                'urutan' => $r->no_nota,
                'nota_manual' => $r->nota_manual,
                'tgl' => $r->tgl,
                'kode' => 'PUM',
                'id_customer' => $r->id_customer,
                'driver' => $r->driver,
                'id_produk' => $r->id_produk[$i],
                'qty' => $r->qty[$i],
                'rp_satuan' => $r->rp_satuan[$i],
                'total_rp' => $r->total_rp[$i],
                'ket' => $r->ket,
                'id_jurnal' => $penjualan->id,
                'admin' => auth()->user()->name,
                'lokasi' => 'alpa',
            ]);
        }

        return redirect()->route('penjualan2.index')->with('sukses', 'Data Berhasil Ditambahkan');
    }

    public function detail($no_nota)
    {
        $penjualan = DB::selectOne("SELECT *,a.id_customer as nm_customer, sum(a.total_rp) as total, count(*) as ttl_produk  FROM `penjualan_agl` as a
        LEFT JOIN customer as b ON a.id_customer = b.id_customer
        WHERE a.urutan = '$no_nota' ");
        $data = [
            'title' => 'Detail Penjaulan Umum',
            'head_jurnal' => $penjualan,
            'produk' => DB::table('penjualan_agl as a')
                ->select('b.nm_produk', 'a.qty', 'a.rp_satuan', 'a.total_rp', 'a.admin')
                ->join('tb_produk as b', 'a.id_produk', 'b.id_produk')
                ->where('urutan', $no_nota)
                ->get()
        ];
        return view('penjualan2.detail', $data);
    }

    public function print(Request $r)
    {
        $penjualan = DB::selectOne("SELECT *, sum(a.total_rp) as total, count(*) as ttl_produk  FROM `penjualan_agl` as a
        LEFT JOIN customer as b ON a.id_customer = b.id_customer
        WHERE a.urutan = '$r->urutan' ");
        $data = [
            'title' => 'Cetak Penjaulan Umum',
            'detail' => $penjualan,
            'produk' => DB::table('penjualan_agl as a')
                ->join('tb_produk as b', 'a.id_produk', 'b.id_produk')
                ->where('urutan', $r->urutan)
                ->get()
        ];
        return view('penjualan2.print', $data);
    }

    public function delete(Request $r)
    {
        DB::table('tb_stok_produk')->where('no_nota', 'PAGL-' . $r->urutan)->delete();
        DB::table('jurnal')->where('no_nota', 'PAGL-' . $r->urutan)->delete();
        DB::table('penjualan_agl')->where('urutan', $r->urutan)->delete();

        return redirect()->route('penjualan2.index', ['period' => 'costume', 'tgl1' => $r->tgl1, 'tgl2' => $r->tgl2, 'id_proyek' => 0])->with('sukses', 'Data Berhasil Dihapus');
    }

    public function piutang(Request $r)
    {
        if (empty($r->tgl1)) {
            $tgl1 = date('Y-m-01');
            $tgl2 = date('Y-m-t');
        } else {
            $tgl1 = $r->tgl1;
            $tgl2 = $r->tgl2;
        }

        $data = [
            'title' => 'Piutang Penjualan Umum',
            'invoice_umum' => DB::select("SELECT a.tgl, a.kode, a.urutan, GROUP_CONCAT(b.nm_produk) AS nm_produk_concat, sum(a.qty) as qty, sum(a.total_rp) as ttl_rp, c.total_bayar, d.nm_customer, a.id_customer, a.lokasi
            FROM penjualan_agl AS a
            LEFT JOIN tb_produk AS b ON a.id_produk = b.id_produk
            left join (
                SELECT c.no_nota, sum(c.kredit - c.debit) as total_bayar
                FROM bayar_umum as c 
                GROUP by c.no_nota
            ) as c on c.no_nota = a.urutan 
            
            left JOIN customer as d on d.id_customer = a.id_customer
            where a.lokasi in('alpa','mtd') and  c.total_bayar != 0
            GROUP BY a.urutan;
            "),
            'customer' => DB::table('customer')->get(),
            'tgl1' => $tgl1,
            'tgl2' => $tgl2,
            'stok_ayam_bjm' => DB::selectOne("SELECT sum(a.debit - a.kredit) as saldo_bjm FROM stok_ayam as a where a.id_gudang = '2' and a.jenis = 'ayam'"),
            'akun' => DB::table('akun')->whereIn('id_klasifikasi', ['1', '2'])->get(),
        ];
        return view("penjualan2.piutang", $data);
    }

    public function bayar_piutang_umum(Request $r)
    {
        $max = DB::table('bayar_umum')->latest('urutan_piutang')->first();

        if ($max->urutan_piutang == '0') {
            $nota_t = '1000';
        } else {
            $nota_t = $max->urutan_piutang + 1;
        }
        $data = [
            'title' => 'Bayar Piutang Umum',
            'no_nota' => $r->no_nota,
            'akun' => DB::table('akun')->whereIn('id_klasifikasi', ['1', '2'])->get(),
            'nota' => $nota_t
        ];
        return view('penjualan2.bayar', $data);
    }

    public function save_bayar_piutang(Request $r)
    {
        $max = DB::table('bayar_umum')->latest('urutan_piutang')->first();

        if ($max->urutan_piutang == '0') {
            $nota_t = '1000';
        } else {
            $nota_t = $max->urutan_piutang + 1;
        }

        for ($x = 0; $x < count($r->no_nota); $x++) {
            $data = [
                'urutan_piutang' => $nota_t,
                'no_nota_piutang' => 'PIUM' . $nota_t,
                'tgl' => $r->tgl,
                'no_nota' => $r->urutan[$x],
                'debit' => $r->pembayaran[$x],
                'kredit' => '0',
                'admin' => Auth::user()->name,
            ];
            DB::table('bayar_umum')->insert($data);
        }
        $max_akun = DB::table('jurnal')->latest('urutan')->where('id_akun', '99')->first();
        $akun = DB::table('akun')->where('id_akun', '99')->first();

        $urutan = empty($max_akun) ? '1001' : ($max_akun->urutan == 0 ? '1001' : $max_akun->urutan + 1);
        $data = [
            'tgl' => $r->tgl,
            'no_nota' => 'PIUM' . $nota_t,
            'id_akun' => '99',
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
                'no_nota' => 'PIUM' . $nota_t,
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
        return redirect()->route('penjualan2.piutang')->with('sukses', 'Data berhasil ditambahkan');
    }
}
