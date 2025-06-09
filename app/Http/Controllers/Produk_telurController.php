<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Exports\TelurExport;
use App\Models\AkunAccurate;
use App\Models\CashIbuModel;
use App\Models\JurnalAccurate;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;

class Produk_telurController extends Controller
{
    public function index(Request $r)
    {
        $id_gudang = $r->id_gudang ?? 1;
        $tgl = date('Y-m-d');

        $tanggal = $r->tgl ?? date("Y-m-d", strtotime("-1 day", strtotime($tgl)));

        $cek = DB::selectOne("SELECT a.check FROM stok_telur as a
                WHERE a.tgl = '$tanggal' and a.id_gudang = '1' and
                a.id_kandang != '0'
                group by a.tgl;");
        $cekTransfer = DB::selectOne("SELECT a.check FROM stok_telur as a
                WHERE a.tgl = '$tanggal' and a.id_gudang = '2' and a.pcs != '0'
                group by a.tgl;");
        $cekPenjualanTelur = DB::selectOne("SELECT a.cek FROM invoice_telur as a
                WHERE a.tgl = '$tanggal' and a.lokasi = 'mtd'
                group by a.tgl;");
        $cekPenjualanUmum = DB::selectOne("SELECT a.cek FROM penjualan_agl as a
                WHERE a.tgl = '$tanggal'
                group by a.tgl;");

        $data = [
            'title' => 'Dashboard Telur',
            'produk' => DB::table('telur_produk')->get(),
            'id_gudang' => $id_gudang,
            'tanggal' => $tanggal,
            'cekStokMasuk' => $cek,
            'cekTransfer' => $cekTransfer,
            'cekPenjualanTelur' => $cekPenjualanTelur,
            'cekPenjualanUmum' => $cekPenjualanUmum,
            'kandang' => DB::table('kandang')->where('selesai', 'T')->get(),
            'gudang' => DB::table('gudang_telur')->get(),
            'penjualan_cek_mtd' => DB::selectOne("SELECT sum(a.total_rp) as ttl_rp FROM invoice_telur as a where a.cek ='Y' and a.lokasi ='mtd';"),
            'penjualan_blmcek_mtd' => DB::selectOne("SELECT sum(a.ttl_rp) as ttl_rp , count(a.no_nota) as jumlah
            FROM 
                (
                    SELECT a.no_nota, sum(a.total_rp) as ttl_rp
                    FROM invoice_telur as a
                    where a.cek ='T' and a.lokasi ='mtd'
                    group by a.no_nota
                ) as a;"),
            'penjualan_umum_mtd' => DB::selectOne("SELECT sum(a.total_rp) as ttl_rp FROM penjualan_agl as a where a.cek ='Y' and a.lokasi ='mtd';"),
            'penjualan_umum_blmcek_mtd' => DB::selectOne("SELECT sum(a.total_rp) as ttl_rp , COUNT(a.urutan) as jumlah
            FROM (
            SELECT a.urutan, sum(a.total_rp) as total_rp
                FROM penjualan_agl as a 
            where a.cek ='T' and a.lokasi ='mtd'
            group by a.urutan
            ) as a;"),
            'penjualan_ayam_mtd' => DB::selectOne("SELECT sum(a.h_satuan * a.qty) as ttl_rp FROM invoice_ayam as a where a.cek ='Y' and a.lokasi ='mtd';"),
            'penjualan_ayam_blmcek_mtd' => DB::selectOne("SELECT sum(a.h_satuan * a.qty) as ttl_rp , COUNT(a.urutan) as jumlah
            FROM (
            SELECT a.urutan, sum(a.h_satuan * a.qty) as total_rp, a.h_satuan,a.qty
                FROM invoice_ayam as a 
            where a.cek ='T' and a.lokasi ='mtd'
            group by a.urutan
            ) as a;"),
            'opname_cek_mtd' => DB::selectOne("SELECT sum(a.total_rp) as ttl_rp FROM invoice_telur as a where a.cek ='Y' and a.lokasi ='opname';"),
            'opname_blmcek_mtd' => DB::selectOne("SELECT sum(a.total_rp) as ttl_rp , count(a.no_nota) as jumlah
            FROM ( SELECT a.no_nota, sum(a.total_rp) as total_rp
                  FROM invoice_telur as a 
                  where a.cek ='T' and a.lokasi ='opname'
                  group by a.no_nota
                ) as a;"),
            'harga_telur' => DB::table('harga_telur')
                ->leftJoin('telur_produk', 'telur_produk.id_produk_telur', '=', 'harga_telur.produk_telur_id')
                ->orderBy('tgl', 'DESC')->get(),
            'telur_produk' => DB::table('telur_produk')->get(),

        ];
        return view('produk_telur.dashboard', $data);
    }

    public function get_edit_hrga_telur(Request $r)
    {

        $data = [
            'get' => DB::table('harga_telur')
                ->leftJoin('telur_produk', 'telur_produk.id_produk_telur', '=', 'harga_telur.produk_telur_id')
                ->where('harga_telur.id', $r->data_id)
                ->first(),
            'id' => $r->data_id,
        ];
        return view('produk_telur.edit_hrga_telur', $data);
    }

    public function edit_harga_telur(Request $r)
    {
        $data = [
            'tgl' => $r->tgl,
            'harga' => $r->harga,
            'admin' => Auth::user()->name
        ];
        DB::table('harga_telur')->where('id', $r->id)->update($data);
        return redirect()->route('produk_telur', ['tgl' => $r->tgl])->with('sukses', 'Data Berhasil Di Simpan');
    }
    // public function index(Request $r)
    // {
    //     $bulan = $r->bulan ?? date('m');
    //     $tahun = $r->tahun ?? date('Y');
    //     $tgl = $tahun . '-' . $bulan . '-01';
    //     $tgl2 = empty($r->bulan) ? date('Y-m-d') : date('Y-m-t', strtotime($tgl));

    //     $data = [
    //         'title' => 'Dashboard Telur',
    //         'bulan' => DB::table('bulan')->get(),
    //         'populasi' => CashIbuModel::ttl_ayam2($tgl2, $bulan, $tahun),
    //         'biaya' => DB::select("SELECT * 
    //         FROM jurnal_accurates as a 
    //         left join akun_accurates as b on b.kode_akun = a.kode_akun
    //         where a.bulan = '$bulan' and a.tahun = '$tahun' and b.tipe_akun = 'Biaya Operasional' "),
    //         'tanggal' => $tgl2,

    //     ];
    //     return view('produk_telur.dashboard2', $data);
    // }
    public function CheckMartadah(Request $r)
    {
        if ($r->cek == 'T') {
            DB::table('stok_telur')->where(['tgl' => $r->tgl, 'id_gudang' => '1'])->where('id_kandang', '!=', '0')->update(['check' => 'Y']);
        } else {
            DB::table('stok_telur')->where(['tgl' => $r->tgl, 'id_gudang' => '1'])->where('id_kandang', '!=', '0')->update(['check' => 'T']);
        }
        return redirect()->route('produk_telur', ['tgl' => $r->tgl])->with('sukses', 'Data berhasil di save');
    }
    public function CheckAlpa(Request $r)
    {
        if ($r->cek == 'T') {
            DB::table('stok_telur')->where([['tgl', $r->tgl],  ['jenis', 'tf']])->update(['check' => 'Y']);
        } else {
            DB::table('stok_telur')->where([['tgl', $r->tgl],  ['jenis', 'tf']])->update(['check' => 'T']);
        }
        return redirect()->route('produk_telur', ['tgl' => $r->tgl])->with('sukses', 'Data berhasil di save');
    }

    public function HistoryMtd(Request $r)
    {
        $today = date("Y-m-d");
        $enamhari = date("Y-m-d", strtotime("-6 days", strtotime($today)));
        if (empty($r->tgl1)) {
            $tgl1 = $enamhari;
            $tgl2 = date('Y-m-d');
        } else {
            $tgl1 = $r->tgl1;
            $tgl2 = $r->tgl2;
        }

        $data = [
            'produk' => DB::table('telur_produk')->get(),
            'gudang' => DB::table('gudang_telur')->get(),
            'invoice' => DB::select("SELECT a.id_kandang, a.tgl, b.nm_kandang
            FROM stok_telur as a 
            left join kandang as b on b.id_kandang = a.id_kandang
            where a.tgl BETWEEN '$tgl1' and '$tgl2' and a.id_gudang='1' and a.nota_transfer in('0',' ')
            group by a.tgl, a.id_kandang"),
            'tgl1' => $tgl1,
            'tgl2' => $tgl2
        ];
        return view('produk_telur.history', $data);
    }

    public function edit_telur_dashboard(Request $r)
    {
        $data = [
            'invoice' => DB::select("SELECT a.id_produk_telur, b.id_stok_telur, a.nm_telur, b.pcs, b.kg
            FROM telur_produk as a 
            left join (
                  SELECT a.*
                  FROM stok_telur as a 
                  where a.id_kandang = '$r->id_kandang' and a.tgl = '$r->tgl'
            ) as b on b.id_telur = a.id_produk_telur"),
            'kandang' => DB::table('kandang')->where('id_kandang', $r->id_kandang)->first(),
            'tgl' => $r->tgl
        ];
        return view('produk_telur.edit_mtd', $data);
    }

    public function export(Request $r)
    {
        $tgl1 =  $r->tgl1;
        $tgl2 =  $r->tgl2;


        $total = DB::selectOne("SELECT count(a.id_kandang) as jumlah
        FROM stok_telur as a 
        where a.tgl BETWEEN '$tgl1' and '$tgl2' and a.id_gudang = '1'
        GROUP by a.id_stok_telur
        ");

        $totalrow = $total->jumlah;

        return Excel::download(new TelurExport($tgl1, $tgl2, $totalrow), 'Telur.xlsx');
    }



    public function HistoryAlpa(Request $r)
    {
        $today = date("Y-m-d");
        $enamhari = date("Y-m-d", strtotime("-6 days", strtotime($today)));
        if (empty($r->tgl1)) {
            $tgl1 = $enamhari;
            $tgl2 = date('Y-m-d');
        } else {
            $tgl1 = $r->tgl1;
            $tgl2 = $r->tgl2;
        }

        $data = [
            'produk' => DB::table('telur_produk')->get(),
            'gudang' => DB::table('gudang_telur')->get(),
            'invoice' => DB::select("SELECT a.id_kandang, a.tgl, b.nm_kandang
            FROM stok_telur as a 
            left join kandang as b on b.id_kandang = a.id_kandang
            where a.tgl BETWEEN '$tgl1' and '$tgl2' and a.id_gudang='2'
            group by a.tgl"),
            'tgl1' => $tgl1,
            'tgl2' => $tgl2
        ];
        return view('produk_telur.history_alpa', $data);
    }

    public function import_biaya(Request $r)
    {
        $r->validate([
            'file' => 'required|mimes:xlsx,xls'
        ]);

        // notulenTinjauanManajemen::where('tanggal', $r->tanggal)->delete();

        $file = $r->file('file');
        $spreadsheet = IOFactory::load($file->getPathname());
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();

        $bulan = $r->bulan;
        $tahun = $r->tahun;


        JurnalAccurate::where('bulan', $bulan)->where('tahun', $tahun)->where('id_kandang', '0')->delete();
        // Skip header
        foreach (array_slice($rows, 1) as $row) {
            $kode_akun = $row[1];
            $nama_akun = $row[3];
            $debit = str_replace([',', ' '], '', $row[5]);


            $akun = AkunAccurate::where('kode_akun', $kode_akun)->first();


            if (empty($akun->kode_akun)) {
                $data = [
                    'kode_akun' => $kode_akun,
                    'nama_akun' => $nama_akun,
                    'tipe_akun' => 'Biaya Operasional',
                ];
                AkunAccurate::create($data);
            } else {
            }

            $data = [
                'kode_akun' => $kode_akun,
                'bulan' => $bulan,
                'tahun' => $tahun,
                'total_biaya' => $debit,
            ];
            JurnalAccurate::create($data);
        }

        return redirect()->route('produk_telur', ['bulan' => $bulan, 'tahun' => $tahun])->with('sukses', 'Data Berhasil Di Import');
    }
    public function import_biaya_hpp(Request $r)
    {
        $r->validate([
            'file' => 'required|mimes:xlsx,xls'
        ]);

        // notulenTinjauanManajemen::where('tanggal', $r->tanggal)->delete();

        $file = $r->file('file');
        $spreadsheet = IOFactory::load($file->getPathname());
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();

        $bulan = $r->bulan;
        $tahun = $r->tahun;


        JurnalAccurate::where('bulan', $bulan)->where('tahun', $tahun)->where('id_kandang', '!=', '0')->delete();
        // Skip header
        foreach (array_slice($rows, 1) as $row) {
            $kode_akun = $row[1];
            $nama_akun = $row[2];
            $debit = str_replace([',', ' '], '', $row[3]);


            $akun = AkunAccurate::where('kode_akun', $kode_akun)->first();
            if (empty($akun->kode_akun)) {
                $data = [
                    'kode_akun' => $kode_akun,
                    'nama_akun' => $nama_akun,
                    'tipe_akun' => 'Biaya HPP',
                ];
                AkunAccurate::create($data);
            } else {
            }
            $kandang = DB::table('kandang')->where('nm_kandang', $row[0])->first();
            $data = [
                'kode_akun' => $kode_akun,
                'id_kandang' => $kandang->id_kandang,
                'bulan' => $bulan,
                'tahun' => $tahun,
                'total_biaya' => $debit,
            ];
            JurnalAccurate::create($data);
        }

        return redirect()->route('produk_telur', ['bulan' => $bulan, 'tahun' => $tahun])->with('sukses', 'Data Berhasil Di Import');
    }

    public function saveHargaTelur(Request $r)
    {
        $invoice = DB::table('harga_telur')->orderBy('invoice', 'desc')->first();
        for ($i = 0; $i < count($r->id_telur); $i++) {
            if ($r->harga[$i] == 0) {
            } else {
                $data = [
                    'tgl' => $r->tgl,
                    'invoice' => empty($invoice->invoice) ? 1001 : $invoice->invoice + 1,
                    'harga' => $r->harga[$i],
                    'produk_telur_id' => $r->id_telur[$i],
                    'admin' => Auth::user()->name
                ];
                DB::table('harga_telur')->insert($data);
            }
        }

        return redirect()->route('produk_telur', ['tgl' => $r->tgl])->with('sukses', 'Data Berhasil Di Simpan');
    }

    public function delete_harga_telur(Request $r)
    {
        DB::table('harga_telur')->where('id', $r->id)->delete();
        return redirect()->route('produk_telur', ['tgl' => $r->tgl])->with('sukses', 'Data Berhasil Di Hapus');
    }
}
