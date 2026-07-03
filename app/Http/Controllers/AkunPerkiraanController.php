<?php

namespace App\Http\Controllers;

use App\Models\LaporanLayerModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use Carbon\Carbon;



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

        DB::beginTransaction();

        try {

            $file = $r->file('file');
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getPathname());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();

            $last_departemen = null;
            $lastDate = null;

            foreach (array_slice($rows, 1) as $row) {

                // =========================
                // Mapping Kolom Excel
                // A=0 | B=1 | C=2 | D=3 | E=4 | F=5 | G=6
                // =========================
                $kode      = trim($row[3] ?? '');
                $tglRaw    = $row[2] ?? null;
                $debitRaw  = $row[5] ?? 0;
                $kreditRaw = $row[6] ?? 0;

                // =========================
                // HANDLE TANGGAL (MERGED)
                // =========================
                if (!empty($tglRaw)) {

                    if (is_numeric($tglRaw)) {
                        $currentDate = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($tglRaw)->format('Y-m-d');
                    } else {
                        $tglRaw = str_replace('/', '-', $tglRaw);
                        $currentDate = date('Y-m-d', strtotime($tglRaw));
                    }

                    $lastDate = $currentDate;
                } else {
                    $currentDate = $lastDate;
                }

                if (!$currentDate) {
                    continue;
                }

                // =========================
                // HANDLE DEPARTEMEN (MERGED)
                // =========================
                if (!empty($row[1])) {
                    $nm_departemen = trim(str_ireplace('Kandang ', '', $row[1]));
                    $last_departemen = $nm_departemen;
                } else {
                    $nm_departemen = $last_departemen;
                }

                if (strtolower($nm_departemen ?? '') === 'kosong') {
                    $nm_departemen = null;
                }

                // =========================
                // CLEAN ANGKA
                // =========================
                $debit  = floatval(str_replace(',', '', $debitRaw));
                $kredit = floatval(str_replace(',', '', $kreditRaw));

                // Skip jika kode kosong
                if ($kode == '') {
                    continue;
                }

                // Skip jika debit & kredit nol semua
                if ($debit == 0 && $kredit == 0) {
                    continue;
                }

                // =========================
                // UPDATE ATAU INSERT (AMAN)
                // =========================
                DB::table('jurnal_accurate')->updateOrInsert(
                    [
                        'tgl'           => $currentDate,
                        'kode'          => $kode,
                        'nm_departemen' => $nm_departemen,
                        'buku'          => '1'
                    ],
                    [
                        'debit'      => $debit,
                        'kredit'     => $kredit,
                        'tgl_import' => now(),
                        'admin'      => auth()->user()->name ?? 'System'
                    ]
                );
            }

            DB::commit();

            return redirect()->route('akun_perkiraan')
                ->with('sukses', 'Data HPP berhasil diimport');
        } catch (\Exception $e) {

            DB::rollBack();

            return back()->with('error', 'Gagal Import: ' . $e->getMessage());
        }
    }


    public function importBiaya(Request $r)
    {
        $r->validate([
            'file' => 'required|mimes:xlsx,xls'
        ]);





        // Gunakan Transaction agar aman (semua masuk atau tidak sama sekali)
        DB::beginTransaction();

        try {
            // 1. Hapus data lama


            $file = $r->file('file');
            $spreadsheet = IOFactory::load($file->getPathname());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();

            $currentDate = null; // Penampung tanggal (untuk merged cells)

            // Mulai loop dari baris ke-5 (Index 4)
            foreach (array_slice($rows, 1) as $index => $row) {

                // --- MAPPING DATA EXCEL ---
                // Index 0=A, 1=B, 2=C, 3=D, 4=E, 5=F

                $tglRaw   = $row[1] ?? null;       // Kolom B
                $kode     = trim($row[2] ?? '');   // Kolom C
                $nama     = trim($row[3] ?? '-');  // Kolom D (Masuk ke nm_departemen)
                $debitRaw = $row[4] ?? 0;          // Kolom F (Debit)

                // --- 1. LOGIKA TANGGAL ---
                if (!empty($tglRaw)) {
                    try {
                        if (is_numeric($tglRaw)) {
                            $currentDate = Date::excelToDateTimeObject($tglRaw)->format('Y-m-d');
                        } else {
                            // Ganti separator '/' jadi '-'
                            $tglRaw = str_replace('/', '-', $tglRaw);
                            $currentDate = date('Y-m-d', strtotime($tglRaw));
                        }
                    } catch (\Exception $e) {
                        // Abaikan jika format tanggal aneh
                    }
                }

                // Jika tanggal masih kosong (header/baris error), skip row ini
                if ($currentDate === null) {
                    continue;
                }

                // --- 2. VALIDASI ROW ---
                // Jika Kode kosong, skip
                if ($kode === '' || $kode === null) {
                    continue;
                }

                // Bersihkan format uang (misal: "17,500." -> buang koma -> "17500")
                // Asumsi format excel Anda: Koma = Ribuan, Titik = Desimal/Akhir
                $cleanDebit = str_replace([',', 'Rp', ' '], '', $debitRaw);
                // Hapus titik di akhir jika ada (misal "17500.")
                $cleanDebit = rtrim($cleanDebit, '.');

                $debit = floatval($cleanDebit);

                // Jika nominal 0, skip
                if ($debit == 0) {
                    continue;
                }

                $jurnal = DB::table('jurnal_accurate')->where('tgl', $currentDate)->where('kode', $kode)->where('buku', '2')->first();

                if ($jurnal) {
                    DB::table('jurnal_accurate')
                        ->where('tgl', $currentDate)
                        ->where('kode', $kode)
                        ->delete();
                }
                // --- 3. INSERT DATABASE ---
                // Pastikan semua kolom tabel terisi
                DB::table('jurnal_accurate')->insert([
                    'tgl'           => $currentDate,
                    'kode'          => $kode,
                    'nm_departemen' => $nama,        // <-- INI YANG TADI KURANG
                    'debit'         => $debit,
                    'kredit'        => 0,
                    'tgl_import'    => now(),
                    'buku'          => '2',
                    'admin'         => auth()->user()->name ?? 'System'
                ]);
            }

            DB::commit(); // Simpan perubahan
            return redirect()->route('akun_perkiraan')->with('sukses', 'Data berhasil diimport');
        } catch (\Exception $e) {
            DB::rollBack(); // Batalkan jika ada error
            // Tampilkan error aslinya untuk debugging
            return back()->with('error', 'Gagal Import: ' . $e->getMessage());
        }
    }
    public function importPenjualan(Request $r)
    {
        $r->validate([
            'file' => 'required|mimes:xlsx,xls'
        ]);

        DB::beginTransaction();

        try {
            $file = $r->file('file');
            $spreadsheet = IOFactory::load($file->getPathname());
            $sheet = $spreadsheet->getActiveSheet();

            $highestRow = $sheet->getHighestRow();

            $getCellValue = function ($cellCoordinate) use ($sheet) {
                [$cellCol, $cellRow] = Coordinate::coordinateFromString($cellCoordinate);
                $cellColIndex = Coordinate::columnIndexFromString($cellCol);

                foreach ($sheet->getMergeCells() as $mergeRange) {
                    [$startCell, $endCell] = Coordinate::rangeBoundaries($mergeRange);

                    $startCol = $startCell[0];
                    $startRow = $startCell[1];
                    $endCol   = $endCell[0];
                    $endRow   = $endCell[1];

                    if (
                        $cellColIndex >= $startCol &&
                        $cellColIndex <= $endCol &&
                        $cellRow >= $startRow &&
                        $cellRow <= $endRow
                    ) {
                        $topLeftCell = Coordinate::stringFromColumnIndex($startCol) . $startRow;
                        return $sheet->getCell($topLeftCell)->getValue();
                    }
                }

                return $sheet->getCell($cellCoordinate)->getValue();
            };

            for ($row = 2; $row <= $highestRow; $row++) {
                // Sesuai Excel:
                // B = Nama Barang
                // C = Kode #
                // D = Tanggal
                // E = Satuan
                // F = Kuantitas
                // G = Total Rp

                $nama      = trim((string) ($getCellValue('B' . $row) ?? ''));
                $kode      = trim((string) ($getCellValue('C' . $row) ?? ''));
                $tglRaw    = $getCellValue('D' . $row);
                $satuan    = trim((string) ($getCellValue('E' . $row) ?? ''));
                $kuantitas = $getCellValue('F' . $row) ?? 0;
                $totalRp   = $getCellValue('G' . $row) ?? 0;

                if ($kode === '' || $nama === '' || empty($tglRaw)) {
                    continue;
                }

                if ($tglRaw instanceof \DateTimeInterface) {
                    $tanggal = $tglRaw->format('Y-m-d');
                } elseif (is_numeric($tglRaw)) {
                    $tanggal = Date::excelToDateTimeObject($tglRaw)->format('Y-m-d');
                } else {
                    $tglRaw = str_replace('/', '-', trim($tglRaw));
                    $tanggal = date('Y-m-d', strtotime($tglRaw));
                }

                $kuantitas = is_numeric($kuantitas)
                    ? (float) $kuantitas
                    : (float) str_replace([',', 'Rp', ' '], '', $kuantitas);

                $totalRp = is_numeric($totalRp)
                    ? (float) $totalRp
                    : (float) rtrim(str_replace([',', 'Rp', ' '], '', $totalRp), '.');

                if ($totalRp == 0) {
                    continue;
                }

                DB::table('penjualan_barang_accurate')
                    ->where('tanggal', $tanggal)
                    ->where('kode', $kode)
                    ->delete();

                DB::table('penjualan_barang_accurate')->insert([
                    'kode'      => $kode,
                    'tanggal'   => $tanggal,
                    'nm_barang' => $nama,
                    'satuan'    => $satuan,
                    'kuantitas' => $kuantitas,
                    'total_rp'  => $totalRp,
                ]);
            }

            DB::commit();

            return redirect()->route('akun_perkiraan')->with('sukses', 'Data berhasil diimport');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal Import: ' . $e->getMessage());
        }
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


        $biaya_operasional = LaporanLayerModel::biayaOperasional($r->id_kandang);

        $populasi_periode = LaporanLayerModel::populasi_periode($r->id_kandang);

        $total = sumBk($populasi_periode, 'stok_awal');
        $jurnal_periode = LaporanLayerModel::jurnal_periode($r->id_kandang);
        $jurnal_periode_detail = LaporanLayerModel::jurnal_periode_detail($r->id_kandang);

        $data = [
            'kandang' => $kandang,
            'total_telur' => empty($total_telur->kuml_kg) ? 0 : $total_telur->kuml_kg - $total_telur->kuml_pcs / 180,
            'rata_rata_telur' => $rata_rata_telur->ttl_rp / $rata_rata_telur->kg_jual,
            'populasi' => $populasi,
            'rata_rata_ayam' => empty($rata_rata_ayam->jumlah) ? 0 : $rata_rata_ayam->total_harga / $rata_rata_ayam->jumlah,
            // 'biaya_pakan_program' => $biaya_pakan_program->ttl_rp,
            'biaya_pakan_program' => $biaya_pakan_program->ttl_rp + $biaya_pakan_accurate->ttl_rp,
            'biaya_vitamin' =>  $biaya_vitamin_program->ttl_rp + $biaya_vitamin_accurate->ttl_rp,
            // 'biaya_vitamin' => $biaya_vitamin_program->ttl_rp,
            'vaksin' => $vaksin->ttl_rp,
            'rak_telur' => empty($total_telur->kuml_pcs) ? 0 : ($total_telur->kuml_pcs / 180) * 6,
            'biaya_operasional' => (($jurnal_periode->debit + $biaya_operasional->debit) / $total) * $kandang->stok_awal,
            // 'biaya_operasional' => $jurnal_periode->debit,
            'pcs_telur' => $total_telur->kuml_pcs ?? 0,
            'total' => $total,
            'stok_awal' => $kandang->stok_awal,
            'jurnal_periode_detail' => $jurnal_periode_detail,
            'operasional_acc' => $biaya_operasional->debit,
            'populasi_periode' => $populasi_periode
        ];
        return view('akun-perkiraan.laba-rugi-kandang', $data);
    }
    public function labaRugiKandang2(Request $r)
    {
        $tgl1 = $r->tgl1 ?? date('Y-m-01');
        $tgl2 = $r->tgl2 ?? date('Y-m-t');

        $kandang = DB::table('kandang')->where('selesai', 'T')->orderBy('nm_kandang', 'ASC')->get();

        $totalTelur = DB::table('stok_telur')
            ->select(
                'id_kandang',
                DB::raw('COUNT(id_stok_telur) as count_bagi'),
                DB::raw('SUM(pcs) as kuml_pcs'),
                DB::raw('SUM(kg) as kuml_kg')
            )
            ->where('pcs', '!=', 0)
            ->whereBetween('tgl', [$tgl1, $tgl2])
            ->groupBy('id_kandang')
            ->get()
            ->keyBy('id_kandang');


        $populasi = DB::table('populasi as a')

            ->select(
                'a.id_kandang',
                DB::raw('SUM(a.mati) as mati'),
                DB::raw('SUM(a.jual) as jual'),
                DB::raw('SUM(a.afkir) as afkir')
            )
            ->whereBetween('a.tgl', [$tgl1, $tgl2])
            ->groupBy('a.id_kandang')
            ->get()
            ->keyBy('id_kandang');
        $rata_rata_telur = LaporanLayerModel::rataRataTelurtgl($tgl1, $tgl2);
        $biaya_pakan = DB::table('jurnal_accurate')
            ->select(
                'nm_departemen',
                DB::raw('SUM(jurnal_accurate.debit) as ttl_rp')
            )
            ->where('jurnal_accurate.kode', '5101-04')
            ->whereBetween('jurnal_accurate.tgl', [$tgl1, $tgl2])
            ->groupBy('jurnal_accurate.nm_departemen')
            ->get()
            ->keyBy('nm_departemen');
        $biaya_vitamin = DB::table('jurnal_accurate')
            ->select(
                'nm_departemen',
                DB::raw('SUM(jurnal_accurate.debit) as ttl_rp')
            )
            ->where('jurnal_accurate.kode', '5101-03')
            ->whereBetween('jurnal_accurate.tgl', [$tgl1, $tgl2])
            ->groupBy('jurnal_accurate.nm_departemen')
            ->get()
            ->keyBy('nm_departemen');

        $biaya_ayam = DB::table('penjualan_barang_accurate as a')
            ->select(
                DB::raw('SUM(a.total_rp) as ttl_rp'),
                DB::raw('SUM(a.kuantitas) as qty'),
            )
            ->where('a.satuan', 'ekor')
            ->whereBetween('a.tanggal', [$tgl1, $tgl2])
            ->first();






        $vaksin = DB::table('tb_vaksin_perencanaan')
            ->select(
                'id_kandang',
                DB::raw('SUM(ttl_rp) as ttl_rp')
            )->whereBetween('tgl', [$tgl1, $tgl2])
            ->groupBy('id_kandang')
            ->get()
            ->keyBy('id_kandang');

        $biaya_operasional = LaporanLayerModel::biayaOperasional2($tgl1, $tgl2);
        $total_populasi = DB::table('kandang')->select(DB::raw('SUM(stok_awal) as stok_awal'))->where('selesai', 'T')->first();





        return view('akun-perkiraan.laba-rugi-kandang2', compact(
            'kandang',
            'totalTelur',
            'tgl1',
            'tgl2',
            'rata_rata_telur',
            'populasi',
            'biaya_pakan',
            'biaya_vitamin',
            'vaksin',
            'biaya_operasional',
            'total_populasi',
            'biaya_ayam'
        ));
    }

    public function getLabaRugiData(Request $r)
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



        $biaya_operasional = LaporanLayerModel::biayaOperasional($r->id_kandang);

        $populasi_periode = LaporanLayerModel::populasi_periode($r->id_kandang);

        $total = sumBk($populasi_periode, 'stok_awal');
        $jurnal_periode = LaporanLayerModel::jurnal_periode($r->id_kandang);
        $jurnal_periode_detail = LaporanLayerModel::jurnal_periode_detail($r->id_kandang);


        $ttl_telur = empty($total_telur->kuml_kg) ? 0 : $total_telur->kuml_kg - $total_telur->kuml_pcs / 180;
        $r2_telur = $rata_rata_telur->ttl_rp / $rata_rata_telur->kg_jual;

        $ayam_jual = ($populasi->jual + $populasi->afkir) * (empty($rata_rata_ayam->jumlah) ? 0 : $rata_rata_ayam->total_harga / $rata_rata_ayam->jumlah);

        $biaya_pakan = $biaya_pakan_program->ttl_rp + $biaya_pakan_accurate->ttl_rp;
        $biaya_vitamin = $biaya_vitamin_program->ttl_rp + $biaya_vitamin_accurate->ttl_rp;
        $biaya_vaksin = $vaksin->ttl_rp;
        $biaya_pullet = $kandang->rupiah;
        $rak = empty($total_telur->kuml_pcs) ? 0 : (($total_telur->kuml_pcs / 180) * 6) * 820;
        $biaya_oper = (($jurnal_periode->debit + $biaya_operasional->debit) / $total) * $kandang->stok_awal;


        $total_biaya = $biaya_pakan + $biaya_vitamin + $biaya_pullet + $rak + $biaya_oper + $biaya_vaksin;
        $penjualan_telur = empty($ttl_telur) ? 0 : ($ttl_telur * $r2_telur) + $ayam_jual;

        $kg_pakan = DB::selectOne("SELECT d.id_kandang, sum(d.pcs_kredit) as kg_pakan_kuml
            FROM stok_produk_perencanaan as d 
            left join tb_produk_perencanaan as b on b.id_produk = d.id_pakan
            where d.tgl between '2020-01-01' and '$r->tgl' and b.kategori = 'pakan' and d.id_kandang = '$r->id_kandang'
            group by d.id_kandang");



        $kg_pakan_kuml = $kg_pakan->kg_pakan_kuml / 1000;
        $fcrk = $kg_pakan_kuml / $ttl_telur;
        $fcrkplus = ($kg_pakan_kuml + (($biaya_vitamin + $biaya_vaksin + $biaya_pullet +  $biaya_oper + $rak) / ($biaya_pakan / $kg_pakan_kuml))) / $ttl_telur;


        // Return semua data dalam satu object JSON
        return response()->json([
            'kandang' => $kandang,
            'penjualan_telur' => number_format($penjualan_telur, 0),
            'total_biaya' => number_format($total_biaya, 0),
            'biaya_pakan' => number_format($biaya_pakan, 0),
            'rata_pakan' => number_format($biaya_pakan / $kg_pakan_kuml, 0),
            'fcrk' => number_format($fcrk, 1),
            'fcrkplus' => number_format($fcrkplus, 1),
            'biaya_vitamin' => number_format($biaya_vitamin, 0),
            'biaya_vaksin' => number_format($biaya_vaksin, 0),
            'biaya_pullet' => number_format($biaya_pullet, 0),
            'biaya_rak' => number_format($rak, 0),
            'biaya_oper' => number_format($biaya_oper, 0),

            'laba' => number_format($penjualan_telur - $total_biaya, 0),
            'rata' => number_format($ttl_telur == 0 ? 0 : ($penjualan_telur - $total_biaya) / $ttl_telur, 0),

            // 'biaya_pakan_program' => $biaya_pakan_program->ttl_rp + $biaya_pakan_accurate->ttl_rp,
            // 'biaya_vitamin' =>  $biaya_vitamin_program->ttl_rp + $biaya_vitamin_accurate->ttl_rp,
            // // 'biaya_vitamin' => $biaya_vitamin_program->ttl_rp,
            // 'vaksin' => $vaksin->ttl_rp,
            // 'rak_telur' => ($total_telur->kuml_pcs / 180) * 6,
            // 'biaya_operasional' => (($jurnal_periode->debit + $biaya_operasional->debit) / $total) * $kandang->stok_awal,
            // // 'biaya_operasional' => $jurnal_periode->debit,
            // 'pcs_telur' => $total_telur->kuml_pcs,
            // 'total' => $total,
            // 'stok_awal' => $kandang->stok_awal,
            // 'jurnal_periode_detail' => $jurnal_periode_detail,
            // 'operasional_acc' => $biaya_operasional->debit,
            // 'populasi_periode' => $populasi_periode
        ]);
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
        $host        = session('accurate_host'); // contoh: https://iris.accurate.id

        if (!$accessToken) {
            return "Access token tidak ditemukan. Lakukan OAuth ulang.";
        }

        if (!$sessionId || !$host) {
            return "Belum membuka database Accurate.";
        }

        $response = Http::withToken($accessToken)
            ->withHeaders([
                'X-Session-ID' => $sessionId,
            ])
            ->get($host . '/accurate/api/purchase-invoice/list.do', [
                'fields' => 'id,number',
                'page' => 1,
                'pageSize' => 20
            ]);

        dd([
            'accessToken' => Str::limit($accessToken, 20),
            'sessionId'   => $sessionId,
            'host'        => $host,
        ]);



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

    public function biaya(Request $r)
    {
        if (empty($r->tgl1)) {
            $tgl1 = date('Y-m-01');
            $tgl2 = date('Y-m-t');
        } else {
            $tgl1 = $r->tgl1;
            $tgl2 = $r->tgl2;
        }
        $data = [
            'title' => 'Biaya',
            'jurnal' => DB::table('jurnal_accurate')
                ->leftJoin('akun_accurate', 'jurnal_accurate.kode', '=', 'akun_accurate.kode')
                ->where('jurnal_accurate.buku', 2)
                ->whereBetween('jurnal_accurate.tgl', [$tgl1, $tgl2])
                ->select(
                    'jurnal_accurate.tgl',
                    'akun_accurate.nama',
                    'akun_accurate.kode',
                    DB::raw('SUM(jurnal_accurate.debit) as total_debit'),
                    DB::raw('SUM(jurnal_accurate.kredit) as total_kredit')
                )
                ->groupBy('akun_accurate.kode', 'jurnal_accurate.tgl')
                ->orderBy('jurnal_accurate.tgl', 'asc')
                ->orderBy('akun_accurate.kode', 'asc')

                ->get()

        ];

        return view('akun-perkiraan.biaya', $data);
    }
    public function biaya_hpp(Request $r)
    {
        if (empty($r->tgl1)) {
            $tgl1 = date('Y-m-01');
            $tgl2 = date('Y-m-t');
        } else {
            $tgl1 = $r->tgl1;
            $tgl2 = $r->tgl2;
        }
        $data = [
            'title' => 'Biaya Hpp',
            'jurnal' => DB::table('jurnal_accurate')
                ->leftJoin('akun_accurate', 'jurnal_accurate.kode', '=', 'akun_accurate.kode')
                ->where('jurnal_accurate.buku', 1)
                ->whereBetween('jurnal_accurate.tgl', [$tgl1, $tgl2])
                ->select(
                    'jurnal_accurate.nm_departemen',
                    'jurnal_accurate.tgl',
                    'akun_accurate.nama',
                    'akun_accurate.kode',
                    DB::raw('SUM(jurnal_accurate.debit) as total_debit'),
                    DB::raw('SUM(jurnal_accurate.kredit) as total_kredit')
                )
                ->groupBy('jurnal_accurate.nm_departemen', 'akun_accurate.kode', 'jurnal_accurate.tgl')
                ->orderBy('jurnal_accurate.tgl', 'asc')
                ->orderBy('akun_accurate.kode', 'asc')

                ->get()

        ];

        return view('akun-perkiraan.biaya_hpp', $data);
    }
    public function kandang(Request $r)
    {
        $data = [
            'title' => 'Kandang',
            'kandang' => DB::table('kandang as a')->join('strain as b', 'a.id_strain', 'b.id_strain')->get()
        ];

        return view('akun-perkiraan.kandang', $data);
    }
}
