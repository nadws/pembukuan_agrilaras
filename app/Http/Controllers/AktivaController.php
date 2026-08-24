<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
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
        $workbook = new Spreadsheet();
        $dataSheet = $workbook->getActiveSheet();
        $dataSheet->setTitle('Data Aktiva');
        $headers = ['id_akun_aset', 'id_kelompok', 'nama_aktiva', 'tanggal_perolehan', 'nilai_perolehan', 'sisa_umur_bulan'];
        $dataSheet->fromArray($headers, null, 'A1');
        $dataSheet->fromArray([74, 1, 'Contoh Aktiva', '2024-01-15', 12000000, 24], null, 'A2');
        $dataSheet->getStyle('A1:F1')->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
        $dataSheet->getStyle('A1:F1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF304F9E');
        $dataSheet->getStyle('D2:D1000')->getNumberFormat()->setFormatCode('yyyy-mm-dd');
        $dataSheet->getStyle('E2:E1000')->getNumberFormat()->setFormatCode('#,##0');
        $dataSheet->freezePane('A2');
        $dataSheet->setAutoFilter('A1:F1');
        foreach (['A' => 17, 'B' => 15, 'C' => 30, 'D' => 21, 'E' => 20, 'F' => 19] as $column => $width) {
            $dataSheet->getColumnDimension($column)->setWidth($width);
        }

        $referenceSheet = $workbook->createSheet();
        $referenceSheet->setTitle('Referensi');
        $referenceSheet->setCellValue('A1', 'REFERENSI AKUN ASET TETAP TUJUAN');
        $referenceSheet->fromArray(['id_akun_aset', 'kode_perkiraan', 'nama_akun'], null, 'A3');
        $akunRows = DB::table('akun_perkiraan')
            ->where('aktif', 1)->where('tipe_akun', 'FASS')
            ->whereNotNull('id_akun_induk')->orderBy('kode_perkiraan')
            ->get(['id_akun_perkiraan', 'kode_perkiraan', 'nama'])
            ->map(fn ($akun) => [$akun->id_akun_perkiraan, $akun->kode_perkiraan, $akun->nama])->all();
        if ($akunRows) $referenceSheet->fromArray($akunRows, null, 'A4');

        $referenceSheet->setCellValue('E1', 'REFERENSI KELOMPOK AKTIVA');
        $referenceSheet->fromArray(['id_kelompok', 'nama_kelompok', 'umur_tahun', 'tarif_tahunan'], null, 'E3');
        $kelompokRows = DB::table('kelompok_aktiva')->orderBy('id_kelompok')->get()
            ->map(fn ($kelompok) => [$kelompok->id_kelompok, $kelompok->nm_kelompok, $kelompok->umur, $kelompok->tarif])->all();
        if ($kelompokRows) $referenceSheet->fromArray($kelompokRows, null, 'E4');

        foreach (['A1:C1', 'E1:H1'] as $range) {
            $referenceSheet->mergeCells($range);
            $referenceSheet->getStyle($range)->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
            $referenceSheet->getStyle($range)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF304F9E');
            $referenceSheet->getStyle($range)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }
        foreach (['A3:C3', 'E3:H3'] as $range) {
            $referenceSheet->getStyle($range)->getFont()->setBold(true);
            $referenceSheet->getStyle($range)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFDCE6F8');
        }
        foreach (['A' => 17, 'B' => 20, 'C' => 30, 'E' => 15, 'F' => 22, 'G' => 15, 'H' => 17] as $column => $width) {
            $referenceSheet->getColumnDimension($column)->setWidth($width);
        }
        $referenceSheet->getStyle('H4:H100')->getNumberFormat()->setFormatCode('0.00%');
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
            $rows = $sheet->toArray(null, true, true, false);
        } catch (\Throwable $e) {
            return back()->withErrors(['file_aktiva' => 'File tidak dapat dibaca. Pastikan memakai format CSV atau Excel yang valid.']);
        }

        if (count($rows) < 2) {
            return back()->withErrors(['file_aktiva' => 'File tidak memiliki data aktiva.']);
        }

        $requiredHeaders = ['id_akun_aset', 'id_kelompok', 'nama_aktiva', 'tanggal_perolehan', 'nilai_perolehan', 'sisa_umur_bulan'];
        $headers = array_map(fn ($value) => strtolower(trim(preg_replace('/^\xEF\xBB\xBF/', '', (string) $value))), $rows[0]);
        $indexes = array_flip($headers);
        $missing = array_values(array_diff($requiredHeaders, $headers));

        if ($missing) {
            return back()->withErrors(['file_aktiva' => 'Kolom wajib tidak ditemukan: ' . implode(', ', $missing) . '. Silakan unduh Format Import terbaru.']);
        }

        $akunAset = DB::table('akun_perkiraan')->where('aktif', 1)->where('tipe_akun', 'FASS')
            ->pluck('id_akun_perkiraan')->map(fn ($id) => (int) $id)->all();
        $kelompokMap = DB::table('kelompok_aktiva')->get()->keyBy(fn ($row) => (int) $row->id_kelompok);
        $dataImport = [];
        $errors = [];

        foreach (array_slice($rows, 1) as $offset => $row) {
            $rowNumber = $offset + 2;
            if (collect($row)->filter(fn ($value) => $value !== null && trim((string) $value) !== '')->isEmpty()) {
                continue;
            }

            $raw = [];
            foreach ($requiredHeaders as $header) {
                $raw[$header] = $row[$indexes[$header]] ?? null;
            }

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
                'id_kelompok' => $raw['id_kelompok'],
                'nama_aktiva' => trim((string) $raw['nama_aktiva']),
                'nilai_perolehan' => $raw['nilai_perolehan'],
                'sisa_umur_bulan' => $raw['sisa_umur_bulan'],
            ], [
                'id_akun_aset' => ['required', 'integer', 'in:' . implode(',', $akunAset)],
                'id_kelompok' => ['required', 'integer', 'in:' . implode(',', $kelompokMap->keys()->all())],
                'nama_aktiva' => ['required', 'string', 'max:255'],
                'nilai_perolehan' => ['required', 'numeric', 'min:0.01'],
                'sisa_umur_bulan' => ['nullable', 'integer', 'min:1'],
            ]);

            if ($validator->fails()) {
                $errors[] = "Baris {$rowNumber}: " . implode(' ', $validator->errors()->all());
                continue;
            }

            $idKelompok = (int) $raw['id_kelompok'];
            $nilai = round((float) $raw['nilai_perolehan'], 2);
            $sisa = $raw['sisa_umur_bulan'] === null || trim((string) $raw['sisa_umur_bulan']) === '' ? null : (int) $raw['sisa_umur_bulan'];
            $kelompok = $kelompokMap[$idKelompok];
            $penyusutanBulanan = $sisa ? $nilai / $sisa : ($nilai * (float) $kelompok->tarif) / 12;

            $dataImport[] = [
                'id_akun_aset' => (int) $raw['id_akun_aset'],
                'id_kelompok' => $idKelompok,
                'nm_aktiva' => trim((string) $raw['nama_aktiva']),
                'tgl' => $tanggal,
                'h_perolehan' => $nilai,
                'biaya_depresiasi' => round($penyusutanBulanan, 2),
                'sisa_umur_bulan' => $sisa,
                'akumulasi_penyusutan' => 0,
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
            'kelompok' => DB::table('kelompok_aktiva')->get(), 'akunAset' => DB::table('akun_perkiraan')->where('aktif',1)->where('tipe_akun','FASS')->orderBy('kode_perkiraan')->get()
        ];
        return view('aktiva.load_aktiva', $data);
    }

    public function tambah_baris_aktiva(Request $r)
    {
        $data =  [
            'kelompok' => DB::table('kelompok_aktiva')->get(), 'akunAset' => DB::table('akun_perkiraan')->where('aktif',1)->where('tipe_akun','FASS')->orderBy('kode_perkiraan')->get(),
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
        $id_kelompok = $r->id_kelompok;
        $nm_aktiva = $r->nm_aktiva;
        $tgl = $r->tgl;
        $h_perolehan = $r->h_perolehan;
        $sisaUmur = $r->sisa_umur_bulan ?? [];
        $akunAset = $r->id_akun_aset ?? [];

        for ($x = 0; $x < count($id_kelompok); $x++) {
            $kelompok =  DB::table('kelompok_aktiva')->where('id_kelompok', $id_kelompok[$x])->first();
            $biaya_depresiasi = ($h_perolehan[$x] * $kelompok->tarif) / 12;
            $sisa = (int) ($sisaUmur[$x] ?? 0);
            if ($sisa > 0) {
                $biaya_depresiasi = $h_perolehan[$x] / $sisa;
            }

            $data = [
                'id_kelompok' => $id_kelompok[$x],
                'id_akun_aset' => $akunAset[$x] ?? null,
                'nm_aktiva' => $nm_aktiva[$x],
                'tgl' => $tgl[$x],
                'h_perolehan' => $h_perolehan[$x],
                'biaya_depresiasi' => $biaya_depresiasi,
                'sisa_umur_bulan' => $sisa ?: null,
                'akumulasi_penyusutan' => $sisa > 0 ? max(0, $h_perolehan[$x] - ($biaya_depresiasi * $sisa)) : 0,
                'admin' => Auth::user()->name,
            ];
                DB::table('aktiva_pembukuan_baru')->insert($data);
        }

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
