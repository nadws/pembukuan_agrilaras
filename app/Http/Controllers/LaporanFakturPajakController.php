<?php

namespace App\Http\Controllers;

use App\Exports\LaporanFakturPajakExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LaporanFakturPajakController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'tgl1' => ['nullable', 'date'],
            'tgl2' => ['nullable', 'date', 'after_or_equal:tgl1'],
            'npwp' => ['nullable', 'string', 'max:20'],
        ]);

        $tgl1 = $filters['tgl1'] ?? date('Y-m-01');
        $tgl2 = $filters['tgl2'] ?? date('Y-m-d');
        $npwpPenjual = trim((string) ($filters['npwp'] ?? '0858196173732000'));

        $export = new LaporanFakturPajakExport($tgl1, $tgl2, $npwpPenjual);
        $nota = $export->nota();
        $rows = collect($nota)->map(function ($n) {
            $dpp = 0.0;
            $ppnExact = 0.0;
            foreach (LaporanFakturPajakExport::detail($n) as $d) {
                $dpp += $d['dpp'];
                $ppnExact += $d['dpp'] * 11 / 12 * 0.12;
            }

            return (object) [
                'tgl' => $n->tgl,
                'no_nota' => $n->no_nota,
                'customer' => $n->nama,
                'npwp' => $n->npwp !== '' ? $n->npwp : ($n->nik !== '' ? $n->nik : '-'),
                'alamat' => $n->alamat,
                'dpp' => $dpp,
                'ppn' => round($ppnExact, 0),
            ];
        })->sortBy([['tgl', 'desc'], ['no_nota', 'desc']])->values();

        $exportButtonId = DB::table('permission_button as b')
            ->join('permission as p', 'p.id_permission', '=', 'b.permission_id')
            ->where('p.url', 'laporan.faktur-pajak')
            ->where('b.nm_permission_button', 'Export Excel')
            ->value('b.id_permission_button');

        return view('laporan.faktur-pajak', [
            'title' => 'Laporan Faktur Pajak',
            'btnExport' => $exportButtonId ? \SettingHal::btnHal($exportButtonId, auth()->id()) : null,
            'tgl1' => $tgl1,
            'tgl2' => $tgl2,
            'npwpPenjual' => $npwpPenjual,
            'rows' => $rows,
            'totalDpp' => (float) $rows->sum('dpp'),
            'totalPpn' => (float) $rows->sum('ppn'),
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $filters = $request->validate([
            'tgl1' => ['nullable', 'date'],
            'tgl2' => ['nullable', 'date', 'after_or_equal:tgl1'],
            'npwp' => ['required', 'regex:/^[0-9]{16}$/'],
        ]);

        $tgl1 = $filters['tgl1'] ?? date('Y-m-01');
        $tgl2 = $filters['tgl2'] ?? date('Y-m-d');

        return (new LaporanFakturPajakExport($tgl1, $tgl2, $filters['npwp']))
            ->unduh("pajak-keluaran-{$tgl1}-sd-{$tgl2}.xlsx");
    }
}
