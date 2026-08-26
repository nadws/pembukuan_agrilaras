<?php

namespace App\Http\Controllers;

use App\Models\ProdukPerencanaan;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class ProdukPerencanaanController extends Controller
{
    public function index(Request $request)
    {
        $cari = trim((string) $request->input('cari', ''));
        $kategoriTerpilih = trim((string) $request->input('kategori', ''));
        $perPage = in_array((int) $request->input('per_page', 15), [10, 15, 25, 50], true)
            ? (int) $request->input('per_page', 15)
            : 15;

        $produk = DB::table('tb_produk_perencanaan as p')
            ->leftJoin('tb_satuan as dosis', 'dosis.id_satuan', '=', 'p.dosis_satuan')
            ->leftJoin('tb_satuan as campuran', 'campuran.id_satuan', '=', 'p.campuran_satuan')
            ->when($kategoriTerpilih !== '', fn ($query) => $query->where('p.kategori', $kategoriTerpilih))
            ->when($cari !== '', fn ($query) => $query->where(function ($search) use ($cari) {
                $search->where('p.nm_produk', 'like', "%{$cari}%")
                    ->orWhere('p.kode_accurate', 'like', "%{$cari}%")
                    ->orWhere('p.kegunaan', 'like', "%{$cari}%");
            }))
            ->orderBy('p.kategori')
            ->orderBy('p.nm_produk')
            ->paginate($perPage, [
                'p.*',
                'dosis.nm_satuan as nm_satuan_dosis',
                'campuran.nm_satuan as nm_satuan_campuran',
            ])->withQueryString();

        $kategoriOptions = $this->categories();
        $kategoriRingkasan = DB::table('tb_produk_perencanaan')
            ->select('kategori')->selectRaw('COUNT(*) as jumlah')
            ->whereNotNull('kategori')->groupBy('kategori')->orderBy('kategori')
            ->pluck('jumlah', 'kategori');

        return view('data_master.produk_perencanaan.index', [
            'title' => 'Master Produk Perencanaan',
            'produk' => $produk,
            'satuan' => DB::table('tb_satuan')->orderBy('nm_satuan')->get(),
            'kategori' => $kategoriOptions,
            'kategoriRingkasan' => $kategoriRingkasan,
            'kategoriTerpilih' => $kategoriTerpilih,
            'cari' => $cari,
            'perPage' => $perPage,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['tgl'] = $data['tgl'] ?? date('Y-m-d');
        $data['admin'] = auth()->user()->name;

        ProdukPerencanaan::create($data);

        return redirect()->route('produk-perencanaan.index')
            ->with('sukses', 'Produk perencanaan berhasil ditambahkan.');
    }

    public function update(Request $request, int $idProduk)
    {
        $produk = ProdukPerencanaan::findOrFail($idProduk);
        $data = $this->validated($request, $idProduk);
        $data['admin'] = auth()->user()->name;

        $produk->update($data);

        return redirect()->route('produk-perencanaan.index')
            ->with('sukses', 'Produk perencanaan berhasil diperbarui.');
    }

    public function destroy(int $idProduk)
    {
        $produk = ProdukPerencanaan::findOrFail($idProduk);
        $dipakaiDiStok = Schema::hasTable('stok_produk_perencanaan')
            && DB::table('stok_produk_perencanaan')->where('id_pakan', $idProduk)->exists();
        $dipakaiDiFaktur = Schema::hasTable('faktur_pembelian_detail')
            && DB::table('faktur_pembelian_detail')->where('pakan_id', $idProduk)->exists();

        if ($dipakaiDiStok || $dipakaiDiFaktur) {
            return redirect()->route('produk-perencanaan.index')
                ->with('error', 'Produk tidak dapat dihapus karena sudah digunakan pada transaksi stok atau faktur pembelian.');
        }

        try {
            $produk->delete();
        } catch (QueryException) {
            return redirect()->route('produk-perencanaan.index')
                ->with('error', 'Produk tidak dapat dihapus karena masih digunakan oleh data lain.');
        }

        return redirect()->route('produk-perencanaan.index')
            ->with('sukses', 'Produk perencanaan berhasil dihapus.');
    }

    private function validated(Request $request, ?int $idProduk = null): array
    {
        return $request->validate([
            'nm_produk' => ['required', 'string', 'max:200'],
            'kode_accurate' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('tb_produk_perencanaan', 'kode_accurate')->ignore($idProduk, 'id_produk'),
            ],
            'kategori' => ['required', 'string', 'max:50', Rule::in($this->categories())],
            'tgl' => ['required', 'date'],
            'dosis_satuan' => ['nullable', 'integer', 'exists:tb_satuan,id_satuan'],
            'campuran_satuan' => ['nullable', 'integer', 'exists:tb_satuan,id_satuan'],
            'kegunaan' => ['nullable', 'string'],
        ]);
    }

    private function categories(): array
    {
        return DB::table('tb_produk_perencanaan')->whereNotNull('kategori')
            ->distinct()->orderBy('kategori')->pluck('kategori')
            ->merge(['pakan', 'obat_pakan', 'obat_air', 'obat_ayam', 'vaksin'])
            ->filter()->unique()->sort()->values()->all();
    }
}
