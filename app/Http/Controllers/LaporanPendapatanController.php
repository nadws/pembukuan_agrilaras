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
            ? $base->filter(fn ($row) => ! empty(array_intersect($pembayaranIds, $row['pembayaran_ids'] ?? [])))->values()
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

        $rows = $this->attachPembayaran($rows);

        return $rows->sortBy([['tgl', 'desc'], ['no_nota', 'desc']])->values();
    }

    private function attachPembayaran($rows)
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
            ->select('j.nomor_transaksi', 'j.id_akun_perkiraan', 'a.kode_perkiraan', 'a.nama')
            ->get()
            ->groupBy('nomor_transaksi');

        $piutang = $this->piutangFallback();

        return $rows->map(function ($row) use ($pembayaran, $piutang) {
            $key = (($row['kategori'] ?? '') === 'umum' && isset($row['urutan']))
                ? 'PUM-'.$row['urutan']
                : $row['no_nota'];
            $candidates = $pembayaran->get($key, collect());
            $label = $candidates
                ->map(fn ($item) => trim(collect([$item->kode_perkiraan ?? '', $item->nama ?? ''])->filter()->implode(' - ')))
                ->filter()->unique()->implode(', ');
            // Tanpa metode pembayaran = masuk Piutang Usaha IDR.
            if ($label === '' && $piutang) {
                $row['pembayaran'] = $piutang['label'];
                $row['pembayaran_ids'] = [$piutang['id']];

                return $row;
            }
            $row['pembayaran'] = $label !== '' ? $label : '-';
            $row['pembayaran_ids'] = $candidates->pluck('id_akun_perkiraan')->map(fn ($id) => (int) $id)->unique()->values()->all();

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
        return $rows->groupBy(fn ($row) => trim((string) ($row['pembayaran'] ?? '')) !== '' ? (string) $row['pembayaran'] : '-')
            ->map(fn ($group, $label) => [
                'pembayaran' => $label,
                'jumlah' => $group->count(),
                'total' => (float) $group->sum('total'),
            ])
            ->sortByDesc('total')
            ->values();
    }

    /**
     * Rangkuman per produk dari nota-nota yang sudah tersaring filter.
     * @return \Illuminate\Support\Collection<int, array{produk:string,tipe:string,pcs:float,kg:float,total:float}>
     */
    private function productSummary($rows)
    {
        $summary = collect();

        $telurRows = $rows->where('kategori', 'telur');
        if ($telurRows->isNotEmpty()) {
            $items = DB::table('invoice_telur as i')
                ->leftJoin('telur_produk as p', 'p.id_produk_telur', '=', 'i.id_produk')
                ->whereIn('i.no_nota', $telurRows->pluck('no_nota')->unique()->all())
                ->whereIn('i.lokasi', $telurRows->pluck('lokasi_raw')->unique()->all())
                ->groupBy('i.id_produk', 'i.tipe')
                ->select('i.id_produk')
                ->selectRaw('MAX(p.nm_telur) as nama, MAX(i.tipe) as tipe_jual')
                ->selectRaw('SUM(i.pcs) as pcs, SUM(i.kg_jual) as kg, SUM(i.total_rp) as total')
                ->orderBy('nama')->orderBy('tipe_jual')
                ->get();
            foreach ($items as $item) {
                $nama = trim((string) ($item->nama ?? '')) !== '' ? (string) $item->nama : 'Telur (tanpa nama)';
                $tipeJual = strtoupper(trim((string) ($item->tipe_jual ?? '')));
                if (! in_array($tipeJual, ['PCS', 'KG'], true)) {
                    $tipeJual = '-';
                }
                $summary->push([
                    'produk' => $nama.' ('.$tipeJual.')',
                    'tipe' => 'telur',
                    'pcs' => (float) $item->pcs,
                    'kg' => (float) $item->kg,
                    'total' => (float) $item->total,
                ]);
            }
        }

        $ayamRows = $rows->where('kategori', 'ayam');
        if ($ayamRows->isNotEmpty()) {
            $total = DB::table('invoice_ayam as i')
                ->whereIn('i.no_nota', $ayamRows->pluck('no_nota')->unique()->all())
                ->whereIn('i.lokasi', $ayamRows->pluck('lokasi_raw')->unique()->all())
                ->selectRaw('SUM(i.qty) as pcs, SUM(i.qty * i.h_satuan) as total')
                ->first();
            $summary->push([
                'produk' => 'Ayam',
                'tipe' => 'ayam',
                'pcs' => (float) ($total->pcs ?? 0),
                'kg' => 0,
                'total' => (float) ($total->total ?? 0),
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
                $summary->push([
                    'produk' => trim((string) ($item->nama ?? '')) !== '' ? (string) $item->nama : 'Umum (tanpa nama)',
                    'tipe' => 'umum',
                    'pcs' => (float) $item->pcs,
                    'kg' => 0,
                    'total' => (float) $item->total,
                ]);
            }
        }

        return $summary->sortBy([['tipe', 'asc'], ['produk', 'asc']])->values();
    }
}
