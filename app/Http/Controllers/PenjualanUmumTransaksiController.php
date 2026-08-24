<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PenjualanUmumTransaksiController extends Controller
{
    private string $tipeJurnal = 'Penjualan Umum';

    public function index(Request $request)
    {
        $awal = $request->input('tanggal_awal', date('Y-m-01'));
        $akhir = $request->input('tanggal_akhir', date('Y-m-d'));
        $cari = trim((string) $request->input('cari', ''));
        $penjualan = DB::table('penjualan_agl as p')
            ->leftJoin('customer as c', 'c.id_customer', '=', 'p.id_customer')
            ->where('p.lokasi', 'alpa')->whereBetween('p.tgl', [$awal, $akhir])
            ->when($cari !== '', fn ($q) => $q->where(fn ($s) => $s->where('p.urutan', 'like', "%{$cari}%")->orWhere('c.nm_customer', 'like', "%{$cari}%")))
            ->select('p.urutan', 'p.tgl', 'p.id_customer', 'p.status', 'c.nm_customer', DB::raw('SUM(p.total_rp) as total_rp'), DB::raw('COUNT(p.id_penjualan) as jumlah_item'))
            ->groupBy('p.urutan', 'p.tgl', 'p.id_customer', 'p.status', 'c.nm_customer')
            ->orderByDesc('p.urutan')->get();

        return view('transaksi.penjualan_umum.index', compact('penjualan', 'awal', 'akhir', 'cari'));
    }

    public function create()
    {
        return view('transaksi.penjualan_umum.create', [
            'customers' => DB::table('customer')->orderBy('nm_customer')->get(),
            'produk' => $this->produk(),
            'akunPembayaran' => $this->akunPembayaran(),
            'nota' => ((int) DB::table('penjualan_agl')->where('lokasi', 'alpa')->max('urutan')) + 1,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'tgl' => ['required', 'date'], 'id_customer' => ['required', 'integer'],
            'id_akun_pembayaran' => ['required', 'integer', 'exists:akun_perkiraan,id_akun_perkiraan'],
            'id_produk' => ['required', 'array', 'min:1'], 'id_produk.*' => ['required', 'integer', 'exists:tb_produk,id_produk'],
            'qty' => ['required', 'array'], 'qty.*' => ['required', 'numeric', 'min:0'],
            'rp_satuan' => ['required', 'array'], 'rp_satuan.*' => ['required', 'numeric', 'min:0'],
        ]);
        $this->validateProdukGudang($data['id_produk']);
        $this->saveNota($data, ((int) DB::table('penjualan_agl')->where('lokasi', 'alpa')->max('urutan')) + 1);
        return redirect()->route('transaksi.penjualan-umum.index')->with('sukses', 'Penjualan umum berhasil disimpan.');
    }

    public function detail(int $urutan)
    {
        return view('transaksi.penjualan_umum.detail', $this->notaData($urutan));
    }

    public function edit(int $urutan)
    {
        $data = $this->notaData($urutan);
        $data['customers'] = DB::table('customer')->orderBy('nm_customer')->get();
        $data['produk'] = $this->produk();
        $data['akunPembayaran'] = $this->akunPembayaran();
        $data['idAkunPembayaran'] = DB::table('jurnal_perkiraan')->where('nomor_transaksi', 'PU-' . $urutan)->where('tipe_transaksi', $this->tipeJurnal)->where('debit', '>', 0)->value('id_akun_perkiraan');
        return view('transaksi.penjualan_umum.edit', $data);
    }

    public function update(Request $request, int $urutan)
    {
        $data = $request->validate([
            'tgl' => ['required', 'date'], 'id_customer' => ['required', 'integer'],
            'id_akun_pembayaran' => ['required', 'integer', 'exists:akun_perkiraan,id_akun_perkiraan'],
            'id_produk' => ['required', 'array', 'min:1'], 'id_produk.*' => ['required', 'integer', 'exists:tb_produk,id_produk'],
            'qty' => ['required', 'array'], 'qty.*' => ['required', 'numeric', 'min:0'],
            'rp_satuan' => ['required', 'array'], 'rp_satuan.*' => ['required', 'numeric', 'min:0'],
        ]);
        $this->validateProdukGudang($data['id_produk']);
        abort_unless(DB::table('penjualan_agl')->where('urutan', $urutan)->where('lokasi', 'alpa')->exists(), 404);
        $this->hapusJurnal('PU-' . $urutan);
        DB::table('penjualan_agl')->where('urutan', $urutan)->where('lokasi', 'alpa')->delete();
        $this->saveNota($data, $urutan);
        return redirect()->route('transaksi.penjualan-umum.index')->with('sukses', 'Penjualan umum berhasil diperbarui.');
    }

    public function destroy(int $urutan)
    {
        DB::transaction(function () use ($urutan) {
            abort_unless(DB::table('penjualan_agl')->where('urutan', $urutan)->where('lokasi', 'alpa')->delete(), 404);
            $this->hapusJurnal('PU-' . $urutan);
        });
        return redirect()->route('transaksi.penjualan-umum.index')->with('sukses', 'Penjualan umum berhasil dihapus.');
    }

    private function saveNota(array $data, int $urutan): void
    {
        $status = DB::table('akun_perkiraan')->where('id_akun_perkiraan', $data['id_akun_pembayaran'])->value('tipe_akun') === 'AREC' ? 'unpaid' : 'paid';
        $nota = 'PU-' . $urutan;
        $now = now();
        $rows = [];
        foreach ($data['id_produk'] as $i => $idProduk) {
            $qty = (float) $data['qty'][$i]; $harga = (float) $data['rp_satuan'][$i];
            $rows[] = ['urutan' => $urutan, 'nota_manual' => $nota, 'tgl' => $data['tgl'], 'kode' => 'PU', 'id_customer' => $data['id_customer'], 'id_produk' => $idProduk, 'qty' => $qty, 'rp_satuan' => $harga, 'total_rp' => $qty * $harga, 'lokasi' => 'alpa', 'status' => $status, 'admin' => auth()->user()->name, 'cek' => 'T', 'void' => 'T'];
        }
        DB::transaction(function () use ($rows, $data, $urutan, $nota) {
            DB::table('penjualan_agl')->insert($rows);
            $this->syncJurnal($nota, $data['tgl'], $data['id_customer'], (int) $data['id_akun_pembayaran'], $urutan);
        });
    }

    private function syncJurnal(string $nota, string $tanggal, int $idCustomer, int $idAkunPembayaran, int $urutan): void
    {
        $total = (float) DB::table('penjualan_agl')->where('urutan', $urutan)->where('lokasi', 'alpa')->sum('total_rp');
        $akunBayar = DB::table('akun_perkiraan')->where('id_akun_perkiraan', $idAkunPembayaran)->where('aktif', 1)->whereIn('tipe_akun', ['BANK', 'AREC'])->first();
        $akunJual = DB::table('akun_perkiraan')->where('kode_perkiraan', '400003')->where('aktif', 1)->first();
        abort_unless($akunBayar && $akunJual, 422, 'Akun pembayaran atau Penjualan Umum belum tersedia.');
        $customer = DB::table('customer')->where('id_customer', $idCustomer)->value('nm_customer') ?? 'Customer';
        $now = now(); $batch = DB::table('impor_jurnal_perkiraan')->insertGetId(['nama_file' => 'Penjualan umum ' . $nota, 'hash_file' => hash('sha256', 'penjualan-umum|' . $nota), 'periode_awal' => $tanggal, 'periode_akhir' => $tanggal, 'jumlah_transaksi' => 1, 'jumlah_detail' => 2, 'total_debit' => $total, 'total_kredit' => $total, 'status' => 'aktif', 'diimpor_oleh' => auth()->id(), 'created_at' => $now, 'updated_at' => $now]);
        DB::table('jurnal_perkiraan')->insert([
            ['id_impor_jurnal_perkiraan' => $batch, 'id_akun_perkiraan' => $akunBayar->id_akun_perkiraan, 'tanggal' => $tanggal, 'nomor_transaksi' => $nota, 'tipe_transaksi' => $this->tipeJurnal, 'urutan_detail' => 1, 'deskripsi' => 'Penerimaan penjualan umum dari ' . $customer, 'debit' => $total, 'kredit' => 0, 'created_at' => $now, 'updated_at' => $now],
            ['id_impor_jurnal_perkiraan' => $batch, 'id_akun_perkiraan' => $akunJual->id_akun_perkiraan, 'tanggal' => $tanggal, 'nomor_transaksi' => $nota, 'tipe_transaksi' => $this->tipeJurnal, 'urutan_detail' => 2, 'deskripsi' => 'Pendapatan penjualan umum kepada ' . $customer, 'debit' => 0, 'kredit' => $total, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    private function hapusJurnal(string $nota): void
    {
        $ids = DB::table('jurnal_perkiraan')->where('nomor_transaksi', $nota)->where('tipe_transaksi', $this->tipeJurnal)->pluck('id_impor_jurnal_perkiraan')->unique();
        DB::table('jurnal_perkiraan')->where('nomor_transaksi', $nota)->where('tipe_transaksi', $this->tipeJurnal)->delete();
        if ($ids->isNotEmpty()) DB::table('impor_jurnal_perkiraan')->whereIn('id_impor_jurnal_perkiraan', $ids)->delete();
    }

    private function notaData(int $urutan): array
    {
        $items = DB::table('penjualan_agl as p')->leftJoin('customer as c', 'c.id_customer', '=', 'p.id_customer')->leftJoin('tb_produk as pr', 'pr.id_produk', '=', 'p.id_produk')->where('p.urutan', $urutan)->where('p.lokasi', 'alpa')->select('p.*', 'c.nm_customer', 'pr.nm_produk')->get();
        abort_unless($items->isNotEmpty(), 404);
        return ['nota' => $items->first(), 'items' => $items, 'total' => $items->sum('total_rp')];
    }

    private function produk() { return DB::table('tb_produk as p')->leftJoin('tb_satuan as s', 's.id_satuan', '=', 'p.satuan_id')->where('p.kategori_id', 3)->where('p.gudang_id', 2)->orderBy('p.nm_produk')->get(['p.id_produk', 'p.nm_produk', 's.nm_satuan']); }
    private function validateProdukGudang(array $produkIds): void
    {
        $allowed = DB::table('tb_produk')->where('kategori_id', 3)->where('gudang_id', 2)->whereIn('id_produk', $produkIds)->count();
        abort_unless($allowed === count(array_unique($produkIds)), 422, 'Produk harus berasal dari kategori 3 dan gudang 2.');
    }
    private function akunPembayaran() { return DB::table('akun_perkiraan')->where('aktif', 1)->whereIn('tipe_akun', ['BANK', 'AREC'])->orderBy('kode_perkiraan')->get(['id_akun_perkiraan', 'kode_perkiraan', 'nama']); }
}
