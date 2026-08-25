<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class GudangPersediaanController extends Controller
{
    public function index()
    {
        $stok = $this->stockRows();

        return view('gudang_persediaan.index', [
            'title' => 'Gudang',
            'stok' => $stok,
            'jumlahProduk' => $stok->count(),
            'produkAdaStok' => $stok->where('stok', '>', 0)->count(),
            'produkKosong' => $stok->where('stok', '<=', 0)->count(),
            'nilaiPersediaan' => $stok->sum(fn ($row) => max(0, (float) $row->nilai_stok)),
            'opnameTerakhir' => DB::table('gudang_opname_perencanaan')->max('tanggal'),
        ]);
    }

    public function opname(Request $request)
    {
        try {
            $tanggal = Carbon::parse($request->input('tanggal', date('Y-m-d')))->format('Y-m-d');
        } catch (\Throwable) {
            $tanggal = date('Y-m-d');
        }

        return view('gudang_persediaan.opname', [
            'title' => 'Stok Opname Gudang',
            'tanggal' => $tanggal,
            'stok' => $this->stockRows($tanggal),
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
                    'total_rp' => round(abs($selisih) * $hargaSatuan, 2),
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
                    'nilai_selisih' => round($selisih * $hargaSatuan, 2),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
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
            ->selectRaw('SUM(pcs - pcs_kredit) as stok')
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

    private function historicalUnitCost(int $idProduk, string $tanggal): float
    {
        $history = DB::table('stok_produk_perencanaan')
            ->where('id_pakan', $idProduk)->whereDate('tgl', '<=', $tanggal)
            ->where('pcs', '>', 0)->where('total_rp', '>', 0)
            ->selectRaw('SUM(total_rp + biaya_dll) as nilai, SUM(pcs) as qty')->first();

        return $history && (float) $history->qty > 0 ? (float) $history->nilai / (float) $history->qty : 0;
    }
}
