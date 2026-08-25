<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class Penjualan_martadah_alpaController extends Controller
{
    protected $tgl1, $tgl2, $id_proyek, $period, $id_buku;
    public function __construct(Request $r)
    {
        if (empty($r->period)) {
            $this->tgl1 = date('Y-m-01');
            $this->tgl2 = date('Y-m-t');
        } elseif ($r->period == 'daily') {
            $this->tgl1 = date('Y-m-d');
            $this->tgl2 = date('Y-m-d');
        } elseif ($r->period == 'weekly') {
            $this->tgl1 = date('Y-m-d', strtotime("-6 days"));
            $this->tgl2 = date('Y-m-d');
        } elseif ($r->period == 'mounthly') {
            $bulan = $r->bulan;
            $tahun = $r->tahun;
            $tglawal = "$tahun" . "-" . "$bulan" . "-" . "01";
            $tglakhir = "$tahun" . "-" . "$bulan" . "-" . "01";

            $this->tgl1 = date('Y-m-01', strtotime($tglawal));
            $this->tgl2 = date('Y-m-t', strtotime($tglakhir));
        } elseif ($r->period == 'costume') {
            $this->tgl1 = $r->tgl1;
            $this->tgl2 = $r->tgl2;
        } elseif ($r->period == 'years') {
            $tahun = $r->tahunfilter;
            $tgl_awal = "$tahun" . "-" . "01" . "-" . "01";
            $tgl_akhir = "$tahun" . "-" . "12" . "-" . "01";

            $this->tgl1 = date('Y-m-01', strtotime($tgl_awal));
            $this->tgl2 = date('Y-m-t', strtotime($tgl_akhir));
        }

        $this->id_proyek = $r->id_proyek ?? 0;
        $this->id_buku = $r->id_buku ?? 2;
    }
    public function index(Request $r)
    {
        $tgl1 =  $this->tgl1;
        $tgl2 =  $this->tgl2;
        $pencarian = trim((string) $r->input('pencarian', ''));
        $perPage = in_array((int) $r->input('per_page', 20), [20, 50, 100], true)
            ? (int) $r->input('per_page', 20)
            : 20;

        $dasarInvoice = DB::table('invoice_telur as a')
            ->leftJoin('customer as b', 'b.id_customer', '=', 'a.id_customer')
            ->where('a.lokasi', 'mtd')
            ->whereBetween('a.tgl', [$tgl1, $tgl2])
            ->when($pencarian !== '', function ($query) use ($pencarian) {
                $query->where(function ($subQuery) use ($pencarian) {
                    $subQuery->where('a.no_nota', 'like', "%{$pencarian}%")
                        ->orWhere('a.customer', 'like', "%{$pencarian}%")
                        ->orWhere('b.nm_customer', 'like', "%{$pencarian}%");
                });
            });

        $total = (clone $dasarInvoice)
            ->selectRaw('COALESCE(SUM(a.total_rp), 0) as ttl_rp')
            ->selectRaw("COALESCE(SUM(CASE WHEN a.cek != 'Y' OR a.cek IS NULL THEN a.total_rp ELSE 0 END), 0) as ttl_belum_dicek")
            ->first();

        $invoice = (clone $dasarInvoice)
            ->select('a.no_nota')
            ->selectRaw('MAX(a.tgl) as tgl')
            ->selectRaw('MAX(a.tipe) as tipe')
            ->selectRaw('MAX(a.customer) as customer')
            ->selectRaw('MAX(b.nm_customer) as nm_customer')
            ->selectRaw('SUM(a.total_rp) as ttl_rp')
            ->selectRaw('MAX(a.status) as status')
            ->selectRaw('MAX(a.cek) as cek')
            ->selectRaw('MAX(a.urutan_customer) as urutan_customer')
            ->selectRaw('MAX(a.admin) as admin')
            ->groupBy('a.no_nota')
            ->orderByRaw("MAX(CASE WHEN a.cek = 'Y' THEN 1 ELSE 0 END) ASC")
            ->orderByRaw('MAX(a.tgl) DESC')
            ->orderByDesc('a.no_nota')
            ->paginate($perPage)
            ->withQueryString();

        $nomorNota = collect($invoice->items())->pluck('no_nota');
        $detailProduk = $nomorNota->isEmpty()
            ? collect()
            : DB::table('invoice_telur')
                ->whereIn('no_nota', $nomorNota)
                ->select('no_nota', 'id_produk')
                ->selectRaw('SUM(pcs) as pcs')
                ->selectRaw('SUM(kg) as kg')
                ->groupBy('no_nota', 'id_produk')
                ->get()
                ->keyBy(fn ($item) => $item->no_nota . '|' . $item->id_produk);

        $data =  [
            'title' => 'Penjualan Telur Martadah',
            'invoice' => $invoice,
            'ttlRp' => (float) $total->ttl_rp,
            'ttlRpBelumDiCek' => (float) $total->ttl_belum_dicek,
            'produk' => DB::table('telur_produk')->orderBy('nm_telur')->get(),
            'detailProduk' => $detailProduk,
            'tgl1' => $tgl1,
            'tgl2' => $tgl2,
            'pencarian' => $pencarian,
            'perPage' => $perPage,

        ];
        return view('penjualan_martadh.index', $data);
    }

    public function detail_penjualan_mtd(Request $r)
    {
        $penjualan_mtd = $this->martadahInvoiceRows((string) $r->no_nota);
        $penjualan_mtd_detail = $penjualan_mtd->first();

        if (! $penjualan_mtd_detail) {
            abort(404, 'Nota Penjualan Martadah tidak ditemukan.');
        }

        $data = [
            'invoice' => $penjualan_mtd,
            'invoice2' => $penjualan_mtd_detail,
        ];

        return view('penjualan_martadh.detail', $data);
    }

    public function terima_invoice_mtd(Request $r)
    {
        $penjualan_mtd = $this->martadahInvoiceRows((string) $r->no_nota);
        $penjualan_mtd_detail = $penjualan_mtd->first();

        if (! $penjualan_mtd_detail) {
            return redirect()->route('penjualan_martadah_cek', ['lokasi' => 'mtd'])
                ->with('error', 'Nota Penjualan Martadah tidak ditemukan.');
        }

        $data = [
            'title' => 'Penerimaan Uang Martadah',
            'nota' => $r->no_nota,
            'akun' => DB::table('akun_perkiraan')
                ->where('aktif', 1)
                ->orderBy('kode_perkiraan')
                ->get(['id_akun_perkiraan', 'kode_perkiraan', 'nama', 'tipe_akun']),
            'jurnal' => DB::table('jurnal_perkiraan as j')
                ->join('akun_perkiraan as a', 'a.id_akun_perkiraan', '=', 'j.id_akun_perkiraan')
                ->where('j.nomor_transaksi', $r->no_nota)
                ->where('j.tipe_transaksi', 'Penjualan Telur')
                ->where('a.kode_perkiraan', '!=', '400001')
                ->orderBy('j.urutan_detail')
                ->get([
                    'j.id_jurnal_perkiraan', 'j.id_akun_perkiraan', 'j.debit', 'j.kredit',
                    'a.kode_perkiraan', 'a.nama as nama_akun',
                ]),
            'invoice' => $penjualan_mtd,
            'invoice2' => $penjualan_mtd_detail,
        ];
        return view('penjualan_martadh.penerimaan_uang', $data);
    }

    private function martadahInvoiceRows(string $noNota): Collection
    {
        return DB::table('invoice_telur as a')
            ->leftJoin('telur_produk as b', 'b.id_produk_telur', '=', 'a.id_produk')
            ->leftJoin('customer as c', 'c.id_customer', '=', 'a.id_customer')
            ->where('a.no_nota', $noNota)
            ->where('a.lokasi', 'mtd')
            ->orderBy('a.id_invoice_telur')
            ->get([
                'a.tgl',
                'a.no_nota',
                'a.id_produk',
                'a.admin',
                'a.void',
                'a.total_rp',
                'b.nm_telur',
                DB::raw("COALESCE(c.nm_customer, NULLIF(a.customer, ''), CONCAT('Customer #', a.id_customer)) as customer"),
                DB::raw("COALESCE(c.no_telp, '') as no_hp"),
                DB::raw("CASE WHEN ABS(a.total_rp - (a.pcs * a.rp_satuan)) <= ABS(a.total_rp - (a.kg_jual * a.rp_satuan)) THEN a.pcs ELSE 0 END as pcs_pcs"),
                DB::raw("CASE WHEN ABS(a.total_rp - (a.pcs * a.rp_satuan)) <= ABS(a.total_rp - (a.kg_jual * a.rp_satuan)) THEN a.kg ELSE 0 END as kg_pcs"),
                DB::raw("CASE WHEN ABS(a.total_rp - (a.pcs * a.rp_satuan)) <= ABS(a.total_rp - (a.kg_jual * a.rp_satuan)) THEN a.rp_satuan ELSE 0 END as rp_pcs"),
                DB::raw("CASE WHEN ABS(a.total_rp - (a.pcs * a.rp_satuan)) > ABS(a.total_rp - (a.kg_jual * a.rp_satuan)) AND a.ikat > 0 THEN a.ikat ELSE 0 END as ikat"),
                DB::raw("CASE WHEN ABS(a.total_rp - (a.pcs * a.rp_satuan)) > ABS(a.total_rp - (a.kg_jual * a.rp_satuan)) AND a.ikat > 0 THEN a.kg ELSE 0 END as kg_ikat"),
                DB::raw("CASE WHEN ABS(a.total_rp - (a.pcs * a.rp_satuan)) > ABS(a.total_rp - (a.kg_jual * a.rp_satuan)) AND a.ikat > 0 THEN a.rp_satuan ELSE 0 END as rp_ikat"),
                DB::raw("CASE WHEN ABS(a.total_rp - (a.pcs * a.rp_satuan)) > ABS(a.total_rp - (a.kg_jual * a.rp_satuan)) AND a.ikat <= 0 THEN a.pcs ELSE 0 END as pcs_kg"),
                DB::raw("CASE WHEN ABS(a.total_rp - (a.pcs * a.rp_satuan)) > ABS(a.total_rp - (a.kg_jual * a.rp_satuan)) AND a.ikat <= 0 THEN a.kg ELSE 0 END as kg_kg_kotor"),
                DB::raw("CASE WHEN ABS(a.total_rp - (a.pcs * a.rp_satuan)) > ABS(a.total_rp - (a.kg_jual * a.rp_satuan)) AND a.ikat <= 0 THEN a.kg_jual ELSE 0 END as kg_kg"),
                DB::raw('0 as rak_kg'),
                DB::raw("CASE WHEN ABS(a.total_rp - (a.pcs * a.rp_satuan)) > ABS(a.total_rp - (a.kg_jual * a.rp_satuan)) AND a.ikat <= 0 THEN a.rp_satuan ELSE 0 END as rp_kg"),
                DB::raw('a.tipe as jenis'),
            ]);
    }

    public function tbh_pembayaran_martadah(Request $r)
    {
        return view('penjualan_martadh.tbh_pembayaran', [
            'count' => max(1, (int) $r->count),
            'akun' => DB::table('akun_perkiraan')
                ->where('aktif', 1)
                ->orderBy('kode_perkiraan')
                ->get(['id_akun_perkiraan', 'kode_perkiraan', 'nama']),
        ]);
    }

    public function save_terima_invoice(Request $r)
    {
        $validated = $r->validate([
            'no_nota' => ['required', 'string', 'max:100'],
            'tgl' => ['required', 'date'],
            'id_akun' => ['required', 'array', 'min:1'],
            'id_akun.*' => ['required', 'integer', 'distinct', 'exists:akun_perkiraan,id_akun_perkiraan'],
            'debit' => ['required', 'array'],
            'debit.*' => ['nullable', 'numeric', 'min:0'],
            'kredit' => ['required', 'array'],
            'kredit.*' => ['nullable', 'numeric', 'min:0'],
            'id_akun_sisa' => ['nullable', 'integer', 'exists:akun_perkiraan,id_akun_perkiraan'],
        ]);

        $invoice = DB::table('invoice_telur')
            ->where('no_nota', $validated['no_nota'])
            ->where('lokasi', 'mtd')
            ->get();

        if ($invoice->isEmpty()) {
            return back()->withErrors(['no_nota' => 'Nota Penjualan Martadah tidak ditemukan.']);
        }

        $totalPenjualan = round((float) $invoice->sum('total_rp'), 2);
        $akunIds = collect($validated['id_akun'])
            ->push($validated['id_akun_sisa'] ?? null)
            ->filter()
            ->unique()
            ->values();
        $akunMap = DB::table('akun_perkiraan')
            ->whereIn('id_akun_perkiraan', $akunIds)
            ->where('aktif', 1)
            ->get()
            ->keyBy('id_akun_perkiraan');

        if ($akunMap->count() !== $akunIds->count()) {
            return back()->withErrors(['id_akun' => 'Pilih akun perkiraan yang masih aktif.'])->withInput();
        }

        $detail = collect($validated['id_akun'])->map(function ($idAkun, $index) use ($validated, $akunMap) {
            return [
                'id_akun_perkiraan' => (int) $idAkun,
                'nama_akun' => $akunMap[(int) $idAkun]->nama,
                'debit' => round((float) ($validated['debit'][$index] ?? 0), 2),
                'kredit' => round((float) ($validated['kredit'][$index] ?? 0), 2),
            ];
        })->filter(fn ($row) => $row['debit'] > 0 || $row['kredit'] > 0)->values();

        if ($detail->isEmpty()) {
            return back()->withErrors(['debit' => 'Nominal akun setor belum diisi.'])->withInput();
        }

        $selisih = round($detail->sum('debit') - ($detail->sum('kredit') + $totalPenjualan), 2);
        if (abs($selisih) > 0.01) {
            $idAkunSisa = $validated['id_akun_sisa'] ?? null;
            if (! $idAkunSisa) {
                return back()->withErrors(['id_akun_sisa' => 'Jurnal belum seimbang. Pilih akun selisih.'])->withInput();
            }

            $detail->push([
                'id_akun_perkiraan' => (int) $idAkunSisa,
                'nama_akun' => $akunMap[(int) $idAkunSisa]->nama,
                'debit' => $selisih < 0 ? abs($selisih) : 0,
                'kredit' => $selisih > 0 ? $selisih : 0,
            ]);
        }

        $akunPenjualan = DB::table('akun_perkiraan')
            ->where('kode_perkiraan', '400001')
            ->where('aktif', 1)
            ->first();

        if (! $akunPenjualan) {
            return back()->withErrors(['id_akun' => 'Akun 400001 - Penjualan Telur tidak ditemukan atau tidak aktif.'])->withInput();
        }

        DB::transaction(function () use ($validated, $invoice, $detail, $akunMap, $akunPenjualan, $totalPenjualan) {
            $now = now();
            $batchIds = DB::table('jurnal_perkiraan')
                ->where('nomor_transaksi', $validated['no_nota'])
                ->where('tipe_transaksi', 'Penjualan Telur')
                ->pluck('id_impor_jurnal_perkiraan')
                ->filter()
                ->unique();

            DB::table('jurnal_perkiraan')
                ->where('nomor_transaksi', $validated['no_nota'])
                ->where('tipe_transaksi', 'Penjualan Telur')
                ->delete();
            if ($batchIds->isNotEmpty()) {
                DB::table('impor_jurnal_perkiraan')->whereIn('id_impor_jurnal_perkiraan', $batchIds)->delete();
            }

            $totalDebit = round($detail->sum('debit'), 2);
            $totalKredit = round($detail->sum('kredit') + $totalPenjualan, 2);
            $batchId = DB::table('impor_jurnal_perkiraan')->insertGetId([
                'nama_file' => 'Penjualan telur Martadah ' . $validated['no_nota'],
                'hash_file' => hash('sha256', 'penjualan-telur-martadah|' . $validated['no_nota']),
                'periode_awal' => $validated['tgl'],
                'periode_akhir' => $validated['tgl'],
                'jumlah_transaksi' => 1,
                'jumlah_detail' => $detail->count() + 1,
                'total_debit' => $totalDebit,
                'total_kredit' => $totalKredit,
                'status' => 'aktif',
                'diimpor_oleh' => auth()->id(),
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $customer = DB::table('customer')
                ->where('id_customer', $invoice->first()->id_customer)
                ->value('nm_customer') ?? 'Customer';
            $rows = $detail->map(function ($row, $index) use ($batchId, $validated, $now) {
                return [
                    'id_impor_jurnal_perkiraan' => $batchId,
                    'id_akun_perkiraan' => $row['id_akun_perkiraan'],
                    'tanggal' => $validated['tgl'],
                    'nomor_transaksi' => $validated['no_nota'],
                    'tipe_transaksi' => 'Penjualan Telur',
                    'urutan_detail' => $index + 1,
                    'deskripsi' => 'Setoran penjualan telur Martadah melalui ' . $row['nama_akun'],
                    'debit' => $row['debit'],
                    'kredit' => $row['kredit'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            })->push([
                'id_impor_jurnal_perkiraan' => $batchId,
                'id_akun_perkiraan' => $akunPenjualan->id_akun_perkiraan,
                'tanggal' => $validated['tgl'],
                'nomor_transaksi' => $validated['no_nota'],
                'tipe_transaksi' => 'Penjualan Telur',
                'urutan_detail' => $detail->count() + 1,
                'deskripsi' => 'Pendapatan penjualan telur Martadah kepada ' . $customer,
                'debit' => 0,
                'kredit' => $totalPenjualan,
                'created_at' => $now,
                'updated_at' => $now,
            ])->all();
            DB::table('jurnal_perkiraan')->insert($rows);

            $adaPiutang = $detail->contains(function ($row) use ($akunMap) {
                return $row['debit'] > 0 && ($akunMap[$row['id_akun_perkiraan']]->tipe_akun ?? null) === 'AREC';
            });
            DB::table('invoice_telur')
                ->where('no_nota', $validated['no_nota'])
                ->where('lokasi', 'mtd')
                ->update([
                    'cek' => 'Y',
                    'admin_cek' => Auth::user()->name,
                    'status' => $adaPiutang ? 'unpaid' : 'paid',
                ]);
        });

        $tgl1 = date('Y-m-01', strtotime($validated['tgl']));
        $tgl2 = date('Y-m-t', strtotime($validated['tgl']));
        return redirect()->route('penjualan_martadah_cek', [
            'lokasi' => 'mtd', 'period' => 'costume', 'tgl1' => $tgl1, 'tgl2' => $tgl2,
        ])->with('sukses', 'Setoran Martadah berhasil disimpan ke jurnal perkiraan.');
    }
}
