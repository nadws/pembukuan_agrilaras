<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Carbon\Carbon;
use SettingHal;

class AktivaController extends Controller
{
    public function index()
    {
        $id_user = auth()->user()->id;
        $aktivaManual = collect(DB::select("SELECT a.*, b.*, a.akumulasi_penyusutan as beban FROM aktiva_pembukuan_baru as a
            left join kelompok_aktiva as b on b.id_kelompok = a.id_kelompok
            order by a.id DESC"));
        $aktivaPembalikan = DB::table('jurnal_perkiraan as j')
            ->leftJoin('akun_perkiraan as a', 'a.id_akun_perkiraan', '=', 'j.id_akun_perkiraan')
            ->where('j.tipe_transaksi', 'Pembalik Aktiva Gantung')
            ->where('j.debit', '>', 0)
            ->orderByDesc('j.tanggal')->orderByDesc('j.id_impor_jurnal_perkiraan')
            ->get(['j.tanggal', 'j.nomor_transaksi', 'j.debit as h_perolehan', 'j.deskripsi', 'a.nama as nm_kelompok', 'j.tanggal as tgl'])
            ->map(function ($row) {
                // Nama aktiva mengikuti nama aset pada aktiva gantung, bukan deskripsi jurnal.
                $nama = preg_replace('/^Pembalikan aktiva gantung\s+/i', '', (string) $row->deskripsi);
                $nama = preg_replace('/\s+ke aset\s+.*$/i', '', $nama);
                $nama = preg_replace('/\s+menjadi aset tetap\s*$/i', '', $nama);
                $row->nm_aktiva = trim($nama) ?: $row->deskripsi;
                $row->biaya_depresiasi = 0;
                $row->beban = 0;
                $row->umur_aktiva_bulan = null;
                return $row;
            });
        $namaManual = $aktivaManual->pluck('nm_aktiva')->map(fn($n) => mb_strtolower(trim($n)));
        $aktiva = $aktivaManual->concat($aktivaPembalikan->reject(fn($a) => $namaManual->contains(mb_strtolower(trim($a->nm_aktiva)))));
        $data =  [
            'title' => 'Aktiva',
            'tahun' => DB::select("SELECT YEAR(a.tgl) as tahun, a.tgl
            FROM depresiasi_aktiva as a
            group by YEAR(a.tgl)
            order by YEAR(a.tgl) ASC;"),
            'aktiva' => $aktiva,
            // Jurnal pembalik adalah sumber aktiva baru pada Pembukuan Baru.

            'user' => User::where('posisi_id', 1)->get(),
            'halaman' => 10,
            'create' => SettingHal::btnHal(41, $id_user),
            'print' => SettingHal::btnHal(42, $id_user),
            'edit' => SettingHal::btnHal(43, $id_user),
            'delete' => SettingHal::btnHal(44, $id_user),
            'detail' => SettingHal::btnHal(45, $id_user),

        ];
        return view('aktiva.index', $data);
    }

    public function add()
    {
        $data =  [
            'title' => 'Add Aktiva',
        ];
        return view('aktiva.add', $data);
    }

    public function templateImport()
    {
        $akunAset = DB::table('akun_perkiraan')
            ->where('aktif', 1)->where('tipe_akun', 'FASS')
            ->whereNotNull('id_akun_induk')->orderBy('kode_perkiraan')
            ->get(['id_akun_perkiraan', 'kode_perkiraan', 'nama']);
        $contohAkun = $akunAset->firstWhere('kode_perkiraan', '120002') ?? $akunAset->first();

        $workbook = new Spreadsheet();
        $dataSheet = $workbook->getActiveSheet();
        $dataSheet->setTitle('Data Aktiva');
        $headers = ['id_akun_aset', 'nama_aktiva', 'tanggal_perolehan', 'nilai_perolehan', 'nilai_sisa_aset', 'umur_tahun', 'umur_bulan'];
        $dataSheet->fromArray($headers, null, 'A1');
        $dataSheet->fromArray([
            $contohAkun?->id_akun_perkiraan,
            'Contoh Aktiva',
            '2024-01-15',
            12000000,
            8000000,
            2,
            3,
        ], null, 'A2');
        $dataSheet->getStyle('A1:G1')->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
        $dataSheet->getStyle('A1:G1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF304F9E');
        $dataSheet->getStyle('C2:C1000')->getNumberFormat()->setFormatCode('yyyy-mm-dd');
        $dataSheet->getStyle('D2:E1000')->getNumberFormat()->setFormatCode('#,##0');
        $dataSheet->freezePane('A2');
        $dataSheet->setAutoFilter('A1:G1');
        foreach (['A' => 17, 'B' => 30, 'C' => 21, 'D' => 20, 'E' => 20, 'F' => 15, 'G' => 15] as $column => $width) {
            $dataSheet->getColumnDimension($column)->setWidth($width);
        }

        $referenceSheet = $workbook->createSheet();
        $referenceSheet->setTitle('Referensi');
        $referenceSheet->setCellValue('A1', 'REFERENSI AKUN ASET TETAP TUJUAN');
        $referenceSheet->fromArray(['id_akun_aset', 'kode_perkiraan', 'nama_akun'], null, 'A3');
        $akunRows = $akunAset->map(fn ($akun) => [$akun->id_akun_perkiraan, $akun->kode_perkiraan, $akun->nama])->all();
        if ($akunRows) $referenceSheet->fromArray($akunRows, null, 'A4');

        $referenceSheet->setCellValue('E1', 'PETUNJUK PENGISIAN');
        $referenceSheet->setCellValue('E3', 'Kolom');
        $referenceSheet->setCellValue('F3', 'Keterangan');
        $referenceSheet->fromArray([
            ['id_akun_aset', 'Salin ID dari daftar akun aset tetap di sebelah kiri.'],
            ['tanggal_perolehan', 'Gunakan format YYYY-MM-DD atau DD/MM/YYYY.'],
            ['nilai_perolehan', 'Nilai awal/perolehan aset, hanya angka.'],
            ['nilai_sisa_aset', 'Boleh 0 jika nilai buku aset sudah habis; tidak boleh melebihi nilai perolehan.'],
            ['umur_tahun', 'Bagian tahun dari umur aktiva yang menjadi dasar penyusutan.'],
            ['umur_bulan', 'Bagian bulan, isi angka 0 sampai 11.'],
        ], null, 'E4');

        foreach (['A1:C1', 'E1:F1'] as $range) {
            $referenceSheet->mergeCells($range);
            $referenceSheet->getStyle($range)->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
            $referenceSheet->getStyle($range)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF304F9E');
            $referenceSheet->getStyle($range)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }
        foreach (['A3:C3', 'E3:F3'] as $range) {
            $referenceSheet->getStyle($range)->getFont()->setBold(true);
            $referenceSheet->getStyle($range)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFDCE6F8');
        }
        foreach (['A' => 17, 'B' => 20, 'C' => 30, 'E' => 24, 'F' => 70] as $column => $width) {
            $referenceSheet->getColumnDimension($column)->setWidth($width);
        }
        $referenceSheet->freezePane('A4');
        $workbook->setActiveSheetIndex(0);

        return response()->streamDownload(function () use ($workbook) {
            (new Xlsx($workbook))->save('php://output');
            $workbook->disconnectWorksheets();
        }, 'format-import-aktiva.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file_aktiva' => ['required', 'file', 'mimes:csv,txt,xlsx,xls', 'max:5120'],
        ]);

        try {
            $sheet = IOFactory::load($request->file('file_aktiva')->getRealPath())
                ->getActiveSheet();
            // Ambil nilai mentah. Jika format tampilan Excel ikut dibaca, angka
            // seperti 12.000.000 dapat berubah menjadi teks dan gagal divalidasi.
            $rows = $sheet->toArray(null, true, false, false);
        } catch (\Throwable $e) {
            return back()->withErrors(['file_aktiva' => 'File tidak dapat dibaca. Pastikan memakai format CSV atau Excel yang valid.']);
        }

        if (count($rows) < 2) {
            return back()->withErrors(['file_aktiva' => 'File tidak memiliki data aktiva.']);
        }

        $requiredHeaders = ['id_akun_aset', 'nama_aktiva', 'tanggal_perolehan', 'nilai_perolehan', 'nilai_sisa_aset', 'umur_tahun', 'umur_bulan'];
        $headers = array_map(function ($value) {
            $header = strtolower(trim(preg_replace('/^\xEF\xBB\xBF/', '', (string) $value)));
            return preg_replace('/\s+/', '_', $header);
        }, $rows[0]);
        $indexes = array_flip($headers);
        $missing = array_values(array_diff($requiredHeaders, $headers));

        if ($missing) {
            return back()->withErrors(['file_aktiva' => 'Kolom wajib tidak ditemukan: ' . implode(', ', $missing) . '. Silakan unduh Format Import terbaru.']);
        }

        $akunAset = DB::table('akun_perkiraan')->where('aktif', 1)->where('tipe_akun', 'FASS')
            ->pluck('id_akun_perkiraan')->map(fn ($id) => (int) $id)->all();
        $dataImport = [];
        $errors = [];
        $normalisasiAngka = function ($value) {
            if (is_int($value) || is_float($value)) return $value;
            $value = preg_replace('/[^0-9,.-]/', '', trim((string) $value));
            if ($value === '') return null;

            $jumlahTitik = substr_count($value, '.');
            $jumlahKoma = substr_count($value, ',');
            if ($jumlahTitik > 0 && $jumlahKoma > 0) {
                $pemisahDesimal = strrpos($value, '.') > strrpos($value, ',') ? '.' : ',';
                $pemisahRibuan = $pemisahDesimal === '.' ? ',' : '.';
                $hasil = str_replace($pemisahRibuan, '', $value);
                return $pemisahDesimal === ',' ? str_replace(',', '.', $hasil) : $hasil;
            }
            if ($jumlahTitik > 1 || $jumlahKoma > 1) {
                return str_replace(['.', ','], '', $value);
            }
            if ($jumlahTitik === 1 || $jumlahKoma === 1) {
                $pemisah = $jumlahTitik === 1 ? '.' : ',';
                $bagian = explode($pemisah, $value);
                if (strlen($bagian[1] ?? '') === 3) return implode('', $bagian);
                return str_replace(',', '.', $value);
            }
            return $value;
        };

        foreach (array_slice($rows, 1) as $offset => $row) {
            $rowNumber = $offset + 2;
            if (collect($row)->filter(fn ($value) => $value !== null && trim((string) $value) !== '')->isEmpty()) {
                continue;
            }

            $raw = [];
            foreach ($requiredHeaders as $header) {
                $raw[$header] = $row[$indexes[$header]] ?? null;
            }
            $raw['nilai_perolehan'] = $normalisasiAngka($raw['nilai_perolehan']);
            $raw['nilai_sisa_aset'] = $normalisasiAngka($raw['nilai_sisa_aset']);
            // Nilai buku tidak boleh negatif. File lama sering memakai tanda '-'
            // untuk nol atau rumus penyusutan yang menghasilkan angka minus.
            if ($raw['nilai_sisa_aset'] === '-' || $raw['nilai_sisa_aset'] === null) {
                $raw['nilai_sisa_aset'] = 0;
            } elseif (is_numeric($raw['nilai_sisa_aset']) && (float) $raw['nilai_sisa_aset'] < 0) {
                $raw['nilai_sisa_aset'] = 0;
            }
            // Excel dapat mengembalikan sel angka 0 sebagai null. Untuk bagian
            // umur, sel kosong memang bermakna 0 tahun atau 0 bulan.
            $raw['umur_tahun'] = $raw['umur_tahun'] === null || $raw['umur_tahun'] === '' ? 0 : $raw['umur_tahun'];
            $raw['umur_bulan'] = $raw['umur_bulan'] === null || $raw['umur_bulan'] === '' ? 0 : $raw['umur_bulan'];

            try {
                if ($raw['tanggal_perolehan'] instanceof \DateTimeInterface) {
                    $tanggal = Carbon::instance($raw['tanggal_perolehan'])->format('Y-m-d');
                } elseif (is_numeric($raw['tanggal_perolehan'])) {
                    $tanggal = Carbon::instance(ExcelDate::excelToDateTimeObject((float) $raw['tanggal_perolehan']))->format('Y-m-d');
                } else {
                    $text = trim((string) $raw['tanggal_perolehan']);
                    $tanggal = null;
                    foreach (['Y-m-d', 'd/m/Y', 'd-m-Y'] as $format) {
                        try { $tanggal = Carbon::createFromFormat($format, $text)->format('Y-m-d'); break; } catch (\Throwable) {}
                    }
                    if (! $tanggal) throw new \RuntimeException('tanggal tidak valid');
                }
            } catch (\Throwable) {
                $errors[] = "Baris {$rowNumber}: tanggal_perolehan harus berformat YYYY-MM-DD atau DD/MM/YYYY.";
                continue;
            }

            $validator = Validator::make([
                'id_akun_aset' => $raw['id_akun_aset'],
                'nama_aktiva' => trim((string) $raw['nama_aktiva']),
                'nilai_perolehan' => $raw['nilai_perolehan'],
                'nilai_sisa_aset' => $raw['nilai_sisa_aset'],
                'umur_tahun' => $raw['umur_tahun'],
                'umur_bulan' => $raw['umur_bulan'],
            ], [
                'id_akun_aset' => ['required', 'integer', 'in:' . implode(',', $akunAset)],
                'nama_aktiva' => ['required', 'string', 'max:255'],
                'nilai_perolehan' => ['required', 'numeric', 'min:0.01'],
                'nilai_sisa_aset' => ['required', 'numeric', 'min:0', 'lte:nilai_perolehan'],
                'umur_tahun' => ['required', 'integer', 'min:0'],
                'umur_bulan' => ['required', 'integer', 'min:0', 'max:11'],
            ]);

            if ($validator->fails()) {
                $errors[] = "Baris {$rowNumber}: " . implode(' ', $validator->errors()->all());
                continue;
            }

            $nilai = round((float) $raw['nilai_perolehan'], 2);
            $nilaiSisa = round((float) $raw['nilai_sisa_aset'], 2);
            $umurAktiva = ((int) $raw['umur_tahun'] * 12) + (int) $raw['umur_bulan'];
            if ($umurAktiva < 1) {
                $errors[] = "Baris {$rowNumber}: umur aktiva minimal 1 bulan.";
                continue;
            }
            $penyusutanBulanan = $nilai / $umurAktiva;
            $sisaPeriode = $penyusutanBulanan > 0 ? (int) ceil($nilaiSisa / $penyusutanBulanan) : 0;

            $dataImport[] = [
                'id_akun_aset' => (int) $raw['id_akun_aset'],
                'id_kelompok' => null,
                'nm_aktiva' => trim((string) $raw['nama_aktiva']),
                'tgl' => $tanggal,
                'h_perolehan' => $nilai,
                'nilai_buku_awal' => $nilaiSisa,
                'biaya_depresiasi' => round($penyusutanBulanan, 2),
                'umur_aktiva_bulan' => $umurAktiva,
                'sisa_umur_bulan' => $sisaPeriode,
                'akumulasi_penyusutan' => round($nilai - $nilaiSisa, 2),
                'admin' => Auth::user()->name,
                'sumber' => 'import',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if ($errors) {
            return back()->withErrors(['file_aktiva' => implode("\n", array_slice($errors, 0, 20))]);
        }
        if (! $dataImport) {
            return back()->withErrors(['file_aktiva' => 'Tidak ada baris data yang dapat diimpor.']);
        }

        DB::transaction(fn () => collect($dataImport)->chunk(200)->each(fn ($chunk) => DB::table('aktiva_pembukuan_baru')->insert($chunk->all())));

        return redirect()->route('aktiva')->with('sukses', count($dataImport) . ' data aktiva berhasil diimpor.');
    }

    public function load_aktiva()
    {
        $data =  [
            'title' => 'Add Aktiva',
            'akunAset' => DB::table('akun_perkiraan')->where('aktif',1)->where('tipe_akun','FASS')->whereNotNull('id_akun_induk')->orderBy('kode_perkiraan')->get()
        ];
        return view('aktiva.load_aktiva', $data);
    }

    public function tambah_baris_aktiva(Request $r)
    {
        $data =  [
            'akunAset' => DB::table('akun_perkiraan')->where('aktif',1)->where('tipe_akun','FASS')->whereNotNull('id_akun_induk')->orderBy('kode_perkiraan')->get(),
            'count' => $r->count

        ];
        return view('aktiva.tbh_baris', $data);
    }
    public function get_data_kelompok(Request $r)
    {
        $id_kelompok = $r->id_kelompok;
        $kelompok =  DB::table('kelompok_aktiva')->where('id_kelompok', $id_kelompok)->first();

        $data = [
            'nilai_persen' => $kelompok->tarif,
            'tahun' => $kelompok->umur
        ];
        echo json_encode($data);
    }

    public function save_aktiva(Request $r)
    {
        $r->validate([
            'id_akun_aset' => ['required', 'array', 'min:1'],
            'id_akun_aset.*' => ['required', 'integer', 'exists:akun_perkiraan,id_akun_perkiraan'],
            'nm_aktiva.*' => ['required', 'string', 'max:255'],
            'tgl.*' => ['required', 'date'],
            'h_perolehan.*' => ['required', 'numeric', 'min:0.01'],
            'nilai_sisa_aset.*' => ['required', 'numeric', 'min:0'],
            'umur_tahun.*' => ['required', 'integer', 'min:0'],
            'umur_bulan.*' => ['required', 'integer', 'min:0', 'max:11'],
        ]);

        $nm_aktiva = $r->nm_aktiva;
        $tgl = $r->tgl;
        $h_perolehan = $r->h_perolehan;
        $nilaiSisaAset = $r->nilai_sisa_aset;
        $umurTahun = $r->umur_tahun ?? [];
        $umurBulan = $r->umur_bulan ?? [];
        $akunAset = $r->id_akun_aset ?? [];

        DB::transaction(function () use ($akunAset, $nm_aktiva, $tgl, $h_perolehan, $nilaiSisaAset, $umurTahun, $umurBulan) {
        for ($x = 0; $x < count($akunAset); $x++) {
            $nilaiPerolehan = round((float) $h_perolehan[$x], 2);
            $nilaiBuku = round((float) $nilaiSisaAset[$x], 2);
            if ($nilaiBuku > $nilaiPerolehan) {
                throw ValidationException::withMessages([
                    "nilai_sisa_aset.{$x}" => 'Nilai Buku Saat Ini tidak boleh melebihi Nilai Perolehan.',
                ]);
            }
            $umurAktiva = ((int) ($umurTahun[$x] ?? 0) * 12) + (int) ($umurBulan[$x] ?? 0);
            if ($umurAktiva < 1) {
                throw ValidationException::withMessages([
                    "umur_tahun.{$x}" => 'Umur aktiva minimal 1 bulan.',
                ]);
            }
            $biaya_depresiasi = $nilaiPerolehan / $umurAktiva;
            $sisaPeriode = $biaya_depresiasi > 0 ? (int) ceil($nilaiBuku / $biaya_depresiasi) : 0;

            $data = [
                'id_kelompok' => null,
                'id_akun_aset' => $akunAset[$x] ?? null,
                'nm_aktiva' => $nm_aktiva[$x],
                'tgl' => $tgl[$x],
                'h_perolehan' => $nilaiPerolehan,
                'nilai_buku_awal' => $nilaiBuku,
                'biaya_depresiasi' => round($biaya_depresiasi, 2),
                'umur_aktiva_bulan' => $umurAktiva,
                'sisa_umur_bulan' => $sisaPeriode,
                'akumulasi_penyusutan' => round($nilaiPerolehan - $nilaiBuku, 2),
                'admin' => Auth::user()->name,
                'sumber' => 'manual',
                'created_at' => now(),
                'updated_at' => now(),
            ];
                DB::table('aktiva_pembukuan_baru')->insert($data);
        }
        });

        return redirect()->route('aktiva')->with('sukses', 'Data berhasil ditambahkan');
    }

    public function print(Request $r)
    {
        $year =  date("Y", strtotime($r->tahun));
        $tahun1 =  date("$year-01-01");
        $tahun1_1 =  date("$year-12-t");

        $tahun2 =  date('Y-01-01', strtotime("-1 year", strtotime($tahun1)));
        $tahun2_1 =  date('Y-12-31', strtotime("-1 year", strtotime($tahun1)));
        $data = [
            'title' => 'Print Aktiva',
            'kelompok' => DB::table('kelompok_aktiva')->get(),
            'tahun1' => $tahun1,
            'tahun1_1' => $tahun1_1,
            'tahun2' => $tahun2,
            'tahun2_1' => $tahun2_1

        ];
        return view('aktiva.print_aktiva', $data);
    }
}
