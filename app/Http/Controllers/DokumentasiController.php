<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class DokumentasiController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->input('q', ''));

        return view('dokumentasi.index', [
            'title' => 'Pusat Panduan & Dokumentasi Pembukuan',
            'search' => $search,
        ]);
    }
}
