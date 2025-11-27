<?php

namespace App\Http\Controllers;

use App\Models\LaporanLayerModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

class AkunPerkiraanController extends Controller
{
    public function index()
    {
        $data = [
            'title' => 'Akun Perkiraan',
            'akun' => DB::select("SELECT a.kode, a.nama, b.debit, b.kredit, a.tipe_akun
            FROM akun_accurate as a 
                left join (
                    SELECT b.kode , sum(b.debit) as debit , sum(b.kredit) as kredit
                    FROM jurnal_accurate as b
                    group by b.kode
                ) as b on b.kode = a.kode
                where a.akun_induk is null
                "),
            'bulan' => DB::table('bulan')->get(),
        ];
        return view('akun-perkiraan.index', $data);
    }


    public function importHpp(Request $r)
    {
        $r->validate([
            'file' => 'required|mimes:xlsx,xls'
        ]);



        $bulan = $r->bulan;
        $tahun = $r->tahun;
        $tgl = $tahun . '-' . $bulan . '-01';
        $tgl = date('Y-m-t', strtotime($tgl));

        $tes =  DB::table('jurnal_accurate')->whereMonth('tgl', $bulan)->whereYear('tgl', $tahun)->where('buku', '1')->delete();


        $file = $r->file('file');
        $spreadsheet = IOFactory::load($file->getPathname());
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();

        // Skip header
        $last_departemen = null;

        foreach (array_slice($rows, 1) as $row) {
            $kode = $row[2];
            $debit = floatval(str_replace(',', '', $row[4]));
            $kredit = floatval(str_replace(',', '', $row[5]));

            // Ambil departemen dan hapus "Kandang "
            if (!empty($row[1])) {
                $nm_departemen = trim(str_ireplace('Kandang ', '', $row[1]));
                $last_departemen = $nm_departemen;
            } else {
                $nm_departemen = $last_departemen;
            }

            // Jika nilainya 'kosong', ubah jadi null
            $nm_departemen = strtolower($nm_departemen) === 'kosong' ? null : $nm_departemen;

            DB::table('jurnal_accurate')->insert([
                'tgl' => $tgl,
                'kode' => $kode,
                'nm_departemen' => $nm_departemen,
                'debit' => $debit,
                'kredit' => $kredit,
                'tgl_import' => date('Y-m-d'),
                'buku' => '1',
                'admin' => auth()->user()->name
            ]);
        }

        return redirect()->route('akun_perkiraan')->with('sukses', 'Data berhasil diimport');
    }
    public function importBiaya(Request $r)
    {
        $r->validate([
            'file' => 'required|mimes:xlsx,xls'
        ]);

        $bulan = $r->bulan;
        $tahun = $r->tahun;
        $tgl = $tahun . '-' . $bulan . '-01';
        $tgl = date('Y-m-t', strtotime($tgl));

        DB::table('jurnal_accurate')
            ->whereMonth('tgl', $bulan)
            ->whereYear('tgl', $tahun)
            ->where('buku', '2')
            ->delete();

        $file = $r->file('file');
        $spreadsheet = IOFactory::load($file->getPathname());
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();

        // Mulai dari baris ke-5 (skip header)
        foreach (array_slice($rows, 4) as $row) {

            $kode = trim($row[2] ?? '');
            $debitRaw = $row[4] ?? '';

            // Jika kode kosong → skip
            if ($kode === '' || $kode === null) {
                continue;
            }

            // Bersihkan angka debit
            $debit = floatval(str_replace(',', '', $debitRaw));

            // Jika debit kosong / nol → skip
            if ($debit == 0 || $debit === null) {
                continue;
            }

            DB::table('jurnal_accurate')->insert([
                'tgl'        => $tgl,
                'kode'       => $kode,
                'debit'      => $debit,
                'kredit'     => 0,
                'tgl_import' => date('Y-m-d'),
                'buku'       => '2',
                'admin'      => auth()->user()->name
            ]);
        }

        return redirect()->route('akun_perkiraan')->with('sukses', 'Data berhasil diimport');
    }

    public function labaRugiKandang(Request $r)
    {
        $kandang = DB::table('kandang')->where('id_kandang', $r->id_kandang)->first();
        $total_telur = DB::selectOne("SELECT h.id_kandang , count(h.id_stok_telur) as count_bagi, sum(h.pcs) as kuml_pcs, sum(h.kg) as kuml_kg FROM stok_telur as h  where h.id_kandang = '$r->id_kandang' and h.pcs != 0 group by h.id_kandang");
        $populasi = DB::selectOne("SELECT sum(`mati`) as mati, sum(`jual`) as jual, sum(`afkir`) as afkir FROM `populasi` WHERE `id_kandang` ='$r->id_kandang';");

        $rata_rata_telur = LaporanLayerModel::rataRataTelur($r->id_kandang);
        $rata_rata_ayam = LaporanLayerModel::rataRataAyam($r->id_kandang);

        $biaya_pakan_program = DB::selectOne("SELECT sum(`total_rp`) as ttl_rp FROM `stok_produk_perencanaan` as a left join tb_produk_perencanaan as b on b.id_produk = a.id_pakan where b.kategori = 'pakan' and a.id_kandang = '$r->id_kandang' and a.tgl BETWEEN '2020-01-01' and '2025-01-31';");
        $biaya_vitamin_program = DB::selectOne("SELECT sum(`total_rp`) as ttl_rp FROM `stok_produk_perencanaan` as a left join tb_produk_perencanaan as b on b.id_produk = a.id_pakan where b.kategori != 'pakan' and a.id_kandang = '$r->id_kandang' and a.tgl BETWEEN '2020-01-01' and '2025-01-31';");

        $vaksin = DB::selectOne("SELECT  sum(a.ttl_rp) as ttl_rp FROM tb_vaksin_perencanaan as a where a.id_kandang = '$r->id_kandang' ");

        $biaya_pakan_accurate = DB::selectOne("SELECT sum(a.debit) as ttl_rp FROM jurnal_accurate as a where a.kode = '5101-04' and a.nm_departemen ='$kandang->nm_kandang'");
        $biaya_vitamin_accurate = DB::selectOne("SELECT sum(a.debit) as ttl_rp FROM jurnal_accurate as a where a.kode = '5101-03' and a.nm_departemen ='$kandang->nm_kandang'");


        $data = [
            'kandang' => $kandang,
            'total_telur' => $total_telur->kuml_kg - $total_telur->kuml_pcs / 180,
            'rata_rata_telur' => $rata_rata_telur->ttl_rp / $rata_rata_telur->kg_jual,
            'populasi' => $populasi,
            'rata_rata_ayam' => $rata_rata_ayam->total_harga / $rata_rata_ayam->jumlah,
            'biaya_pakan_program' => $biaya_pakan_program->ttl_rp + $biaya_pakan_accurate->ttl_rp,
            'biaya_vitamin' => $biaya_vitamin_accurate->ttl_rp + $biaya_vitamin_program->ttl_rp,
            'vaksin' => $vaksin->ttl_rp,
            'rak_telur' => ($total_telur->kuml_pcs / 180) * 6
        ];
        return view('akun-perkiraan.laba-rugi-kandang', $data);
    }

    public function accurate(Request $request)
    {
        $code = $request->get('code');

        if (!$code) {
            return "Kode OAuth tidak ditemukan!";
        }

        $basicAuth = base64_encode(env('ACCURATE_CLIENT_ID') . ':' . env('ACCURATE_CLIENT_SECRET'));

        $response = Http::withHeaders([
            'Authorization' => 'Basic ' . $basicAuth,
            'Content-Type' => 'application/x-www-form-urlencoded',
        ])->asForm()->post('https://account.accurate.id/oauth/token', [
            'grant_type'   => 'authorization_code',
            'code'         => $code,
            'redirect_uri' => env('ACCURATE_REDIRECT_URI'),
        ]);

        if ($response->failed()) {
            return $response->body(); // biar kelihatan errornya
        }

        $token = $response->json();

        session([
            'accurate_access_token'  => $token['access_token'],
            'accurate_refresh_token' => $token['refresh_token'],
        ]);

        return "Token berhasil diterima!";
    }


    public function getDatabases()
    {
        $accessToken = session('accurate_access_token');

        if (!$accessToken) {
            return "Access token tidak ditemukan. Lakukan OAuth ulang.";
        }

        $response = Http::withToken($accessToken)
            ->get('https://account.accurate.id/api/db-list.do');

        return $response->json();
    }

    public function openDatabase(Request $request)
    {
        $dbId = $request->db_id; // ID database Accurate

        $accessToken = session('accurate_access_token');

        if (!$accessToken) {
            return "Access token tidak ditemukan. Lakukan OAuth ulang.";
        }

        // Panggil Accurate Open DB
        $response = Http::withToken($accessToken)
            ->asForm()
            ->post('https://account.accurate.id/api/open-db.do', [
                'id' => $dbId
            ]);

        if ($response->failed()) {
            return $response->body();
        }

        $data = $response->json();

        // Simpan session dan host dari Accurate
        session([
            'accurate_session' => $data['session'],   // penting
            'accurate_host' => $data['host'],         // penting
        ]);

        return "Database berhasil dibuka!";
    }

    public function getItems()
    {
        $accessToken = session('accurate_access_token');
        $sessionId   = session('accurate_session');
        $host        = session('accurate_host');

        if (!$accessToken || !$sessionId || !$host) {
            return "Belum membuka database Accurate.";
        }

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
            'X-Session-ID'  => $sessionId
        ])->get($host . '/api/item/list.do');

        return $response->json();
    }



    public function openDb(Request $request)
    {
        $accessToken = session('accurate_access_token');

        if (!$accessToken) {
            return "Access token tidak ditemukan. Lakukan OAuth ulang.";
        }

        $dbId = 1794095; // pilih salah satu ID database kamu

        $response = Http::withToken($accessToken)
            ->asForm()
            ->post('https://account.accurate.id/api/open-db.do', [
                'id' => $dbId
            ]);

        return $response->json();
    }
}
