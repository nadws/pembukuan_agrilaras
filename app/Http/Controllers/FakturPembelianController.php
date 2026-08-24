<?php

namespace App\Http\Controllers;

use App\Models\FakturModel;
use App\Models\ProdukPerencanaan;
use App\Models\Suplier;
use Illuminate\Pagination\LengthAwarePaginator;
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
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $penerimaanFaktur = DB::table('faktur_pembelian as f')
            ->leftJoin('stok_produk_perencanaan as s', 's.no_nota', '=', 'f.no_faktur')
            ->whereIn('f.no_faktur', $faktur->getCollection()->pluck('no_faktur'))
            ->groupBy('f.no_faktur', 'f.jenis_faktur')
            ->select('f.no_faktur')
            ->selectRaw("COALESCE(SUM(CASE WHEN f.jenis_faktur = 'pakan' THEN s.pcs / 50000 ELSE s.pcs END), 0) as qty_diterima")
            ->pluck('qty_diterima', 'no_faktur');

        return view('transaksi.faktur_pembelian.index', [
            'title' => 'Faktur Pembelian',
            'tanggalAwal' => $tanggalAwal,
            'tanggalAkhir' => $tanggalAkhir,
            'faktur' => $faktur,
            'penerimaanFaktur' => $penerimaanFaktur,
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

    public function penerimaanIndex(Request $request): View
    {
        $tanggalAwal = $request->input('tanggal_awal', now()->startOfMonth()->toDateString());
        $tanggalAkhir = $request->input('tanggal_akhir', now()->toDateString());
        $cari = $request->input('cari');
        $statusPenerimaan = $request->input('status', 'belum') === 'selesai' ? 'selesai' : 'belum';

        $semuaFaktur = FakturModel::with('supplier')
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
            ->get();

        $penerimaanFaktur = $this->qtyDiterimaFaktur($semuaFaktur);
        $belumHabis = $semuaFaktur->filter(function ($item) use ($penerimaanFaktur) {
            return (float) ($penerimaanFaktur[$item->no_faktur] ?? 0) < (float) $item->total_qty;
        })->values();
        $sudahHabis = $semuaFaktur->filter(function ($item) use ($penerimaanFaktur) {
            return (float) ($penerimaanFaktur[$item->no_faktur] ?? 0) >= (float) $item->total_qty;
        })->values();

        $dataFaktur = $statusPenerimaan === 'selesai' ? $sudahHabis : $belumHabis;
        $page = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 15;
        $faktur = new LengthAwarePaginator(
            $dataFaktur->forPage($page, $perPage)->values(),
            $dataFaktur->count(),
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        return view('transaksi.penerimaan.index', [
            'title' => 'Penerimaan Stok',
            'tanggalAwal' => $tanggalAwal,
            'tanggalAkhir' => $tanggalAkhir,
            'faktur' => $faktur,
            'penerimaanFaktur' => $penerimaanFaktur,
            'statusPenerimaan' => $statusPenerimaan,
            'jumlahBelumHabis' => $belumHabis->count(),
            'jumlahSudahHabis' => $sudahHabis->count(),
        ]);
    }

    public function bukuHutangIndex(Request $request): View
    {
        $tanggalAwal = $request->input('tanggal_awal', now()->startOfMonth()->toDateString());
        $tanggalAkhir = $request->input('tanggal_akhir', now()->toDateString());
        $cari = $request->input('cari');
        $status = in_array($request->input('status'), ['berjalan', 'lunas'], true)
            ? $request->input('status')
            : 'berjalan';

        $pelunasan = DB::table('pelunasan_faktur_pembelian')
            ->select('faktur_pembelian_id')
            ->selectRaw('COALESCE(SUM(jumlah_bayar), 0) as total_bayar')
            ->groupBy('faktur_pembelian_id');

        $query = FakturModel::query()
            ->with('supplier')
            ->leftJoinSub($pelunasan, 'p', 'p.faktur_pembelian_id', '=', 'faktur_pembelian.id')
            ->whereBetween('faktur_pembelian.tanggal_faktur', [$tanggalAwal, $tanggalAkhir])
            ->when($cari, function ($query) use ($cari) {
                $query->where(function ($q) use ($cari) {
                    $q->where('faktur_pembelian.no_faktur', 'like', "%{$cari}%")
                        ->orWhereHas('supplier', function ($sq) use ($cari) {
                            $sq->where('nm_suplier', 'like', "%{$cari}%");
                        });
                });
            })
            ->select('faktur_pembelian.*')
            ->selectRaw('COALESCE(p.total_bayar, 0) as total_bayar')
            ->selectRaw('(faktur_pembelian.total_harga - COALESCE(p.total_bayar, 0)) as sisa_hutang');

        $ringkasan = (clone $query)->get();
        $totalHutang = $ringkasan->sum('total_harga');
        $totalTerbayar = $ringkasan->sum('total_bayar');
        $totalSisa = $ringkasan->sum('sisa_hutang');
        $jumlahBerjalan = $ringkasan->filter(fn($item) => (float) $item->sisa_hutang > 0)->count();
        $jumlahLunas = $ringkasan->filter(fn($item) => (float) $item->sisa_hutang <= 0)->count();

        $faktur = $query
            ->when($status === 'berjalan', fn($q) => $q->havingRaw('sisa_hutang > 0'))
            ->when($status === 'lunas', fn($q) => $q->havingRaw('sisa_hutang <= 0'))
            ->orderByDesc('faktur_pembelian.tanggal_faktur')
            ->orderByDesc('faktur_pembelian.id')
            ->paginate(15)
            ->withQueryString();

        return view('transaksi.buku_hutang.index', [
            'title' => 'Buku Hutang Faktur Pembelian',
            'tanggalAwal' => $tanggalAwal,
            'tanggalAkhir' => $tanggalAkhir,
            'status' => $status,
            'faktur' => $faktur,
            'totalHutang' => $totalHutang,
            'totalTerbayar' => $totalTerbayar,
            'totalSisa' => $totalSisa,
            'jumlahBerjalan' => $jumlahBerjalan,
            'jumlahLunas' => $jumlahLunas,
        ]);
    }

    public function pelunasan(FakturModel $faktur_pembelian): View|RedirectResponse
    {
        $faktur_pembelian->load('supplier', 'detail.produk');

        $totalTerbayar = $this->totalPelunasanFaktur($faktur_pembelian);
        $sisaHutang = max((float) $faktur_pembelian->total_harga - $totalTerbayar, 0);

        if ($sisaHutang <= 0) {
            return redirect()
                ->route('transaksi.buku-hutang.index', ['status' => 'lunas'])
                ->with('sukses', 'Faktur ini sudah lunas.');
        }

        return view('transaksi.buku_hutang.pelunasan', [
            'title' => 'Pelunasan Hutang Faktur Pembelian',
            'faktur' => $faktur_pembelian,
            'totalTerbayar' => $totalTerbayar,
            'sisaHutang' => $sisaHutang,
            'akunKas' => $this->akunKasBankAktif(),
            'riwayatPelunasan' => $this->riwayatPelunasanFaktur($faktur_pembelian),
        ]);
    }

    public function storePelunasan(Request $request, FakturModel $faktur_pembelian): RedirectResponse
    {
        $validated = $request->validate([
            'tanggal_bayar' => ['required', 'date'],
            'jumlah_bayar' => ['required', 'numeric', 'min:0.01'],
            'id_akun_kas' => ['required', 'exists:akun_perkiraan,id_akun_perkiraan'],
            'keterangan' => ['nullable', 'string'],
        ]);

        $akunHutang = $this->akunAktif('210220');
        $akunKas = DB::table('akun_perkiraan')
            ->where('id_akun_perkiraan', $validated['id_akun_kas'])
            ->where('aktif', 1)
            ->first();

        if (! $akunHutang || ! $akunKas) {
            return back()
                ->withErrors(['akun' => 'Akun Hutang Lainnya atau akun pembayaran belum tersedia/aktif.'])
                ->withInput();
        }

        $sisaHutang = max((float) $faktur_pembelian->total_harga - $this->totalPelunasanFaktur($faktur_pembelian), 0);
        $jumlahBayar = round((float) $validated['jumlah_bayar'], 2);

        if ($jumlahBayar > $sisaHutang) {
            return back()
                ->withErrors(['jumlah_bayar' => 'Jumlah bayar melebihi sisa hutang. Sisa hutang Rp ' . number_format($sisaHutang, 0, ',', '.')])
                ->withInput();
        }

        DB::transaction(function () use ($validated, $faktur_pembelian, $akunHutang, $akunKas, $jumlahBayar) {
            $sekarang = now();
            $nomorTransaksi = $faktur_pembelian->no_faktur;

            $pelunasanId = DB::table('pelunasan_faktur_pembelian')->insertGetId([
                'faktur_pembelian_id' => $faktur_pembelian->id,
                'tanggal_bayar' => $validated['tanggal_bayar'],
                'jumlah_bayar' => $jumlahBayar,
                'id_akun_kas' => $validated['id_akun_kas'],
                'keterangan' => $validated['keterangan'] ?? null,
                'created_by' => auth()->id(),
                'created_at' => $sekarang,
                'updated_at' => $sekarang,
            ]);

            $batchId = DB::table('impor_jurnal_perkiraan')->insertGetId([
                'nama_file' => 'Pelunasan hutang faktur ' . $faktur_pembelian->no_faktur,
                'hash_file' => hash('sha256', 'pelunasan-faktur-pembelian|' . $pelunasanId),
                'periode_awal' => $validated['tanggal_bayar'],
                'periode_akhir' => $validated['tanggal_bayar'],
                'jumlah_transaksi' => 1,
                'jumlah_detail' => 2,
                'total_debit' => $jumlahBayar,
                'total_kredit' => $jumlahBayar,
                'status' => 'aktif',
                'diimpor_oleh' => auth()->id(),
                'created_at' => $sekarang,
                'updated_at' => $sekarang,
            ]);

            DB::table('pelunasan_faktur_pembelian')
                ->where('id', $pelunasanId)
                ->update(['id_impor_jurnal_perkiraan' => $batchId]);

            DB::table('jurnal_perkiraan')->insert([
                [
                    'id_impor_jurnal_perkiraan' => $batchId,
                    'id_akun_perkiraan' => $akunHutang->id_akun_perkiraan,
                    'tanggal' => $validated['tanggal_bayar'],
                    'nomor_transaksi' => $nomorTransaksi,
                    'tipe_transaksi' => 'Pelunasan Hutang Faktur Pembelian',
                    'urutan_detail' => 1,
                    'deskripsi' => 'Pelunasan hutang ' . $faktur_pembelian->no_faktur . ' (' . $sekarang->format('YmdHis') . ')',
                    'debit' => $jumlahBayar,
                    'kredit' => 0,
                    'created_at' => $sekarang,
                    'updated_at' => $sekarang,
                ],
                [
                    'id_impor_jurnal_perkiraan' => $batchId,
                    'id_akun_perkiraan' => $akunKas->id_akun_perkiraan,
                    'tanggal' => $validated['tanggal_bayar'],
                    'nomor_transaksi' => $nomorTransaksi,
                    'tipe_transaksi' => 'Pelunasan Hutang Faktur Pembelian',
                    'urutan_detail' => 2,
                    'deskripsi' => 'Pembayaran hutang ' . $faktur_pembelian->no_faktur . ' dari ' . $akunKas->nama . ' (' . $sekarang->format('YmdHis') . ')',
                    'debit' => 0,
                    'kredit' => $jumlahBayar,
                    'created_at' => $sekarang,
                    'updated_at' => $sekarang,
                ],
            ]);

            $totalTerbayar = $this->totalPelunasanFaktur($faktur_pembelian);
            $sisaSetelahBayar = max((float) $faktur_pembelian->total_harga - $totalTerbayar, 0);

            $faktur_pembelian->update([
                'status_bayar' => $sisaSetelahBayar <= 0 ? 'lunas' : 'sebagian',
            ]);
        });

        return redirect()
            ->route('transaksi.buku-hutang.index')
            ->with('sukses', 'Pelunasan hutang berhasil disimpan.');
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
            'diskon_total' => ['nullable', 'numeric', 'min:0'],
            'item' => ['required', 'array', 'min:1'],
            'item.*.pakan_id' => ['required', 'exists:tb_produk_perencanaan,id_produk'],
            'item.*.qty' => ['required', 'numeric', 'min:0.01'],
            'item.*.satuan' => ['nullable', 'string', 'max:20'],
            'item.*.harga_satuan' => ['required', 'numeric', 'min:0'],
            'item.*.subtotal' => ['required', 'numeric', 'min:0'],
            'item.*.no_batch' => ['nullable', 'string', 'max:50'],
            'item.*.tanggal_expired' => ['nullable', 'date'],
        ]);

        $items = $this->normalisasiItemFaktur($validated['item']);
        $diskonTotal = round((float) ($validated['diskon_total'] ?? 0), 2);

        if ($diskonTotal > $items->sum(fn($item) => (float) $item['subtotal'])) {
            return back()
                ->withErrors(['diskon_total' => 'Diskon tidak boleh lebih besar dari total pembelian.'])
                ->withInput();
        }

        $items = $this->terapkanDiskonKeItem($items, $diskonTotal);
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

        $fakturId = DB::transaction(function () use ($validated, $items, $produk, $akunHutang, $akunPersediaan, $diskonTotal) {
            $sekarang = now();
            $totalQty = $items->sum(fn($item) => (float) $item['qty']);
            $totalHarga = $items->sum(fn($item) => (float) $item['subtotal']);

            $faktur = FakturModel::create([
                'no_faktur' => $validated['no_faktur'],
                'jenis_faktur' => $validated['jenis_faktur'],
                'tanggal_faktur' => $validated['tanggal_faktur'],
                'supplier_id' => $validated['supplier_id'],
                'jatuh_tempo' => $validated['jatuh_tempo'] ?? null,
                'status_bayar' => 'belum_lunas',
                'total_qty' => $totalQty,
                'total_harga' => $totalHarga,
                'diskon_total' => $diskonTotal,
                'keterangan' => $validated['keterangan'] ?? null,
                'created_by' => auth()->id(),
                'created_at' => $sekarang,
            ]);

            foreach ($items as $item) {
                $qty = (float) $item['qty'];
                $hargaSatuan = (float) $item['harga_satuan'];
                $subtotal = (float) $item['subtotal'];

                $faktur->detail()->create([
                    'pakan_id' => $item['pakan_id'],
                    'qty' => $qty,
                    'satuan' => $validated['jenis_faktur'] === 'pakan'
                        ? 'zak'
                        : ($produk->get((int) $item['pakan_id'])?->satuan_dosis ?? null),
                    'harga_satuan' => $hargaSatuan,
                    'subtotal' => $subtotal,
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
                $subtotal = (float) $item['subtotal'];

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

    public function detail(FakturModel $faktur_pembelian): View
    {
        $faktur_pembelian->load(['supplier', 'detail.produk']);

        $qtyDiterimaByProduk = DB::table('stok_produk_perencanaan')
            ->where('no_nota', $faktur_pembelian->no_faktur)
            ->groupBy('id_pakan')
            ->select('id_pakan')
            ->selectRaw($faktur_pembelian->jenis_faktur === 'pakan' ? 'SUM(pcs / 50000) as qty' : 'SUM(pcs) as qty')
            ->pluck('qty', 'id_pakan');

        $jurnal = DB::table('jurnal_perkiraan as j')
            ->leftJoin('akun_perkiraan as a', 'a.id_akun_perkiraan', '=', 'j.id_akun_perkiraan')
            ->where('j.nomor_transaksi', $faktur_pembelian->no_faktur)
            ->orderBy('j.urutan_detail')
            ->get(['j.*', 'a.kode_perkiraan', 'a.nama']);

        return view('transaksi.faktur_pembelian.detail', [
            'title' => 'Detail Faktur Pembelian',
            'faktur' => $faktur_pembelian,
            'qtyDiterimaByProduk' => $qtyDiterimaByProduk,
            'jurnal' => $jurnal,
            'sudahAdaPenerimaan' => $this->fakturSudahAdaPenerimaan($faktur_pembelian),
        ]);
    }

    public function edit(FakturModel $faktur_pembelian): View|RedirectResponse
    {
        if ($this->fakturSudahAdaPenerimaan($faktur_pembelian)) {
            return redirect()
                ->route('transaksi.faktur-pembelian.detail', $faktur_pembelian)
                ->with('error', 'Faktur sudah ada penerimaan stok, jadi tidak bisa diedit.');
        }

        $faktur_pembelian->load('detail');

        return view('transaksi.faktur_pembelian.edit', [
            'title' => 'Edit Faktur Pembelian',
            'faktur' => $faktur_pembelian,
            'suppliers' => Suplier::orderBy('nm_suplier')->get(),
            'produk' => $this->produkFakturOptions(),
        ]);
    }

    public function update(Request $request, FakturModel $faktur_pembelian): RedirectResponse
    {
        if ($this->fakturSudahAdaPenerimaan($faktur_pembelian)) {
            return redirect()
                ->route('transaksi.faktur-pembelian.detail', $faktur_pembelian)
                ->with('error', 'Faktur sudah ada penerimaan stok, jadi tidak bisa diedit.');
        }

        $validated = $request->validate([
            'jenis_faktur' => ['required', 'in:pakan,vitamin'],
            'no_faktur' => ['required', 'max:30', 'unique:faktur_pembelian,no_faktur,' . $faktur_pembelian->id],
            'tanggal_faktur' => ['required', 'date'],
            'supplier_id' => ['required', 'exists:tb_suplier,id_suplier'],
            'jatuh_tempo' => ['nullable', 'date'],
            'keterangan' => ['nullable', 'string'],
            'diskon_total' => ['nullable', 'numeric', 'min:0'],
            'item' => ['required', 'array', 'min:1'],
            'item.*.pakan_id' => ['required', 'exists:tb_produk_perencanaan,id_produk'],
            'item.*.qty' => ['required', 'numeric', 'min:0.01'],
            'item.*.satuan' => ['nullable', 'string', 'max:20'],
            'item.*.harga_satuan' => ['required', 'numeric', 'min:0'],
            'item.*.subtotal' => ['required', 'numeric', 'min:0'],
            'item.*.no_batch' => ['nullable', 'string', 'max:50'],
            'item.*.tanggal_expired' => ['nullable', 'date'],
        ]);

        $items = $this->normalisasiItemFaktur($validated['item']);
        $diskonTotal = round((float) ($validated['diskon_total'] ?? 0), 2);

        if ($diskonTotal > $items->sum(fn($item) => (float) $item['subtotal'])) {
            return back()
                ->withErrors(['diskon_total' => 'Diskon tidak boleh lebih besar dari total pembelian.'])
                ->withInput();
        }

        $items = $this->terapkanDiskonKeItem($items, $diskonTotal);
        $produk = $this->produkFakturOptions()
            ->whereIn('id_produk', $items->pluck('pakan_id'))
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

        DB::transaction(function () use ($validated, $items, $produk, $faktur_pembelian, $akunHutang, $akunPersediaan, $diskonTotal) {
            $noFakturLama = $faktur_pembelian->no_faktur;
            $totalQty = $items->sum(fn($item) => (float) $item['qty']);
            $totalHarga = $items->sum(fn($item) => (float) $item['subtotal']);

            $faktur_pembelian->update([
                'no_faktur' => $validated['no_faktur'],
                'jenis_faktur' => $validated['jenis_faktur'],
                'tanggal_faktur' => $validated['tanggal_faktur'],
                'supplier_id' => $validated['supplier_id'],
                'jatuh_tempo' => $validated['jatuh_tempo'] ?? null,
                'status_bayar' => 'belum_lunas',
                'total_qty' => $totalQty,
                'total_harga' => $totalHarga,
                'diskon_total' => $diskonTotal,
                'keterangan' => $validated['keterangan'] ?? null,
            ]);

            $faktur_pembelian->detail()->delete();

            foreach ($items as $item) {
                $qty = (float) $item['qty'];
                $hargaSatuan = (float) $item['harga_satuan'];
                $subtotal = (float) $item['subtotal'];

                $faktur_pembelian->detail()->create([
                    'pakan_id' => $item['pakan_id'],
                    'qty' => $qty,
                    'satuan' => $validated['jenis_faktur'] === 'pakan'
                        ? 'zak'
                        : ($produk->get((int) $item['pakan_id'])?->satuan_dosis ?? null),
                    'harga_satuan' => $hargaSatuan,
                    'subtotal' => $subtotal,
                    'no_batch' => $item['no_batch'] ?? null,
                    'tanggal_expired' => $item['tanggal_expired'] ?? null,
                ]);
            }

            $this->rebuildJurnalFaktur($faktur_pembelian, $items, $produk, $akunHutang, $akunPersediaan, $noFakturLama);
        });

        return redirect()
            ->route('transaksi.faktur-pembelian.detail', $faktur_pembelian)
            ->with('sukses', 'Faktur pembelian berhasil diperbarui.');
    }

    public function terima(FakturModel $faktur_pembelian): View
    {
        $faktur_pembelian->load(['supplier', 'detail.produk']);

        $qtyDiterimaByProduk = DB::table('stok_produk_perencanaan')
            ->where('no_nota', $faktur_pembelian->no_faktur)
            ->groupBy('id_pakan')
            ->select('id_pakan')
            ->selectRaw($faktur_pembelian->jenis_faktur === 'pakan' ? 'SUM(pcs / 50000) as qty' : 'SUM(pcs) as qty')
            ->pluck('qty', 'id_pakan');

        $sudahDiterima = $faktur_pembelian->detail->every(function ($detail) use ($qtyDiterimaByProduk) {
            return (float) ($qtyDiterimaByProduk[$detail->pakan_id] ?? 0) >= (float) $detail->qty;
        });

        return view('transaksi.faktur_pembelian.terima', [
            'title' => 'Penerimaan Stok',
            'faktur' => $faktur_pembelian,
            'sudahDiterima' => $sudahDiterima,
            'qtyDiterimaByProduk' => $qtyDiterimaByProduk,
        ]);
    }

    public function storeTerima(Request $request, FakturModel $faktur_pembelian): RedirectResponse
    {
        $faktur_pembelian->load('detail.produk');

        $qtyDiterimaByProduk = DB::table('stok_produk_perencanaan')
            ->where('no_nota', $faktur_pembelian->no_faktur)
            ->groupBy('id_pakan')
            ->select('id_pakan')
            ->selectRaw($faktur_pembelian->jenis_faktur === 'pakan' ? 'SUM(pcs / 50000) as qty' : 'SUM(pcs) as qty')
            ->pluck('qty', 'id_pakan');

        $sudahDiterima = $faktur_pembelian->detail->every(function ($detail) use ($qtyDiterimaByProduk) {
            return (float) ($qtyDiterimaByProduk[$detail->pakan_id] ?? 0) >= (float) $detail->qty;
        });

        if ($sudahDiterima) {
            return redirect()
                ->route('transaksi.faktur-pembelian.index')
                ->with('error', 'Semua stok faktur ini sudah diterima.');
        }

        $validated = $request->validate([
            'tanggal_terima' => ['required', 'date'],
            'detail' => ['required', 'array', 'min:1'],
            'detail.*.qty_diterima' => ['required', 'numeric', 'min:0.01'],
        ]);

        DB::transaction(function () use ($validated, $faktur_pembelian, $qtyDiterimaByProduk) {
            $admin = auth()->user()->name ?? 'system';
            $rows = [];

            foreach ($faktur_pembelian->detail as $detail) {
                $qtyDiterima = (float) data_get($validated, 'detail.' . $detail->id . '.qty_diterima', 0);
                $qtySebelumnya = (float) ($qtyDiterimaByProduk[$detail->pakan_id] ?? 0);
                $qtySisa = max((float) $detail->qty - $qtySebelumnya, 0);

                if ($qtyDiterima <= 0) {
                    continue;
                }

                abort_if(
                    $qtyDiterima > $qtySisa,
                    422,
                    'Qty diterima ' . ($detail->produk->nm_produk ?? 'produk') . ' melebihi sisa faktur.'
                );

                $qtyStok = $faktur_pembelian->jenis_faktur === 'pakan'
                    ? $qtyDiterima * 50000
                    : $qtyDiterima;

                $rows[] = [
                    'id_kandang' => 0,
                    'id_pakan' => $detail->pakan_id,
                    'tgl' => $validated['tanggal_terima'],
                    'pcs' => $qtyStok,
                    'pcs_kredit' => 0,
                    'pcs_selisih' => null,
                    'admin' => $admin,
                    'check' => 'Y',
                    'cek_admin' => $admin,
                    'opname' => 'T',
                    'total_rp' => round($qtyDiterima * (float) $detail->harga_satuan, 2),
                    'biaya_dll' => 0,
                    'no_nota' => $faktur_pembelian->no_faktur,
                    'h_opname' => 'T',
                    'penyesuaian' => 'T',
                ];
            }

            abort_if(empty($rows), 422, 'Qty diterima harus diisi minimal 1 item.');

            DB::table('stok_produk_perencanaan')->insert($rows);
        });

        return redirect()
            ->route('transaksi.faktur-pembelian.index')
            ->with('sukses', 'Stok faktur berhasil diterima.');
    }

    public function terimaBatch(Request $request): View|RedirectResponse
    {
        $ids = collect($request->input('faktur', []))
            ->filter()
            ->map(fn($id) => (int) $id)
            ->unique()
            ->values();

        if ($ids->isEmpty()) {
            return redirect()
                ->route('transaksi.penerimaan.index')
                ->with('error', 'Pilih minimal 1 faktur untuk penerimaan stok.');
        }

        $fakturs = FakturModel::with(['supplier', 'detail.produk'])
            ->whereIn('id', $ids)
            ->orderBy('tanggal_faktur')
            ->orderBy('no_faktur')
            ->get();

        $qtyDiterimaByNota = $this->qtyDiterimaByNota($fakturs);

        return view('transaksi.penerimaan.terima_batch', [
            'title' => 'Penerimaan Stok Beberapa Nota',
            'fakturs' => $fakturs,
            'qtyDiterimaByNota' => $qtyDiterimaByNota,
        ]);
    }

    public function storeTerimaBatch(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'tanggal_terima' => ['required', 'date'],
            'faktur' => ['required', 'array', 'min:1'],
            'faktur.*' => ['required', 'integer', 'exists:faktur_pembelian,id'],
            'detail' => ['required', 'array', 'min:1'],
            'detail.*.qty_diterima' => ['required', 'numeric', 'min:0.01'],
        ]);

        $fakturs = FakturModel::with('detail.produk')
            ->whereIn('id', $validated['faktur'])
            ->get();

        $qtyDiterimaByNota = $this->qtyDiterimaByNota($fakturs);

        DB::transaction(function () use ($validated, $fakturs, $qtyDiterimaByNota) {
            $admin = auth()->user()->name ?? 'system';
            $rows = [];

            foreach ($fakturs as $faktur) {
                foreach ($faktur->detail as $detail) {
                    $qtyDiterima = (float) data_get($validated, 'detail.' . $detail->id . '.qty_diterima', 0);
                    $qtySebelumnya = (float) data_get($qtyDiterimaByNota, $faktur->no_faktur . '.' . $detail->pakan_id, 0);
                    $qtySisa = max((float) $detail->qty - $qtySebelumnya, 0);

                    if ($qtyDiterima <= 0) {
                        continue;
                    }

                    abort_if(
                        $qtyDiterima > $qtySisa,
                        422,
                        'Qty diterima ' . $faktur->no_faktur . ' - ' . ($detail->produk->nm_produk ?? 'produk') . ' melebihi sisa faktur.'
                    );

                    $qtyStok = $faktur->jenis_faktur === 'pakan'
                        ? $qtyDiterima * 50000
                        : $qtyDiterima;

                    $rows[] = [
                        'id_kandang' => 0,
                        'id_pakan' => $detail->pakan_id,
                        'tgl' => $validated['tanggal_terima'],
                        'pcs' => $qtyStok,
                        'pcs_kredit' => 0,
                        'pcs_selisih' => null,
                        'admin' => $admin,
                        'check' => 'Y',
                        'cek_admin' => $admin,
                        'opname' => 'T',
                        'total_rp' => round($qtyDiterima * (float) $detail->harga_satuan, 2),
                        'biaya_dll' => 0,
                        'no_nota' => $faktur->no_faktur,
                        'h_opname' => 'T',
                        'penyesuaian' => 'T',
                    ];
                }
            }

            abort_if(empty($rows), 422, 'Qty diterima harus diisi minimal 1 item.');

            DB::table('stok_produk_perencanaan')->insert($rows);
        });

        return redirect()
            ->route('transaksi.penerimaan.index')
            ->with('sukses', 'Stok beberapa faktur berhasil diterima.');
    }

    private function qtyDiterimaFaktur($fakturs)
    {
        $fakturs = collect($fakturs);

        if ($fakturs->isEmpty()) {
            return collect();
        }

        return DB::table('faktur_pembelian as f')
            ->leftJoin('stok_produk_perencanaan as s', 's.no_nota', '=', 'f.no_faktur')
            ->whereIn('f.no_faktur', $fakturs->pluck('no_faktur'))
            ->groupBy('f.no_faktur', 'f.jenis_faktur')
            ->select('f.no_faktur')
            ->selectRaw("COALESCE(SUM(CASE WHEN f.jenis_faktur = 'pakan' THEN s.pcs / 50000 ELSE s.pcs END), 0) as qty_diterima")
            ->pluck('qty_diterima', 'no_faktur');
    }

    private function qtyDiterimaByNota($fakturs): array
    {
        $fakturs = collect($fakturs);

        if ($fakturs->isEmpty()) {
            return [];
        }

        return DB::table('stok_produk_perencanaan as s')
            ->join('faktur_pembelian as f', 'f.no_faktur', '=', 's.no_nota')
            ->whereIn('f.no_faktur', $fakturs->pluck('no_faktur'))
            ->groupBy('f.no_faktur', 'f.jenis_faktur', 's.id_pakan')
            ->select('f.no_faktur', 's.id_pakan')
            ->selectRaw("SUM(CASE WHEN f.jenis_faktur = 'pakan' THEN s.pcs / 50000 ELSE s.pcs END) as qty")
            ->get()
            ->groupBy('no_faktur')
            ->map(fn($rows) => $rows->pluck('qty', 'id_pakan')->all())
            ->all();
    }

    private function fakturSudahAdaPenerimaan(FakturModel $faktur): bool
    {
        return DB::table('stok_produk_perencanaan')
            ->where('no_nota', $faktur->no_faktur)
            ->exists();
    }

    private function produkFakturOptions()
    {
        return ProdukPerencanaan::query()
            ->leftJoin('tb_satuan as s', 's.id_satuan', '=', 'tb_produk_perencanaan.dosis_satuan')
            ->orderBy('tb_produk_perencanaan.nm_produk')
            ->get([
                'tb_produk_perencanaan.*',
                's.nm_satuan as satuan_dosis',
            ]);
    }

    private function normalisasiItemFaktur(array $items)
    {
        return collect($items)->values()->map(function ($item) {
            $qty = (float) $item['qty'];
            $subtotal = round((float) $item['subtotal'], 2);

            $item['qty'] = $qty;
            $item['subtotal'] = $subtotal;
            $item['harga_satuan'] = $qty > 0
                ? round($subtotal / $qty, 6)
                : round((float) $item['harga_satuan'], 6);

            return $item;
        });
    }

    private function terapkanDiskonKeItem($items, float $diskonTotal)
    {
        if ($diskonTotal <= 0) {
            return $items;
        }

        $totalSebelumDiskon = round($items->sum(fn($item) => (float) $item['subtotal']), 2);
        $sisaDiskon = $diskonTotal;
        $jumlahItem = $items->count();

        return $items->values()->map(function ($item, $index) use ($totalSebelumDiskon, $diskonTotal, &$sisaDiskon, $jumlahItem) {
            $qty = (float) $item['qty'];
            $subtotalAwal = round((float) $item['subtotal'], 2);
            $diskonItem = $index === $jumlahItem - 1
                ? $sisaDiskon
                : round($subtotalAwal / $totalSebelumDiskon * $diskonTotal, 2);
            $sisaDiskon = round($sisaDiskon - $diskonItem, 2);
            $subtotalBersih = max(round($subtotalAwal - $diskonItem, 2), 0);

            $item['subtotal'] = $subtotalBersih;
            $item['harga_satuan'] = $qty > 0 ? round($subtotalBersih / $qty, 6) : 0;

            return $item;
        });
    }

    private function totalPelunasanFaktur(FakturModel $faktur): float
    {
        return (float) DB::table('pelunasan_faktur_pembelian')
            ->where('faktur_pembelian_id', $faktur->id)
            ->sum('jumlah_bayar');
    }

    private function riwayatPelunasanFaktur(FakturModel $faktur)
    {
        return DB::table('pelunasan_faktur_pembelian as p')
            ->leftJoin('akun_perkiraan as a', 'a.id_akun_perkiraan', '=', 'p.id_akun_kas')
            ->where('p.faktur_pembelian_id', $faktur->id)
            ->orderByDesc('p.tanggal_bayar')
            ->orderByDesc('p.id')
            ->get([
                'p.*',
                'a.kode_perkiraan',
                'a.nama as nama_akun',
            ]);
    }

    private function akunKasBankAktif()
    {
        return DB::table('akun_perkiraan')
            ->where('aktif', 1)
            ->where(function ($query) {
                $query->where('kode_perkiraan', 'like', '1101%')
                    ->orWhere('nama', 'like', '%kas%')
                    ->orWhere('nama', 'like', '%bank%');
            })
            ->whereNotNull('id_akun_induk')
            ->orderBy('kode_perkiraan')
            ->get(['id_akun_perkiraan', 'kode_perkiraan', 'nama']);
    }

    private function rebuildJurnalFaktur(
        FakturModel $faktur,
        $items,
        $produk,
        object $akunHutang,
        object $akunPersediaan,
        string $noFakturLama
    ): void {
        $sekarang = now();
        $totalHarga = $items->sum(fn($item) => (float) $item['subtotal']);

        $batchId = DB::table('jurnal_perkiraan')
            ->where('nomor_transaksi', $noFakturLama)
            ->where('tipe_transaksi', 'like', 'Faktur Pembelian%')
            ->value('id_impor_jurnal_perkiraan');

        if ($batchId) {
            DB::table('jurnal_perkiraan')
                ->where('id_impor_jurnal_perkiraan', $batchId)
                ->where('nomor_transaksi', $noFakturLama)
                ->delete();

            DB::table('impor_jurnal_perkiraan')
                ->where('id_impor_jurnal_perkiraan', $batchId)
                ->update([
                    'nama_file' => 'Faktur pembelian ' . $faktur->jenis_faktur . ' ' . $faktur->no_faktur,
                    'periode_awal' => $faktur->tanggal_faktur,
                    'periode_akhir' => $faktur->tanggal_faktur,
                    'jumlah_transaksi' => 1,
                    'jumlah_detail' => $items->count() + 1,
                    'total_debit' => $totalHarga,
                    'total_kredit' => $totalHarga,
                    'updated_at' => $sekarang,
                ]);
        } else {
            $batchId = DB::table('impor_jurnal_perkiraan')->insertGetId([
                'nama_file' => 'Faktur pembelian ' . $faktur->jenis_faktur . ' ' . $faktur->no_faktur,
                'hash_file' => hash('sha256', 'faktur-pembelian|' . $faktur->no_faktur),
                'periode_awal' => $faktur->tanggal_faktur,
                'periode_akhir' => $faktur->tanggal_faktur,
                'jumlah_transaksi' => 1,
                'jumlah_detail' => $items->count() + 1,
                'total_debit' => $totalHarga,
                'total_kredit' => $totalHarga,
                'status' => 'aktif',
                'diimpor_oleh' => auth()->id(),
                'created_at' => $sekarang,
                'updated_at' => $sekarang,
            ]);
        }

        $detailJurnal = [];
        $urutanDetail = 1;

        foreach ($items as $item) {
            $namaProduk = $produk->get((int) $item['pakan_id'])?->nm_produk ?? 'Produk';
            $subtotal = (float) $item['subtotal'];

            $detailJurnal[] = [
                'id_impor_jurnal_perkiraan' => $batchId,
                'id_akun_perkiraan' => $akunPersediaan->id_akun_perkiraan,
                'tanggal' => $faktur->tanggal_faktur,
                'nomor_transaksi' => $faktur->no_faktur,
                'tipe_transaksi' => 'Faktur Pembelian ' . ucfirst($faktur->jenis_faktur),
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
            'tanggal' => $faktur->tanggal_faktur,
            'nomor_transaksi' => $faktur->no_faktur,
            'tipe_transaksi' => 'Faktur Pembelian ' . ucfirst($faktur->jenis_faktur),
            'urutan_detail' => $urutanDetail,
            'deskripsi' => 'Hutang pembelian ' . $faktur->jenis_faktur,
            'debit' => 0,
            'kredit' => $totalHarga,
            'created_at' => $sekarang,
            'updated_at' => $sekarang,
        ];

        DB::table('jurnal_perkiraan')->insert($detailJurnal);
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
