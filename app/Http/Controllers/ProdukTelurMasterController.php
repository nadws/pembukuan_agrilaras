<?php

namespace App\Http\Controllers;

use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class ProdukTelurMasterController extends Controller
{
    public function index()
    {
        return view('data_master.produk_telur.index', [
            'title' => 'Master Produk Telur',
            'produk' => DB::table('telur_produk')->orderBy('nm_telur')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['kode_produk'] = strtoupper(trim($data['kode_produk']));
        $data['nm_telur'] = trim($data['nm_telur']);

        DB::table('telur_produk')->insert($data);

        return redirect()->route('produk-telur-master.index')
            ->with('sukses', 'Produk telur berhasil ditambahkan.');
    }

    public function update(Request $request, int $idProdukTelur)
    {
        abort_unless(DB::table('telur_produk')->where('id_produk_telur', $idProdukTelur)->exists(), 404);

        $data = $this->validated($request, $idProdukTelur);
        $data['kode_produk'] = strtoupper(trim($data['kode_produk']));
        $data['nm_telur'] = trim($data['nm_telur']);

        DB::table('telur_produk')->where('id_produk_telur', $idProdukTelur)->update($data);

        return redirect()->route('produk-telur-master.index')
            ->with('sukses', 'Produk telur berhasil diperbarui.');
    }

    public function destroy(int $idProdukTelur)
    {
        abort_unless(DB::table('telur_produk')->where('id_produk_telur', $idProdukTelur)->exists(), 404);

        $references = [
            ['table' => 'stok_telur', 'column' => 'id_telur'],
            ['table' => 'stok_telur_alpa', 'column' => 'id_telur'],
            ['table' => 'stok_telur_new', 'column' => 'id_telur'],
            ['table' => 'invoice_telur', 'column' => 'id_produk'],
            ['table' => 'harga_telur', 'column' => 'produk_telur_id'],
        ];

        foreach ($references as $reference) {
            if (Schema::hasTable($reference['table'])
                && Schema::hasColumn($reference['table'], $reference['column'])
                && DB::table($reference['table'])->where($reference['column'], $idProdukTelur)->exists()) {
                return redirect()->route('produk-telur-master.index')
                    ->with('error', 'Produk telur tidak dapat dihapus karena sudah digunakan pada transaksi atau stok.');
            }
        }

        try {
            DB::table('telur_produk')->where('id_produk_telur', $idProdukTelur)->delete();
        } catch (QueryException) {
            return redirect()->route('produk-telur-master.index')
                ->with('error', 'Produk telur tidak dapat dihapus karena masih digunakan oleh data lain.');
        }

        return redirect()->route('produk-telur-master.index')
            ->with('sukses', 'Produk telur berhasil dihapus.');
    }

    private function validated(Request $request, ?int $idProdukTelur = null): array
    {
        return $request->validate([
            'kode_produk' => [
                'required',
                'string',
                'max:25',
                Rule::unique('telur_produk', 'kode_produk')->ignore($idProdukTelur, 'id_produk_telur'),
            ],
            'nm_telur' => [
                'required',
                'string',
                'max:50',
                Rule::unique('telur_produk', 'nm_telur')->ignore($idProdukTelur, 'id_produk_telur'),
            ],
        ]);
    }
}
