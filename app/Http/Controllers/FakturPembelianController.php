<?php

namespace App\Http\Controllers;

use App\Models\FakturModel;
use App\Models\ProdukPerencanaan;
use App\Models\Suplier;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class FakturPembelianController extends Controller
{
    public function index(Request $request): View
    {
        $tanggalAwal = $request->input('tanggal_awal', now()->startOfMonth()->toDateString());
        $tanggalAkhir = $request->input('tanggal_akhir', now()->toDateString());
        $cari = $request->input('cari');

        $faktur = FakturModel::with('supplier')
            ->whereBetween('tanggal_faktur', [$tanggalAwal, $tanggalAkhir])
            ->when($cari, function ($query) use ($cari) {
                $query->where(function ($q) use ($cari) {
                    $q->where('no_faktur', 'like', "%{$cari}%")
                        ->orWhereHas('supplier', function ($sq) use ($cari) {
                            $sq->where('nm_suplier', 'like', "%{$cari}%");
                        });
                });
            })
            ->orderByDesc('tanggal_faktur')
            ->paginate(15)
            ->withQueryString();

        return view('transaksi.faktur_pembelian.index', [
            'title' => 'Faktur Pembelian',
            'tanggalAwal' => $tanggalAwal,
            'tanggalAkhir' => $tanggalAkhir,
            'faktur' => $faktur,
        ]);
    }

    public function create(): View
    {
        return view('transaksi.faktur_pembelian.create', [
            'title' => 'Tambah Faktur Pembelian',
            'suppliers' => Suplier::orderBy('nm_suplier')->get(),
            'produk' => ProdukPerencanaan::query()
                ->leftJoin('tb_satuan as s', 's.id_satuan', '=', 'tb_produk_perencanaan.dosis_satuan')
                ->orderBy('tb_produk_perencanaan.nm_produk')
                ->get([
                    'tb_produk_perencanaan.*',
                    's.nm_satuan as satuan_dosis',
                ]),
            'noFakturDefault' => $this->generateNoFaktur(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'jenis_faktur' => ['required', 'in:pakan,vitamin'],
            'no_faktur' => ['required', 'max:30', 'unique:faktur_pembelian,no_faktur'],
            'tanggal_faktur' => ['required', 'date'],
            'supplier_id' => ['required', 'exists:tb_suplier,id_suplier'],
            'jatuh_tempo' => ['nullable', 'date'],
            'keterangan' => ['nullable', 'string'],
            'item' => ['required', 'array', 'min:1'],
            'item.*.pakan_id' => ['required', 'exists:tb_produk_perencanaan,id_produk'],
            'item.*.qty' => ['required', 'numeric', 'min:0.01'],
            'item.*.satuan' => ['nullable', 'string', 'max:20'],
            'item.*.harga_satuan' => ['required', 'numeric', 'min:0'],
            'item.*.no_batch' => ['nullable', 'string', 'max:50'],
            'item.*.tanggal_expired' => ['nullable', 'date'],
        ]);

        $items = collect($validated['item'])->values();
        $produk = ProdukPerencanaan::query()
            ->leftJoin('tb_satuan as s', 's.id_satuan', '=', 'tb_produk_perencanaan.dosis_satuan')
            ->whereIn('tb_produk_perencanaan.id_produk', $items->pluck('pakan_id'))
            ->get([
                'tb_produk_perencanaan.*',
                's.nm_satuan as satuan_dosis',
            ])
            ->keyBy('id_produk');

        $produkTidakSesuai = $items->contains(function ($item) use ($produk, $validated) {
            $kategori = $produk->get((int) $item['pakan_id'])?->kategori;

            return $validated['jenis_faktur'] === 'pakan'
                ? $kategori !== 'pakan'
                : $kategori === 'pakan';
        });

        if ($produkTidakSesuai) {
            return back()
                ->withErrors(['item' => 'Produk yang dipilih tidak sesuai dengan jenis faktur.'])
                ->withInput();
        }

        $akunHutang = $this->akunAktif('210220');
        $akunPersediaan = $this->akunAktif($validated['jenis_faktur'] === 'pakan' ? '110403' : '110404');

        if (! $akunHutang || ! $akunPersediaan) {
            return back()
                ->withErrors(['akun' => 'Akun Hutang Lainnya atau akun persediaan belum tersedia/aktif.'])
                ->withInput();
        }

        $fakturId = DB::transaction(function () use ($validated, $items, $produk, $akunHutang, $akunPersediaan) {
            $sekarang = now();
            $totalQty = $items->sum(fn($item) => (float) $item['qty']);
            $totalHarga = $items->sum(fn($item) => round((float) $item['qty'] * (float) $item['harga_satuan'], 2));

            $faktur = FakturModel::create([
                'no_faktur' => $validated['no_faktur'],
                'jenis_faktur' => $validated['jenis_faktur'],
                'tanggal_faktur' => $validated['tanggal_faktur'],
                'supplier_id' => $validated['supplier_id'],
                'jatuh_tempo' => $validated['jatuh_tempo'] ?? null,
                'status_bayar' => 'belum_lunas',
                'total_qty' => $totalQty,
                'total_harga' => $totalHarga,
                'keterangan' => $validated['keterangan'] ?? null,
                'created_by' => auth()->id(),
                'created_at' => $sekarang,
            ]);

            foreach ($items as $item) {
                $qty = (float) $item['qty'];
                $hargaSatuan = (float) $item['harga_satuan'];

                $faktur->detail()->create([
                    'pakan_id' => $item['pakan_id'],
                    'qty' => $qty,
                    'satuan' => $validated['jenis_faktur'] === 'pakan'
                        ? 'zak'
                        : ($produk->get((int) $item['pakan_id'])?->satuan_dosis ?? null),
                    'harga_satuan' => $hargaSatuan,
                    'subtotal' => round($qty * $hargaSatuan, 2),
                    'no_batch' => $item['no_batch'] ?? null,
                    'tanggal_expired' => $item['tanggal_expired'] ?? null,
                ]);
            }

            $batchId = DB::table('impor_jurnal_perkiraan')->insertGetId([
                'nama_file' => 'Faktur pembelian ' . $validated['jenis_faktur'] . ' ' . $validated['no_faktur'],
                'hash_file' => hash('sha256', 'faktur-pembelian|' . $validated['no_faktur']),
                'periode_awal' => $validated['tanggal_faktur'],
                'periode_akhir' => $validated['tanggal_faktur'],
                'jumlah_transaksi' => 1,
                'jumlah_detail' => $items->count() + 1,
                'total_debit' => $totalHarga,
                'total_kredit' => $totalHarga,
                'status' => 'aktif',
                'diimpor_oleh' => auth()->id(),
                'created_at' => $sekarang,
                'updated_at' => $sekarang,
            ]);

            $detailJurnal = [];
            $urutanDetail = 1;

            foreach ($items as $item) {
                $namaProduk = $produk->get((int) $item['pakan_id'])?->nm_produk ?? 'Produk';
                $subtotal = round((float) $item['qty'] * (float) $item['harga_satuan'], 2);

                $detailJurnal[] = [
                    'id_impor_jurnal_perkiraan' => $batchId,
                    'id_akun_perkiraan' => $akunPersediaan->id_akun_perkiraan,
                    'tanggal' => $validated['tanggal_faktur'],
                    'nomor_transaksi' => $validated['no_faktur'],
                    'tipe_transaksi' => 'Faktur Pembelian ' . ucfirst($validated['jenis_faktur']),
                    'urutan_detail' => $urutanDetail++,
                    'deskripsi' => 'Pembelian ' . $namaProduk,
                    'debit' => $subtotal,
                    'kredit' => 0,
                    'created_at' => $sekarang,
                    'updated_at' => $sekarang,
                ];
            }

            $detailJurnal[] = [
                'id_impor_jurnal_perkiraan' => $batchId,
                'id_akun_perkiraan' => $akunHutang->id_akun_perkiraan,
                'tanggal' => $validated['tanggal_faktur'],
                'nomor_transaksi' => $validated['no_faktur'],
                'tipe_transaksi' => 'Faktur Pembelian ' . ucfirst($validated['jenis_faktur']),
                'urutan_detail' => $urutanDetail,
                'deskripsi' => 'Hutang pembelian ' . $validated['jenis_faktur'],
                'debit' => 0,
                'kredit' => $totalHarga,
                'created_at' => $sekarang,
                'updated_at' => $sekarang,
            ];

            DB::table('jurnal_perkiraan')->insert($detailJurnal);

            return $faktur->getKey();
        });

        return redirect()
            ->route('transaksi.faktur-pembelian.index')
            ->with('sukses', 'Faktur pembelian berhasil disimpan dan jurnal hutang sudah dibuat.');
    }

    private function akunAktif(string $kode): ?object
    {
        return DB::table('akun_perkiraan')
            ->where('kode_perkiraan', $kode)
            ->where('aktif', true)
            ->first();
    }

    private function generateNoFaktur(): string
    {
        $prefix = 'FP-' . now()->format('Ymd') . '-';
        $terakhir = FakturModel::where('no_faktur', 'like', $prefix . '%')
            ->orderByDesc('no_faktur')
            ->value('no_faktur');

        $urut = $terakhir ? ((int) substr($terakhir, -3)) + 1 : 1;

        return $prefix . str_pad($urut, 3, '0', STR_PAD_LEFT);
    }
}
