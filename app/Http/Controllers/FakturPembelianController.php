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

        $nomorFaktur = $faktur->getCollection()->pluck('no_faktur');
        $penerimaanFaktur = DB::table('stok_produk_perencanaan')->whereIn('no_nota', $nomorFaktur)
            ->groupBy('no_nota')->select('no_nota')->selectRaw('SUM(pcs) as qty_diterima')->pluck('qty_diterima', 'no_nota');
        $jenisByNota = $faktur->getCollection()->pluck('jenis_faktur', 'no_faktur');
        foreach ($penerimaanFaktur as $nota => $qty) {
            if (($jenisByNota[$nota] ?? null) === 'pakan') $penerimaanFaktur[$nota] = (float) $qty / 50000;
        }
        $penerimaanUmum = DB::table('pembukuan_baru_stok')->whereIn('nomor_transaksi', $nomorFaktur)
            ->groupBy('nomor_transaksi')->select('nomor_transaksi')->selectRaw('SUM(qty) as qty_diterima')->pluck('qty_diterima', 'nomor_transaksi');
        foreach ($penerimaanUmum as $nota => $qty) $penerimaanFaktur[$nota] = $qty;

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
        $produkUmum = DB::table('tb_produk as p')
            ->leftJoin('tb_satuan as s', 's.id_satuan', '=', 'p.satuan_id')
            ->where('p.kategori_id', 1)
            ->orderBy('p.nm_produk')
            ->get(['p.id_produk', 'p.kd_produk', 'p.nm_produk', 's.nm_satuan as satuan_dosis'])
            ->each(function ($item) { $item->kategori = 'barang_umum'; $item->sumber_produk = 'barang_umum'; });
        $produkPerencanaan = ProdukPerencanaan::query()
            ->leftJoin('tb_satuan as s', 's.id_satuan', '=', 'tb_produk_perencanaan.dosis_satuan')
            ->orderBy('tb_produk_perencanaan.nm_produk')
            ->get(['tb_produk_perencanaan.*', 's.nm_satuan as satuan_dosis'])
            ->each(fn ($item) => $item->sumber_produk = 'perencanaan');
        $hargaRataRata = DB::table('faktur_pembelian_detail as d')
            ->select('d.pakan_id', 'd.sumber_produk')
            ->selectRaw('SUM(d.subtotal) / NULLIF(SUM(d.qty), 0) as harga_rata_rata')
            ->groupBy('d.pakan_id', 'd.sumber_produk')
            ->get()
            ->mapWithKeys(fn ($row) => [($row->sumber_produk ?: 'perencanaan') . ':' . $row->pakan_id => (float) $row->harga_rata_rata]);
        return view('transaksi.faktur_pembelian.create', [
            'title' => 'Tambah Faktur Pembelian',
            'suppliers' => Suplier::orderBy('nm_suplier')->get(),
            'produk' => $produkPerencanaan->concat($produkUmum),
            'hargaRataRata' => $hargaRataRata,
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

        $fakturs = FakturModel::query()
            ->with('supplier')
            ->whereBetween('faktur_pembelian.tanggal_faktur', [$tanggalAwal, $tanggalAkhir])
            ->where('faktur_pembelian.total_hutang', '>', 0)
            ->when($cari, function ($query) use ($cari) {
                $query->where(function ($q) use ($cari) {
                    $q->where('faktur_pembelian.no_faktur', 'like', "%{$cari}%")
                        ->orWhereHas('supplier', function ($sq) use ($cari) {
                            $sq->where('nm_suplier', 'like', "%{$cari}%");
                        });
                });
            })
            ->orderByDesc('faktur_pembelian.tanggal_faktur')
            ->orderByDesc('faktur_pembelian.id')
            ->get();

        $tagihan = $this->tagihanKomponen($fakturs);
        $totalHutang = (float) $tagihan->sum('nominal_hutang');
        $totalHutangBarang = (float) $tagihan->where('komponen_hutang', 'barang')->sum('nominal_hutang');
        $totalHutangOngkir = (float) $tagihan->where('komponen_hutang', 'ongkir')->sum('nominal_hutang');
        $totalHutangAdmin = (float) $tagihan->where('komponen_hutang', 'admin')->sum('nominal_hutang');
        $totalTerbayar = (float) $tagihan->sum('total_bayar');
        $totalSisa = (float) $tagihan->sum('sisa_hutang');
        $jumlahBerjalan = $tagihan->where('sisa_hutang', '>', 0)->count();
        $jumlahLunas = $tagihan->where('sisa_hutang', '<=', 0)->count();

        $dataTagihan = $tagihan->filter(fn ($item) => $status === 'berjalan'
            ? (float) $item->sisa_hutang > 0
            : (float) $item->sisa_hutang <= 0)->values();
        $page = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 15;
        $faktur = new LengthAwarePaginator(
            $dataTagihan->forPage($page, $perPage)->values(),
            $dataTagihan->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('transaksi.buku_hutang.index', [
            'title' => 'Buku Hutang Faktur Pembelian',
            'tanggalAwal' => $tanggalAwal,
            'tanggalAkhir' => $tanggalAkhir,
            'status' => $status,
            'faktur' => $faktur,
            'totalHutang' => $totalHutang,
            'totalHutangBarang' => $totalHutangBarang,
            'totalHutangOngkir' => $totalHutangOngkir,
            'totalHutangAdmin' => $totalHutangAdmin,
            'totalTerbayar' => $totalTerbayar,
            'totalSisa' => $totalSisa,
            'jumlahBerjalan' => $jumlahBerjalan,
            'jumlahLunas' => $jumlahLunas,
        ]);
    }

    public function pelunasan(Request $request, FakturModel $faktur_pembelian): View|RedirectResponse
    {
        $faktur_pembelian->load('supplier', 'detail.produk', 'detail.produkUmum');
        $komponen = in_array($request->input('komponen'), ['barang', 'ongkir', 'admin'], true)
            ? $request->input('komponen')
            : 'barang';
        $tagihan = $this->tagihanKomponen(collect([$faktur_pembelian]))->firstWhere('komponen_hutang', $komponen);

        if (! $tagihan) {
            return redirect()->route('transaksi.buku-hutang.index')->with('error', 'Komponen tagihan tidak ditemukan.');
        }

        $totalTerbayar = (float) $tagihan->total_bayar;
        $sisaHutang = (float) $tagihan->sisa_hutang;

        if ($sisaHutang <= 0) {
            return redirect()
                ->route('transaksi.buku-hutang.index', ['status' => 'lunas'])
                ->with('sukses', 'Tagihan ini sudah lunas.');
        }

        return view('transaksi.buku_hutang.pelunasan', [
            'title' => 'Pelunasan ' . $tagihan->nama_komponen,
            'faktur' => $faktur_pembelian,
            'tagihan' => $tagihan,
            'komponen' => $komponen,
            'totalTerbayar' => $totalTerbayar,
            'sisaHutang' => $sisaHutang,
            'akunKas' => $this->akunKasBankAktif(),
            'riwayatPelunasan' => $this->riwayatPelunasanFaktur($faktur_pembelian, $komponen),
        ]);
    }

    public function storePelunasan(Request $request, FakturModel $faktur_pembelian): RedirectResponse
    {
        $validated = $request->validate([
            'tanggal_bayar' => ['required', 'date'],
            'komponen_hutang' => ['required', 'in:barang,ongkir,admin'],
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

        $tagihan = $this->tagihanKomponen(collect([$faktur_pembelian]))
            ->firstWhere('komponen_hutang', $validated['komponen_hutang']);
        if (! $tagihan) {
            return back()->withErrors(['komponen_hutang' => 'Komponen tagihan tidak ditemukan.'])->withInput();
        }
        $sisaHutang = (float) $tagihan->sisa_hutang;
        $jumlahBayar = round((float) $validated['jumlah_bayar'], 2);

        if ($jumlahBayar > $sisaHutang) {
            return back()
                ->withErrors(['jumlah_bayar' => 'Jumlah bayar melebihi sisa hutang. Sisa hutang Rp ' . number_format($sisaHutang, 0, ',', '.')])
                ->withInput();
        }

        DB::transaction(function () use ($validated, $faktur_pembelian, $akunHutang, $akunKas, $jumlahBayar, $tagihan) {
            $sekarang = now();
            $nomorTransaksi = $tagihan->nomor_tagihan;

            $pelunasanId = DB::table('pelunasan_faktur_pembelian')->insertGetId([
                'faktur_pembelian_id' => $faktur_pembelian->id,
                'komponen_hutang' => $validated['komponen_hutang'],
                'tanggal_bayar' => $validated['tanggal_bayar'],
                'jumlah_bayar' => $jumlahBayar,
                'id_akun_kas' => $validated['id_akun_kas'],
                'keterangan' => $validated['keterangan'] ?? null,
                'created_by' => auth()->id(),
                'created_at' => $sekarang,
                'updated_at' => $sekarang,
            ]);

            $batchId = DB::table('impor_jurnal_perkiraan')->insertGetId([
                'nama_file' => 'Pelunasan hutang ' . $tagihan->nomor_tagihan,
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
                    'deskripsi' => 'Pelunasan ' . strtolower($tagihan->nama_komponen) . ' ' . $tagihan->nomor_tagihan . ' (' . $sekarang->format('YmdHis') . ')',
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
                    'deskripsi' => 'Pembayaran ' . strtolower($tagihan->nama_komponen) . ' ' . $tagihan->nomor_tagihan . ' dari ' . $akunKas->nama . ' (' . $sekarang->format('YmdHis') . ')',
                    'debit' => 0,
                    'kredit' => $jumlahBayar,
                    'created_at' => $sekarang,
                    'updated_at' => $sekarang,
                ],
            ]);

            $totalTerbayar = $this->totalPelunasanFaktur($faktur_pembelian);
            $sisaSetelahBayar = max((float) $faktur_pembelian->total_hutang - $totalTerbayar, 0);

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
             'jenis_faktur' => ['required', 'in:pakan,vitamin,vaksin,barang_umum'],
             'no_faktur' => ['required', 'max:30', 'unique:faktur_pembelian,no_faktur'],
             'tanggal_faktur' => ['required', 'date'],
             'supplier_id' => ['required', 'exists:tb_suplier,id_suplier'],
             'jatuh_tempo' => ['nullable', 'date'],
             'keterangan' => ['nullable', 'string'],
             'diskon_total' => ['nullable', 'numeric', 'min:0'],
             'biaya_lain' => ['nullable', 'array'],
             'biaya_lain.ongkir.nominal' => ['nullable', 'numeric', 'min:0'],
             'biaya_lain.admin.nominal' => ['nullable', 'numeric', 'min:0'],
             'pph23_manual' => ['nullable', 'numeric', 'min:0'],
             'item' => ['required', 'array', 'min:1'],
             'item.*.pakan_id' => ['required', 'integer', 'min:1'],
             'item.*.sumber_produk' => ['required', 'in:perencanaan,barang_umum'],
             'item.*.qty' => ['required', 'numeric', 'min:0.01'],
             'item.*.satuan' => ['nullable', 'string', 'max:20'],
             'item.*.harga_satuan' => ['required', 'numeric', 'min:0'],
             'item.*.subtotal' => ['required', 'numeric', 'min:0'],
             'item.*.no_batch' => ['nullable', 'string', 'max:50'],
             'item.*.tanggal_expired' => ['nullable', 'date'],
         ]);

         $akunHutangPakan = $this->akunAktif('210221');
         $akunHutangEkspedisi = $this->akunAktif('210222');
         $akunHutangLainnya = $this->akunAktif('210220');
         
         if (! $akunHutangPakan) {
             return back()->withErrors(['akun' => 'Akun Hutang Pakan (210221) belum tersedia atau tidak aktif.'])->withInput();
         }

         $items = $this->normalisasiItemFaktur($validated['item'])->map(function ($item) use ($akunHutangPakan) {
             $item['id_akun_pembayaran'] = (int) $akunHutangPakan->id_akun_perkiraan;
             return $item;
         });
         // Satu faktur hanya berisi satu sumber produk. Untuk faktur Barang Umum,
         // paksa sumbernya agar tidak terpengaruh oleh baris lama/JS browser.
         if ($validated['jenis_faktur'] === 'barang_umum') {
             $items = $items->map(function ($item) {
                 $item['sumber_produk'] = 'barang_umum';
                 return $item;
             });
         }
         $diskonTotal = round((float) ($validated['diskon_total'] ?? 0), 2);

         if ($diskonTotal > $items->sum(fn($item) => (float) $item['subtotal'])) {
             return back()
                 ->withErrors(['diskon_total' => 'Diskon tidak boleh lebih besar dari total pembelian.'])
                 ->withInput();
         }

         $items = $this->terapkanDiskonKeItem($items, $diskonTotal);
         $biayaLain = $this->normalisasiBiayaLain($validated['biaya_lain'] ?? [], $akunHutangEkspedisi, $akunHutangLainnya);
         $pph23Manual = round((float) ($validated['pph23_manual'] ?? 0), 2);
         $totalPph23 = $pph23Manual;
         $akunPph23 = $totalPph23 > 0 ? $this->akunAktif('210203') : null;
         // Simpan PPh 23 ke dalam biaya_lain (menempel pada ongkir) agar tersimpan & dipakai rebuild jurnal
         if ($totalPph23 > 0) {
             $biayaLain = collect($biayaLain)->map(function ($b) use ($totalPph23) {
                 if ($b['kode'] === 'ongkir') {
                     $b['pph23_nominal'] = $totalPph23;
                 }
                 return $b;
             })->values()->all();
         }
         // Simpan PPh 23 ke dalam biaya_lain (menempel pada ongkir) agar tersimpan & dipakai rebuild jurnal
         if ($totalPph23 > 0) {
             $biayaLain = collect($biayaLain)->map(function ($b) use ($totalPph23) {
                 if ($b['kode'] === 'ongkir') {
                     $b['pph23_nominal'] = $totalPph23;
                 }
                 return $b;
             })->values()->all();
         }

        $produk = ProdukPerencanaan::query()
            ->leftJoin('tb_satuan as s', 's.id_satuan', '=', 'tb_produk_perencanaan.dosis_satuan')
            ->whereIn('tb_produk_perencanaan.id_produk', $items->pluck('pakan_id'))
            ->get([
                'tb_produk_perencanaan.*',
                's.nm_satuan as satuan_dosis',
            ])
            ->keyBy('id_produk');
        $produkUmum = DB::table('tb_produk as p')->leftJoin('tb_satuan as s','s.id_satuan','=','p.satuan_id')->where('p.kategori_id',1)->whereIn('p.id_produk',$items->pluck('pakan_id'))->get(['p.*','s.nm_satuan as satuan_dosis'])->keyBy('id_produk');

        $produkTidakSesuai = $items->contains(function ($item) use ($produk, $produkUmum, $validated) {
            $kategori = ($item['sumber_produk'] ?? 'perencanaan') === 'barang_umum'
                ? ($produkUmum->get((int) $item['pakan_id']) ? 'barang_umum' : null)
                : $produk->get((int) $item['pakan_id'])?->kategori;

            return ! $this->produkSesuaiJenisFaktur($kategori, $validated['jenis_faktur']);
        });

        if ($produkTidakSesuai) {
            return back()
                ->withErrors(['item' => 'Produk yang dipilih tidak sesuai dengan jenis faktur.'])
                ->withInput();
        }

         $idAkunPembayaran = $items->pluck('id_akun_pembayaran')
             ->map(fn ($id) => (int) $id)->unique()->values();
         $akunPembayaran = collect([$akunHutangPakan])
             ->whereIn('id_akun_perkiraan', $idAkunPembayaran)->keyBy('id_akun_perkiraan');
         $akunBiaya = collect([$akunHutangEkspedisi, $akunHutangLainnya])
             ->whereIn('id_akun_perkiraan', collect($biayaLain)->pluck('id_akun'))->keyBy('id_akun_perkiraan');
         $kodeAkunPersediaan = $items->map(function ($item) use ($validated, $produk, $produkUmum) {
             $kategori = ($item['sumber_produk'] ?? 'perencanaan') === 'barang_umum'
                 ? 'barang_umum'
                 : $produk->get((int) $item['pakan_id'])?->kategori;

             return $this->kodeAkunPersediaanItem($validated['jenis_faktur'], $kategori);
         })->unique()->values();
         $akunPersediaan = DB::table('akun_perkiraan')->where('aktif', 1)
             ->whereIn('kode_perkiraan', $kodeAkunPersediaan)->get()->keyBy('kode_perkiraan');

         if (! $akunHutangPakan || ! $akunHutangEkspedisi
             || ($totalPph23 > 0 && ! $akunPph23)
             || $akunPembayaran->count() !== $idAkunPembayaran->count()
             || $akunBiaya->count() !== collect($biayaLain)->pluck('id_akun')->unique()->count()
             || $akunPersediaan->count() !== $kodeAkunPersediaan->count()) {
             return back()
                 ->withErrors(['akun' => 'Akun Hutang Pakan/Ekspedisi atau akun persediaan belum tersedia/aktif.'])
                 ->withInput();
         }

         $fakturId = DB::transaction(function () use ($validated, $items, $produk, $produkUmum, $akunHutangPakan, $akunHutangEkspedisi, $akunPembayaran, $akunBiaya, $akunPersediaan, $diskonTotal, $biayaLain, $totalPph23, $akunPph23) {
             $sekarang = now();
             $tipeJurnal = $validated['jenis_faktur'] === 'barang_umum'
                 ? 'Pembelian Umum'
                 : ($validated['jenis_faktur'] === 'vitamin' ? 'Faktur Pembelian Vitamin & Vaksin' : 'Faktur Pembelian ' . ucfirst($validated['jenis_faktur']));
             $totalQty = $items->sum(fn($item) => (float) $item['qty']);
             $totalItem = $items->sum(fn($item) => (float) $item['subtotal']);
             $totalBiayaLain = collect($biayaLain)->sum('nominal');
             $totalHarga = round($totalItem + $totalBiayaLain, 2);
             $kreditPerAkun = $items->groupBy(fn ($item) => (int) $item['id_akun_pembayaran'])
                 ->map(fn ($baris) => round($baris->sum(fn ($item) => (float) $item['subtotal']), 2));
             $totalHutang = round(
                 (float) ($kreditPerAkun[$akunHutangPakan->id_akun_perkiraan] ?? 0)
                 + collect($biayaLain)->sum('nominal')
                 - $totalPph23,
                 2
             );
            $metodePembayaran = $totalHutang <= 0
                ? 'tunai'
                : ($totalHutang >= $totalHarga ? 'hutang' : 'campuran');

            $faktur = FakturModel::create([
                'no_faktur' => $validated['no_faktur'],
                'jenis_faktur' => $validated['jenis_faktur'],
                'metode_pembayaran' => $metodePembayaran,
                'tanggal_faktur' => $validated['tanggal_faktur'],
                'supplier_id' => $validated['supplier_id'],
                'jatuh_tempo' => $validated['jatuh_tempo'] ?? null,
                'status_bayar' => $totalHutang > 0 ? 'belum_lunas' : 'lunas',
                'total_qty' => $totalQty,
                'total_harga' => $totalHarga,
                'total_hutang' => $totalHutang,
                'diskon_total' => $diskonTotal,
                'biaya_lain' => $biayaLain ?: null,
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
                    'sumber_produk' => $item['sumber_produk'] ?? 'perencanaan',
                    'qty' => $qty,
                    'satuan' => $validated['jenis_faktur'] === 'pakan'
                        ? 'zak'
                        : (($item['sumber_produk'] ?? 'perencanaan') === 'barang_umum' ? ($produkUmum->get((int) $item['pakan_id'])?->satuan_dosis ?? null) : ($produk->get((int) $item['pakan_id'])?->satuan_dosis ?? null)),
                    'harga_satuan' => $hargaSatuan,
                    'subtotal' => $subtotal,
                    'id_akun_pembayaran' => (int) $item['id_akun_pembayaran'],
                    'no_batch' => $item['no_batch'] ?? null,
                    'tanggal_expired' => $item['tanggal_expired'] ?? null,
                ]);
            }

            $batchId = DB::table('impor_jurnal_perkiraan')->insertGetId([
                'nama_file' => ($validated['jenis_faktur'] === 'barang_umum' ? 'Pembelian Umum ' : 'Faktur pembelian ' . $validated['jenis_faktur'] . ' ') . $validated['no_faktur'],
                'hash_file' => hash('sha256', 'faktur-pembelian|' . $validated['no_faktur']),
                'periode_awal' => $validated['tanggal_faktur'],
                'periode_akhir' => $validated['tanggal_faktur'],
                'jumlah_transaksi' => 1,
                'jumlah_detail' => $items->count() + $kreditPerAkun->count() + count($biayaLain) + ($totalPph23 > 0 ? 2 : 0),
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
                $itemProduk = ($item['sumber_produk'] ?? 'perencanaan') === 'barang_umum'
                    ? $produkUmum->get((int) $item['pakan_id'])
                    : $produk->get((int) $item['pakan_id']);
                $namaProduk = $itemProduk?->nm_produk ?? 'Produk';
                $subtotal = (float) $item['subtotal'];
                $kategori = ($item['sumber_produk'] ?? 'perencanaan') === 'barang_umum' ? 'barang_umum' : $itemProduk?->kategori;
                $akunDebit = $akunPersediaan->get($this->kodeAkunPersediaanItem($validated['jenis_faktur'], $kategori));

                $rasio = $totalItem > 0 ? ((float) $subtotal / $totalItem) : (1 / max($items->count(), 1));
                $biayaAlokasi = round($totalBiayaLain * $rasio, 2);
                if ($item === $items->last()) {
                    $biayaAlokasi = round($totalBiayaLain - collect($detailJurnal)->sum(fn ($j) => (float) ($j['_biaya_alokasi'] ?? 0)), 2);
                }
                $detailJurnal[] = [
                    'id_impor_jurnal_perkiraan' => $batchId,
                    'id_akun_perkiraan' => $akunDebit->id_akun_perkiraan,
                    'tanggal' => $validated['tanggal_faktur'],
                    'nomor_transaksi' => $validated['no_faktur'],
                    'tipe_transaksi' => $tipeJurnal,
                    'urutan_detail' => $urutanDetail++,
                    'deskripsi' => 'Pembelian ' . $namaProduk,
                    'debit' => round($subtotal + $biayaAlokasi, 2),
                    'kredit' => 0,
                    'created_at' => $sekarang,
                    'updated_at' => $sekarang,
                    '_biaya_alokasi' => $biayaAlokasi,
                ];
            }
            $detailJurnal = array_map(function ($row) { unset($row['_biaya_alokasi']); return $row; }, $detailJurnal);

             foreach ($kreditPerAkun as $idAkun => $nominal) {
                 $akunKredit = $akunPembayaran->get((int) $idAkun) ?? $akunBiaya->get((int) $idAkun);
                 $isHutang = (int) $idAkun === (int) $akunHutangPakan->id_akun_perkiraan;

                $detailJurnal[] = [
                    'id_impor_jurnal_perkiraan' => $batchId,
                    'id_akun_perkiraan' => $akunKredit->id_akun_perkiraan,
                    'tanggal' => $validated['tanggal_faktur'],
                    'nomor_transaksi' => $validated['no_faktur'],
                    'tipe_transaksi' => $tipeJurnal,
                    'urutan_detail' => $urutanDetail++,
                    'deskripsi' => ($isHutang ? 'Hutang pembelian ' : 'Pembayaran pembelian ') . $validated['jenis_faktur'] . ' via ' . $akunKredit->nama,
                    'debit' => 0,
                    'kredit' => $nominal,
                    'created_at' => $sekarang,
                    'updated_at' => $sekarang,
                ];
             }

             foreach ($biayaLain as $biaya) {
                 $akunKredit = $akunBiaya->get((int) $biaya['id_akun']);
                 $nominalHutangBiaya = round((float) $biaya['nominal'], 2);
                 
                 // Jika ada PPH dan ini biaya ekspedisi, kurangi dari hutang ekspedisi
                 $pphUntukBiayaIni = 0;
                 if ($totalPph23 > 0 && $biaya['kode'] === 'ongkir') {
                     $pphUntukBiayaIni = $totalPph23;
                     $nominalHutangBiaya = round($nominalHutangBiaya - $pphUntukBiayaIni, 2);
                 }
                 
                 $detailJurnal[] = [
                     'id_impor_jurnal_perkiraan' => $batchId,
                     'id_akun_perkiraan' => $akunKredit->id_akun_perkiraan,
                     'tanggal' => $validated['tanggal_faktur'],
                     'nomor_transaksi' => $validated['no_faktur'],
                     'tipe_transaksi' => $tipeJurnal,
                     'urutan_detail' => $urutanDetail++,
                     'deskripsi' => 'Hutang biaya ' . strtolower($biaya['nama']) . ' pembelian ' . $validated['jenis_faktur'],
                     'debit' => 0,
                     'kredit' => $nominalHutangBiaya,
                     'created_at' => $sekarang,
                     'updated_at' => $sekarang,
                 ];
                 
                 // Jika ada PPH, kredit ke akun pajak (hutang ekspedisi sudah dikurangi PPh di atas)
                 if ($pphUntukBiayaIni > 0) {
                     $detailJurnal[] = [
                         'id_impor_jurnal_perkiraan' => $batchId,
                         'id_akun_perkiraan' => $akunPph23->id_akun_perkiraan,
                         'tanggal' => $validated['tanggal_faktur'],
                         'nomor_transaksi' => $validated['no_faktur'],
                         'tipe_transaksi' => $tipeJurnal,
                         'urutan_detail' => $urutanDetail++,
                         'deskripsi' => 'Hutang pajak PPh 23 dari ' . strtolower($biaya['nama']) . ' pembelian ' . $validated['jenis_faktur'],
                         'debit' => 0,
                         'kredit' => $pphUntukBiayaIni,
                         'created_at' => $sekarang,
                         'updated_at' => $sekarang,
                     ];
                 }
             }

             DB::table('jurnal_perkiraan')->insert($detailJurnal);

            return $faktur->getKey();
        });

        return redirect()
            ->route('transaksi.faktur-pembelian.index')
            ->with('sukses', 'Faktur pembelian berhasil disimpan dan jurnal pembayaran sudah dibuat.');
    }

    public function detail(FakturModel $faktur_pembelian): View
    {
        $faktur_pembelian->load(['supplier', 'detail.produk', 'detail.produkUmum', 'detail.akunPembayaran']);
        $biayaLain = collect($faktur_pembelian->biaya_lain ?? []);
        $akunBiaya = DB::table('akun_perkiraan')
            ->whereIn('id_akun_perkiraan', $biayaLain->pluck('id_akun')->filter()->unique())
            ->get(['id_akun_perkiraan', 'kode_perkiraan', 'nama'])
            ->keyBy('id_akun_perkiraan');

        if ($faktur_pembelian->jenis_faktur === 'barang_umum') {
            $qtyDiterimaByProduk = DB::table('pembukuan_baru_stok')->where('nomor_transaksi', $faktur_pembelian->no_faktur)
                ->groupBy('id_produk')->select('id_produk')->selectRaw('SUM(qty) as qty')->pluck('qty', 'id_produk');
        } else {
            $qtyDiterimaByProduk = DB::table('stok_produk_perencanaan')->where('no_nota', $faktur_pembelian->no_faktur)
                ->groupBy('id_pakan')->select('id_pakan')->selectRaw($faktur_pembelian->jenis_faktur === 'pakan' ? 'SUM(pcs / 50000) as qty' : 'SUM(pcs) as qty')->pluck('qty', 'id_pakan');
        }

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
            'akunBiaya' => $akunBiaya,
            'sudahAdaPenerimaan' => $this->fakturSudahAdaPenerimaan($faktur_pembelian),
        ]);
    }

    public function destroy(FakturModel $faktur_pembelian): RedirectResponse
    {
        if ($this->fakturSudahAdaPenerimaan($faktur_pembelian)) {
            return redirect()->route('transaksi.faktur-pembelian.index')
                ->with('error', 'Faktur tidak dapat dihapus karena stoknya sudah diterima.');
        }

        DB::transaction(function () use ($faktur_pembelian) {
            $batchIds = DB::table('jurnal_perkiraan')
                ->where('nomor_transaksi', $faktur_pembelian->no_faktur)
                ->pluck('id_impor_jurnal_perkiraan')->filter()->unique();
            DB::table('jurnal_perkiraan')->where('nomor_transaksi', $faktur_pembelian->no_faktur)->delete();
            foreach ($batchIds as $batchId) {
                if (! DB::table('jurnal_perkiraan')->where('id_impor_jurnal_perkiraan', $batchId)->exists()) {
                    DB::table('impor_jurnal_perkiraan')->where('id_impor_jurnal_perkiraan', $batchId)->delete();
                }
            }
            $faktur_pembelian->detail()->delete();
            $faktur_pembelian->delete();
        });

        return redirect()->route('transaksi.faktur-pembelian.index')->with('sukses', 'Faktur pembelian berhasil dihapus.');
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
            'akunPembayaran' => $this->akunPembayaranPembelianAktif(),
            'akunPembayaranDefaultId' => $this->akunPembayaranDefaultFaktur($faktur_pembelian),
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
             'jenis_faktur' => ['required', 'in:pakan,vitamin,vaksin'],
             'no_faktur' => ['required', 'max:30', 'unique:faktur_pembelian,no_faktur,' . $faktur_pembelian->id],
             'tanggal_faktur' => ['required', 'date'],
             'supplier_id' => ['required', 'exists:tb_suplier,id_suplier'],
             'jatuh_tempo' => ['nullable', 'date'],
             'keterangan' => ['nullable', 'string'],
             'diskon_total' => ['nullable', 'numeric', 'min:0'],
             'biaya_lain' => ['nullable', 'array'],
             'biaya_lain.ongkir.nominal' => ['nullable', 'numeric', 'min:0'],
             'biaya_lain.ongkir.id_akun' => ['nullable', 'integer', 'exists:akun_perkiraan,id_akun_perkiraan'],
             'biaya_lain.admin.nominal' => ['nullable', 'numeric', 'min:0'],
             'biaya_lain.admin.id_akun' => ['nullable', 'integer', 'exists:akun_perkiraan,id_akun_perkiraan'],
             'pph23_manual' => ['nullable', 'numeric', 'min:0'],
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
         $akunHutangPakan = $this->akunAktif('210221');
         $akunHutangEkspedisi = $this->akunAktif('210222');
         $akunPph23 = $this->akunAktif('210203');
         $akunHutangLainnya = $this->akunAktif('210220');
         
         // Satu faktur hanya berisi satu sumber produk. Untuk faktur Barang Umum,
         // paksa sumbernya agar tidak terpengaruh oleh baris lama/JS browser.
         if ($validated['jenis_faktur'] === 'barang_umum') {
             $items = $items->map(function ($item) {
                 $item['sumber_produk'] = 'barang_umum';
                 return $item;
             });
         }
         // Item otomatis masuk ke Hutang Pakan (sama seperti tambah data).
         $items = $items->map(function ($item) use ($akunHutangPakan) {
             $item['id_akun_pembayaran'] = (int) $akunHutangPakan->id_akun_perkiraan;
             return $item;
         });
         
         $biayaLain = $this->normalisasiBiayaLain($validated['biaya_lain'] ?? [], $akunHutangEkspedisi, $akunHutangLainnya);
         $pph23Manual = round((float) ($validated['pph23_manual'] ?? 0), 2);
         $totalPph23 = $pph23Manual;
        $produk = $this->produkFakturOptions()
            ->whereIn('id_produk', $items->pluck('pakan_id'))
            ->keyBy('id_produk');

        $produkTidakSesuai = $items->contains(function ($item) use ($produk, $validated) {
            $kategori = $produk->get((int) $item['pakan_id'])?->kategori;

            return ! $this->produkSesuaiJenisFaktur($kategori, $validated['jenis_faktur']);
        });

        if ($produkTidakSesuai) {
            return back()
                ->withErrors(['item' => 'Produk yang dipilih tidak sesuai dengan jenis faktur.'])
                ->withInput();
        }

        $akunHutang = $this->akunAktif('210220');
        $idAkunPembayaran = $items->pluck('id_akun_pembayaran')
            ->map(fn ($id) => (int) $id)->unique()->values();
        $akunPembayaran = collect([$akunHutangPakan])
            ->whereIn('id_akun_perkiraan', $idAkunPembayaran)->keyBy('id_akun_perkiraan');
        $akunBiaya = collect([$akunHutangEkspedisi, $akunHutangLainnya])
            ->whereIn('id_akun_perkiraan', collect($biayaLain)->pluck('id_akun'))->keyBy('id_akun_perkiraan');
        $kodeAkunPersediaan = $items->map(function ($item) use ($validated, $produk) {
            return $this->kodeAkunPersediaanItem(
                $validated['jenis_faktur'],
                $produk->get((int) $item['pakan_id'])?->kategori
            );
        })->unique()->values();
        $akunPersediaan = DB::table('akun_perkiraan')->where('aktif', 1)
            ->whereIn('kode_perkiraan', $kodeAkunPersediaan)->get()->keyBy('kode_perkiraan');

         if (! $akunHutangPakan || ! $akunHutangEkspedisi
             || ($totalPph23 > 0 && ! $akunPph23)
             || $akunPembayaran->count() !== $idAkunPembayaran->count()
             || $akunBiaya->count() !== collect($biayaLain)->pluck('id_akun')->unique()->count()
             || $akunPersediaan->count() !== $kodeAkunPersediaan->count()) {
             return back()
                 ->withErrors(['akun' => 'Akun Hutang Pakan/Ekspedisi atau akun persediaan belum tersedia/aktif.'])
                 ->withInput();
         }

         DB::transaction(function () use ($validated, $items, $produk, $faktur_pembelian, $akunHutang, $akunHutangPakan, $akunHutangEkspedisi, $akunHutangLainnya, $akunPph23, $akunPembayaran, $akunBiaya, $akunPersediaan, $diskonTotal, $biayaLain, $totalPph23) {
             $noFakturLama = $faktur_pembelian->no_faktur;
             $totalQty = $items->sum(fn($item) => (float) $item['qty']);
             $totalHarga = $items->sum(fn($item) => (float) $item['subtotal']);
             $totalItem = $totalHarga;
             $totalBiayaLain = collect($biayaLain)->sum('nominal');
             $totalHarga = round($totalItem + $totalBiayaLain, 2);
             $kreditPerAkun = $items->groupBy(fn ($item) => (int) $item['id_akun_pembayaran'])
                 ->map(fn ($baris) => round($baris->sum(fn ($item) => (float) $item['subtotal']), 2));
             foreach ($biayaLain as $biaya) $kreditPerAkun[$biaya['id_akun']] = round(($kreditPerAkun[$biaya['id_akun']] ?? 0) + $biaya['nominal'], 2);
             $totalHutang = round((float) ($kreditPerAkun[$akunHutangPakan->id_akun_perkiraan] ?? 0) + collect($biayaLain)->sum('nominal') - $totalPph23, 2);
            $metodePembayaran = $totalHutang <= 0 ? 'tunai' : ($totalHutang >= $totalHarga ? 'hutang' : 'campuran');

            $faktur_pembelian->update([
                'no_faktur' => $validated['no_faktur'],
                'jenis_faktur' => $validated['jenis_faktur'],
                'metode_pembayaran' => $metodePembayaran,
                'tanggal_faktur' => $validated['tanggal_faktur'],
                'supplier_id' => $validated['supplier_id'],
                'jatuh_tempo' => $validated['jatuh_tempo'] ?? null,
                'status_bayar' => $totalHutang > 0 ? 'belum_lunas' : 'lunas',
                'total_qty' => $totalQty,
                'total_harga' => $totalHarga,
                'biaya_lain' => $biayaLain ?: null,
                'total_hutang' => $totalHutang,
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
                    'id_akun_pembayaran' => (int) $item['id_akun_pembayaran'],
                    'no_batch' => $item['no_batch'] ?? null,
                    'tanggal_expired' => $item['tanggal_expired'] ?? null,
                ]);
            }

            $this->rebuildJurnalFaktur($faktur_pembelian, $items, $produk, $akunHutang, $akunPembayaran->union($akunBiaya), $akunPersediaan, $noFakturLama, $totalBiayaLain, $biayaLain, $akunHutangPakan);
        });

        return redirect()
            ->route('transaksi.faktur-pembelian.detail', $faktur_pembelian)
            ->with('sukses', 'Faktur pembelian berhasil diperbarui.');
    }

    public function terima(FakturModel $faktur_pembelian): View
    {
        $faktur_pembelian->load(['supplier', 'detail.produk', 'detail.produkUmum']);

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
            'hargaHppByDetail' => $this->hargaHppByDetail(collect([$faktur_pembelian])),
        ]);
    }

    public function storeTerima(Request $request, FakturModel $faktur_pembelian): RedirectResponse
    {
        $faktur_pembelian->load('detail.produk', 'detail.produkUmum');

        $qtyDiterimaByProduk = DB::table('stok_produk_perencanaan')
            ->where('no_nota', $faktur_pembelian->no_faktur)
            ->groupBy('id_pakan')
            ->select('id_pakan')
            ->selectRaw($faktur_pembelian->jenis_faktur === 'pakan' ? 'SUM(pcs / 50000) as qty' : 'SUM(pcs) as qty')
            ->pluck('qty', 'id_pakan');
        $hargaHppByDetail = $this->hargaHppByDetail(collect([$faktur_pembelian]));

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

        DB::transaction(function () use ($validated, $faktur_pembelian, $qtyDiterimaByProduk, $hargaHppByDetail) {
            $admin = auth()->user()->name ?? 'system';
            $rows = [];
            $jumlahDiterima = 0;
            foreach ($faktur_pembelian->detail as $detail) {
                $qtyDiterima = (float) data_get($validated, 'detail.' . $detail->id . '.qty_diterima', 0);
                $qtySebelumnya = (float) ($qtyDiterimaByProduk[$detail->pakan_id] ?? 0);
                $qtySisa = max((float) $detail->qty - $qtySebelumnya, 0);

                if ($qtyDiterima <= 0) {
                    continue;
                }
                $jumlahDiterima++;

                abort_if(
                    $qtyDiterima > $qtySisa,
                    422,
                    'Qty diterima ' . ($detail->produk->nm_produk ?? 'produk') . ' melebihi sisa faktur.'
                );

                $qtyStok = $faktur_pembelian->jenis_faktur === 'pakan'
                    ? $qtyDiterima * 50000
                    : $qtyDiterima;
                $hargaHpp = (float) ($hargaHppByDetail[$detail->id] ?? $detail->harga_satuan);
                $biayaTambahanPerUnit = max($hargaHpp - (float) $detail->harga_satuan, 0);

                if ($faktur_pembelian->jenis_faktur === 'barang_umum') {
                    DB::table('pembukuan_baru_stok')->insert([
                        'id_produk' => $detail->pakan_id,
                        'nama_produk' => $detail->produkUmum->nm_produk ?? 'Barang Umum',
                        'satuan' => $detail->satuan,
                        'qty' => $qtyDiterima,
                        'harga_satuan' => $hargaHpp,
                        'tanggal' => $validated['tanggal_terima'],
                        'nomor_transaksi' => $faktur_pembelian->no_faktur,
                        'created_at' => now(), 'updated_at' => now(),
                    ]);
                    continue;
                }

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
                    'total_rp' => round($qtyDiterima * $hargaHpp, 2),
                    'biaya_dll' => round($qtyDiterima * $biayaTambahanPerUnit, 2),
                    'no_nota' => $faktur_pembelian->no_faktur,
                    'h_opname' => 'T',
                    'penyesuaian' => 'T',
                ];
            }

            abort_if($jumlahDiterima === 0, 422, 'Qty diterima harus diisi minimal 1 item.');

            if ($rows) DB::table('stok_produk_perencanaan')->insert($rows);
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

        $fakturs = FakturModel::with(['supplier', 'detail.produk', 'detail.produkUmum'])
            ->whereIn('id', $ids)
            ->orderBy('tanggal_faktur')
            ->orderBy('no_faktur')
            ->get();

        $qtyDiterimaByNota = $this->qtyDiterimaByNota($fakturs);

        return view('transaksi.penerimaan.terima_batch', [
            'title' => 'Penerimaan Stok Beberapa Nota',
            'fakturs' => $fakturs,
            'qtyDiterimaByNota' => $qtyDiterimaByNota,
            'hargaHppByDetail' => $this->hargaHppByDetail($fakturs),
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

        $fakturs = FakturModel::with(['detail.produk', 'detail.produkUmum'])
            ->whereIn('id', $validated['faktur'])
            ->get();

        $qtyDiterimaByNota = $this->qtyDiterimaByNota($fakturs);
        $hargaHppByDetail = $this->hargaHppByDetail($fakturs);

        DB::transaction(function () use ($validated, $fakturs, $qtyDiterimaByNota, $hargaHppByDetail) {
            $admin = auth()->user()->name ?? 'system';
            $rows = [];
            $jumlahDiterima = 0;

            foreach ($fakturs as $faktur) {
                foreach ($faktur->detail as $detail) {
                    $qtyDiterima = (float) data_get($validated, 'detail.' . $detail->id . '.qty_diterima', 0);
                    $qtySebelumnya = (float) data_get($qtyDiterimaByNota, $faktur->no_faktur . '.' . $detail->pakan_id, 0);
                    $qtySisa = max((float) $detail->qty - $qtySebelumnya, 0);

                    if ($qtyDiterima <= 0) {
                        continue;
                    }
                    $jumlahDiterima++;

                    abort_if(
                        $qtyDiterima > $qtySisa,
                        422,
                        'Qty diterima ' . $faktur->no_faktur . ' - ' . (($detail->sumber_produk ?? 'perencanaan') === 'barang_umum' ? ($detail->produkUmum->nm_produk ?? 'produk') : ($detail->produk->nm_produk ?? 'produk')) . ' melebihi sisa faktur.'
                    );

                    $qtyStok = $faktur->jenis_faktur === 'pakan'
                        ? $qtyDiterima * 50000
                        : $qtyDiterima;
                    $hargaHpp = (float) ($hargaHppByDetail[$detail->id] ?? $detail->harga_satuan);
                    $biayaTambahanPerUnit = max($hargaHpp - (float) $detail->harga_satuan, 0);

                    if ($faktur->jenis_faktur === 'barang_umum') {
                        DB::table('pembukuan_baru_stok')->insert([
                            'id_produk' => $detail->pakan_id,
                            'nama_produk' => $detail->produkUmum->nm_produk ?? 'Barang Umum',
                            'satuan' => $detail->satuan,
                            'qty' => $qtyDiterima,
                            'harga_satuan' => $hargaHpp,
                            'tanggal' => $validated['tanggal_terima'],
                            'nomor_transaksi' => $faktur->no_faktur,
                            'created_at' => now(), 'updated_at' => now(),
                        ]);
                        continue;
                    }

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
                        'total_rp' => round($qtyDiterima * $hargaHpp, 2),
                        'biaya_dll' => round($qtyDiterima * $biayaTambahanPerUnit, 2),
                        'no_nota' => $faktur->no_faktur,
                        'h_opname' => 'T',
                        'penyesuaian' => 'T',
                    ];
                }
            }

            abort_if($jumlahDiterima === 0, 422, 'Qty diterima harus diisi minimal 1 item.');

            if ($rows) DB::table('stok_produk_perencanaan')->insert($rows);
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

        $hasil = DB::table('faktur_pembelian as f')
            ->leftJoin('stok_produk_perencanaan as s', 's.no_nota', '=', 'f.no_faktur')
            ->whereIn('f.no_faktur', $fakturs->pluck('no_faktur'))
            ->groupBy('f.no_faktur', 'f.jenis_faktur')
            ->select('f.no_faktur')
            ->selectRaw("COALESCE(SUM(CASE WHEN f.jenis_faktur = 'pakan' THEN s.pcs / 50000 ELSE s.pcs END), 0) as qty_diterima")
            ->pluck('qty_diterima', 'no_faktur');
        $umum = DB::table('pembukuan_baru_stok')->whereIn('nomor_transaksi', $fakturs->pluck('no_faktur'))
            ->groupBy('nomor_transaksi')->select('nomor_transaksi')->selectRaw('SUM(qty) as qty_diterima')->pluck('qty_diterima', 'nomor_transaksi');
        foreach ($umum as $nota => $qty) $hasil[$nota] = $qty;
        return $hasil;
    }

    private function qtyDiterimaByNota($fakturs): array
    {
        $fakturs = collect($fakturs);

        if ($fakturs->isEmpty()) {
            return [];
        }

        $hasil = DB::table('stok_produk_perencanaan as s')
            ->join('faktur_pembelian as f', 'f.no_faktur', '=', 's.no_nota')
            ->whereIn('f.no_faktur', $fakturs->pluck('no_faktur'))
            ->groupBy('f.no_faktur', 'f.jenis_faktur', 's.id_pakan')
            ->select('f.no_faktur', 's.id_pakan')
            ->selectRaw("SUM(CASE WHEN f.jenis_faktur = 'pakan' THEN s.pcs / 50000 ELSE s.pcs END) as qty")
            ->get()
            ->groupBy('no_faktur')
            ->map(fn($rows) => $rows->pluck('qty', 'id_pakan')->all())
            ->all();
        $umum = DB::table('pembukuan_baru_stok as s')->join('faktur_pembelian as f', 'f.no_faktur', '=', 's.nomor_transaksi')
            ->where('f.jenis_faktur', 'barang_umum')->whereIn('f.no_faktur', $fakturs->pluck('no_faktur'))
            ->groupBy('f.no_faktur', 's.id_produk')->select('f.no_faktur', 's.id_produk')->selectRaw('SUM(s.qty) as qty')->get()
            ->groupBy('no_faktur')->map(fn ($rows) => $rows->pluck('qty', 'id_produk')->all())->all();
        return array_replace($hasil, $umum);
    }

    private function fakturSudahAdaPenerimaan(FakturModel $faktur): bool
    {
        return $faktur->jenis_faktur === 'barang_umum'
            ? DB::table('pembukuan_baru_stok')->where('nomor_transaksi', $faktur->no_faktur)->exists()
            : DB::table('stok_produk_perencanaan')->where('no_nota', $faktur->no_faktur)->exists();
    }

    private function hargaHppByDetail($fakturs)
    {
        $hasil = collect();

        foreach (collect($fakturs) as $faktur) {
            $details = collect($faktur->detail)->values();
            $totalItem = (float) $details->sum(fn ($detail) => (float) $detail->subtotal);
            $totalBiayaTambahan = (float) collect($faktur->biaya_lain ?? [])->sum('nominal');
            $sisaBiaya = round($totalBiayaTambahan, 2);

            foreach ($details as $index => $detail) {
                $subtotal = (float) $detail->subtotal;
                $qty = (float) $detail->qty;
                $alokasi = $index === $details->count() - 1
                    ? $sisaBiaya
                    : round($totalItem > 0
                        ? $totalBiayaTambahan * $subtotal / $totalItem
                        : $totalBiayaTambahan / max($details->count(), 1), 2);
                $sisaBiaya = round($sisaBiaya - $alokasi, 2);

                $hasil->put($detail->id, $qty > 0
                    ? round(($subtotal + $alokasi) / $qty, 6)
                    : (float) $detail->harga_satuan);
            }
        }

        return $hasil;
    }

    private function isiRincianHutang($faktur): void
    {
        $biaya = collect($faktur->biaya_lain ?? []);
        $ongkirData = $biaya->firstWhere('kode', 'ongkir');
        $ongkir = max(
            (float) data_get($ongkirData, 'nominal', 0) - (float) data_get($ongkirData, 'pph23_nominal', 0),
            0
        );
        $admin = (float) data_get($biaya->firstWhere('kode', 'admin'), 'nominal', 0);
        $totalHutang = (float) $faktur->total_hutang;

        $faktur->hutang_barang = max(round($totalHutang - $ongkir - $admin, 2), 0);
        $sisa = max($totalHutang - $faktur->hutang_barang, 0);
        $faktur->hutang_ongkir = min($ongkir, $sisa);
        $faktur->hutang_admin = min($admin, max($sisa - $faktur->hutang_ongkir, 0));

        if ($faktur instanceof \Illuminate\Database\Eloquent\Model) {
            $faktur->syncOriginalAttributes(['hutang_barang', 'hutang_ongkir', 'hutang_admin']);
        }
    }

    private function tagihanKomponen($fakturs)
    {
        $fakturs = collect($fakturs);
        $pelunasan = DB::table('pelunasan_faktur_pembelian')
            ->whereIn('faktur_pembelian_id', $fakturs->pluck('id'))
            ->select('faktur_pembelian_id', 'komponen_hutang')
            ->selectRaw('SUM(jumlah_bayar) as total_bayar')
            ->groupBy('faktur_pembelian_id', 'komponen_hutang')
            ->get()
            ->groupBy('faktur_pembelian_id');

        return $fakturs->flatMap(function ($faktur) use ($pelunasan) {
            $this->isiRincianHutang($faktur);
            $bayar = collect($pelunasan->get($faktur->id, []));
            $sisaBayarLama = (float) $bayar->whereNull('komponen_hutang')->sum('total_bayar');
            $komponen = [
                'barang' => ['label' => 'Barang / ' . ucfirst(str_replace('_', ' ', $faktur->jenis_faktur)), 'kode' => 'BRG', 'nominal' => (float) $faktur->hutang_barang],
                'ongkir' => ['label' => 'Ongkir', 'kode' => 'ONGKIR', 'nominal' => (float) $faktur->hutang_ongkir],
                'admin' => ['label' => 'Admin', 'kode' => 'ADMIN', 'nominal' => (float) $faktur->hutang_admin],
            ];

            return collect($komponen)->map(function ($detail, $kode) use ($faktur, $bayar, &$sisaBayarLama) {
                if ($detail['nominal'] <= 0) {
                    return null;
                }
                $bayarKomponen = (float) $bayar->where('komponen_hutang', $kode)->sum('total_bayar');
                $alokasiLama = min(max($detail['nominal'] - $bayarKomponen, 0), $sisaBayarLama);
                $sisaBayarLama -= $alokasiLama;
                $totalBayar = min($detail['nominal'], $bayarKomponen + $alokasiLama);

                return (object) [
                    'id' => $faktur->id,
                    'tanggal_faktur' => $faktur->tanggal_faktur,
                    'no_faktur' => $faktur->no_faktur,
                    'nomor_tagihan' => $faktur->no_faktur . '-' . $detail['kode'],
                    'jenis_faktur' => $faktur->jenis_faktur,
                    'supplier' => $faktur->supplier,
                    'komponen_hutang' => $kode,
                    'nama_komponen' => $detail['label'],
                    'nominal_hutang' => $detail['nominal'],
                    'total_bayar' => $totalBayar,
                    'sisa_hutang' => max($detail['nominal'] - $totalBayar, 0),
                ];
            })->filter()->values();
        })->values();
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

    private function produkSesuaiJenisFaktur(?string $kategori, string $jenisFaktur): bool
    {
        return match ($jenisFaktur) {
            'pakan' => $kategori === 'pakan',
            'vaksin' => $kategori === 'vaksin',
            'vitamin' => in_array($kategori, ['obat_pakan', 'obat_air', 'obat_ayam', 'vaksin'], true),
            'barang_umum' => $kategori === 'barang_umum',
            default => false,
        };
    }

    private function kodeAkunPersediaanFaktur(string $jenisFaktur): string
    {
        return match ($jenisFaktur) {
            'pakan' => '110403',
            'barang_umum' => '110406',
            'vaksin' => '110521',
            default => '110404',
        };
    }

    private function kodeAkunPersediaanItem(string $jenisFaktur, ?string $kategori): string
    {
        // Vitamin dan vaksin digabung dalam satu faktur, tetapi jurnal debit
        // tetap masuk ke akun persediaan masing-masing sesuai kategori produk.
        if ($jenisFaktur === 'vitamin' && $kategori === 'vaksin') {
            return '110521';
        }

        return $this->kodeAkunPersediaanFaktur($jenisFaktur);
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

     private function normalisasiBiayaLain(array $biaya, ?object $akunEkspedisi = null, ?object $akunLainnya = null): array
     {
         $hasil = [];
         foreach (['ongkir' => 'Ongkir', 'admin' => 'Admin'] as $kode => $label) {
             $nominal = round((float) data_get($biaya, $kode . '.nominal', 0), 2);
             
             // Tentukan akun default berdasarkan jenis biaya
             $idAkunDefault = null;
             if ($kode === 'ongkir' && $akunEkspedisi) {
                 $idAkunDefault = (int) $akunEkspedisi->id_akun_perkiraan;
             } elseif ($kode === 'admin' && $akunLainnya) {
                 $idAkunDefault = (int) $akunLainnya->id_akun_perkiraan;
             }
             
             $idAkun = $idAkunDefault;
             if ($nominal > 0) {
                 if (! $idAkun) {
                     throw \Illuminate\Validation\ValidationException::withMessages([
                         'biaya_lain.' . $kode . '.id_akun' => 'Pilih akun untuk biaya ' . $label . '.',
                     ]);
                 }
                 $hasil[] = [
                     'kode' => $kode,
                     'nama' => $label,
                     'nominal' => $nominal,
                     'id_akun' => (int) $idAkun,
                 ];
             }
         }
         return $hasil;
     }

    private function totalPelunasanFaktur(FakturModel $faktur): float
    {
        return (float) DB::table('pelunasan_faktur_pembelian')
            ->where('faktur_pembelian_id', $faktur->id)
            ->sum('jumlah_bayar');
    }

    private function riwayatPelunasanFaktur(FakturModel $faktur, ?string $komponen = null)
    {
        return DB::table('pelunasan_faktur_pembelian as p')
            ->leftJoin('akun_perkiraan as a', 'a.id_akun_perkiraan', '=', 'p.id_akun_kas')
            ->where('p.faktur_pembelian_id', $faktur->id)
            ->when($komponen, function ($query) use ($komponen) {
                $query->where(function ($query) use ($komponen) {
                    $query->where('p.komponen_hutang', $komponen);
                    if ($komponen === 'barang') {
                        $query->orWhereNull('p.komponen_hutang');
                    }
                });
            })
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
            // Hanya akun kas/bank detail (1101xx). Jangan ikutkan akun
            // biaya administrasi bank atau pendapatan bunga yang namanya
            // kebetulan mengandung kata "bank".
            ->where('kode_perkiraan', 'like', '1101%')
            ->whereNotNull('id_akun_induk')
            ->orderBy('kode_perkiraan')
            ->get(['id_akun_perkiraan', 'kode_perkiraan', 'nama']);
    }

    private function akunPembayaranPembelianAktif()
    {
        $akunHutang = $this->akunAktif('210220');

        return collect($akunHutang ? [$akunHutang] : [])
            ->concat($this->akunKasBankAktif())
            ->unique('id_akun_perkiraan')
            ->values();
    }

    private function akunPembayaranDefaultFaktur(FakturModel $faktur): ?int
    {
        $akunHutang = $this->akunAktif('210220');

        if (($faktur->metode_pembayaran ?? 'hutang') === 'hutang') {
            return $akunHutang ? (int) $akunHutang->id_akun_perkiraan : null;
        }

        $idAkun = DB::table('jurnal_perkiraan')
            ->where('nomor_transaksi', $faktur->no_faktur)
            ->where('kredit', '>', 0)
            ->where(function ($query) {
                $query->where('tipe_transaksi', 'like', 'Faktur Pembelian%')
                    ->orWhere('tipe_transaksi', 'Pembelian Umum');
            })
            ->value('id_akun_perkiraan');

        return $idAkun ? (int) $idAkun : null;
    }

    private function rebuildJurnalFaktur(
        FakturModel $faktur,
        $items,
        $produk,
        ?object $akunHutang,
        $akunPembayaran,
        $akunPersediaan,
        string $noFakturLama,
        float $totalBiayaLain = 0,
        array $biayaLain = [],
        ?object $akunHutangPakan = null
    ): void {
        $sekarang = now();
        $totalItem = $items->sum(fn($item) => (float) $item['subtotal']);
        $totalHarga = round($totalItem + $totalBiayaLain, 2);
        $kreditPerAkun = $items->groupBy(fn ($item) => (int) $item['id_akun_pembayaran'])
            ->map(fn ($baris) => round($baris->sum(fn ($item) => (float) $item['subtotal']), 2));
        $totalPph23 = round((float) collect($biayaLain)->sum('pph23_nominal'), 2);
        $akunPph23 = $totalPph23 > 0 ? $this->akunAktif('210203') : null;

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
                    'jumlah_detail' => $items->count() + $kreditPerAkun->count() + count($biayaLain) + ($totalPph23 > 0 ? 2 : 0),
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
                'jumlah_detail' => $items->count() + $kreditPerAkun->count() + count($biayaLain) + ($totalPph23 > 0 ? 2 : 0),
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
            $itemProduk = $produk->get((int) $item['pakan_id']);
            $namaProduk = $itemProduk?->nm_produk ?? 'Produk';
            $subtotal = (float) $item['subtotal'];
            $akunDebit = $akunPersediaan->get(
                $this->kodeAkunPersediaanItem($faktur->jenis_faktur, $itemProduk?->kategori)
            );

            $biayaAlokasi = $totalItem > 0 ? round($totalBiayaLain * $subtotal / $totalItem, 2) : 0;
            if ($item === $items->last()) $biayaAlokasi = round($totalBiayaLain - collect($detailJurnal)->sum(fn ($j) => (float) ($j['_biaya_alokasi'] ?? 0)), 2);
            $detailJurnal[] = [
                'id_impor_jurnal_perkiraan' => $batchId,
                'id_akun_perkiraan' => $akunDebit->id_akun_perkiraan,
                'tanggal' => $faktur->tanggal_faktur,
                'nomor_transaksi' => $faktur->no_faktur,
                'tipe_transaksi' => $faktur->jenis_faktur === 'vitamin' ? 'Faktur Pembelian Vitamin & Vaksin' : 'Faktur Pembelian ' . ucfirst($faktur->jenis_faktur),
                'urutan_detail' => $urutanDetail++,
                'deskripsi' => 'Pembelian ' . $namaProduk,
                'debit' => round($subtotal + $biayaAlokasi, 2),
                'kredit' => 0,
                'created_at' => $sekarang,
                'updated_at' => $sekarang,
                '_biaya_alokasi' => $biayaAlokasi,
            ];
        }
        $detailJurnal = array_map(function ($row) { unset($row['_biaya_alokasi']); return $row; }, $detailJurnal);

         foreach ($kreditPerAkun as $idAkun => $nominal) {
             $akunKredit = $akunPembayaran->get((int) $idAkun);
             $isHutang = (int) $idAkun === (int) $akunHutangPakan->id_akun_perkiraan;

            $detailJurnal[] = [
                'id_impor_jurnal_perkiraan' => $batchId,
                'id_akun_perkiraan' => $akunKredit->id_akun_perkiraan,
                'tanggal' => $faktur->tanggal_faktur,
                'nomor_transaksi' => $faktur->no_faktur,
                'tipe_transaksi' => $faktur->jenis_faktur === 'vitamin' ? 'Faktur Pembelian Vitamin & Vaksin' : 'Faktur Pembelian ' . ucfirst($faktur->jenis_faktur),
                'urutan_detail' => $urutanDetail++,
                'deskripsi' => ($isHutang ? 'Hutang pembelian ' : 'Pembayaran pembelian ') . $faktur->jenis_faktur . ' via ' . $akunKredit->nama,
                'debit' => 0,
                'kredit' => $nominal,
                'created_at' => $sekarang,
                'updated_at' => $sekarang,
            ];
        }

         foreach ($biayaLain as $biaya) {
             $akunKredit = $akunPembayaran->get((int) $biaya['id_akun']);
             $nominalHutangBiaya = round((float) $biaya['nominal'], 2);
             
             // Jika ada PPH dan ini biaya ekspedisi, kurangi dari hutang ekspedisi
             $pphUntukBiayaIni = 0;
             if ($totalPph23 > 0 && $biaya['kode'] === 'ongkir') {
                 $pphUntukBiayaIni = $totalPph23;
                 $nominalHutangBiaya = round($nominalHutangBiaya - $pphUntukBiayaIni, 2);
             }
             
             $detailJurnal[] = [
                 'id_impor_jurnal_perkiraan' => $batchId,
                 'id_akun_perkiraan' => $akunKredit->id_akun_perkiraan,
                 'tanggal' => $faktur->tanggal_faktur,
                 'nomor_transaksi' => $faktur->no_faktur,
                 'tipe_transaksi' => $faktur->jenis_faktur === 'vitamin' ? 'Faktur Pembelian Vitamin & Vaksin' : 'Faktur Pembelian ' . ucfirst($faktur->jenis_faktur),
                 'urutan_detail' => $urutanDetail++,
                 'deskripsi' => 'Hutang biaya ' . strtolower($biaya['nama']) . ' pembelian ' . $faktur->jenis_faktur,
                 'debit' => 0,
                 'kredit' => $nominalHutangBiaya,
                 'created_at' => $sekarang,
                 'updated_at' => $sekarang,
             ];
             
             // Jika ada PPH, kredit ke akun pajak (hutang ekspedisi sudah dikurangi PPh di atas)
             if ($pphUntukBiayaIni > 0) {
                 $detailJurnal[] = [
                     'id_impor_jurnal_perkiraan' => $batchId,
                     'id_akun_perkiraan' => $akunPph23->id_akun_perkiraan,
                     'tanggal' => $faktur->tanggal_faktur,
                     'nomor_transaksi' => $faktur->no_faktur,
                     'tipe_transaksi' => $faktur->jenis_faktur === 'vitamin' ? 'Faktur Pembelian Vitamin & Vaksin' : 'Faktur Pembelian ' . ucfirst($faktur->jenis_faktur),
                     'urutan_detail' => $urutanDetail++,
                     'deskripsi' => 'Hutang pajak PPh 23 dari ' . strtolower($biaya['nama']) . ' pembelian ' . $faktur->jenis_faktur,
                     'debit' => 0,
                     'kredit' => $pphUntukBiayaIni,
                     'created_at' => $sekarang,
                     'updated_at' => $sekarang,
                 ];
             }
         }

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
