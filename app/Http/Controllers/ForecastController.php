<?php

namespace App\Http\Controllers;

use App\Models\Forecast;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ForecastController extends Controller
{
    public function detailEggProduction()
    {
        $bulan = date('m');
        $tahun = date('Y');
        $data = [
            'title' => 'Detail Egg Production',
            'kandang' => Forecast::detailEggProduction(),
            'bulan' => DB::table('bulan')->where('bulan', '>=', $bulan)->get(),
            'tahun' => $tahun
        ];
        return view('forecast.detail_egg_production', $data);
    }
}
