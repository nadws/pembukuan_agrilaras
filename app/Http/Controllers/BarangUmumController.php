<?php

namespace App\Http\Controllers;

use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class BarangUmumController extends Controller
{
    private const KATEGORI_BARANG_UMUM = 1;
    private const DEPARTEMEN_DEFAULT = 1;

    public function index()
    {
        $barang = DB::table('tb_produk as p')
            ->leftJoin('tb_satuan as s', 's.id_satuan', '=', 'p.satuan_id')
            ->leftJoin('tb_gudang as g', 'g.id_gudang', '=', 'p.gudang_id')
            ->where('p.kategori_id', self::KATEGORI_BARANG_UMUM)
            ->orderBy('p.nm_produk')
            ->get(['p.*', 's.nm_satuan', 'g.nm_gudang']);

        return view('data_master.barang_umum.index', [
            'title' => 'Master Barang Umum',
            'barang' => $barang,
            'satuan' => DB::table('tb_satuan')->orderBy('nm_satuan')->get(),
            'gudang' => DB::table('tb_gudang')->orderBy('nm_gudang')->get(),
            'kodeBerikutnya' => ((int) DB::table('tb_produk')->max('kd_produk')) + 1,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data += [
            'kategori_id' => self::KATEGORI_BARANG_UMUM,
            'departemen_id' => self::DEPARTEMEN_DEFAULT,
            'img' => '',
            'tgl' => date('Y-m-d'),
            'admin' => auth()->user()->name,
        ];
        $data['nm_produk'] = trim($data['nm_produk']);

        DB::table('tb_produk')->insert($data);

        return redirect()->route('barang-umum.index')->with('sukses', 'Barang umum berhasil ditambahkan.');
    }

    public function update(Request $request, int $idProduk)
    {
        $barang = DB::table('tb_produk')->where('id_produk', $idProduk)
            ->where('kategori_id', self::KATEGORI_BARANG_UMUM)->first();
        abort_unless($barang, 404);

        $data = $this->validated($request, $idProduk);
        $data['nm_produk'] = trim($data['nm_produk']);
        $data['tgl'] = date('Y-m-d');
        $data['admin'] = auth()->user()->name;

        DB::table('tb_produk')->where('id_produk', $idProduk)->update($data);

        return redirect()->route('barang-umum.index')->with('sukses', 'Barang umum berhasil diperbarui.');
    }

    public function destroy(int $idProduk)
    {
        $barang = DB::table('tb_produk')->where('id_produk', $idProduk)
            ->where('kategori_id', self::KATEGORI_BARANG_UMUM)->first();
        abort_unless($barang, 404);

        foreach (['pembelian', 'penjualan_agl', 'tb_stok_produk', 'penjualan', 'pembukuan_baru_stok', 'pembukuan_baru_stok_opname'] as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'id_produk')
                && DB::table($table)->where('id_produk', $idProduk)->exists()) {
                return redirect()->route('barang-umum.index')
                    ->with('error', 'Barang tidak dapat dihapus karena sudah digunakan pada transaksi atau stok.');
            }
        }

        try {
            DB::table('tb_produk')->where('id_produk', $idProduk)->delete();
        } catch (QueryException) {
            return redirect()->route('barang-umum.index')
                ->with('error', 'Barang tidak dapat dihapus karena masih digunakan oleh data lain.');
        }

        return redirect()->route('barang-umum.index')->with('sukses', 'Barang umum berhasil dihapus.');
    }

    private function validated(Request $request, ?int $idProduk = null): array
    {
        return $request->validate([
            'kd_produk' => [
                'required', 'integer', 'min:1',
                Rule::unique('tb_produk', 'kd_produk')->ignore($idProduk, 'id_produk'),
            ],
            'nm_produk' => ['required', 'string', 'max:100'],
            'satuan_id' => ['required', 'integer', 'exists:tb_satuan,id_satuan'],
            'gudang_id' => ['required', 'integer', 'exists:tb_gudang,id_gudang'],
            'kontrol_stok' => ['required', Rule::in(['Y', 'T'])],
        ]);
    }
}
