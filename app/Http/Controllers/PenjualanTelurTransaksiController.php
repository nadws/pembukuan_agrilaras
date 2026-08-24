<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PenjualanTelurTransaksiController extends Controller
{
    public function index(Request $request)
    {
        $tanggalAwal = $request->input('tanggal_awal', date('Y-m-01'));
        $tanggalAkhir = $request->input('tanggal_akhir', date('Y-m-d'));
        $cari = trim((string) $request->input('cari', ''));

        $penjualan = DB::table('invoice_telur as i')
            ->leftJoin('customer as c', 'c.id_customer', '=', 'i.id_customer')
            ->where('i.lokasi', 'alpa')
            ->whereBetween('i.tgl', [$tanggalAwal, $tanggalAkhir])
            ->when($cari !== '', function ($query) use ($cari) {
                $query->where(function ($search) use ($cari) {
                    $search->where('i.no_nota', 'like', "%{$cari}%")
                        ->orWhere('c.nm_customer', 'like', "%{$cari}%");
                });
            })
            ->select(
                'i.no_nota',
                'i.tgl',
                'i.admin',
                'i.tipe',
                'i.status',
                'c.nm_customer',
                DB::raw('SUM(i.total_rp) as total_rp'),
                DB::raw('COUNT(i.id_invoice_telur) as jumlah_item')
            )
            ->groupBy('i.no_nota', 'i.tgl', 'i.admin', 'i.tipe', 'i.status', 'c.nm_customer')
            ->orderByDesc('i.urutan')
            ->get();

        return view('transaksi.penjualan_telur.index', compact(
            'penjualan',
            'tanggalAwal',
            'tanggalAkhir',
            'cari'
        ));
    }

    public function create()
    {
        $last = DB::table('invoice_telur')
            ->where('lokasi', 'alpa')
            ->orderByDesc('urutan')
            ->value('urutan');

        return view('transaksi.penjualan_telur.create', [
            'customers' => DB::table('customer')->orderBy('nm_customer')->get(),
            'produk' => DB::table('telur_produk')->orderBy('nm_telur')->get(),
            'akunPembayaran' => $this->akunPembayaran(),
            'nota' => ((int) $last) + 1,
        ]);
    }

    public function detail(string $noNota)
    {
        $data = $this->getNota($noNota);

        return view('transaksi.penjualan_telur.detail', $data);
    }

    public function edit(string $noNota)
    {
        $data = $this->getNota($noNota);
        $data['customers'] = DB::table('customer')->orderBy('nm_customer')->get();
        $data['produk'] = DB::table('telur_produk')->orderBy('nm_telur')->get();
        $data['akunPembayaran'] = $this->akunPembayaran();
        $data['idAkunPembayaran'] = DB::table('jurnal_perkiraan')
            ->where('nomor_transaksi', $noNota)
            ->where('tipe_transaksi', 'Penjualan Telur')
            ->where('debit', '>', 0)
            ->value('id_akun_perkiraan');

        return view('transaksi.penjualan_telur.edit', $data);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tgl' => ['required', 'date'],
            'id_customer' => ['required', 'integer'],
            'id_akun_pembayaran' => ['required', 'integer', 'exists:akun_perkiraan,id_akun_perkiraan'],
            'tipe_penjualan' => ['required', 'in:pcs,kg'],
            'id_produk' => ['required', 'array', 'min:1'],
            'id_produk.*' => ['required', 'integer'],
            'pcs' => ['required', 'array'],
            'pcs.*' => ['required', 'numeric', 'min:0'],
            'kg' => ['required', 'array'],
            'kg.*' => ['required', 'numeric', 'min:0'],
            'kg_jual' => ['required', 'array'],
            'kg_jual.*' => ['required', 'numeric', 'min:0'],
            'rp_satuan' => ['required', 'array'],
            'rp_satuan.*' => ['required', 'numeric', 'min:0'],
            'total_rp' => ['required', 'array'],
            'total_rp.*' => ['required', 'numeric', 'min:0'],
        ]);

        $last = DB::table('invoice_telur')
            ->where('lokasi', 'alpa')
            ->lockForUpdate()
            ->orderByDesc('urutan')
            ->value('urutan');
        $urutan = ((int) $last) + 1;
        $noNota = 'TP' . $urutan;
        $urutanCustomer = ((int) DB::table('invoice_telur')
            ->where('lokasi', 'alpa')
            ->where('id_customer', $validated['id_customer'])
            ->max('urutan_customer')) + 1;
        $status = $this->statusPembayaran((int) $validated['id_akun_pembayaran']);

        DB::transaction(function () use ($validated, $urutan, $noNota, $urutanCustomer, $status) {
            foreach ($validated['id_produk'] as $i => $idProduk) {
                $kgBersih = (float) $validated['kg_jual'][$i];
                $hargaSatuan = (float) $validated['rp_satuan'][$i];
                $jumlahJual = $validated['tipe_penjualan'] === 'pcs'
                    ? (float) $validated['pcs'][$i]
                    : $kgBersih;

                DB::table('invoice_telur')->insert([
                    'tgl' => $validated['tgl'],
                    'id_customer' => $validated['id_customer'],
                    'tipe' => $validated['tipe_penjualan'],
                    'status' => $status,
                    'no_nota' => $noNota,
                    'id_produk' => $idProduk,
                    'pcs' => $validated['pcs'][$i],
                    'kg' => $validated['kg'][$i],
                    'kg_jual' => $validated['kg_jual'][$i],
                    'rp_satuan' => $hargaSatuan,
                    'total_rp' => $jumlahJual * $hargaSatuan,
                    'admin' => auth()->user()->name,
                    'urutan' => $urutan,
                    'urutan_customer' => $urutanCustomer,
                    'driver' => '',
                    'lokasi' => 'alpa',
                ]);
            }

            $this->syncJurnal($noNota, $validated['tgl'], $validated['id_customer'], (int) $validated['id_akun_pembayaran']);
        });

        return redirect()->route('transaksi.penjualan-telur.index')
            ->with('sukses', 'Penjualan telur berhasil disimpan.');
    }

    public function update(Request $request, string $noNota)
    {
        $validated = $request->validate([
            'tgl' => ['required', 'date'],
            'id_customer' => ['required', 'integer'],
            'id_akun_pembayaran' => ['required', 'integer', 'exists:akun_perkiraan,id_akun_perkiraan'],
            'tipe_penjualan' => ['required', 'in:pcs,kg'],
            'id_produk' => ['required', 'array', 'min:1'],
            'id_produk.*' => ['required', 'integer'],
            'pcs' => ['required', 'array'],
            'pcs.*' => ['required', 'numeric', 'min:0'],
            'kg' => ['required', 'array'],
            'kg.*' => ['required', 'numeric', 'min:0'],
            'kg_jual' => ['required', 'array'],
            'kg_jual.*' => ['required', 'numeric', 'min:0'],
            'rp_satuan' => ['required', 'array'],
            'rp_satuan.*' => ['required', 'numeric', 'min:0'],
        ]);

        $nota = DB::table('invoice_telur')
            ->where('no_nota', $noNota)
            ->where('lokasi', 'alpa')
            ->first();
        abort_unless($nota, 404);

        $status = $this->statusPembayaran((int) $validated['id_akun_pembayaran']);
        DB::transaction(function () use ($validated, $noNota, $nota, $status) {
            DB::table('invoice_telur')->where('no_nota', $noNota)->where('lokasi', 'alpa')->delete();

            foreach ($validated['id_produk'] as $i => $idProduk) {
                $kgBersih = (float) $validated['kg_jual'][$i];
                $hargaSatuan = (float) $validated['rp_satuan'][$i];
                $jumlahJual = $validated['tipe_penjualan'] === 'pcs'
                    ? (float) $validated['pcs'][$i]
                    : $kgBersih;
                DB::table('invoice_telur')->insert([
                    'tgl' => $validated['tgl'],
                    'id_customer' => $validated['id_customer'],
                    'tipe' => $validated['tipe_penjualan'],
                    'status' => $status,
                    'no_nota' => $noNota,
                    'id_produk' => $idProduk,
                    'pcs' => $validated['pcs'][$i],
                    'kg' => $validated['kg'][$i],
                    'kg_jual' => $kgBersih,
                    'rp_satuan' => $hargaSatuan,
                    'total_rp' => $jumlahJual * $hargaSatuan,
                    'admin' => auth()->user()->name,
                    'urutan' => $nota->urutan,
                    'urutan_customer' => $nota->urutan_customer,
                    'driver' => '',
                    'lokasi' => 'alpa',
                ]);
            }

            $this->syncJurnal($noNota, $validated['tgl'], $validated['id_customer'], (int) $validated['id_akun_pembayaran']);
        });

        return redirect()->route('transaksi.penjualan-telur.index')
            ->with('sukses', 'Penjualan telur berhasil diperbarui.');
    }

    public function destroy(string $noNota)
    {
        DB::transaction(function () use ($noNota) {
            $deleted = DB::table('invoice_telur')
                ->where('no_nota', $noNota)
                ->where('lokasi', 'alpa')
                ->delete();
            abort_unless($deleted, 404);
            $this->hapusJurnal($noNota);
        });

        return redirect()->route('transaksi.penjualan-telur.index')
            ->with('sukses', 'Penjualan telur berhasil dihapus.');
    }

    private function getNota(string $noNota): array
    {
        $items = DB::table('invoice_telur as i')
            ->leftJoin('customer as c', 'c.id_customer', '=', 'i.id_customer')
            ->leftJoin('telur_produk as p', 'p.id_produk_telur', '=', 'i.id_produk')
            ->where('i.no_nota', $noNota)
            ->where('i.lokasi', 'alpa')
            ->select('i.*', 'c.nm_customer', 'p.nm_telur')
            ->orderBy('i.id_invoice_telur')
            ->get();
        abort_unless($items->isNotEmpty(), 404);

        return [
            'nota' => $items->first(),
            'items' => $items,
        ];
    }

    private function syncJurnal(string $noNota, string $tanggal, int $idCustomer, int $idAkunPembayaran): void
    {
        $total = (float) DB::table('invoice_telur')
            ->where('no_nota', $noNota)
            ->where('lokasi', 'alpa')
            ->sum('total_rp');

        $akunPembayaran = DB::table('akun_perkiraan')
            ->where('id_akun_perkiraan', $idAkunPembayaran)
            ->where('aktif', 1)
            ->whereIn('tipe_akun', ['BANK', 'AREC'])
            ->first();
        $akunPenjualan = DB::table('akun_perkiraan')->where('kode_perkiraan', '400001')->where('aktif', 1)->first();
        abort_unless($akunPembayaran && $akunPenjualan, 422, 'Akun pembayaran atau Penjualan Telur belum tersedia.');

        $customer = DB::table('customer')->where('id_customer', $idCustomer)->value('nm_customer') ?? 'Customer';
        $this->hapusJurnal($noNota);
        $sekarang = now();
        $batchId = DB::table('impor_jurnal_perkiraan')->insertGetId([
            'nama_file' => 'Penjualan telur ' . $noNota,
            'hash_file' => hash('sha256', 'penjualan-telur|' . $noNota),
            'periode_awal' => $tanggal,
            'periode_akhir' => $tanggal,
            'jumlah_transaksi' => 1,
            'jumlah_detail' => 2,
            'total_debit' => $total,
            'total_kredit' => $total,
            'status' => 'aktif',
            'diimpor_oleh' => auth()->id(),
            'created_at' => $sekarang,
            'updated_at' => $sekarang,
        ]);

        DB::table('jurnal_perkiraan')->insert([
            [
                'id_impor_jurnal_perkiraan' => $batchId,
                'id_akun_perkiraan' => $akunPembayaran->id_akun_perkiraan,
                'tanggal' => $tanggal,
                'nomor_transaksi' => $noNota,
                'tipe_transaksi' => 'Penjualan Telur',
                'urutan_detail' => 1,
                'deskripsi' => 'Penerimaan penjualan telur dari ' . $customer,
                'debit' => $total,
                'kredit' => 0,
                'created_at' => $sekarang,
                'updated_at' => $sekarang,
            ],
            [
                'id_impor_jurnal_perkiraan' => $batchId,
                'id_akun_perkiraan' => $akunPenjualan->id_akun_perkiraan,
                'tanggal' => $tanggal,
                'nomor_transaksi' => $noNota,
                'tipe_transaksi' => 'Penjualan Telur',
                'urutan_detail' => 2,
                'deskripsi' => 'Pendapatan penjualan telur kepada ' . $customer,
                'debit' => 0,
                'kredit' => $total,
                'created_at' => $sekarang,
                'updated_at' => $sekarang,
            ],
        ]);
    }

    private function hapusJurnal(string $noNota): void
    {
        $batchIds = DB::table('jurnal_perkiraan')
            ->where('nomor_transaksi', $noNota)
            ->where('tipe_transaksi', 'Penjualan Telur')
            ->pluck('id_impor_jurnal_perkiraan')
            ->filter()
            ->unique();

        DB::table('jurnal_perkiraan')
            ->where('nomor_transaksi', $noNota)
            ->where('tipe_transaksi', 'Penjualan Telur')
            ->delete();

        if ($batchIds->isNotEmpty()) {
            DB::table('impor_jurnal_perkiraan')->whereIn('id_impor_jurnal_perkiraan', $batchIds)->delete();
        }
    }

    private function akunPembayaran()
    {
        return DB::table('akun_perkiraan')
            ->where('aktif', 1)
            ->where(function ($query) {
                $query->where('tipe_akun', 'BANK')
                    ->orWhere('tipe_akun', 'AREC');
            })
            ->orderBy('kode_perkiraan')
            ->get(['id_akun_perkiraan', 'kode_perkiraan', 'nama', 'tipe_akun']);
    }

    private function statusPembayaran(int $idAkun): string
    {
        return DB::table('akun_perkiraan')->where('id_akun_perkiraan', $idAkun)->value('tipe_akun') === 'AREC'
            ? 'unpaid'
            : 'paid';
    }
}


