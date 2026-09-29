<?php

namespace App\Http\Controllers;

use App\Exports\TagihanMcdExport;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LaporanTagihanCustomerController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'tgl1' => ['nullable', 'date'],
            'tgl2' => ['nullable', 'date', 'after_or_equal:tgl1'],
            'format' => ['nullable', 'in:mcd'],
        ]);

        $tgl1 = $filters['tgl1'] ?? date('Y-m-01');
        $tgl2 = $filters['tgl2'] ?? date('Y-m-d');
        $format = $filters['format'] ?? 'mcd';
        $rows = (new TagihanMcdExport($tgl1, $tgl2))->baris();

        return view('laporan.tagihan-customer', [
            'title' => 'Tagihan Customer',
            'tgl1' => $tgl1,
            'tgl2' => $tgl2,
            'format' => $format,
            'rows' => $rows,
        ]);
    }

    public function export(Request $request)
    {
        $filters = $request->validate([
            'tgl1' => ['required', 'date'],
            'tgl2' => ['required', 'date', 'after_or_equal:tgl1'],
            'format' => ['required', 'in:mcd'],
        ]);

        $filename = "tagihan-mcd-{$filters['tgl1']}-sd-{$filters['tgl2']}.xlsx";

        return (new TagihanMcdExport($filters['tgl1'], $filters['tgl2']))->unduh($filename);
    }
}
