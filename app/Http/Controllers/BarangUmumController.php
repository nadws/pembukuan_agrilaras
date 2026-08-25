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
        $stok = DB::table('pembukuan_baru_stok')
            ->select('id_produk')
            ->selectRaw('SUM(qty) as stok_sistem')
            ->selectRaw('SUM(qty * harga_satuan) as nilai_stok')
            ->groupBy('id_produk');
        $stokAwal = DB::table('pembukuan_baru_stok')
            ->where('nomor_transaksi', 'like', 'STOK-AWAL-%')
            ->select(['id_produk', 'qty as qty_stok_awal', 'harga_satuan as harga_stok_awal', 'tanggal as tanggal_stok_awal']);

        $barang = DB::table('tb_produk as p')
            ->leftJoin('tb_satuan as s', 's.id_satuan', '=', 'p.satuan_id')
            ->leftJoin('tb_gudang as g', 'g.id_gudang', '=', 'p.gudang_id')
            ->leftJoinSub($stok, 'st', 'st.id_produk', '=', 'p.id_produk')
            ->leftJoinSub($stokAwal, 'sa', 'sa.id_produk', '=', 'p.id_produk')
            ->where('p.kategori_id', self::KATEGORI_BARANG_UMUM)
            ->orderBy('p.nm_produk')
            ->get(['p.*', 's.nm_satuan', 'g.nm_gudang', 'st.stok_sistem', 'st.nilai_stok', 'sa.qty_stok_awal', 'sa.harga_stok_awal', 'sa.tanggal_stok_awal']);

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

    public function storeStokAwal(Request $request, ?int $idProduk = null)
    {
        $idProduk ??= (int) $request->input('id_produk');
        $request->merge(['id_produk' => $idProduk]);
        $request->validate(['id_produk' => ['required', 'integer', 'exists:tb_produk,id_produk']]);

        $barang = DB::table('tb_produk as p')
            ->leftJoin('tb_satuan as s', 's.id_satuan', '=', 'p.satuan_id')
            ->where('p.id_produk', $idProduk)
            ->where('p.kategori_id', self::KATEGORI_BARANG_UMUM)
            ->first(['p.id_produk', 'p.nm_produk', 's.nm_satuan']);
        abort_unless($barang, 404);

        $data = $request->validate([
            'id_produk' => ['required', 'integer', 'exists:tb_produk,id_produk'],
            'tanggal' => ['required', 'date'],
            'qty' => ['required', 'numeric', 'min:0'],
            'harga_satuan' => ['required', 'numeric', 'min:0'],
        ]);
        $nomor = 'STOK-AWAL-' . $idProduk;
        $now = now();

        DB::table('pembukuan_baru_stok')->updateOrInsert(
            ['nomor_transaksi' => $nomor, 'id_produk' => $idProduk],
            [
                'nama_produk' => $barang->nm_produk,
                'satuan' => $barang->nm_satuan,
                'qty' => $data['qty'],
                'harga_satuan' => $data['harga_satuan'],
                'tanggal' => $data['tanggal'],
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        return redirect()->route('barang-umum.index')
            ->with('sukses', 'Stok awal ' . $barang->nm_produk . ' berhasil disimpan dan sudah dapat di-opname.');
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
