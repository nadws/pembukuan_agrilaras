<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx\Rels;

class CostGlobalController extends Controller
{
    public function index()
    {
        $data = [
            'title' => 'Cost Global'
        ];
    }
}
