<?php

namespace App\Http\Controllers;

use App\Models\Akun;
use App\Models\Jurnal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SaldoController extends Controller
{
    public function index(Request $r)
    {
        $data =  [
            'title' => 'Saldo Awal',
            'akun' => DB::select("SELECT a.* , b.debit, b.kredit, b.penutup
            FROM akun as a 
            left join (
            SELECT b.id_akun, b.penutup, sum(b.debit) as debit , sum(b.kredit) as kredit FROM jurnal as b 
            where b.saldo = 'Y'
            group by b.id_akun
            ) as b on b.id_akun = a.id_akun
            order by a.kode_akun ASC
            "),
            'tgl_saldo' => DB::SelectOne("SELECT a.tgl FROM jurnal as a where a.saldo = 'Y'")

        ];
        return view('saldo.index', $data);
    }

    public function saveSaldo(Request $r)
    {
        $id_akun = $r->id_akun;
        Jurnal::where('saldo', 'Y')->delete();
        for ($i = 0; $i < count($id_akun); $i++) {
            $data = [
                'id_akun' => $r->id_akun[$i],
                'debit' => $r->debit[$i],
                'kredit' => $r->kredit[$i],
                'ket' => 'Saldo Awal',
                'id_buku' => '1',
                'tgl' => date('Y-01-01'),
                'admin' => Auth::user()->name,
                'saldo' => 'Y'
            ];
            Jurnal::create($data);
        }
        return redirect()->route('saldo_awal')->with('sukses', 'Data berhasil ditambahkan');
    }
}
