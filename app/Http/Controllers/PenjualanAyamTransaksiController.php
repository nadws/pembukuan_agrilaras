<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PenjualanAyamTransaksiController extends Controller
{
    public function index(Request $request)
    {
        $awal = $request->input('tanggal_awal', date('Y-m-01'));
        $akhir = $request->input('tanggal_akhir', date('Y-m-d'));
        $cari = trim((string) $request->input('cari', ''));
        $penjualan = DB::table('invoice_ayam as i')
            ->leftJoin('customer as c', 'c.id_customer', '=', 'i.id_customer')
            ->where('i.lokasi', 'alpa')->whereBetween('i.tgl', [$awal, $akhir])
            ->when($cari !== '', fn ($q) => $q->where(fn ($s) => $s->where('i.no_nota', 'like', "%{$cari}%")->orWhere('c.nm_customer', 'like', "%{$cari}%")))
            ->select('i.no_nota', 'i.tgl', 'i.qty', 'i.h_satuan', 'i.status', 'c.nm_customer', DB::raw('SUM(i.qty * i.h_satuan) as total_rp'))
            ->groupBy('i.no_nota', 'i.tgl', 'i.qty', 'i.h_satuan', 'i.status', 'c.nm_customer')
            ->orderByDesc('i.urutan')->get();

        return view('transaksi.penjualan_ayam.index', compact('penjualan', 'awal', 'akhir', 'cari'));
    }

    public function create()
    {
        $last = DB::table('invoice_ayam')->where('lokasi', 'alpa')->orderByDesc('urutan')->value('urutan');
        return view('transaksi.penjualan_ayam.create', [
            'customers' => DB::table('customer')->orderBy('nm_customer')->get(),
            'akunPembayaran' => $this->akunPembayaran(),
            'nota' => ((int) $last) + 1,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'tgl' => ['required', 'date'], 'id_customer' => ['required', 'integer'],
            'qty' => ['required', 'numeric', 'min:0'], 'h_satuan' => ['required', 'numeric', 'min:0'],
            'id_akun_pembayaran' => ['required', 'integer', 'exists:akun_perkiraan,id_akun_perkiraan'],
        ]);
        $urutan = ((int) DB::table('invoice_ayam')->where('lokasi', 'alpa')->max('urutan')) + 1;
        $nota = 'PA-' . $urutan;
        $urutanCustomer = ((int) DB::table('invoice_ayam')->where('lokasi', 'alpa')->where('id_customer', $data['id_customer'])->max('urutan_customer')) + 1;
        $data['status'] = $this->statusPembayaran((int) $data['id_akun_pembayaran']);
        DB::transaction(function () use ($data, $urutan, $nota, $urutanCustomer) {
            DB::table('invoice_ayam')->insert($this->invoiceRow($data, $urutan, $nota, $urutanCustomer));
            $this->syncJurnal($nota, $data['tgl'], $data['id_customer'], (int) $data['id_akun_pembayaran']);
        });
        return redirect()->route('transaksi.penjualan-ayam.index')->with('sukses', 'Penjualan ayam berhasil disimpan.');
    }

    public function detail(string $noNota) { return view('transaksi.penjualan_ayam.detail', $this->notaData($noNota)); }

    public function edit(string $noNota)
    {
        $data = $this->notaData($noNota);
        $data['customers'] = DB::table('customer')->orderBy('nm_customer')->get();
        $data['akunPembayaran'] = $this->akunPembayaran();
        $data['idAkunPembayaran'] = DB::table('jurnal_perkiraan')->where('nomor_transaksi', $noNota)->where('tipe_transaksi', 'Penjualan Ayam')->where('debit', '>', 0)->value('id_akun_perkiraan');
        return view('transaksi.penjualan_ayam.edit', $data);
    }

    public function update(Request $request, string $noNota)
    {
        $data = $request->validate([
            'tgl' => ['required', 'date'], 'id_customer' => ['required', 'integer'],
            'qty' => ['required', 'numeric', 'min:0'], 'h_satuan' => ['required', 'numeric', 'min:0'],
            'id_akun_pembayaran' => ['required', 'integer', 'exists:akun_perkiraan,id_akun_perkiraan'],
        ]);
        $old = DB::table('invoice_ayam')->where('no_nota', $noNota)->where('lokasi', 'alpa')->first();
        abort_unless($old, 404);
        $data['status'] = $this->statusPembayaran((int) $data['id_akun_pembayaran']);
        DB::transaction(function () use ($data, $noNota, $old) {
            DB::table('invoice_ayam')->where('no_nota', $noNota)->where('lokasi', 'alpa')->delete();
            DB::table('invoice_ayam')->insert($this->invoiceRow($data, $old->urutan, $noNota, $old->urutan_customer));
            $this->syncJurnal($noNota, $data['tgl'], $data['id_customer'], (int) $data['id_akun_pembayaran']);
        });
        return redirect()->route('transaksi.penjualan-ayam.index')->with('sukses', 'Penjualan ayam berhasil diperbarui.');
    }

    public function destroy(string $noNota)
    {
        DB::transaction(function () use ($noNota) {
            $deleted = DB::table('invoice_ayam')->where('no_nota', $noNota)->where('lokasi', 'alpa')->delete();
            abort_unless($deleted, 404); $this->hapusJurnal($noNota);
        });
        return redirect()->route('transaksi.penjualan-ayam.index')->with('sukses', 'Penjualan ayam berhasil dihapus.');
    }

    private function invoiceRow(array $data, int $urutan, string $nota, int $urutanCustomer): array
    {
        return ['tgl' => $data['tgl'], 'id_customer' => $data['id_customer'], 'customer' => DB::table('customer')->where('id_customer', $data['id_customer'])->value('nm_customer') ?? '', 'no_nota' => $nota, 'qty' => $data['qty'], 'h_satuan' => $data['h_satuan'], 'admin' => auth()->user()->name, 'urutan' => $urutan, 'lokasi' => 'alpa', 'status' => $data['status'], 'cek' => 'T', 'urutan_customer' => $urutanCustomer, 'id_customer2' => 0, 'id_kandang' => 0];
    }

    private function notaData(string $noNota): array
    {
        $items = DB::table('invoice_ayam as i')->leftJoin('customer as c', 'c.id_customer', '=', 'i.id_customer')->where('i.no_nota', $noNota)->where('i.lokasi', 'alpa')->select('i.*', 'c.nm_customer')->get();
        abort_unless($items->isNotEmpty(), 404);
        return ['nota' => $items->first(), 'items' => $items];
    }

    private function syncJurnal(string $noNota, string $tanggal, int $idCustomer, int $idAkunPembayaran): void
    {
        $item = DB::table('invoice_ayam')->where('no_nota', $noNota)->where('lokasi', 'alpa')->first();
        $total = (float) $item->qty * (float) $item->h_satuan;
        $akunBayar = DB::table('akun_perkiraan')->where('id_akun_perkiraan', $idAkunPembayaran)->where('aktif', 1)->whereIn('tipe_akun', ['BANK', 'AREC'])->first();
        $akunJual = DB::table('akun_perkiraan')->where('kode_perkiraan', '400002')->where('aktif', 1)->first();
        abort_unless($akunBayar && $akunJual, 422, 'Akun pembayaran atau Penjualan Ayam belum tersedia.');
        $customer = DB::table('customer')->where('id_customer', $idCustomer)->value('nm_customer') ?? 'Customer'; $this->hapusJurnal($noNota); $now = now();
        $batch = DB::table('impor_jurnal_perkiraan')->insertGetId(['nama_file' => 'Penjualan ayam ' . $noNota, 'hash_file' => hash('sha256', 'penjualan-ayam|' . $noNota), 'periode_awal' => $tanggal, 'periode_akhir' => $tanggal, 'jumlah_transaksi' => 1, 'jumlah_detail' => 2, 'total_debit' => $total, 'total_kredit' => $total, 'status' => 'aktif', 'diimpor_oleh' => auth()->id(), 'created_at' => $now, 'updated_at' => $now]);
        DB::table('jurnal_perkiraan')->insert([
            ['id_impor_jurnal_perkiraan' => $batch, 'id_akun_perkiraan' => $akunBayar->id_akun_perkiraan, 'tanggal' => $tanggal, 'nomor_transaksi' => $noNota, 'tipe_transaksi' => 'Penjualan Ayam', 'urutan_detail' => 1, 'deskripsi' => 'Penerimaan penjualan ayam dari ' . $customer, 'debit' => $total, 'kredit' => 0, 'created_at' => $now, 'updated_at' => $now],
            ['id_impor_jurnal_perkiraan' => $batch, 'id_akun_perkiraan' => $akunJual->id_akun_perkiraan, 'tanggal' => $tanggal, 'nomor_transaksi' => $noNota, 'tipe_transaksi' => 'Penjualan Ayam', 'urutan_detail' => 2, 'deskripsi' => 'Pendapatan penjualan ayam kepada ' . $customer, 'debit' => 0, 'kredit' => $total, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    private function hapusJurnal(string $noNota): void
    {
        $ids = DB::table('jurnal_perkiraan')->where('nomor_transaksi', $noNota)->where('tipe_transaksi', 'Penjualan Ayam')->pluck('id_impor_jurnal_perkiraan')->unique();
        DB::table('jurnal_perkiraan')->where('nomor_transaksi', $noNota)->where('tipe_transaksi', 'Penjualan Ayam')->delete();
        if ($ids->isNotEmpty()) DB::table('impor_jurnal_perkiraan')->whereIn('id_impor_jurnal_perkiraan', $ids)->delete();
    }

    private function akunPembayaran() { return DB::table('akun_perkiraan')->where('aktif', 1)->whereIn('tipe_akun', ['BANK', 'AREC'])->orderBy('kode_perkiraan')->get(['id_akun_perkiraan', 'kode_perkiraan', 'nama']); }

    private function statusPembayaran(int $idAkun): string
    {
        return DB::table('akun_perkiraan')->where('id_akun_perkiraan', $idAkun)->value('tipe_akun') === 'AREC'
            ? 'unpaid'
            : 'paid';
    }
}
