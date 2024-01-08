<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerController extends Controller
{
    public function index()
    {
        $data = [
            'title' => 'Data Customer',
            'customer' => DB::table('customer')->get()
        ];
        return view('customer.customer', $data);
    }

    public function create(Request $r)
    {
        DB::table('customer')->insert([
            'nm_customer' => $r->nm_customer,
            'alamat' => $r->alamat,
            'no_telp' => $r->telepon,
            'npwp' => $r->npwp,
            'ktp' => $r->ktp,
        ]);

        return redirect()->route('customer.index')->with('sukses', 'Data Berhasil Ditambahkan');
    }

    public function edit($id_customer)
    {
        $data = [
            'customer' => DB::table('customer')->where('id_customer', $id_customer)->first(),
            'id_customer' => $id_customer
        ];
        return view('customer.edit', $data);
    }

    public function update(Request $r)
    {
        $data = [
            'nm_customer' => $r->nm_customer,
            'alamat' => $r->alamat,
            'no_telp' => $r->telepon,
            'npwp' => $r->npwp,
            'ktp' => $r->ktp,
        ];
        DB::table('customer')->where('id_customer', $r->id_customer)->update($data);

        return redirect()->route('customer.index')->with('sukses', 'Data Berhasil Diedit');
    }

    public function delete($id_customer)
    {
        DB::table('customer')->where('id_customer', $id_customer)->delete();
        return redirect()->route('customer.index')->with('sukses', 'Data Berhasil Dihapus');
    }
}
