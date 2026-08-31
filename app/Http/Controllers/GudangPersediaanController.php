<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class GudangPersediaanController extends Controller
{
    public function index(Request $request)
    {
        // Di halaman ringkasan hanya tampilkan produk yang memiliki saldo
        // (atau minus). Produk dengan saldo tepat 0 disembunyikan agar daftar
        // gudang tidak penuh; halaman Telur memiliki aturan tampilannya sendiri.
        $cari = trim((string) $request->input('cari'));
        $stokSemua = $this->stockRows()
            ->filter(fn ($row) => (float) $row->stok != 0)
            ->when($cari !== '', function (Collection $rows) use ($cari) {
                $needle = mb_strtolower($cari);

                return $rows->filter(function ($row) use ($needle) {
                    return str_contains(mb_strtolower((string) $row->nm_produk), $needle)
                        || str_contains(mb_strtolower((string) $row->kode_accurate), $needle)
                        || str_contains(mb_strtolower((string) $row->kategori), $needle)
                        || str_contains(mb_strtolower((string) $row->nm_satuan), $needle);
                });
            })
            ->values();

        $perPage = 15;
        $page = LengthAwarePaginator::resolveCurrentPage();
        $stok = new LengthAwarePaginator(
            $stokSemua->forPage($page, $perPage)->values(),
            $stokSemua->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('gudang_persediaan.index', [
            'title' => 'Gudang',
            'stok' => $stok,
            'cari' => $cari,
            'jumlahProduk' => $stokSemua->count(),
            'produkAdaStok' => $stokSemua->where('stok', '>', 0)->count(),
            'produkKosong' => $stokSemua->where('stok', '<=', 0)->count(),
            'nilaiPersediaan' => $stokSemua->sum(fn ($row) => max(0, (float) $row->nilai_stok)),
            'opnameTerakhir' => DB::table('gudang_opname_perencanaan')->max('tanggal'),
        ]);
    }

    public function barangUmum(Request $request)
    {
        $saldo = DB::table('pembukuan_baru_stok')
            ->select('id_produk')
            ->selectRaw('SUM(qty) as stok')
            ->selectRaw('SUM(qty * harga_satuan) as nilai_stok')
            ->groupBy('id_produk');

        $base = DB::table('tb_produk as p')
            ->leftJoin('tb_satuan as s', 's.id_satuan', '=', 'p.satuan_id')
            ->leftJoin('tb_gudang as g', 'g.id_gudang', '=', 'p.gudang_id')
            ->leftJoinSub($saldo, 'st', 'st.id_produk', '=', 'p.id_produk')
            ->where('p.kategori_id', 1)
            ->whereRaw('COALESCE(st.stok, 0) <> 0')
            ->when($request->filled('gudang'), fn ($q) => $q->where('p.gudang_id', $request->integer('gudang')))
            ->when($request->filled('cari'), function ($q) use ($request) {
                $cari = '%' . trim((string) $request->input('cari')) . '%';
                $q->where(function ($sub) use ($cari) {
                    $sub->where('p.nm_produk', 'like', $cari)
                        ->orWhere('p.kd_produk', 'like', $cari)
                        ->orWhere('s.nm_satuan', 'like', $cari)
                        ->orWhere('g.nm_gudang', 'like', $cari);
                });
            });

        $barang = (clone $base)
            ->orderBy('g.nm_gudang')->orderBy('p.nm_produk')
            ->select([
                'p.id_produk', 'p.kd_produk', 'p.nm_produk', 'p.kontrol_stok',
                'p.gudang_id', 'g.nm_gudang', 's.nm_satuan',
                DB::raw('COALESCE(st.stok, 0) as stok'),
                DB::raw('COALESCE(st.nilai_stok, 0) as nilai_stok'),
            ])->paginate(15)->withQueryString();

        $ringkasan = DB::table('tb_produk as p')
            ->leftJoinSub($saldo, 'st', 'st.id_produk', '=', 'p.id_produk')
            ->where('p.kategori_id', 1)
            ->whereRaw('COALESCE(st.stok, 0) <> 0')
            ->selectRaw('COUNT(p.id_produk) as total_produk')
            ->selectRaw('SUM(CASE WHEN COALESCE(st.stok, 0) > 0 THEN 1 ELSE 0 END) as tersedia')
            ->selectRaw('SUM(CASE WHEN COALESCE(st.stok, 0) <= 0 THEN 1 ELSE 0 END) as kosong')
            ->selectRaw('SUM(COALESCE(st.nilai_stok, 0)) as nilai_persediaan')
            ->first();

        return view('gudang_persediaan.barang_umum', [
            'title' => 'Stok Barang Umum',
            'barang' => $barang,
            'ringkasan' => $ringkasan,
            'gudang' => DB::table('tb_gudang')->whereIn('id_gudang', function ($q) {
                $q->select('gudang_id')->from('tb_produk')->where('kategori_id', 1)->whereNotNull('gudang_id');
            })->orderBy('nm_gudang')->get(),
        ]);
    }

    public function opnameBarangUmum(Request $request)
    {
        $saldo = DB::table('pembukuan_baru_stok')
            ->select('id_produk')
            ->selectRaw('SUM(qty) as qty_masuk')
            ->selectRaw('SUM(qty * harga_satuan) as nilai_masuk')
            ->groupBy('id_produk');

        $tampilkanKosong = $request->boolean('tampilkan_kosong');
        $items = DB::table('tb_produk as p')
            ->leftJoin('tb_satuan as s', 's.id_satuan', '=', 'p.satuan_id')
            ->leftJoinSub($saldo, 'st', 'st.id_produk', '=', 'p.id_produk')
            ->where('p.kategori_id', 1)
            ->where('p.kontrol_stok', 'Y')
            ->when(!$tampilkanKosong, fn ($q) => $q->whereRaw('COALESCE(st.qty_masuk, 0) <> 0'))
            ->orderBy('p.nm_produk')
            ->get([
                'p.id_produk',
                'p.nm_produk as nama_produk',
                's.nm_satuan as satuan',
                DB::raw('COALESCE(st.qty_masuk, 0) as qty_masuk'),
                DB::raw('COALESCE(st.nilai_masuk, 0) as nilai_masuk'),
            ]);

        return view('pembukuan_baru.jurnal_penyesuaian.stok_opname', [
            'title' => 'Stok Opname Barang Umum',
            'items' => $items,
            'tampilkanKosong' => $tampilkanKosong,
        ]);
    }

    public function telur()
    {
        $stokTelurPerGudang = $this->eggStockRows()->groupBy('id_gudang_telur');

        return view('gudang_persediaan.telur', [
            'title' => 'Stok Telur per Gudang',
            'stokTelurPerGudang' => $stokTelurPerGudang,
            'jumlahGudangTelur' => $stokTelurPerGudang->count(),
            'totalStokTelurPcs' => $stokTelurPerGudang->flatten(1)->sum(fn ($row) => (float) $row->stok_pcs),
            'totalStokTelurKg' => $stokTelurPerGudang->flatten(1)->sum(fn ($row) => (float) $row->stok_kg),
        ]);
    }

    public function opnameTelur(Request $request, int $idGudang)
    {
        abort_unless(DB::table('gudang_telur')->where('id_gudang_telur', $idGudang)->exists(), 404);

        try {
            $tanggal = Carbon::parse($request->input('tanggal', date('Y-m-d')))->format('Y-m-d');
        } catch (\Throwable) {
            $tanggal = date('Y-m-d');
        }

        return view('gudang_persediaan.telur_opname', [
            'title' => 'Stok Opname Telur',
            'tanggal' => $tanggal,
            'gudang' => DB::table('gudang_telur')->where('id_gudang_telur', $idGudang)->first(),
            'stok' => $this->eggStockRows($tanggal, $idGudang),
        ]);
    }

    public function storeOpnameTelur(Request $request, int $idGudang)
    {
        abort_unless(DB::table('gudang_telur')->where('id_gudang_telur', $idGudang)->exists(), 404);

        $validated = $request->validate([
            'tanggal' => ['required', 'date', 'before_or_equal:today'],
            'produk' => ['required', 'array', 'min:1'],
            'produk.*' => ['required', 'integer', 'distinct', 'exists:telur_produk,id_produk_telur'],
            'stok_fisik_pcs' => ['required', 'array'],
            'stok_fisik_pcs.*' => ['required', 'numeric', 'min:0'],
            'stok_fisik_kg' => ['required', 'array'],
            'stok_fisik_kg.*' => ['required', 'numeric', 'min:0'],
        ]);

        foreach ($validated['produk'] as $idTelur) {
            if (! array_key_exists($idTelur, $validated['stok_fisik_pcs'])
                || ! array_key_exists($idTelur, $validated['stok_fisik_kg'])) {
                return back()->withErrors(['produk' => 'Stok fisik PCS dan KG wajib diisi untuk semua jenis telur.'])->withInput();
            }
        }

        $opnameLebihBaru = DB::table('gudang_opname_telur')
            ->where('id_gudang', $idGudang)
            ->whereDate('tanggal', '>', $validated['tanggal'])
            ->exists();
        if ($opnameLebihBaru) {
            return back()->withErrors(['tanggal' => 'Tanggal tidak boleh lebih lama dari opname telur terakhir di gudang ini.'])->withInput();
        }

        $opnameId = DB::transaction(function () use ($validated, $idGudang) {
            $now = now();
            $opnameId = DB::table('gudang_opname_telur')->insertGetId([
                'nomor_opname' => 'TMP-' . str()->uuid(),
                'id_gudang' => $idGudang,
                'tanggal' => $validated['tanggal'],
                'admin' => auth()->user()->name,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $nomorOpname = 'OPT-' . Carbon::parse($validated['tanggal'])->format('Ymd') . '-' . str_pad((string) $opnameId, 6, '0', STR_PAD_LEFT);
            DB::table('gudang_opname_telur')->where('id', $opnameId)->update(['nomor_opname' => $nomorOpname]);

            foreach ($validated['produk'] as $idTelur) {
                $mutasi = DB::table('stok_telur')
                    ->where('id_gudang', $idGudang)
                    ->where('id_telur', $idTelur)
                    ->where('opname', 'T')
                    ->whereDate('tgl', '<=', $validated['tanggal'])
                    ->lockForUpdate()
                    ->get(['pcs', 'pcs_kredit', 'kg', 'kg_kredit']);

                $stokSistemPcs = round($mutasi->sum(fn ($row) => (float) $row->pcs - (float) $row->pcs_kredit), 4);
                $stokSistemKg = round($mutasi->sum(fn ($row) => (float) $row->kg - (float) $row->kg_kredit), 4);
                $stokFisikPcs = round((float) $validated['stok_fisik_pcs'][$idTelur], 4);
                $stokFisikKg = round((float) $validated['stok_fisik_kg'][$idTelur], 4);

                DB::table('stok_telur')
                    ->where('id_gudang', $idGudang)
                    ->where('id_telur', $idTelur)
                    ->where('opname', 'T')
                    ->whereDate('tgl', '<=', $validated['tanggal'])
                    ->update(['opname' => 'Y']);

                DB::table('stok_telur')->insert([
                    'id_kandang' => 0,
                    'id_telur' => $idTelur,
                    'tgl' => $validated['tanggal'],
                    'pcs' => $stokFisikPcs,
                    'kg' => $stokFisikKg,
                    'pcs_kredit' => 0,
                    'kg_kredit' => 0,
                    'admin' => auth()->user()->name,
                    'id_gudang' => $idGudang,
                    'nota_transfer' => $nomorOpname,
                    'ket' => 'Stok opname telur',
                    'check' => 'Y',
                    'jenis' => 'Opname',
                    'opname' => 'T',
                    'cek_admin' => auth()->user()->name,
                    'pcs_selisih' => round($stokFisikPcs - $stokSistemPcs, 4),
                    'kg_selisih' => round($stokFisikKg - $stokSistemKg, 4),
                ]);

                DB::table('gudang_opname_telur_detail')->insert([
                    'opname_id' => $opnameId,
                    'id_telur' => $idTelur,
                    'stok_sistem_pcs' => $stokSistemPcs,
                    'stok_sistem_kg' => $stokSistemKg,
                    'stok_fisik_pcs' => $stokFisikPcs,
                    'stok_fisik_kg' => $stokFisikKg,
                    'selisih_pcs' => round($stokFisikPcs - $stokSistemPcs, 4),
                    'selisih_kg' => round($stokFisikKg - $stokSistemKg, 4),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            return $opnameId;
        });

        return redirect()->route('gudang-persediaan.telur', ['opname' => $opnameId])
            ->with('sukses', 'Stok opname telur berhasil disimpan. Saldo gudang sekarang mengikuti stok fisik.');
    }

    public function riwayatOpnameTelur(Request $request)
    {
        $query = DB::table('gudang_opname_telur as o')
            ->join('gudang_telur as g', 'g.id_gudang_telur', '=', 'o.id_gudang')
            ->leftJoin('gudang_opname_telur_detail as d', 'd.opname_id', '=', 'o.id')
            ->when($request->filled('id_gudang'), fn ($q) => $q->where('o.id_gudang', $request->integer('id_gudang')))
            ->when($request->filled('tgl1'), fn ($q) => $q->whereDate('o.tanggal', '>=', $request->input('tgl1')))
            ->when($request->filled('tgl2'), fn ($q) => $q->whereDate('o.tanggal', '<=', $request->input('tgl2')))
            ->when($request->filled('cari'), function ($q) use ($request) {
                $cari = '%' . trim((string) $request->input('cari')) . '%';
                $q->where(function ($sub) use ($cari) {
                    $sub->where('o.nomor_opname', 'like', $cari)
                        ->orWhere('o.admin', 'like', $cari)
                        ->orWhere('g.nm_gudang', 'like', $cari);
                });
            })
            ->select(
                'o.id', 'o.nomor_opname', 'o.tanggal', 'o.admin', 'o.created_at',
                'g.nm_gudang',
                DB::raw('COUNT(d.id) as jumlah_produk'),
                DB::raw('SUM(CASE WHEN COALESCE(d.selisih_pcs, 0) != 0 OR COALESCE(d.selisih_kg, 0) != 0 THEN 1 ELSE 0 END) as jumlah_selisih'),
                DB::raw('SUM(COALESCE(d.selisih_pcs, 0)) as total_selisih_pcs'),
                DB::raw('SUM(COALESCE(d.selisih_kg, 0)) as total_selisih_kg')
            )
            ->groupBy('o.id', 'o.nomor_opname', 'o.tanggal', 'o.admin', 'o.created_at', 'g.nm_gudang')
            ->orderByDesc('o.tanggal')->orderByDesc('o.id');

        $riwayat = $query->paginate(10)->withQueryString();
        $detail = DB::table('gudang_opname_telur_detail as d')
            ->join('telur_produk as p', 'p.id_produk_telur', '=', 'd.id_telur')
            ->whereIn('d.opname_id', $riwayat->pluck('id'))
            ->orderBy('p.id_produk_telur')
            ->get(['d.*', 'p.nm_telur', 'p.kode_produk'])
            ->groupBy('opname_id');

        return view('gudang_persediaan.telur_riwayat', [
            'title' => 'Riwayat Stok Opname Telur',
            'riwayat' => $riwayat,
            'detail' => $detail,
            'gudang' => DB::table('gudang_telur')->orderBy('nm_gudang')->get(),
        ]);
    }

    public function opname(Request $request)
    {
        try {
            $tanggal = Carbon::parse($request->input('tanggal', date('Y-m-d')))->format('Y-m-d');
        } catch (\Throwable) {
            $tanggal = date('Y-m-d');
        }

        // Filter stok yang tidak 0
        $stokSemua = $this->stockRows($tanggal);
        $stokTampil = $stokSemua->filter(fn($item) => (float)$item->stok != 0);
        $stokKosong = $stokSemua->filter(fn($item) => (float)$item->stok == 0);

        return view('gudang_persediaan.opname', [
            'title' => 'Stok Opname Gudang',
            'tanggal' => $tanggal,
            'stok' => $stokTampil,
            'stokKosong' => $stokKosong,
            'kategori' => DB::table('tb_produk_perencanaan')->distinct()->orderBy('kategori')->pluck('kategori'),
        ]);
    }

    public function storeOpname(Request $request)
    {
        $validated = $request->validate([
            'tanggal' => ['required', 'date', 'before_or_equal:today'],
            'produk' => ['required', 'array', 'min:1'],
            'produk.*' => ['required', 'integer', 'distinct', 'exists:tb_produk_perencanaan,id_produk'],
            'stok_fisik' => ['required', 'array'],
            'stok_fisik.*' => ['nullable', 'numeric', 'min:0'],
        ]);

        foreach ($validated['produk'] as $idProduk) {
            if (! array_key_exists($idProduk, $validated['stok_fisik'])
                || $validated['stok_fisik'][$idProduk] === null
                || $validated['stok_fisik'][$idProduk] === '') {
                return back()->withErrors(['stok_fisik' => 'Stok fisik wajib diisi untuk seluruh produk yang dipilih.'])->withInput();
            }
        }

        $opnameId = DB::transaction(function () use ($validated) {
            $now = now();
            $temporaryNumber = 'TMP-' . str()->uuid();
            $opnameId = DB::table('gudang_opname_perencanaan')->insertGetId([
                'nomor_opname' => $temporaryNumber,
                'tanggal' => $validated['tanggal'],
                'admin' => auth()->user()->name,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $nomorOpname = 'OPG-' . Carbon::parse($validated['tanggal'])->format('Ymd') . '-' . str_pad((string) $opnameId, 6, '0', STR_PAD_LEFT);
            DB::table('gudang_opname_perencanaan')->where('id', $opnameId)->update(['nomor_opname' => $nomorOpname]);

            // Untuk jurnal perkiraan
            $jurnalPerkiraanData = [];
            $totalDebit = 0;
            $totalKredit = 0;

            foreach ($validated['produk'] as $idProduk) {
                $mutasi = DB::table('stok_produk_perencanaan')
                    ->where('id_pakan', $idProduk)
                    ->whereDate('tgl', '<=', $validated['tanggal'])
                    ->lockForUpdate()
                    ->get(['id_stok_telur', 'pcs', 'pcs_kredit', 'total_rp', 'biaya_dll']);

                $stokSistem = round($mutasi->sum(fn ($row) => (float) $row->pcs - (float) $row->pcs_kredit), 4);
                $nilaiSistem = $mutasi->sum(function ($row) {
                    $nilai = 0;
                    if ((float) $row->pcs > 0) {
                        $nilai += (float) $row->total_rp + (float) $row->biaya_dll;
                    }
                    if ((float) $row->pcs_kredit > 0) {
                        $nilai -= (float) $row->total_rp;
                    }
                    return $nilai;
                });
                $hargaSatuan = $stokSistem > 0 ? max(0, $nilaiSistem / $stokSistem) : $this->historicalUnitCost($idProduk, $validated['tanggal']);
                $stokFisik = round((float) $validated['stok_fisik'][$idProduk], 4);
                $selisih = round($stokFisik - $stokSistem, 4);
                $nilaiSelisih = round(abs($selisih) * $hargaSatuan, 2);

                DB::table('stok_produk_perencanaan')->insert([
                    'id_kandang' => 0,
                    'id_pakan' => $idProduk,
                    'tgl' => $validated['tanggal'],
                    'pcs' => $selisih > 0 ? $selisih : 0,
                    'pcs_kredit' => $selisih < 0 ? abs($selisih) : 0,
                    'pcs_selisih' => $selisih,
                    'admin' => auth()->user()->name,
                    'check' => 'Y',
                    'cek_admin' => auth()->user()->name,
                    'opname' => 'T',
                    'total_rp' => $nilaiSelisih,
                    'biaya_dll' => 0,
                    'no_nota' => $nomorOpname,
                    'h_opname' => 'Y',
                    'penyesuaian' => 'Y',
                ]);

                DB::table('gudang_opname_perencanaan_detail')->insert([
                    'opname_id' => $opnameId,
                    'id_produk' => $idProduk,
                    'stok_sistem' => $stokSistem,
                    'stok_fisik' => $stokFisik,
                    'selisih' => $selisih,
                    'harga_satuan' => round($hargaSatuan, 6),
                    'nilai_selisih' => $nilaiSelisih,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                // Jika ada selisih, buat jurnal perkiraan
                if ($selisih != 0 && $nilaiSelisih > 0) {
                    // Ambil kategori produk untuk menentukan akun
                    $produk = DB::table('tb_produk_perencanaan')->where('id_produk', $idProduk)->first();
                    $kategori = $produk->kategori ?? 'pakan';
                    
                    // Tentukan kode akun berdasarkan kategori
                    // Pakan: BPP 5101-04, Persediaan 110403
                    // Vitamin/Obat: BPP 5101-03, Persediaan 110404
                    if ($kategori === 'pakan') {
                        $kodeAkunBiaya = '5101-04';
                        $kodeAkunPersediaan = '110403';
                    } elseif (in_array($kategori, ['obat_pakan', 'obat_air', 'vitamin'])) {
                        $kodeAkunBiaya = '5101-03';
                        $kodeAkunPersediaan = '110404';
                    } else {
                        // Default untuk kategori lain, skip jurnal perkiraan
                        continue;
                    }
                    
                    // Ambil id_akun_perkiraan dari tabel akun_perkiraan
                    $akunBiaya = DB::table('akun_perkiraan')->where('kode_perkiraan', $kodeAkunBiaya)->first();
                    $akunPersediaan = DB::table('akun_perkiraan')->where('kode_perkiraan', $kodeAkunPersediaan)->first();
                    
                    if ($akunPersediaan && $akunBiaya) {
                        if ($selisih > 0) {
                            // Stok fisik > sistem: tambah persediaan, kurangi biaya
                            $jurnalPerkiraanData[] = [
                                'id_akun_perkiraan' => $akunPersediaan->id_akun_perkiraan,
                                'tanggal' => $validated['tanggal'],
                                'nomor_transaksi' => $nomorOpname,
                                'tipe_transaksi' => 'Stok Opname',
                                'urutan_detail' => (count($jurnalPerkiraanData) + 1),
                                'deskripsi' => 'Penyesuaian stok opname ' . $produk->nm_produk . ' (fisik > sistem)',
                                'debit' => $nilaiSelisih,
                                'kredit' => 0,
                                'created_at' => $now,
                                'updated_at' => $now,
                            ];
                            $jurnalPerkiraanData[] = [
                                'id_akun_perkiraan' => $akunBiaya->id_akun_perkiraan,
                                'tanggal' => $validated['tanggal'],
                                'nomor_transaksi' => $nomorOpname,
                                'tipe_transaksi' => 'Stok Opname',
                                'urutan_detail' => (count($jurnalPerkiraanData) + 1),
                                'deskripsi' => 'Penyesuaian stok opname ' . $produk->nm_produk . ' (fisik > sistem)',
                                'debit' => 0,
                                'kredit' => $nilaiSelisih,
                                'created_at' => $now,
                                'updated_at' => $now,
                            ];
                        } else {
                            // Stok fisik < sistem: kurangi persediaan, tambah biaya
                            $jurnalPerkiraanData[] = [
                                'id_akun_perkiraan' => $akunBiaya->id_akun_perkiraan,
                                'tanggal' => $validated['tanggal'],
                                'nomor_transaksi' => $nomorOpname,
                                'tipe_transaksi' => 'Stok Opname',
                                'urutan_detail' => (count($jurnalPerkiraanData) + 1),
                                'deskripsi' => 'Penyesuaian stok opname ' . $produk->nm_produk . ' (fisik < sistem)',
                                'debit' => $nilaiSelisih,
                                'kredit' => 0,
                                'created_at' => $now,
                                'updated_at' => $now,
                            ];
                            $jurnalPerkiraanData[] = [
                                'id_akun_perkiraan' => $akunPersediaan->id_akun_perkiraan,
                                'tanggal' => $validated['tanggal'],
                                'nomor_transaksi' => $nomorOpname,
                                'tipe_transaksi' => 'Stok Opname',
                                'urutan_detail' => (count($jurnalPerkiraanData) + 1),
                                'deskripsi' => 'Penyesuaian stok opname ' . $produk->nm_produk . ' (fisik < sistem)',
                                'debit' => 0,
                                'kredit' => $nilaiSelisih,
                                'created_at' => $now,
                                'updated_at' => $now,
                            ];
                        }
                        $totalDebit += $nilaiSelisih;
                        $totalKredit += $nilaiSelisih;
                    }
                }
            }

            // Simpan batch dan detail jurnal perkiraan
            if (!empty($jurnalPerkiraanData)) {
                $batchId = DB::table('impor_jurnal_perkiraan')->insertGetId([
                    'nama_file' => 'Opname Gudang - ' . $nomorOpname,
                    'hash_file' => hash('sha256', 'opname-gudang|' . $nomorOpname . '|' . $validated['tanggal']),
                    'periode_awal' => $validated['tanggal'],
                    'periode_akhir' => $validated['tanggal'],
                    'jumlah_transaksi' => 1,
                    'jumlah_detail' => count($jurnalPerkiraanData),
                    'total_debit' => $totalDebit,
                    'total_kredit' => $totalKredit,
                    'status' => 'aktif',
                    'diimpor_oleh' => auth()->id(),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
                
                // Set id_impor_jurnal_perkiraan untuk semua detail
                foreach ($jurnalPerkiraanData as &$detail) {
                    $detail['id_impor_jurnal_perkiraan'] = $batchId;
                }
                
                DB::table('jurnal_perkiraan')->insert($jurnalPerkiraanData);
            }

            return $opnameId;
        });

        return redirect()->route('gudang-persediaan.riwayat', ['opname' => $opnameId])
            ->with('sukses', 'Stok opname berhasil disimpan dan saldo gudang telah disesuaikan.');
    }

    public function riwayat()
    {
        $riwayat = DB::table('gudang_opname_perencanaan as o')
            ->leftJoin('gudang_opname_perencanaan_detail as d', 'd.opname_id', '=', 'o.id')
            ->select(
                'o.id', 'o.nomor_opname', 'o.tanggal', 'o.admin', 'o.created_at',
                DB::raw('COUNT(d.id) as jumlah_produk'),
                DB::raw('SUM(CASE WHEN d.selisih != 0 THEN 1 ELSE 0 END) as jumlah_selisih'),
                DB::raw('SUM(d.nilai_selisih) as total_nilai_selisih')
            )
            ->groupBy('o.id', 'o.nomor_opname', 'o.tanggal', 'o.admin', 'o.created_at')
            ->orderByDesc('o.tanggal')->orderByDesc('o.id')->get();

        $detail = DB::table('gudang_opname_perencanaan_detail as d')
            ->join('tb_produk_perencanaan as p', 'p.id_produk', '=', 'd.id_produk')
            ->leftJoin('tb_satuan as s', 's.id_satuan', '=', 'p.dosis_satuan')
            ->get(['d.*', 'p.nm_produk', 'p.kategori', 's.nm_satuan'])
            ->groupBy('opname_id');

        return view('gudang_persediaan.riwayat', [
            'title' => 'Riwayat Stok Opname Gudang',
            'riwayat' => $riwayat,
            'detail' => $detail,
        ]);
    }

    private function stockRows(?string $tanggal = null): Collection
    {
        $mutasi = DB::table('stok_produk_perencanaan')
            ->select('id_pakan')
            // Normalisasi selisih floating point yang sangat kecil agar stok
            // seperti -0,000000005 tidak dianggap sebagai stok minus.
            ->selectRaw('ROUND(SUM(pcs - pcs_kredit), 4) as stok')
            ->selectRaw('SUM(CASE WHEN pcs > 0 THEN total_rp + biaya_dll ELSE 0 END) - SUM(CASE WHEN pcs_kredit > 0 THEN total_rp ELSE 0 END) as nilai_stok')
            ->when($tanggal, fn ($query) => $query->whereDate('tgl', '<=', $tanggal))
            ->groupBy('id_pakan');

        return DB::table('tb_produk_perencanaan as p')
            ->leftJoinSub($mutasi, 'm', 'm.id_pakan', '=', 'p.id_produk')
            ->leftJoin('tb_satuan as s', 's.id_satuan', '=', 'p.dosis_satuan')
            ->orderBy('p.kategori')->orderBy('p.nm_produk')
            ->get([
                'p.id_produk', 'p.nm_produk', 'p.kode_accurate', 'p.kategori',
                's.nm_satuan', DB::raw('COALESCE(m.stok, 0) as stok'),
                DB::raw('COALESCE(m.nilai_stok, 0) as nilai_stok'),
            ]);
    }

    private function eggStockRows(?string $tanggal = null, ?int $idGudang = null): Collection
    {
        $saldo = DB::table('stok_telur')
            ->where('opname', 'T')
            ->when($tanggal, fn ($query) => $query->whereDate('tgl', '<=', $tanggal))
            ->select('id_gudang', 'id_telur')
            ->selectRaw('SUM(COALESCE(pcs, 0) - COALESCE(pcs_kredit, 0)) as stok_pcs')
            ->selectRaw('SUM(COALESCE(kg, 0) - COALESCE(kg_kredit, 0)) as stok_kg')
            ->groupBy('id_gudang', 'id_telur');

        return DB::table('gudang_telur as g')
            ->crossJoin('telur_produk as p')
            ->when($idGudang, fn ($query) => $query->where('g.id_gudang_telur', $idGudang))
            ->leftJoinSub($saldo, 's', function ($join) {
                $join->on('s.id_gudang', '=', 'g.id_gudang_telur')
                    ->on('s.id_telur', '=', 'p.id_produk_telur');
            })
            ->orderBy('g.nm_gudang')
            ->orderBy('p.id_produk_telur')
            ->get([
                'g.id_gudang_telur',
                'g.nm_gudang',
                'p.id_produk_telur',
                'p.kode_produk',
                'p.nm_telur',
                DB::raw('COALESCE(s.stok_pcs, 0) as stok_pcs'),
                DB::raw('COALESCE(s.stok_kg, 0) as stok_kg'),
            ]);
    }

    private function historicalUnitCost(int $idProduk, string $tanggal): float
    {
        $history = DB::table('stok_produk_perencanaan')
            ->where('id_pakan', $idProduk)->whereDate('tgl', '<=', $tanggal)
            ->where('pcs', '>', 0)->where('total_rp', '>', 0)
            ->selectRaw('SUM(total_rp + biaya_dll) as nilai, SUM(pcs) as qty')->first();

        return $history && (float) $history->qty > 0 ? (float) $history->nilai / (float) $history->qty : 0;
    }
}
