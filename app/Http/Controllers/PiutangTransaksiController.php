<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class PiutangTransaksiController extends Controller
{
    public function index(Request $request)
    {
        $jenis = in_array($request->input('jenis'), ['telur', 'ayam', 'umum'], true) ? $request->input('jenis') : 'telur';
        $filterKey = 'transaksi_piutang_filter.' . $jenis;
        $savedFilter = (array) session($filterKey, []);
        $awal = $request->input('tanggal_awal', $savedFilter['tanggal_awal'] ?? date('Y-m-01'));
        $akhir = $request->input('tanggal_akhir', $savedFilter['tanggal_akhir'] ?? date('Y-m-d'));
        $cari = trim((string) $request->input('cari', $savedFilter['cari'] ?? ''));
        session()->put($filterKey, ['tanggal_awal' => $awal, 'tanggal_akhir' => $akhir, 'cari' => $cari]);

        if ($jenis === 'ayam') {
            $piutangQuery = DB::table('invoice_ayam as i')
                ->leftJoin('customer as c', 'c.id_customer', '=', 'i.id_customer')
                ->where('i.lokasi', 'alpa')->where('i.status', 'unpaid')
                ->when($cari !== '', fn ($q) => $q->where(fn ($s) => $s->where('i.no_nota', 'like', "%{$cari}%")->orWhere('c.nm_customer', 'like', "%{$cari}%")))
                ->select('i.no_nota', 'i.tgl', 'i.id_customer', 'i.qty', 'i.h_satuan', 'c.nm_customer', DB::raw('i.qty * i.h_satuan as total_rp'))
                ->orderByDesc('i.tgl')->orderByDesc('i.urutan');
        } elseif ($jenis === 'umum') {
            $piutangQuery = DB::table('penjualan_agl as i')
                ->leftJoin('customer as c', 'c.id_customer', '=', 'i.id_customer')
                ->where('i.lokasi', 'alpa')->where('i.status', 'unpaid')
                ->when($cari !== '', fn ($q) => $q->where(fn ($s) => $s->where('i.urutan', 'like', "%{$cari}%")->orWhere('c.nm_customer', 'like', "%{$cari}%")))
                ->select(DB::raw("CONCAT('PU-', i.urutan) as no_nota"), 'i.tgl', 'i.id_customer', 'c.nm_customer', DB::raw('SUM(i.total_rp) as total_rp'), DB::raw('SUM(i.qty) as qty'))
                ->groupBy('i.urutan', 'i.tgl', 'i.id_customer', 'c.nm_customer')
                ->orderByDesc('i.tgl')->orderByDesc('i.urutan');
        } else {
            $piutangQuery = DB::table('invoice_telur as i')
                ->leftJoin('customer as c', 'c.id_customer', '=', 'i.id_customer')
                ->whereIn('i.lokasi', ['alpa', 'mtd'])->where('i.status', 'unpaid')
                ->when($cari !== '', fn ($q) => $q->where(fn ($s) => $s->where('i.no_nota', 'like', "%{$cari}%")->orWhere('c.nm_customer', 'like', "%{$cari}%")))
                ->select('i.no_nota', 'i.tgl', 'i.id_customer', 'i.tipe', 'c.nm_customer', DB::raw('SUM(i.total_rp) as total_rp'))
                ->groupBy('i.no_nota', 'i.tgl', 'i.id_customer', 'i.tipe', 'c.nm_customer')
                ->orderByDesc('i.tgl')->orderByDesc('i.no_nota');
        }

        $piutangPaginator = $piutangQuery->paginate(50);
        $piutang = $piutangPaginator->getCollection();

        $paidByNota = DB::table('pelunasan_piutang_penjualan')
            ->where('jenis', $jenis)
            ->where('tanggal_bayar', '<=', $akhir)
            ->whereIn('no_nota', $piutang->pluck('no_nota')->all())
            ->groupBy('no_nota')
            ->pluck(DB::raw('SUM(COALESCE(nilai_piutang_dilunasi, jumlah_bayar))'), 'no_nota');
        $piutang = $piutang->map(function ($item) use ($paidByNota) {
            $item->nilai_piutang = (float) $item->total_rp;
            $item->jumlah_dibayar = min($item->nilai_piutang, (float) ($paidByNota[$item->no_nota] ?? 0));
            $item->sisa_piutang = max(0, $item->nilai_piutang - $item->jumlah_dibayar);
            // Keep total_rp as the outstanding value for older view consumers.
            $item->total_rp = $item->sisa_piutang;
            return $item;
        })->filter(fn ($item) => $item->total_rp > 0.005)->values();

        $totalNilaiPiutang = (float) $piutang->sum('nilai_piutang');
        $totalDibayar = (float) $piutang->sum('jumlah_dibayar');
        $totalPiutang = (float) $piutang->sum('total_rp');
        $jumlahFaktur = $piutang->pluck('no_nota')->unique()->count();
        $tabFilters = collect(['telur', 'ayam', 'umum'])->mapWithKeys(function ($tab) {
            $saved = (array) session('transaksi_piutang_filter.' . $tab, []);
            return [$tab => [
                'jenis' => $tab,
                'tanggal_awal' => $saved['tanggal_awal'] ?? date('Y-m-01'),
                'tanggal_akhir' => $saved['tanggal_akhir'] ?? date('Y-m-d'),
                'cari' => $saved['cari'] ?? '',
            ]];
        })->all();

        $riwayat = DB::table('pelunasan_piutang_penjualan as p')
            ->leftJoin('customer as c', 'c.id_customer', '=', 'p.id_customer')
            ->leftJoin('akun_perkiraan as a', 'a.id_akun_perkiraan', '=', 'p.id_akun_pembayaran')
            ->where('p.jenis', $jenis)
            ->whereBetween('p.tanggal_bayar', [$awal, $akhir])
            ->select('p.id', 'p.id_impor_jurnal_perkiraan', 'p.tanggal_bayar', 'p.no_nota', 'c.nm_customer', 'a.kode_perkiraan', 'a.nama as nama_akun', 'p.jumlah_bayar', 'p.nilai_piutang_dilunasi', 'p.jenis_selisih', 'p.selisih_pembayaran')
            ->orderByDesc('p.tanggal_bayar')->orderByDesc('p.id')
            ->get();
        $riwayat = $riwayat->groupBy(fn ($row) => $row->id_impor_jurnal_perkiraan ?: 'baris-'.$row->id)
            ->map(function ($notaRows) {
                $first = clone $notaRows->first();
                $first->nota_rows = $notaRows;
                $first->daftar_nota = $notaRows->pluck('no_nota')->unique()->implode(', ');
                $first->jumlah_bayar = (float) $notaRows->sum('jumlah_bayar');
                $first->nilai_piutang_dilunasi = (float) $notaRows->sum('nilai_piutang_dilunasi');
                $first->selisih_pembayaran = (float) $notaRows->sum('selisih_pembayaran');
                $first->jenis_selisih = $notaRows->pluck('jenis_selisih')->unique()->implode(', ');
                return $first;
            })->values();

        $batchIds = $riwayat->pluck('id_impor_jurnal_perkiraan')->filter()->values()->all();
        $allJurnal = collect();
        if ($batchIds !== []) {
            $allJurnal = DB::table('jurnal_perkiraan as j')
                ->leftJoin('akun_perkiraan as a', 'a.id_akun_perkiraan', '=', 'j.id_akun_perkiraan')
                ->whereIn('j.id_impor_jurnal_perkiraan', $batchIds)
                ->orderBy('j.urutan_detail')
                ->get(['j.id_impor_jurnal_perkiraan', 'j.nomor_transaksi', 'j.deskripsi', 'j.debit', 'j.kredit', 'a.kode_perkiraan', 'a.nama as nama_akun'])
                ->groupBy('id_impor_jurnal_perkiraan');
        }
        $riwayat->each(function ($row) use ($allJurnal) {
            $row->jurnal_detail = $row->id_impor_jurnal_perkiraan
                ? ($allJurnal[$row->id_impor_jurnal_perkiraan] ?? collect())
                : collect();
        });
        $totalRiwayat = (float) $riwayat->sum('jumlah_bayar');

        $btnImport = \SettingHal::btnHal(179, auth()->id());
        $btnRiwayat = \SettingHal::btnHal(180, auth()->id());
        $btnPelunasan = \SettingHal::btnHal(181, auth()->id());

        return view('transaksi.piutang.index', compact('jenis', 'awal', 'akhir', 'cari', 'piutang', 'piutangPaginator', 'totalNilaiPiutang', 'totalDibayar', 'totalPiutang', 'jumlahFaktur', 'tabFilters', 'riwayat', 'totalRiwayat', 'btnImport', 'btnRiwayat', 'btnPelunasan'));
    }

    public function importAccurate(Request $request)
    {
        $target = $request->attributes->get('accurate_target') === 'ayam' ? 'ayam' : 'telur';
        $targetTable = $target === 'ayam' ? 'invoice_ayam' : 'invoice_telur';
        $request->validate([
            'file_accurate' => ['required', 'file', 'mimes:xlsx,xls', 'max:10240'],
        ]);

        try {
            $spreadsheet = IOFactory::load($request->file('file_accurate')->getRealPath());
            $sheet = $spreadsheet->getActiveSheet();
            $highestRow = $sheet->getHighestDataRow();
            $highestColumn = $sheet->getHighestDataColumn();
            $rows = $sheet->rangeToArray("A1:{$highestColumn}{$highestRow}", null, true, false, false);
        } catch (\Throwable $e) {
            return back()->withErrors(['file_accurate' => 'File Accurate tidak dapat dibaca. Gunakan export Faktur Penjualan Belum Lunas berformat Excel.']);
        }

        $headerRow = null;
        $columns = [];
        foreach (array_slice($rows, 0, 10, true) as $rowIndex => $row) {
            foreach ($row as $columnIndex => $value) {
                $label = mb_strtolower(trim((string) $value));
                if ($label === 'pelanggan') $columns['pelanggan'] = $columnIndex;
                if (in_array($label, ['nomor #', 'nomor'], true)) $columns['nomor'] = $columnIndex;
                if ($label === 'tanggal') $columns['tanggal'] = $columnIndex;
                if ($label === 'jatuh tempo') $columns['jatuh_tempo'] = $columnIndex;
                if ($label === 'keterangan') $columns['keterangan'] = $columnIndex;
                if ($label === 'pelanggan') $headerRow = $rowIndex;
            }
        }
        if ($headerRow === null || !isset($columns['pelanggan'], $columns['nomor'], $columns['tanggal'])) {
            $spreadsheet->disconnectWorksheets();
            return back()->withErrors(['file_accurate' => 'Header Pelanggan, Nomor #, dan Tanggal tidak ditemukan pada file Accurate.']);
        }

        $piutangColumn = null;
        for ($r = $headerRow; $r <= min($headerRow + 2, count($rows) - 1); $r++) {
            foreach ($rows[$r] as $columnIndex => $value) {
                if (mb_strtolower(trim((string) $value)) === 'piutang') $piutangColumn = $columnIndex;
            }
        }
        if ($piutangColumn === null) {
            $spreadsheet->disconnectWorksheets();
            return back()->withErrors(['file_accurate' => 'Kolom Piutang tidak ditemukan. Pastikan laporan yang dipakai adalah Faktur Penjualan Belum Lunas.']);
        }

        $customerMap = DB::table('customer')->where('active', 'Y')->orderByDesc('id_customer')->get(['id_customer', 'nm_customer'])
            ->mapWithKeys(fn ($customer) => [$this->normalizeCustomer($customer->nm_customer) => $customer]);
        $existingNotes = DB::table($targetTable)->whereNotNull('no_nota')->pluck('no_nota')
            ->mapWithKeys(fn ($note) => [mb_strtoupper(trim((string) $note)) => true]);

        $currentCustomer = '';
        $data = [];
        $errors = [];
        $duplicates = 0;
        $dates = [];
        $nextSequence = ((int) DB::table($targetTable)->max('urutan')) + 1;

        for ($r = $headerRow + 2; $r < count($rows); $r++) {
            $row = $rows[$r];
            $customerCell = trim((string) ($row[$columns['pelanggan']] ?? ''));
            if ($customerCell !== '') $currentCustomer = $customerCell;
            $note = trim((string) ($row[$columns['nomor']] ?? ''));
            if ($note === '') continue;

            $description = mb_strtoupper(trim((string) ($row[$columns['keterangan']] ?? '')));
            if ($target === 'ayam' && !str_contains($description, 'AYAM')) continue;
            $amount = $this->numericExcelValue($row[$piutangColumn] ?? null);
            $date = $this->excelDateValue($row[$columns['tanggal']] ?? null);
            $customer = $customerMap->get($this->normalizeCustomer($currentCustomer));
            $validator = Validator::make([
                'pelanggan' => $currentCustomer,
                'nomor' => $note,
                'tanggal' => $date,
                'piutang' => $amount,
            ], [
                'pelanggan' => ['required'], 'nomor' => ['required', 'max:200'],
                'tanggal' => ['required', 'date'], 'piutang' => ['required', 'numeric', 'gt:0'],
            ]);
            if ($validator->fails()) {
                $errors[] = 'Baris ' . ($r + 1) . ': ' . implode(' ', $validator->errors()->all());
                continue;
            }
            if (!$customer) {
                $errors[] = 'Baris ' . ($r + 1) . ': customer "' . $currentCustomer . '" belum ada atau tidak aktif di Data Customer.';
                continue;
            }
            $noteKey = mb_strtoupper($note);
            if ($existingNotes->has($noteKey)) {
                $duplicates++;
                continue;
            }
            $existingNotes->put($noteKey, true);
            $dates[] = $date;
            if ($target === 'ayam') {
                $data[] = [
                    'tgl' => $date, 'id_customer' => $customer->id_customer, 'customer' => $currentCustomer,
                    'no_nota' => $note, 'qty' => 1, 'h_satuan' => $amount,
                    'admin' => auth()->user()->name ?? 'Import Accurate', 'urutan' => $nextSequence++,
                    'lokasi' => 'alpa', 'status' => 'unpaid', 'cek' => 'T', 'urutan_customer' => 0,
                    'admin_cek' => '', 'id_customer2' => 0, 'id_kandang' => 0,
                ];
            } else {
                $data[] = [
                    'tgl' => $date, 'id_customer' => $customer->id_customer, 'customer' => '', 'id_customer2' => 0,
                    'no_nota' => $note, 'id_produk' => 0, 'pcs' => 0, 'kg' => 0, 'ikat' => 0, 'kg_jual' => 0,
                    'rp_satuan' => 0, 'total_rp' => $amount, 'tipe' => 'kg', 'status' => 'unpaid',
                    'admin' => auth()->user()->name ?? 'Import Accurate', 'urutan' => $nextSequence++,
                    'urutan_customer' => 0, 'driver' => 'Import Accurate', 'lokasi' => 'alpa', 'cek' => 'T',
                    'admin_cek' => '', 'void' => 'T', 'import' => 'Y',
                ];
            }
        }
        $spreadsheet->disconnectWorksheets();

        if ($errors !== []) {
            return back()->withErrors(['file_accurate' => implode(' | ', array_slice($errors, 0, 20))]);
        }
        if ($data === []) {
            $message = $duplicates > 0
                ? 'Semua faktur pada file sudah pernah diimpor.'
                : 'Tidak ada piutang ' . $target . ' yang dapat diimpor dari file.';
            return back()->withErrors(['file_accurate' => $message]);
        }

        DB::transaction(fn () => collect($data)->chunk(200)->each(fn ($chunk) => DB::table($targetTable)->insert($chunk->all())));
        $message = count($data) . ' faktur Piutang ' . ucfirst($target) . ' Accurate berhasil diimpor.';
        if ($duplicates) $message .= ' ' . $duplicates . ' faktur duplikat dilewati.';

        return redirect()->route('transaksi.piutang.index', [
            'jenis' => $target,
            'tanggal_awal' => min($dates),
            'tanggal_akhir' => max($dates),
        ])->with('sukses', $message);
    }

    public function importAccurateAyam(Request $request)
    {
        $request->attributes->set('accurate_target', 'ayam');
        return $this->importAccurate($request);
    }

    private function normalizeCustomer(?string $value): string
    {
        return mb_strtoupper(preg_replace('/\s+/u', ' ', trim((string) $value)));
    }

    private function numericExcelValue(mixed $value): float
    {
        if (is_numeric($value)) return (float) $value;
        $clean = preg_replace('/[^0-9,.-]/', '', (string) $value);
        if (substr_count($clean, ',') === 1 && substr_count($clean, '.') === 0) $clean = str_replace(',', '.', $clean);
        else $clean = str_replace(',', '', $clean);
        return (float) $clean;
    }

    private function excelDateValue(mixed $value): ?string
    {
        if (is_numeric($value)) return ExcelDate::excelToDateTimeObject((float) $value)->format('Y-m-d');
        $timestamp = strtotime(trim((string) $value));
        return $timestamp === false ? null : date('Y-m-d', $timestamp);
    }

    public function pelunasan(Request $request)
    {
        $jenis = in_array($request->input('jenis'), ['telur', 'ayam', 'umum'], true) ? $request->input('jenis') : 'telur';
        $nota = array_values(array_unique((array) $request->input('nota', [])));

        if ($nota === []) {
            return redirect()->route('transaksi.piutang.index', ['jenis' => $jenis])->withErrors(['nota' => 'Pilih minimal satu nota untuk dilunasi.']);
        }

        $table = $jenis === 'ayam' ? 'invoice_ayam' : ($jenis === 'umum' ? 'penjualan_agl' : 'invoice_telur');
        $notaIds = $jenis === 'umum' ? array_map(fn ($value) => (int) str_replace('PU-', '', $value), $nota) : $nota;
        if ($jenis === 'umum') {
            $rows = DB::table('penjualan_agl as i')->leftJoin('customer as c', 'c.id_customer', '=', 'i.id_customer')->where('i.lokasi', 'alpa')->where('i.status', 'unpaid')->whereIn('i.urutan', $notaIds)->select('i.*', DB::raw("CONCAT('PU-', i.urutan) as no_nota"), 'c.nm_customer')->orderBy('i.tgl')->orderBy('i.urutan')->get();
        } else {
            $query = DB::table($table . ' as i')
                ->leftJoin('customer as c', 'c.id_customer', '=', 'i.id_customer')
                ->where('i.status', 'unpaid')->whereIn('i.no_nota', $nota);
            $jenis === 'telur'
                ? $query->whereIn('i.lokasi', ['alpa', 'mtd'])
                : $query->where('i.lokasi', 'alpa');
            $rows = $query->select('i.*', 'c.nm_customer')->orderBy('i.tgl')->orderBy('i.no_nota')->get();
        }

        if ($rows->isEmpty() || $rows->pluck('no_nota')->unique()->count() !== count($nota) || $rows->pluck('id_customer')->unique()->count() !== 1) {
            return redirect()->route('transaksi.piutang.index', ['jenis' => $jenis])->withErrors(['nota' => 'Nota harus masih belum lunas dan berasal dari customer yang sama.']);
        }

        $paidByNota = DB::table('pelunasan_piutang_penjualan')
            ->where('jenis', $jenis)->whereIn('no_nota', $nota)
            ->groupBy('no_nota')->pluck(DB::raw('SUM(COALESCE(nilai_piutang_dilunasi, jumlah_bayar))'), 'no_nota');
        $noteSummaries = $rows->groupBy('no_nota')->map(function ($items, $noNota) use ($jenis, $paidByNota) {
            $invoiceTotal = $jenis === 'ayam'
                ? $items->sum(fn ($row) => (float) $row->qty * (float) $row->h_satuan)
                : $items->sum(fn ($row) => (float) $row->total_rp);
            $paid = (float) ($paidByNota[$noNota] ?? 0);

            return (object) [
                'item' => $items->first(), 'items' => $items,
                'invoice_total' => $invoiceTotal, 'paid' => $paid,
                'outstanding' => max(0, $invoiceTotal - $paid),
            ];
        });
        if ($noteSummaries->contains(fn ($summary) => $summary->outstanding <= 0.005)) {
            return redirect()->route('transaksi.piutang.index', ['jenis' => $jenis])
                ->withErrors(['nota' => 'Salah satu nota sudah lunas. Silakan pilih ulang nota.']);
        }
        $total = $noteSummaries->sum('outstanding');
        $akunPembayaran = DB::table('akun_perkiraan')->where('aktif', 1)->where('tipe_akun', 'BANK')->orderBy('kode_perkiraan')->get(['id_akun_perkiraan', 'kode_perkiraan', 'nama']);

        return view('transaksi.piutang.pelunasan', compact('jenis', 'nota', 'rows', 'noteSummaries', 'total', 'akunPembayaran'));
    }

    public function storePelunasan(Request $request)
    {
        $validated = $request->validate([
            'jenis' => ['required', 'in:telur,ayam,umum'],
            'tanggal_bayar' => ['required', 'date'],
            'id_akun_pembayaran' => ['required', 'exists:akun_perkiraan,id_akun_perkiraan'],
            'nota' => ['required', 'array', 'min:1'],
            'nota.*' => ['required', 'string', 'max:100', 'distinct'],
            'jumlah_bayar' => ['required', 'array', 'min:1'],
            'jumlah_bayar.*' => ['required', 'numeric', 'gt:0'],
            'jenis_selisih' => ['required', 'array', 'min:1'],
            'jenis_selisih.*' => ['required', 'in:tidak,lebih,kurang'],
        ]);

        $rowCount = count($validated['nota']);
        if ($rowCount !== count($validated['jumlah_bayar']) || $rowCount !== count($validated['jenis_selisih'])) {
            return back()->withErrors(['jumlah_bayar' => 'Data pembayaran setiap nota belum lengkap.'])->withInput();
        }

        $akunPembayaran = DB::table('akun_perkiraan')
            ->where('id_akun_perkiraan', $validated['id_akun_pembayaran'])
            ->where('aktif', 1)
            ->where('tipe_akun', 'BANK')
            ->first();

        if (! $akunPembayaran) {
            return back()->withErrors(['id_akun_pembayaran' => 'Pilih akun kas atau bank yang aktif.'])->withInput();
        }

        $table = $validated['jenis'] === 'ayam' ? 'invoice_ayam' : ($validated['jenis'] === 'umum' ? 'penjualan_agl' : 'invoice_telur');
        $tipeJurnal = $validated['jenis'] === 'ayam' ? 'Pelunasan Piutang Ayam' : ($validated['jenis'] === 'umum' ? 'Pelunasan Piutang Umum' : 'Pelunasan Piutang Telur');
        $nota = array_values(array_unique($validated['nota']));
        $notaIds = $validated['jenis'] === 'umum' ? array_map(fn ($value) => (int) str_replace('PU-', '', $value), $nota) : $nota;
        if ($validated['jenis'] === 'umum') {
            $rows = DB::table('penjualan_agl as i')->leftJoin('customer as c', 'c.id_customer', '=', 'i.id_customer')->where('i.lokasi', 'alpa')->where('i.status', 'unpaid')->whereIn('i.urutan', $notaIds)->select('i.*', DB::raw("CONCAT('PU-', i.urutan) as no_nota"), 'c.nm_customer')->get();
        } else {
            $query = DB::table($table . ' as i')
                ->leftJoin('customer as c', 'c.id_customer', '=', 'i.id_customer')
                ->where('i.status', 'unpaid')
                ->whereIn('i.no_nota', $nota);
            $validated['jenis'] === 'telur'
                ? $query->whereIn('i.lokasi', ['alpa', 'mtd'])
                : $query->where('i.lokasi', 'alpa');
            $rows = $query->select('i.*', 'c.nm_customer')->get();
        }

        if ($rows->isEmpty() || $rows->pluck('no_nota')->unique()->count() !== count($nota)) {
            return back()->withErrors(['nota' => 'Sebagian nota sudah lunas atau tidak ditemukan. Silakan muat ulang halaman.'])->withInput();
        }

        if ($rows->pluck('id_customer')->unique()->count() !== 1) {
            return back()->withErrors(['nota' => 'Nota yang dilunasi harus berasal dari customer yang sama.'])->withInput();
        }

        $paidByNota = DB::table('pelunasan_piutang_penjualan')
            ->where('jenis', $validated['jenis'])->whereIn('no_nota', $nota)
            ->groupBy('no_nota')->pluck(DB::raw('SUM(COALESCE(nilai_piutang_dilunasi, jumlah_bayar))'), 'no_nota');
        $cashPayments = collect($validated['nota'])->mapWithKeys(
            fn ($noNota, $index) => [$noNota => (float) $validated['jumlah_bayar'][$index]]
        );
        $differenceTypes = collect($validated['nota'])->mapWithKeys(
            fn ($noNota, $index) => [$noNota => $validated['jenis_selisih'][$index]]
        );
        $outstandingByNota = $rows->groupBy('no_nota')->map(function ($items, $noNota) use ($validated, $paidByNota) {
            $invoiceTotal = $validated['jenis'] === 'ayam'
                ? $items->sum(fn ($row) => (float) $row->qty * (float) $row->h_satuan)
                : $items->sum(fn ($row) => (float) $row->total_rp);
            return max(0, $invoiceTotal - (float) ($paidByNota[$noNota] ?? 0));
        });
        $settledPayments = collect();
        $differences = collect();
        foreach ($cashPayments as $noNota => $cashAmount) {
            $outstanding = (float) ($outstandingByNota[$noNota] ?? 0);
            $type = $differenceTypes[$noNota];
            if ($outstanding <= 0.005) {
                return back()->withErrors(['nota' => "Nota {$noNota} sudah lunas."])->withInput();
            }
            if ($type === 'tidak' && $cashAmount - $outstanding > 0.005) {
                return back()->withErrors(['jumlah_bayar' => "Bayar nota {$noNota} melebihi sisa. Pilih Lebih Bayar jika memang ada selisih."])->withInput();
            }
            if ($type === 'lebih' && $cashAmount - $outstanding <= 0.005) {
                return back()->withErrors(['jumlah_bayar' => "Nominal nota {$noNota} harus lebih besar dari sisa untuk pilihan Lebih Bayar."])->withInput();
            }
            if ($type === 'kurang' && $outstanding - $cashAmount <= 0.005) {
                return back()->withErrors(['jumlah_bayar' => "Nominal nota {$noNota} harus lebih kecil dari sisa untuk pilihan Kurang Bayar."])->withInput();
            }

            $settled = $type === 'tidak' ? $cashAmount : $outstanding;
            $settledPayments->put($noNota, $settled);
            $differences->put($noNota, [
                'type' => $type,
                'amount' => $type === 'lebih' ? $cashAmount - $outstanding : ($type === 'kurang' ? $outstanding - $cashAmount : 0),
            ]);
        }
        $total = (float) $settledPayments->sum();
        $totalMore = (float) $differences->where('type', 'lebih')->sum('amount');
        $totalLess = (float) $differences->where('type', 'kurang')->sum('amount');
        $cashTotal = (float) $cashPayments->sum();
        $akunSelisihLebih = null;
        $akunSelisihKurang = null;
        if ($totalMore > 0) {
            $akunSelisihLebih = DB::table('akun_perkiraan')->where('aktif', 1)
                ->where('nama', 'Pendapatan Selisih Lebih Bayar')->first();
            if (! $akunSelisihLebih) {
                return back()->withErrors(['selisih' => 'Akun Pendapatan Selisih Lebih Bayar belum tersedia atau tidak aktif.'])->withInput();
            }
        }
        if ($totalLess > 0) {
            $akunSelisihKurang = DB::table('akun_perkiraan')->where('aktif', 1)
                ->where('nama', 'Biaya Selisih Kurang Bayar')->first();
            if (! $akunSelisihKurang) {
                return back()->withErrors(['selisih' => 'Akun Biaya Selisih Kurang Bayar belum tersedia atau tidak aktif.'])->withInput();
            }
        }
        $akunPiutang = DB::table('jurnal_perkiraan as j')
            ->whereIn('j.nomor_transaksi', $nota)
            ->where('j.tipe_transaksi', $validated['jenis'] === 'ayam' ? 'Penjualan Ayam' : ($validated['jenis'] === 'umum' ? 'Penjualan Umum' : 'Penjualan Telur'))
            ->where('j.debit', '>', 0)
            ->orderBy('j.id_jurnal_perkiraan')
            ->first(['j.id_akun_perkiraan']);
        $akunPiutang ??= DB::table('akun_perkiraan')
            ->where('aktif', 1)
            ->where('tipe_akun', 'AREC')
            ->orderBy('kode_perkiraan')
            ->first(['id_akun_perkiraan']);

        if (! $akunPiutang) {
            return back()->withErrors(['nota' => 'Akun piutang aktif belum tersedia.'])->withInput();
        }

        DB::transaction(function () use ($validated, $rows, $table, $akunPembayaran, $akunPiutang, $akunSelisihLebih, $akunSelisihKurang, $total, $totalMore, $totalLess, $cashTotal, $tipeJurnal, $nota, $cashPayments, $settledPayments, $differences, $outstandingByNota) {
            $now = now();
            $nomorTransaksi = 'PL-' . strtoupper($validated['jenis']) . '-' . $now->format('YmdHis');
            $batchId = DB::table('impor_jurnal_perkiraan')->insertGetId([
                'nama_file' => 'Pelunasan piutang ' . strtoupper($validated['jenis']),
                'hash_file' => hash('sha256', 'pelunasan-piutang|' . $nomorTransaksi),
                'periode_awal' => $validated['tanggal_bayar'],
                'periode_akhir' => $validated['tanggal_bayar'],
                'jumlah_transaksi' => count($nota),
                'jumlah_detail' => 2 + ($totalMore > 0 ? 1 : 0) + ($totalLess > 0 ? 1 : 0),
                'total_debit' => $cashTotal + $totalLess,
                'total_kredit' => $total + $totalMore,
                'status' => 'aktif',
                'diimpor_oleh' => auth()->id(),
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $journalRows = [
                ['id_impor_jurnal_perkiraan' => $batchId, 'id_akun_perkiraan' => $akunPembayaran->id_akun_perkiraan, 'tanggal' => $validated['tanggal_bayar'], 'nomor_transaksi' => $nomorTransaksi, 'tipe_transaksi' => $tipeJurnal, 'urutan_detail' => 1, 'deskripsi' => 'Penerimaan pembayaran piutang ' . implode(', ', $nota), 'debit' => $cashTotal, 'kredit' => 0, 'created_at' => $now, 'updated_at' => $now],
                ['id_impor_jurnal_perkiraan' => $batchId, 'id_akun_perkiraan' => $akunPiutang->id_akun_perkiraan, 'tanggal' => $validated['tanggal_bayar'], 'nomor_transaksi' => $nomorTransaksi, 'tipe_transaksi' => $tipeJurnal, 'urutan_detail' => 2, 'deskripsi' => 'Pelunasan piutang ' . implode(', ', $nota), 'debit' => 0, 'kredit' => $total, 'created_at' => $now, 'updated_at' => $now],
            ];
            if ($totalMore > 0) {
                $journalRows[] = ['id_impor_jurnal_perkiraan' => $batchId, 'id_akun_perkiraan' => $akunSelisihLebih->id_akun_perkiraan, 'tanggal' => $validated['tanggal_bayar'], 'nomor_transaksi' => $nomorTransaksi, 'tipe_transaksi' => $tipeJurnal, 'urutan_detail' => count($journalRows) + 1, 'deskripsi' => 'Pendapatan selisih lebih bayar ' . implode(', ', $nota), 'debit' => 0, 'kredit' => $totalMore, 'created_at' => $now, 'updated_at' => $now];
            }
            if ($totalLess > 0) {
                $journalRows[] = ['id_impor_jurnal_perkiraan' => $batchId, 'id_akun_perkiraan' => $akunSelisihKurang->id_akun_perkiraan, 'tanggal' => $validated['tanggal_bayar'], 'nomor_transaksi' => $nomorTransaksi, 'tipe_transaksi' => $tipeJurnal, 'urutan_detail' => count($journalRows) + 1, 'deskripsi' => 'Biaya selisih kurang bayar ' . implode(', ', $nota), 'debit' => $totalLess, 'kredit' => 0, 'created_at' => $now, 'updated_at' => $now];
            }
            DB::table('jurnal_perkiraan')->insert($journalRows);

            $paymentRows = $rows->groupBy('no_nota')->map(function ($items, $noNota) use ($validated, $akunPembayaran, $batchId, $cashPayments, $settledPayments, $differences) {
                return [
                    'jenis' => $validated['jenis'],
                    'no_nota' => $noNota,
                    'id_customer' => $items->first()->id_customer,
                    'tanggal_bayar' => $validated['tanggal_bayar'],
                    'jumlah_bayar' => $cashPayments[$noNota],
                    'nilai_piutang_dilunasi' => $settledPayments[$noNota],
                    'jenis_selisih' => $differences[$noNota]['type'],
                    'selisih_pembayaran' => $differences[$noNota]['amount'],
                    'id_akun_pembayaran' => $akunPembayaran->id_akun_perkiraan,
                    'id_impor_jurnal_perkiraan' => $batchId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })->values()->all();
            DB::table('pelunasan_piutang_penjualan')->insert($paymentRows);

            foreach ($nota as $noNota) {
                if ($outstandingByNota[$noNota] - $settledPayments[$noNota] > 0.005) continue;
                if ($validated['jenis'] === 'umum') {
                    DB::table($table)->where('lokasi', 'alpa')
                        ->where('urutan', (int) str_replace('PU-', '', $noNota))->update(['status' => 'paid']);
                } else {
                    $update = DB::table($table)->where('no_nota', $noNota);
                    $validated['jenis'] === 'telur'
                        ? $update->whereIn('lokasi', ['alpa', 'mtd'])
                        : $update->where('lokasi', 'alpa');
                    $update->update(['status' => 'paid']);
                }
            }
        });

        return redirect()->route('transaksi.piutang.index', ['jenis' => $validated['jenis']])->with('sukses', 'Pembayaran piutang berhasil disimpan. Nota yang masih memiliki sisa tetap dapat dicicil.');
    }

    public function editVoucher(Request $request, int $id)
    {
        $payment = DB::table('pelunasan_piutang_penjualan')->where('id', $id)->first();
        abort_unless($payment, 404);
        $payments = DB::table('pelunasan_piutang_penjualan')->when($payment->id_impor_jurnal_perkiraan,
            fn ($q) => $q->where('id_impor_jurnal_perkiraan', $payment->id_impor_jurnal_perkiraan),
            fn ($q) => $q->where('id', $id))->orderBy('id')->get();
        $jenis = $payment->jenis;
        $noteSummaries = $payments->mapWithKeys(function ($p) use ($jenis) {
            $table = $jenis === 'ayam' ? 'invoice_ayam' : ($jenis === 'umum' ? 'penjualan_agl' : 'invoice_telur');
            $items = DB::table($table.' as i')->leftJoin('customer as c', 'c.id_customer', '=', 'i.id_customer')
                ->when($jenis === 'umum', fn ($q) => $q->where('i.urutan', (int) str_replace('PU-', '', $p->no_nota)), fn ($q) => $q->where('i.no_nota', $p->no_nota))
                ->select('i.*', 'c.nm_customer')->get();
            abort_if($items->isEmpty(), 404, 'Nota asal tidak ditemukan.');
            $paid = (float) DB::table('pelunasan_piutang_penjualan')->where('jenis', $jenis)->where('no_nota', $p->no_nota)->where('id', '<>', $p->id)->sum(DB::raw('COALESCE(nilai_piutang_dilunasi, jumlah_bayar)'));
            $total = $this->invoiceTotal($jenis, $p->no_nota);
            return [$p->no_nota => (object) ['item' => $items->first(), 'items' => $items, 'invoice_total' => $total, 'paid' => $paid, 'outstanding' => max(0, $total - $paid), 'payment' => $p]];
        });
        $akunPembayaran = DB::table('akun_perkiraan')->where('aktif', 1)->where('tipe_akun', 'BANK')->orderBy('kode_perkiraan')->get();
        return view('transaksi.piutang.pelunasan', compact('jenis', 'noteSummaries', 'akunPembayaran', 'payment'));
    }

    public function updateVoucher(Request $request, int $id)
    {
        $request->validate(['tanggal_bayar' => 'required|date', 'id_akun_pembayaran' => 'required|integer', 'jumlah_bayar' => 'required|array', 'jumlah_bayar.*' => 'required|numeric|gt:0', 'jenis_selisih' => 'required|array', 'jenis_selisih.*' => 'required|in:tidak,lebih,kurang']);
        return DB::transaction(function () use ($request, $id) {
            $payment = DB::table('pelunasan_piutang_penjualan')->where('id', $id)->lockForUpdate()->first();
            abort_unless($payment, 404);
            $payments = DB::table('pelunasan_piutang_penjualan')->when($payment->id_impor_jurnal_perkiraan,
                fn ($q) => $q->where('id_impor_jurnal_perkiraan', $payment->id_impor_jurnal_perkiraan), fn ($q) => $q->where('id', $id))->orderBy('id')->lockForUpdate()->get();
            if (count($request->jumlah_bayar) !== $payments->count() || count($request->jenis_selisih) !== $payments->count()) {
                throw \Illuminate\Validation\ValidationException::withMessages(['jumlah_bayar' => 'Data nota tidak lengkap. Muat ulang halaman edit.']);
            }
            foreach ($payments as $index => $p) {
                $input = new Request(['tanggal_bayar' => $request->tanggal_bayar, 'id_akun_pembayaran' => $request->id_akun_pembayaran, 'jumlah_bayar' => $request->jumlah_bayar[$index], 'jenis_selisih' => $request->jenis_selisih[$index]]);
                $response = $this->updatePelunasan($input, $p->id);
                if (session()->has('errors')) {
                    $errors = session()->get('errors')->getBag('default')->messages();
                    session()->forget('errors');
                    throw \Illuminate\Validation\ValidationException::withMessages($errors);
                }
            }
            return $response;
        });
    }

    public function editPelunasan(Request $request, int $id)
    {
        $row = DB::table('pelunasan_piutang_penjualan as p')
            ->leftJoin('customer as c', 'c.id_customer', '=', 'p.id_customer')
            ->where('p.id', $id)
            ->select('p.*', 'c.nm_customer')
            ->first();
        abort_unless($row, 404);

        $jenis = $row->jenis;
        $invoiceTotal = $this->invoiceTotal($jenis, $row->no_nota);
        abort_unless($invoiceTotal !== null, 404, 'Nota asal tidak ditemukan.');
        $othersSettled = (float) DB::table('pelunasan_piutang_penjualan')
            ->where('jenis', $jenis)->where('no_nota', $row->no_nota)->where('id', '<>', $id)
            ->sum(DB::raw('COALESCE(nilai_piutang_dilunasi, jumlah_bayar)'));
        $outstanding = max(0, $invoiceTotal - $othersSettled);

        $akunPembayaran = DB::table('akun_perkiraan')->where('aktif', 1)->where('tipe_akun', 'BANK')->orderBy('kode_perkiraan')->get(['id_akun_perkiraan', 'kode_perkiraan', 'nama']);

        return view('transaksi.piutang.edit_pelunasan', [
            'row' => $row, 'jenis' => $jenis,
            'invoiceTotal' => $invoiceTotal, 'outstanding' => $outstanding,
            'akunPembayaran' => $akunPembayaran,
            'kembali' => $request->input('kembali', route('transaksi.piutang.index', ['jenis' => $jenis])),
        ]);
    }

    public function updatePelunasan(Request $request, int $id)
    {
        $validated = $request->validate([
            'tanggal_bayar' => ['required', 'date'],
            'id_akun_pembayaran' => ['required', 'exists:akun_perkiraan,id_akun_perkiraan'],
            'jumlah_bayar' => ['required', 'numeric', 'gt:0'],
            'jenis_selisih' => ['required', 'in:tidak,lebih,kurang'],
        ]);

        $row = DB::table('pelunasan_piutang_penjualan')->where('id', $id)->first();
        abort_unless($row, 404);
        $jenis = $row->jenis;

        $akunPembayaran = DB::table('akun_perkiraan')
            ->where('id_akun_perkiraan', $validated['id_akun_pembayaran'])
            ->where('aktif', 1)->where('tipe_akun', 'BANK')->first();
        abort_unless($akunPembayaran, 422, 'Pilih akun kas atau bank yang aktif.');

        $invoiceTotal = $this->invoiceTotal($jenis, $row->no_nota);
        abort_unless($invoiceTotal !== null, 404, 'Nota asal tidak ditemukan.');
        $othersSettled = (float) DB::table('pelunasan_piutang_penjualan')
            ->where('jenis', $jenis)->where('no_nota', $row->no_nota)->where('id', '<>', $id)
            ->sum(DB::raw('COALESCE(nilai_piutang_dilunasi, jumlah_bayar)'));
        $outstanding = max(0, $invoiceTotal - $othersSettled);
        if ($outstanding <= 0.005) {
            return back()->withErrors(['jumlah_bayar' => 'Nota sudah lunas oleh pembayaran lain.'])->withInput();
        }

        $cash = (float) $validated['jumlah_bayar'];
        $type = $validated['jenis_selisih'];
        if ($type === 'tidak' && $cash - $outstanding > 0.005) {
            return back()->withErrors(['jumlah_bayar' => 'Bayar melebihi sisa. Pilih Lebih Bayar jika memang ada selisih.'])->withInput();
        }
        if ($type === 'lebih' && $cash - $outstanding <= 0.005) {
            return back()->withErrors(['jumlah_bayar' => 'Nominal harus lebih besar dari sisa untuk pilihan Lebih Bayar.'])->withInput();
        }
        if ($type === 'kurang' && $outstanding - $cash <= 0.005) {
            return back()->withErrors(['jumlah_bayar' => 'Nominal harus lebih kecil dari sisa untuk pilihan Kurang Bayar.'])->withInput();
        }
        $settled = $type === 'tidak' ? $cash : $outstanding;
        $more = $type === 'lebih' ? $cash - $outstanding : 0;
        $less = $type === 'kurang' ? $outstanding - $cash : 0;

        $akunSelisihLebih = DB::table('akun_perkiraan')->where('aktif', 1)->where('nama', 'Pendapatan Selisih Lebih Bayar')->first();
        $akunSelisihKurang = DB::table('akun_perkiraan')->where('aktif', 1)->where('nama', 'Biaya Selisih Kurang Bayar')->first();
        if ($more > 0 && ! $akunSelisihLebih) {
            return back()->withErrors(['selisih' => 'Akun Pendapatan Selisih Lebih Bayar belum tersedia atau tidak aktif.'])->withInput();
        }
        if ($less > 0 && ! $akunSelisihKurang) {
            return back()->withErrors(['selisih' => 'Akun Biaya Selisih Kurang Bayar belum tersedia atau tidak aktif.'])->withInput();
        }
        $lebihId = $akunSelisihLebih?->id_akun_perkiraan;
        $kurangId = $akunSelisihKurang?->id_akun_perkiraan;

        DB::transaction(function () use ($row, $jenis, $validated, $akunPembayaran, $lebihId, $kurangId, $cash, $settled, $more, $less, $invoiceTotal, $othersSettled) {
            $oldCash = (float) $row->jumlah_bayar;
            $oldSettled = (float) ($row->nilai_piutang_dilunasi ?? $row->jumlah_bayar);
            $oldMore = $row->jenis_selisih === 'lebih' ? (float) $row->selisih_pembayaran : 0;
            $oldLess = $row->jenis_selisih === 'kurang' ? (float) $row->selisih_pembayaran : 0;

            DB::table('pelunasan_piutang_penjualan')->where('id', $row->id)->update([
                'tanggal_bayar' => $validated['tanggal_bayar'],
                'jumlah_bayar' => $cash,
                'nilai_piutang_dilunasi' => $settled,
                'jenis_selisih' => $validated['jenis_selisih'],
                'selisih_pembayaran' => $validated['jenis_selisih'] === 'lebih' ? $more : ($validated['jenis_selisih'] === 'kurang' ? $less : 0),
                'id_akun_pembayaran' => $akunPembayaran->id_akun_perkiraan,
                'updated_at' => now(),
            ]);

            // Sesuaikan jurnal satu voucher (tanggal & akun kas berlaku untuk seluruh voucher).
            $batchId = $row->id_impor_jurnal_perkiraan;
            if ($batchId) {
                $selisihIds = collect([$lebihId, $kurangId])->filter()->values()->all();
                $cashLine = DB::table('jurnal_perkiraan')->where('id_impor_jurnal_perkiraan', $batchId)->where('debit', '>', 0)
                    ->when(! empty($selisihIds), fn ($q) => $q->whereNotIn('id_akun_perkiraan', $selisihIds))->orderBy('urutan_detail')->first();
                $piutangLine = DB::table('jurnal_perkiraan')->where('id_impor_jurnal_perkiraan', $batchId)->where('kredit', '>', 0)
                    ->when(! empty($selisihIds), fn ($q) => $q->whereNotIn('id_akun_perkiraan', $selisihIds))->orderBy('urutan_detail')->first();
                if ($cashLine) {
                    DB::table('jurnal_perkiraan')->where('id_jurnal_perkiraan', $cashLine->id_jurnal_perkiraan)->update([
                        'tanggal' => $validated['tanggal_bayar'],
                        'id_akun_perkiraan' => $akunPembayaran->id_akun_perkiraan,
                        'debit' => round((float) $cashLine->debit + ($cash - $oldCash), 2),
                        'updated_at' => now(),
                    ]);
                }
                if ($piutangLine) {
                    DB::table('jurnal_perkiraan')->where('id_jurnal_perkiraan', $piutangLine->id_jurnal_perkiraan)->update([
                        'tanggal' => $validated['tanggal_bayar'],
                        'kredit' => round((float) $piutangLine->kredit + ($settled - $oldSettled), 2),
                        'updated_at' => now(),
                    ]);
                }
                // Tulis ulang baris selisih (lebih → pendapatan, kurang → biaya).
                if (! empty($selisihIds)) {
                    DB::table('jurnal_perkiraan')->where('id_impor_jurnal_perkiraan', $batchId)->whereIn('id_akun_perkiraan', $selisihIds)->delete();
                }
                $voucherPayments = DB::table('pelunasan_piutang_penjualan')->where('id_impor_jurnal_perkiraan', $batchId)->get();
                $more = (float) $voucherPayments->where('jenis_selisih', 'lebih')->sum('selisih_pembayaran');
                $less = (float) $voucherPayments->where('jenis_selisih', 'kurang')->sum('selisih_pembayaran');
                DB::table('pelunasan_piutang_penjualan')->where('id_impor_jurnal_perkiraan', $batchId)->update(['tanggal_bayar' => $validated['tanggal_bayar'], 'id_akun_pembayaran' => $akunPembayaran->id_akun_perkiraan, 'updated_at' => now()]);
                DB::table('jurnal_perkiraan')->where('id_impor_jurnal_perkiraan', $batchId)->update(['tanggal' => $validated['tanggal_bayar'], 'updated_at' => now()]);
                $sekarang = now();
                $voucher = DB::table('jurnal_perkiraan')->where('id_impor_jurnal_perkiraan', $batchId)->first(['nomor_transaksi', 'tipe_transaksi']);
                $maxUrut = (int) DB::table('jurnal_perkiraan')->where('id_impor_jurnal_perkiraan', $batchId)->max('urutan_detail');
                if ($more > 0.005 && $lebihId && $voucher) {
                    DB::table('jurnal_perkiraan')->insert([
                        'id_impor_jurnal_perkiraan' => $batchId, 'id_akun_perkiraan' => $lebihId,
                        'tanggal' => $validated['tanggal_bayar'], 'nomor_transaksi' => $voucher->nomor_transaksi, 'tipe_transaksi' => $voucher->tipe_transaksi,
                        'urutan_detail' => $maxUrut + 1, 'deskripsi' => 'Pendapatan selisih lebih bayar (koreksi)',
                        'debit' => 0, 'kredit' => round($more, 2), 'created_at' => $sekarang, 'updated_at' => $sekarang,
                    ]);
                    $maxUrut++;
                }
                if ($less > 0.005 && $kurangId && $voucher) {
                    DB::table('jurnal_perkiraan')->insert([
                        'id_impor_jurnal_perkiraan' => $batchId, 'id_akun_perkiraan' => $kurangId,
                        'tanggal' => $validated['tanggal_bayar'], 'nomor_transaksi' => $voucher->nomor_transaksi, 'tipe_transaksi' => $voucher->tipe_transaksi,
                        'urutan_detail' => $maxUrut + 1, 'deskripsi' => 'Biaya selisih kurang bayar (koreksi)',
                        'debit' => round($less, 2), 'kredit' => 0, 'created_at' => $sekarang, 'updated_at' => $sekarang,
                    ]);
                }

                $detailCount = DB::table('jurnal_perkiraan')->where('id_impor_jurnal_perkiraan', $batchId)->count();
                $debitAdj = sprintf('%.2F', $cash - $oldCash + $less - $oldLess);
                $kreditAdj = sprintf('%.2F', $settled - $oldSettled + $more - $oldMore);
                DB::table('impor_jurnal_perkiraan')->where('id_impor_jurnal_perkiraan', $batchId)->update([
                    'periode_awal' => $validated['tanggal_bayar'],
                    'periode_akhir' => $validated['tanggal_bayar'],
                    'jumlah_detail' => $detailCount,
                    'total_debit' => DB::table('jurnal_perkiraan')->where('id_impor_jurnal_perkiraan', $batchId)->sum('debit'),
                    'total_kredit' => DB::table('jurnal_perkiraan')->where('id_impor_jurnal_perkiraan', $batchId)->sum('kredit'),
                    'updated_at' => now(),
                ]);
            }

            // Status nota mengikuti sisa terbaru.
            $newOutstanding = max(0, $invoiceTotal - ($othersSettled + $settled));
            $this->updateInvoiceStatus($jenis, $row->no_nota, $newOutstanding);
        });

        return redirect()->route('transaksi.piutang.index', ['jenis' => $jenis])->with('sukses', 'Pelunasan berhasil diperbarui, jurnal ikut disesuaikan.');
    }

    private function invoiceTotal(string $jenis, string $noNota): ?float
    {
        if ($jenis === 'ayam') {
            $total = DB::table('invoice_ayam')->where('no_nota', $noNota)->selectRaw('SUM(qty * h_satuan) as total')->value('total');
        } elseif ($jenis === 'umum') {
            $total = DB::table('penjualan_agl')->where('urutan', (int) str_replace('PU-', '', $noNota))->selectRaw('SUM(total_rp) as total')->value('total');
        } else {
            $total = DB::table('invoice_telur')->where('no_nota', $noNota)->selectRaw('SUM(total_rp) as total')->value('total');
        }

        return $total === null ? null : (float) $total;
    }

    private function updateInvoiceStatus(string $jenis, string $noNota, float $outstanding): void
    {
        $lunas = $outstanding <= 0.005;
        if ($jenis === 'umum') {
            DB::table('penjualan_agl')->where('urutan', (int) str_replace('PU-', '', $noNota))->update(['status' => $lunas ? 'paid' : 'unpaid']);
        } else {
            $query = DB::table($jenis === 'ayam' ? 'invoice_ayam' : 'invoice_telur')->where('no_nota', $noNota);
            $jenis === 'telur' ? $query->whereIn('lokasi', ['alpa', 'mtd']) : $query->where('lokasi', 'alpa');
            $query->update(['status' => $lunas ? 'paid' : 'unpaid']);
        }
    }

}
