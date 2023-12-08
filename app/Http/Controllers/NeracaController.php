<?php

namespace App\Http\Controllers;

use App\Models\NeracaModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx\Rels;

class NeracaController extends Controller
{
    public function index(Request $r)
    {
        $bulan =  $r->bulan ?? date('m');
        $tahun =  $r->tahun ?? date('Y');
        $data = [
            'title' => 'Laporan Neraca',
            'bulan' => $bulan,
            'bulans' => DB::table('bulan')->get(),
            'tahun' => $tahun,
        ];
        return view('neraca.index', $data);
    }
}
