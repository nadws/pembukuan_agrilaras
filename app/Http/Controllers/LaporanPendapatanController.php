<?php

namespace App\Http\Controllers;

use App\Exports\LaporanPendapatanExport;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class LaporanPendapatanController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'tanggal_awal' => ['nullable', 'date'],
            'tanggal_akhir' => ['nullable', 'date', 'after_or_equal:tanggal_awal'],
            'kategori' => ['nullable', 'string', 'in:telur,umum,ayam'],
            'lokasi' => ['nullable', 'string', 'in:alpa,mtd'],
            'pembayaran' => ['nullable', 'array'],
            'pembayaran.*' => ['integer', 'exists:akun_perkiraan,id_akun_perkiraan'],
            'per_page' => ['nullable', 'integer', 'in:25,50,100'],
        ]);

        $tanggalAwal = $filters['tanggal_awal'] ?? date('Y-m-01');
        $tanggalAkhir = $filters['tanggal_akhir'] ?? date('Y-m-d');
        $kategori = $filters['kategori'] ?? '';
        $lokasi = $filters['lokasi'] ?? '';
        $pembayaranIds = collect($filters['pembayaran'] ?? [])->map(fn ($id) => (int) $id)->unique()->values()->all();
        $perPage = (int) ($filters['per_page'] ?? 50);

        [$allRows, $baseIds, $pembayaranIds] = $this->resolveRows($tanggalAwal, $tanggalAkhir, $kategori, $lokasi, $pembayaranIds);
        $akunPembayaran = $this->akunPembayaranOptions($baseIds);
        $totals = $this->totals($allRows);
        $summary = $this->productSummary($allRows);
        $paySummary = $this->paymentSummary($allRows);
        $telurSummary = $summary->firstWhere('tipe', 'telur');
        $summaryLain = $summary->where('tipe', '!=', 'telur')->values();
        $telurRows = $allRows->where('kategori', 'telur');
        $telurDetail = $this->telurDetail($tanggalAwal, $tanggalAkhir, $telurRows);

        $page = max(1, (int) $request->input('page', 1));
        $paginated = new LengthAwarePaginator(
            $allRows->forPage($page, $perPage)->values(),
            $allRows->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('laporan.pendapatan', [
            'title' => 'Laporan Pendapatan',
            'rows' => $paginated,
            'totals' => $totals,
            'tanggalAwal' => $tanggalAwal,
            'tanggalAkhir' => $tanggalAkhir,
            'kategori' => $kategori,
            'lokasi' => $lokasi,
            'pembayaranIds' => $pembayaranIds,
            'akunPembayaran' => $akunPembayaran,
            'perPage' => $perPage,
            'summary' => $summary,
            'telurSummary' => $telurSummary,
            'summaryLain' => $summaryLain,
            'telurDetail' => $telurDetail,
            'paySummary' => $paySummary,
        ]);
    }

    public function export(Request $request): BinaryFileResponse
    {
        $filters = $request->validate([
            'tanggal_awal' => ['nullable', 'date'],
            'tanggal_akhir' => ['nullable', 'date', 'after_or_equal:tanggal_awal'],
            'kategori' => ['nullable', 'string', 'in:telur,umum,ayam'],
            'lokasi' => ['nullable', 'string', 'in:alpa,mtd'],
            'pembayaran' => ['nullable', 'array'],
            'pembayaran.*' => ['integer', 'exists:akun_perkiraan,id_akun_perkiraan'],
        ]);

        $tanggalAwal = $filters['tanggal_awal'] ?? date('Y-m-01');
        $tanggalAkhir = $filters['tanggal_akhir'] ?? date('Y-m-d');
        $kategori = $filters['kategori'] ?? '';
        $lokasi = $filters['lokasi'] ?? '';
        $pembayaranIds = collect($filters['pembayaran'] ?? [])->map(fn ($id) => (int) $id)->unique()->values()->all();

        [$rows] = $this->resolveRows($tanggalAwal, $tanggalAkhir, $kategori, $lokasi, $pembayaranIds);
        $filename = "laporan-pendapatan-{$tanggalAwal}-sampai-{$tanggalAkhir}.xlsx";

        return Excel::download(
            new LaporanPendapatanExport($rows, $tanggalAwal, $tanggalAkhir, $this->productSummary($rows), $this->paymentSummary($rows)),
            $filename
        );
    }

    /**
     * Ambil baris + buang pilihan pembayaran yang tidak ada di hasil.
     * @return array{0:\Illuminate\Support\Collection,1:\Illuminate\Support\Collection,2:array}
     */
    private function resolveRows(string $tanggalAwal, string $tanggalAkhir, string $kategori, string $lokasi, array $pembayaranIds): array
    {
        $base = $this->fetchRows($tanggalAwal, $tanggalAkhir, $kategori, $lokasi);
        $baseIds = $base->pluck('pembayaran_ids')->flatten()->map(fn ($id) => (int) $id)->filter()->unique()->values();
        $pembayaranIds = array_values(array_intersect($pembayaranIds, $baseIds->all()));
        $rows = ! empty($pembayaranIds)
            ? $base->filter(fn ($row) => ! empty(array_intersect($pembayaranIds, $row['pembayaran_ids'] ?? [])))
                ->map(function ($row) use ($pembayaranIds) {
                    $selected = collect($row['pembayaran_breakdown'] ?? [])
                        ->filter(fn ($item) => in_array((int) ($item['id'] ?? 0), $pembayaranIds, true))
                        ->values();
                    if ($selected->isEmpty()) return null;
                    $row['pembayaran_breakdown'] = $selected->all();
                    $row['pembayaran'] = $selected->pluck('pembayaran')->implode(', ');
                    $row['pembayaran_ids'] = $selected->pluck('id')->map(fn ($id) => (int) $id)->all();
                    $row['total'] = (float) $selected->sum('total');
                    return $row;
                })->filter()->values()
            : $base;

        return [$rows, $baseIds, $pembayaranIds];
    }

    /**
     * @return \Illuminate\Support\Collection<int, array{no_nota:string,tgl:string,customer:string,kategori:string,lokasi:string,total:float}>
     */
    private function fetchRows(string $tanggalAwal, string $tanggalAkhir, string $kategori, string $lokasi)
    {
        $rows = collect();
        $lokasiOptions = ['alpa', 'mtd'];

        if ($kategori === '' || $kategori === 'telur') {
            $telur = DB::table('invoice_telur as i')
                ->leftJoin('customer as c', 'c.id_customer', '=', 'i.id_customer')
                ->leftJoin('customer as c2', 'c2.id_customer', '=', 'i.id_customer2')
                ->when($lokasi !== '', fn ($q) => $q->where('i.lokasi', $lokasi), fn ($q) => $q->whereIn('i.lokasi', $lokasiOptions))
                ->whereBetween('i.tgl', [$tanggalAwal, $tanggalAkhir])
                ->groupBy('i.no_nota', 'i.tgl', 'i.lokasi', 'c.nm_customer', 'c2.nm_customer')
                ->select('i.no_nota', 'i.tgl', 'i.lokasi', 'c.nm_customer', 'c2.nm_customer as nm_customer2')
                ->selectRaw('SUM(i.total_rp) as total_rp')
                ->get();

            foreach ($telur as $row) {
                $rows->push([
                    'no_nota' => (string) $row->no_nota,
                    'tgl' => (string) $row->tgl,
                    'customer' => trim((string) ($row->nm_customer ?? '')) !== '' ? (string) $row->nm_customer : '-',
                    'kategori' => 'telur',
                    'lokasi' => $this->lokasiLabel((string) ($row->lokasi ?? '')),
                    'lokasi_raw' => (string) ($row->lokasi ?? ''),
                    'total' => (float) $row->total_rp,
                ]);
            }
        }

        if ($kategori === '' || $kategori === 'ayam') {
            $ayam = DB::table('invoice_ayam as i')
                ->leftJoin('customer as c', 'c.id_customer', '=', 'i.id_customer')
                ->when($lokasi !== '', fn ($q) => $q->where('i.lokasi', $lokasi), fn ($q) => $q->whereIn('i.lokasi', $lokasiOptions))
                ->whereBetween('i.tgl', [$tanggalAwal, $tanggalAkhir])
                ->groupBy('i.no_nota', 'i.tgl', 'i.lokasi', 'c.nm_customer')
                ->select('i.no_nota', 'i.tgl', 'i.lokasi', 'c.nm_customer')
                ->selectRaw('SUM(i.qty * i.h_satuan) as total_rp')
                ->get();

            foreach ($ayam as $row) {
                $rows->push([
                    'no_nota' => (string) $row->no_nota,
                    'tgl' => (string) $row->tgl,
                    'customer' => (string) ($row->nm_customer ?: '-'),
                    'kategori' => 'ayam',
                    'lokasi' => $this->lokasiLabel((string) ($row->lokasi ?? '')),
                    'lokasi_raw' => (string) ($row->lokasi ?? ''),
                    'total' => (float) $row->total_rp,
                ]);
            }
        }

        if ($kategori === '' || $kategori === 'umum') {
            $umum = DB::table('penjualan_agl as p')
                ->leftJoin('customer as c', 'c.id_customer', '=', 'p.id_customer')
                ->when($lokasi !== '', fn ($q) => $q->where('p.lokasi', $lokasi), fn ($q) => $q->whereIn('p.lokasi', $lokasiOptions))
                ->whereBetween('p.tgl', [$tanggalAwal, $tanggalAkhir])
                ->groupBy('p.urutan', 'p.tgl', 'p.lokasi', 'p.kode', 'p.id_customer', 'c.nm_customer')
                ->select('p.urutan', 'p.tgl', 'p.lokasi', 'p.kode', 'p.id_customer', 'c.nm_customer')
                ->selectRaw("MAX(p.nota_manual) as nota_manual")
                ->selectRaw('SUM(p.total_rp) as total_rp')
                ->get();

            foreach ($umum as $row) {
                $nota = trim((string) ($row->nota_manual ?? ''));
                if ($nota === '') {
                    $nota = trim((string) ($row->kode ?? '')) !== '' ? $row->kode.'-'.$row->urutan : 'PUM-'.$row->urutan;
                }
                // id_customer kadang berisi nama langsung (mis. 'warno').
                $rawCustomer = trim((string) ($row->id_customer ?? ''));
                $customer = trim((string) ($row->nm_customer ?? '')) !== ''
                    ? (string) $row->nm_customer
                    : ($rawCustomer !== '' && ! is_numeric($rawCustomer) ? $rawCustomer : '-');
                $rows->push([
                    'no_nota' => $nota,
                    'tgl' => (string) $row->tgl,
                    'customer' => $customer,
                    'kategori' => 'umum',
                    'lokasi' => $this->lokasiLabel((string) ($row->lokasi ?? '')),
                    'lokasi_raw' => (string) ($row->lokasi ?? ''),
                    'urutan' => (int) $row->urutan,
                    'total' => (float) $row->total_rp,
                ]);
            }
        }

        $rows = $this->attachPembayaran($rows, $tanggalAkhir);

        return $rows->sortBy([['tgl', 'desc'], ['no_nota', 'desc']])->values();
    }

    private function attachPembayaran($rows, ?string $tanggalAkhir = null)
    {
        if ($rows->isEmpty()) {
            return $rows;
        }

        // Jurnal penjualan umum memakai prefix PUM-, bukan PU-.
        $notas = $rows->map(fn ($row) => (($row['kategori'] ?? '') === 'umum' && isset($row['urutan']))
            ? 'PUM-'.$row['urutan']
            : $row['no_nota'])->unique()->values()->all();
        $pembayaran = DB::table('jurnal_perkiraan as j')
            ->leftJoin('impor_jurnal_perkiraan as imp', 'imp.id_impor_jurnal_perkiraan', '=', 'j.id_impor_jurnal_perkiraan')
            ->leftJoin('akun_perkiraan as a', 'a.id_akun_perkiraan', '=', 'j.id_akun_perkiraan')
            ->whereIn('j.nomor_transaksi', $notas)
            ->whereIn('j.tipe_transaksi', ['Penjualan Telur', 'Penjualan Ayam', 'Penjualan Umum'])
            ->where('j.debit', '>', 0)
            ->where(fn ($q) => $q->whereNull('imp.id_impor_jurnal_perkiraan')->orWhere('imp.status', 'aktif'))
            ->select('j.nomor_transaksi', 'j.id_akun_perkiraan', 'a.kode_perkiraan', 'a.nama', 'j.debit')
            ->get()
            ->groupBy('nomor_transaksi');

        $piutang = $this->piutangFallback();
        $settlementJenis = ['telur' => 'telur', 'ayam' => 'ayam', 'umum' => 'umum'];
        $settlements = DB::table('pelunasan_piutang_penjualan as p')
            ->leftJoin('akun_perkiraan as a', 'a.id_akun_perkiraan', '=', 'p.id_akun_pembayaran')
            ->when($tanggalAkhir, fn ($q) => $q->where('p.tanggal_bayar', '<=', $tanggalAkhir))
            ->get(['p.jenis', 'p.no_nota', 'p.nilai_piutang_dilunasi', 'a.id_akun_perkiraan', 'a.kode_perkiraan', 'a.nama'])
            ->groupBy(fn ($item) => $item->jenis.'|'.$item->no_nota);

        return $rows->map(function ($row) use ($pembayaran, $piutang, $settlements, $settlementJenis) {
            $key = (($row['kategori'] ?? '') === 'umum' && isset($row['urutan']))
                ? 'PUM-'.$row['urutan']
                : $row['no_nota'];
            $candidates = $pembayaran->get($key, collect());
            $jenis = $settlementJenis[$row['kategori'] ?? ''] ?? null;
            $settled = $jenis ? $settlements->get($jenis.'|'.$row['no_nota'], collect()) : collect();
            if ($settled->isNotEmpty()) {
                $breakdown = $settled->groupBy('id_akun_perkiraan')->map(function ($items) {
                    $first = $items->first();
                    return [
                        'id' => (int) $first->id_akun_perkiraan,
                        'pembayaran' => trim(collect([$first->kode_perkiraan ?? '', $first->nama ?? ''])->filter()->implode(' - ')),
                        'total' => (float) $items->sum('nilai_piutang_dilunasi'),
                    ];
                })->values()->all();
                $sudahDilunasi = (float) $settled->sum('nilai_piutang_dilunasi');
                $sisa = max(0, (float) $row['total'] - $sudahDilunasi);
                if ($sisa > 0.005 && $piutang) {
                    $breakdown[] = ['id' => $piutang['id'], 'pembayaran' => $piutang['label'], 'total' => $sisa];
                }
                $row['pembayaran'] = collect($breakdown)->pluck('pembayaran')->filter()->unique()->implode(', ');
                $row['pembayaran_ids'] = $settled->pluck('id_akun_perkiraan')->map(fn ($id) => (int) $id)->unique()->values()->all();
                if ($sisa > 0.005 && $piutang) {
                    $row['pembayaran_ids'][] = $piutang['id'];
                }
                $row['pembayaran_breakdown'] = $breakdown;
                return $row;
            }
            $label = $candidates
                ->map(fn ($item) => trim(collect([$item->kode_perkiraan ?? '', $item->nama ?? ''])->filter()->implode(' - ')))
                ->filter()->unique()->implode(', ');
            // Tanpa metode pembayaran = masuk Piutang Usaha IDR.
            if ($label === '' && $piutang) {
                $row['pembayaran'] = $piutang['label'];
                $row['pembayaran_ids'] = [$piutang['id']];
                $row['pembayaran_breakdown'] = [['id' => $piutang['id'], 'pembayaran' => $piutang['label'], 'total' => (float) $row['total']]];

                return $row;
            }
            $row['pembayaran'] = $label !== '' ? $label : '-';
            $row['pembayaran_ids'] = $candidates->pluck('id_akun_perkiraan')->map(fn ($id) => (int) $id)->unique()->values()->all();
            $row['pembayaran_breakdown'] = $candidates->groupBy('id_akun_perkiraan')->map(function ($items) {
                $first = $items->first();
                return [
                    'id' => (int) $first->id_akun_perkiraan,
                    'pembayaran' => trim(collect([$first->kode_perkiraan ?? '', $first->nama ?? ''])->filter()->implode(' - ')),
                    'total' => (float) $items->sum('debit'),
                ];
            })->values()->all();

            return $row;
        });
    }

    private function piutangFallback(): ?array
    {
        $akun = DB::table('akun_perkiraan')
            ->where('kode_perkiraan', '110301')
            ->where('aktif', 1)
            ->first(['id_akun_perkiraan', 'kode_perkiraan', 'nama']);
        if (! $akun) {
            return null;
        }

        return [
            'id' => (int) $akun->id_akun_perkiraan,
            'label' => trim($akun->kode_perkiraan.' - '.$akun->nama),
        ];
    }

    private function akunPembayaranOptions($availableIds = null)
    {
        $query = DB::table('akun_perkiraan')
            ->where('aktif', 1)
            ->whereIn('tipe_akun', ['BANK', 'AREC'])
            ->orderBy('kode_perkiraan');

        if ($availableIds !== null) {
            if ($availableIds->isEmpty()) {
                return collect();
            }
            $query->whereIn('id_akun_perkiraan', $availableIds->all());
        }

        return $query->get(['id_akun_perkiraan', 'kode_perkiraan', 'nama']);
    }

    private function lokasiLabel(string $lokasi): string
    {
        return match (strtolower($lokasi)) {
            'alpa' => 'BJM',
            'mtd' => 'MTD',
            default => strtoupper($lokasi) !== '' ? strtoupper($lokasi) : '-',
        };
    }

    private function totals($rows): array
    {
        return [
            'telur' => (float) $rows->where('kategori', 'telur')->sum('total'),
            'ayam' => (float) $rows->where('kategori', 'ayam')->sum('total'),
            'umum' => (float) $rows->where('kategori', 'umum')->sum('total'),
            'grand' => (float) $rows->sum('total'),
            'count' => $rows->count(),
        ];
    }

    /**
     * Total per akun pembayaran dari nota-nota yang sudah tersaring filter.
     * @return \Illuminate\Support\Collection<int, array{pembayaran:string,jumlah:int,total:float}>
     */
    private function paymentSummary($rows)
    {
        return $rows->flatMap(fn ($row) => $row['pembayaran_breakdown'] ?? [['pembayaran' => $row['pembayaran'] ?? '-', 'total' => $row['total']]])
            ->groupBy('pembayaran')
            ->map(fn ($group, $label) => [
                'pembayaran' => $label,
                'total' => (float) $group->sum('total'),
            ])
            ->sortByDesc('total')
            ->values();
    }

    /**
     * Rangkuman per produk dari nota-nota yang sudah tersaring filter.
     * Telur digabung satu baris: pcs = total butir penjualan PCS,
     * kg = total kg penjualan KG, qty setara = kg + pcs*63/1000 (1 butir = 63 gram).
     * @return \Illuminate\Support\Collection<int, array{produk:string,tipe:string,tipe_jual:string,pcs:float,kg:float,qty_setara:float|null,rata2:float,satuan:string,total:float}>
     */
    private function productSummary($rows)
    {
        $summary = collect();

        $telurRows = $rows->where('kategori', 'telur');
        if ($telurRows->isNotEmpty()) {
            $telur = DB::table('invoice_telur as i')
                ->whereIn('i.no_nota', $telurRows->pluck('no_nota')->unique()->all())
                ->whereIn('i.lokasi', $telurRows->pluck('lokasi_raw')->unique()->all())
                ->selectRaw("SUM(CASE WHEN UPPER(i.tipe) = 'PCS' THEN i.pcs ELSE 0 END) as pcs")
                ->selectRaw("SUM(CASE WHEN UPPER(i.tipe) = 'KG' THEN i.kg_jual ELSE 0 END) as kg")
                ->selectRaw('SUM(i.total_rp) as total')
                ->first();
            $pcsTelur = (float) ($telur->pcs ?? 0);
            $kgTelur = (float) ($telur->kg ?? 0);
            $totalTelur = (float) ($telur->total ?? 0);
            // 1 butir = 63 gram -> kg setara agar rata-rata Rp/Kg gabungan valid.
            $qtySetaraTelur = $kgTelur + $pcsTelur * 63 / 1000;
            $summary->push([
                'produk' => 'Telur',
                'tipe' => 'telur',
                'tipe_jual' => 'CAMPURAN',
                'pcs' => $pcsTelur,
                'kg' => $kgTelur,
                'qty_setara' => $qtySetaraTelur,
                'rata2' => $qtySetaraTelur > 0 ? $totalTelur / $qtySetaraTelur : 0,
                'satuan' => 'kg',
                'total' => $totalTelur,
            ]);
        }

        $ayamRows = $rows->where('kategori', 'ayam');
        if ($ayamRows->isNotEmpty()) {
            $total = DB::table('invoice_ayam as i')
                ->whereIn('i.no_nota', $ayamRows->pluck('no_nota')->unique()->all())
                ->whereIn('i.lokasi', $ayamRows->pluck('lokasi_raw')->unique()->all())
                ->selectRaw('SUM(i.qty) as pcs, SUM(i.qty * i.h_satuan) as total')
                ->first();
            $pcsAyam = (float) ($total->pcs ?? 0);
            $totalAyam = (float) ($total->total ?? 0);
            $summary->push([
                'produk' => 'Ayam',
                'tipe' => 'ayam',
                'tipe_jual' => '-',
                'pcs' => $pcsAyam,
                'kg' => 0,
                'qty_setara' => null,
                'rata2' => $pcsAyam > 0 ? $totalAyam / $pcsAyam : 0,
                'satuan' => 'ekor',
                'total' => $totalAyam,
            ]);
        }

        $umumRows = $rows->where('kategori', 'umum');
        if ($umumRows->isNotEmpty()) {
            $items = DB::table('penjualan_agl as p')
                ->leftJoin('tb_produk as pr', 'pr.id_produk', '=', 'p.id_produk')
                ->whereIn('p.urutan', $umumRows->pluck('urutan')->unique()->all())
                ->whereIn('p.lokasi', $umumRows->pluck('lokasi_raw')->unique()->all())
                ->groupBy('p.id_produk')
                ->select('p.id_produk')
                ->selectRaw('MAX(pr.nm_produk) as nama')
                ->selectRaw('SUM(p.qty) as pcs, SUM(p.total_rp) as total')
                ->orderBy('nama')
                ->get();
            foreach ($items as $item) {
                $pcsUmum = (float) $item->pcs;
                $totalUmum = (float) $item->total;
                $summary->push([
                    'produk' => trim((string) ($item->nama ?? '')) !== '' ? (string) $item->nama : 'Umum (tanpa nama)',
                    'tipe' => 'umum',
                    'tipe_jual' => '-',
                    'pcs' => $pcsUmum,
                    'kg' => 0,
                    'qty_setara' => null,
                    'rata2' => $pcsUmum > 0 ? $totalUmum / $pcsUmum : 0,
                    'satuan' => 'pcs',
                    'total' => $totalUmum,
                ]);
            }
        }

        return $summary->sortBy([['tipe', 'asc'], ['produk', 'asc']])->values();
    }

    /**
     * Komponen (baris invoice) satu nota telur untuk drill-down.
     */
    public function detailNota(Request $request): \Illuminate\Http\JsonResponse
    {
        $filters = $request->validate([
            'no_nota' => ['required', 'string', 'max:200'],
            'lokasi' => ['required', 'string', 'in:alpa,mtd'],
        ]);

        $head = DB::table('invoice_telur as i')
            ->leftJoin('customer as c', 'c.id_customer', '=', 'i.id_customer')
            ->where('i.no_nota', $filters['no_nota'])
            ->where('i.lokasi', $filters['lokasi'])
            ->select('i.no_nota', 'i.tgl', 'i.lokasi')
            ->selectRaw('MAX(c.nm_customer) as customer')
            ->selectRaw('SUM(i.total_rp) as total')
            ->groupBy('i.no_nota', 'i.tgl', 'i.lokasi')
            ->first();

        if (! $head) {
            return response()->json(['message' => 'Nota tidak ditemukan.'], 404);
        }

        $lines = DB::table('invoice_telur as i')
            ->leftJoin('telur_produk as p', 'p.id_produk_telur', '=', 'i.id_produk')
            ->where('i.no_nota', $filters['no_nota'])
            ->where('i.lokasi', $filters['lokasi'])
            ->orderBy('i.id_invoice_telur')
            ->get(['i.tipe', 'i.pcs', 'i.kg', 'i.kg_jual', 'i.ikat', 'i.rp_satuan', 'i.total_rp', 'p.nm_telur']);

        return response()->json([
            'no_nota' => (string) $head->no_nota,
            'tgl' => (string) $head->tgl,
            'lokasi' => $this->lokasiLabel((string) ($head->lokasi ?? '')),
            'customer' => trim((string) ($head->customer ?? '')) !== '' ? (string) $head->customer : '-',
            'total' => (float) $head->total,
            'lines' => $lines->map(function ($l) {
                $tipe = strtoupper((string) ($l->tipe ?? ''));
                $pcs = (float) $l->pcs;
                $kgJual = (float) $l->kg_jual;
                $total = (float) $l->total_rp;
                // Baris PCS dikonversi 1 butir = 63 gram dulu baru dihitung rata-ratanya.
                $qtySetara = $tipe === 'PCS' ? $pcs * 63 / 1000 : $kgJual;

                return [
                    'produk' => trim((string) ($l->nm_telur ?? '')) !== '' ? (string) $l->nm_telur : '-',
                    'tipe' => $tipe,
                    'pcs' => $pcs,
                    'kg' => (float) $l->kg,
                    'kg_jual' => $kgJual,
                    'ikat' => (float) $l->ikat,
                    'rp_satuan' => (float) $l->rp_satuan,
                    'qty_setara' => $qtySetara,
                    'rata2' => $qtySetara > 0 ? $total / $qtySetara : 0,
                    'total' => $total,
                ];
            })->values(),
        ]);
    }

    /**
     * Rincian per nota untuk tabel telur (1 butir = 63 gram).
     * @return \Illuminate\Support\Collection<int, array{no_nota:string,tgl:string,lokasi:string,lokasi_raw:string,customer:string,tipe_jual:string,pcs:float,kg:float,qty_setara:float,total:float,rata2:float}>
     */
    private function telurDetail(string $tanggalAwal, string $tanggalAkhir, $telurRows)
    {
        if ($telurRows->isEmpty()) {
            return collect();
        }

        $items = DB::table('invoice_telur as i')
            ->leftJoin('customer as c', 'c.id_customer', '=', 'i.id_customer')
            ->whereIn('i.no_nota', $telurRows->pluck('no_nota')->unique()->all())
            ->whereIn('i.lokasi', $telurRows->pluck('lokasi_raw')->unique()->all())
            ->whereBetween('i.tgl', [$tanggalAwal, $tanggalAkhir])
            ->groupBy('i.no_nota', 'i.lokasi', 'i.tgl')
            ->select('i.no_nota', 'i.lokasi', 'i.tgl')
            ->selectRaw('MAX(c.nm_customer) as customer')
            ->selectRaw("SUM(CASE WHEN UPPER(i.tipe) = 'PCS' THEN i.pcs ELSE 0 END) as pcs")
            ->selectRaw("SUM(CASE WHEN UPPER(i.tipe) = 'KG' THEN i.kg_jual ELSE 0 END) as kg")
            ->selectRaw('SUM(i.total_rp) as total')
            ->orderByDesc('i.tgl')
            ->orderByDesc('i.no_nota')
            ->get();

        return $items->map(function ($item) {
            $pcs = (float) $item->pcs;
            $kg = (float) $item->kg;
            $total = (float) $item->total;
            $qtySetara = $kg + $pcs * 63 / 1000;

            return [
                'no_nota' => (string) $item->no_nota,
                'tgl' => (string) $item->tgl,
                'lokasi' => $this->lokasiLabel((string) ($item->lokasi ?? '')),
                'lokasi_raw' => (string) ($item->lokasi ?? ''),
                'customer' => trim((string) ($item->customer ?? '')) !== '' ? (string) $item->customer : '-',
                'tipe_jual' => $pcs > 0 && $kg > 0 ? 'Campuran' : ($pcs > 0 ? 'PCS' : 'KG'),
                'pcs' => $pcs,
                'kg' => $kg,
                'qty_setara' => $qtySetara,
                'total' => $total,
                'rata2' => $qtySetara > 0 ? $total / $qtySetara : 0,
            ];
        })->values();
    }
}
