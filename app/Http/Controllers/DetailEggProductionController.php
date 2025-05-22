<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DetailEggProductionController extends Controller
{
    public function index()
    {
        $data = [
            'index' => 'detail_egg_production',
        ];
        return view('detail_egg_production.index', $data);
    }
}
