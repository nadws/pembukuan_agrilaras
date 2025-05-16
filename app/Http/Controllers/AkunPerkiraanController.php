<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

class AkunPerkiraanController extends Controller
{
    public function index()
    {
        $data = [
            'title' => 'Akun Perkiraan',
            'akun' => DB::table('akun_accurate')->whereNull('akun_induk')->get(),
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

        $file = $r->file('file');
        $spreadsheet = IOFactory::load($file->getPathname());
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();

        // Skip header


        foreach (array_slice($rows, 1) as $row) {
            $kode = $row[1];
            $debit = floatval(str_replace(',', '', $row[4]));


            // Jika nilainya 'kosong', ubah jadi null
            DB::table('jurnal_accurate')->insert([
                'tgl' => $tgl,
                'kode' => $kode,
                'debit' => $debit,
                'kredit' => 0,
                'tgl_import' => date('Y-m-d'),
                'buku' => '2',
                'admin' => auth()->user()->name
            ]);
        }

        return redirect()->route('akun_perkiraan')->with('sukses', 'Data berhasil diimport');
    }
}
