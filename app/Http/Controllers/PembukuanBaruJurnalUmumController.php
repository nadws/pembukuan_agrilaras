<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PembukuanBaruJurnalUmumController extends Controller
{
    public function index(Request $request): View
    {
        $tanggalAwal = $request->input('tanggal_awal', now()->startOfMonth()->toDateString());
        $tanggalAkhir = $request->input('tanggal_akhir', now()->toDateString());
        $cari = $request->input('cari');
        $kelompok = in_array($request->input('kelompok'), ['faktur-pembelian', 'penjualan', 'pelunasan-hutang', 'biaya', 'penyesuaian', 'aktiva-gantung', 'pembalik-aktiva-gantung', 'manual'], true)
            ? $request->input('kelompok')
            : 'faktur-pembelian';

        $batchManualQuery = DB::table('impor_jurnal_perkiraan as i')
            ->where('i.nama_file', 'like', 'Jurnal umum manual%')
            ->whereBetween('i.periode_awal', [$tanggalAwal, $tanggalAkhir])
            ->when($cari, function ($query) use ($cari) {
                $query->where(function ($q) use ($cari) {
                    $q->where('i.nama_file', 'like', "%{$cari}%")
                        ->orWhereExists(function ($sq) use ($cari) {
                            $sq->selectRaw(1)
                                ->from('jurnal_perkiraan as j')
                                ->whereColumn('j.id_impor_jurnal_perkiraan', 'i.id_impor_jurnal_perkiraan')
                                ->where(function ($jq) use ($cari) {
                                    $jq->where('j.nomor_transaksi', 'like', "%{$cari}%")
                                        ->orWhere('j.deskripsi', 'like', "%{$cari}%");
                                });
                        });
                });
            })
            ->orderByDesc('i.periode_awal')
            ->orderByDesc('i.id_impor_jurnal_perkiraan');

        $jurnalFakturDetailQuery = DB::table('jurnal_perkiraan as j')
            ->leftJoin('akun_perkiraan as a', 'a.id_akun_perkiraan', '=', 'j.id_akun_perkiraan')
            ->where(function ($q) {
                $q->where('j.tipe_transaksi', 'like', 'Faktur Pembelian%')
                    ->orWhere('j.tipe_transaksi', 'Pembelian Umum')
                    ->orWhere('j.tipe_transaksi', 'Pembelian Pullet');
            })
            ->whereBetween('j.tanggal', [$tanggalAwal, $tanggalAkhir])
            ->when($cari, function ($query) use ($cari) {
                $query->where(function ($q) use ($cari) {
                    $q->where('j.nomor_transaksi', 'like', "%{$cari}%")
                        ->orWhere('j.deskripsi', 'like', "%{$cari}%")
                        ->orWhere('a.kode_perkiraan', 'like', "%{$cari}%")
                        ->orWhere('a.nama', 'like', "%{$cari}%");
                });
            })
            ->orderBy('j.tanggal')
            ->orderBy('j.nomor_transaksi')
            ->orderBy('j.urutan_detail');

        $filterFaktur = function ($query) use ($tanggalAwal, $tanggalAkhir, $cari) {
            $query->where(function ($q) {
                $q->where('j.tipe_transaksi', 'like', 'Faktur Pembelian%')
                    ->orWhere('j.tipe_transaksi', 'Pembelian Umum')
                    ->orWhere('j.tipe_transaksi', 'Pembelian Pullet');
            })
                ->whereBetween('j.tanggal', [$tanggalAwal, $tanggalAkhir])
                ->when($cari, function ($query) use ($cari) {
                    $query->where(function ($q) use ($cari) {
                        $q->where('j.nomor_transaksi', 'like', "%{$cari}%")
                            ->orWhere('j.deskripsi', 'like', "%{$cari}%")
                            ->orWhere('a.kode_perkiraan', 'like', "%{$cari}%")
                            ->orWhere('a.nama', 'like', "%{$cari}%");
                    });
                });
        };

        $jurnalFakturQuery = DB::table('jurnal_perkiraan as j')
            ->leftJoin('akun_perkiraan as a', 'a.id_akun_perkiraan', '=', 'j.id_akun_perkiraan')
            ->tap($filterFaktur)
            ->select([
                'j.nomor_transaksi',
            ])
            ->selectRaw('MIN(j.tanggal) as tanggal')
            ->selectRaw('MAX(j.tipe_transaksi) as tipe_transaksi')
            ->selectRaw('COUNT(*) as jumlah_detail')
            ->selectRaw('COALESCE(SUM(j.debit), 0) as total_debit')
            ->selectRaw('COALESCE(SUM(j.kredit), 0) as total_kredit')
            ->groupBy('j.nomor_transaksi')
            ->orderByDesc('tanggal')
            ->orderByDesc('j.nomor_transaksi');

        $ringkasanFaktur = DB::table('jurnal_perkiraan as j')
            ->leftJoin('akun_perkiraan as a', 'a.id_akun_perkiraan', '=', 'j.id_akun_perkiraan')
            ->tap($filterFaktur)
            ->selectRaw('COALESCE(SUM(j.debit), 0) as total_debit, COALESCE(SUM(j.kredit), 0) as total_kredit, COUNT(*) as jumlah_detail')
            ->first();

        $jurnalFaktur = $kelompok === 'faktur-pembelian'
            ? $jurnalFakturQuery->paginate(15)->withQueryString()
            : collect();

        $detailFaktur = $kelompok === 'faktur-pembelian' && $jurnalFaktur->count()
            ? $jurnalFakturDetailQuery
            ->whereIn('j.nomor_transaksi', $jurnalFaktur->getCollection()->pluck('nomor_transaksi'))
            ->get([
                'j.id_jurnal_perkiraan',
                'j.tanggal',
                'j.nomor_transaksi',
                'j.tipe_transaksi',
                'j.deskripsi',
                'j.debit',
                'j.kredit',
                'a.kode_perkiraan',
                'a.nama as nama_akun',
            ])
            ->groupBy('nomor_transaksi')
            : collect();

        [$jurnalPelunasan, $detailPelunasan, $ringkasanPelunasan] = $this->jurnalGroupedByTransaction(
            tipeTransaksi: 'Pelunasan Hutang Faktur Pembelian',
            tanggalAwal: $tanggalAwal,
            tanggalAkhir: $tanggalAkhir,
            cari: $cari,
            aktif: $kelompok === 'pelunasan-hutang'
        );

        [$jurnalPenjualanTelur, $detailPenjualanTelur, $ringkasanPenjualanTelur] = $this->jurnalGroupedByTransaction(
            tipeTransaksi: ['Penjualan Telur', 'Penjualan Ayam', 'Penjualan Umum', 'Pelunasan Piutang Telur', 'Pelunasan Piutang Ayam', 'Pelunasan Piutang Umum'],
            tanggalAwal: $tanggalAwal,
            tanggalAkhir: $tanggalAkhir,
            cari: $cari,
            aktif: $kelompok === 'penjualan'
        );

        [$jurnalBiaya, $detailBiaya, $ringkasanBiaya] = $this->jurnalGroupedByTransaction(
            tipeTransaksi: 'Jurnal Biaya',
            tanggalAwal: $tanggalAwal,
            tanggalAkhir: $tanggalAkhir,
            cari: $cari,
            aktif: $kelompok === 'biaya'
        );

        [$jurnalPembelianUmum, $detailPembelianUmum, $ringkasanPembelianUmum] = $this->jurnalGroupedByTransaction(
            tipeTransaksi: 'Pembelian Umum',
            tanggalAwal: $tanggalAwal,
            tanggalAkhir: $tanggalAkhir,
            cari: $cari,
            aktif: $kelompok === 'pembelian-umum'
        );

        [$aktivaGantung, $detailAktivaGantung, $ringkasanAktivaGantung] = $this->aktivaGantungData(
            tanggalAwal: $tanggalAwal,
            tanggalAkhir: $tanggalAkhir,
            cari: $cari,
            aktif: $kelompok === 'aktiva-gantung'
        );

        [$jurnalPembalik, $detailPembalik, $ringkasanPembalik] = $this->jurnalGroupedByTransaction(
            tipeTransaksi: 'Pembalik Aktiva Gantung',
            tanggalAwal: $tanggalAwal,
            tanggalAkhir: $tanggalAkhir,
            cari: $cari,
            aktif: $kelompok === 'pembalik-aktiva-gantung'
        );

        [$jurnalPenyesuaian, $detailPenyesuaian, $ringkasanPenyesuaian] = $this->jurnalGroupedByTransaction(
            tipeTransaksi: ['Stok Opname', 'Penyusutan Aktiva', 'Penyesuaian Aktiva','Penyesuaian Ayam'],
            tanggalAwal: $tanggalAwal,
            tanggalAkhir: $tanggalAkhir,
            cari: $cari,
            aktif: $kelompok === 'penyesuaian'
        );

        $batch = $kelompok === 'manual'
            ? $batchManualQuery->paginate(15)->withQueryString()
            : collect();

        $ringkasanManual = $kelompok === 'manual'
            ? (clone $batchManualQuery)
            ->reorder()
            ->selectRaw('COALESCE(SUM(i.jumlah_detail), 0) as jumlah_detail')
            ->selectRaw('COALESCE(SUM(i.total_debit), 0) as total_debit')
            ->selectRaw('COALESCE(SUM(i.total_kredit), 0) as total_kredit')
            ->first()
            : (object) ['jumlah_detail' => 0, 'total_debit' => 0, 'total_kredit' => 0];

        $detailManual = $kelompok === 'manual' && $batch->isNotEmpty()
            ? DB::table('jurnal_perkiraan as j')
            ->leftJoin('akun_perkiraan as a', 'a.id_akun_perkiraan', '=', 'j.id_akun_perkiraan')
            ->whereIn(
                'j.id_impor_jurnal_perkiraan',
                $batch->getCollection()->pluck('id_impor_jurnal_perkiraan')
            )
            ->orderBy('j.id_impor_jurnal_perkiraan')
            ->orderBy('j.urutan_detail')
            ->get([
                'j.id_impor_jurnal_perkiraan',
                'j.nomor_transaksi',
                'j.deskripsi',
                'j.debit',
                'j.kredit',
                'a.kode_perkiraan',
                'a.nama as nama_akun',
            ])
            ->groupBy('id_impor_jurnal_perkiraan')
            : collect();

        return view('pembukuan_baru.jurnal_umum.index', [
            'title' => 'Jurnal Umum',
            'batch' => $batch,
            'detailManual' => $detailManual,
            'ringkasanManual' => $ringkasanManual,
            'jurnalFaktur' => $jurnalFaktur,
            'detailFaktur' => $detailFaktur,
            'jurnalPelunasan' => $jurnalPelunasan,
            'detailPelunasan' => $detailPelunasan,
            'jurnalPenjualanTelur' => $jurnalPenjualanTelur,
            'detailPenjualanTelur' => $detailPenjualanTelur,
            'jurnalBiaya' => $jurnalBiaya,
            'detailBiaya' => $detailBiaya,
            'jurnalPembelianUmum' => $jurnalPembelianUmum,
            'detailPembelianUmum' => $detailPembelianUmum,
            'aktivaGantung' => $aktivaGantung,
            'detailAktivaGantung' => $detailAktivaGantung,
            'jurnalPembalik' => $jurnalPembalik,
            'detailPembalik' => $detailPembalik,
            'kelompok' => $kelompok,
            'tanggalAwal' => $tanggalAwal,
            'tanggalAkhir' => $tanggalAkhir,
            'ringkasanFaktur' => $ringkasanFaktur,
            'ringkasanPelunasan' => $ringkasanPelunasan,
            'ringkasanPenjualanTelur' => $ringkasanPenjualanTelur,
            'ringkasanBiaya' => $ringkasanBiaya,
            'ringkasanPembelianUmum' => $ringkasanPembelianUmum,
            'ringkasanAktivaGantung' => $ringkasanAktivaGantung,
            'ringkasanPembalik' => $ringkasanPembalik,
            'jurnalPenyesuaian' => $jurnalPenyesuaian,
            'detailPenyesuaian' => $detailPenyesuaian,
            'ringkasanPenyesuaian' => $ringkasanPenyesuaian,
        ]);
    }

    public function create(): View
    {
        return view('pembukuan_baru.jurnal_umum.create', [
            'title' => 'Tambah Jurnal Umum',
            'noTransaksi' => $this->generateNomorTransaksi(),
            'akun' => DB::table('akun_perkiraan')
                ->where('aktif', 1)
                ->orderBy('kode_perkiraan')
                ->get(['id_akun_perkiraan', 'kode_perkiraan', 'nama']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'tanggal' => ['required', 'date'],
            'nomor_transaksi' => ['required', 'string', 'max:100'],
            'keterangan' => ['nullable', 'string'],
            'detail' => ['required', 'array', 'min:2'],
            'detail.*.id_akun_perkiraan' => ['required', 'exists:akun_perkiraan,id_akun_perkiraan'],
            'detail.*.deskripsi' => ['nullable', 'string'],
            'detail.*.debit' => ['nullable', 'numeric', 'min:0'],
            'detail.*.kredit' => ['nullable', 'numeric', 'min:0'],
        ]);

        $detail = collect($validated['detail'])->values()->map(function ($item) {
            $item['debit'] = round((float) ($item['debit'] ?? 0), 2);
            $item['kredit'] = round((float) ($item['kredit'] ?? 0), 2);
            return $item;
        })->filter(fn($item) => $item['debit'] > 0 || $item['kredit'] > 0)->values();

        if ($detail->count() < 2) {
            return back()
                ->withErrors(['detail' => 'Minimal isi 2 baris jurnal.'])
                ->withInput();
        }

        $totalDebit = $detail->sum('debit');
        $totalKredit = $detail->sum('kredit');

        if (round($totalDebit - $totalKredit, 2) !== 0.0) {
            return back()
                ->withErrors(['detail' => 'Total debit dan kredit harus sama.'])
                ->withInput();
        }

        DB::transaction(function () use ($validated, $detail, $totalDebit, $totalKredit) {
            $sekarang = now();
            $namaFile = 'Jurnal umum manual ' . $validated['nomor_transaksi'];
            $batchId = DB::table('impor_jurnal_perkiraan')->insertGetId([
                'nama_file' => $namaFile,
                'hash_file' => hash('sha256', 'jurnal-umum-manual|' . $validated['nomor_transaksi'] . '|' . $sekarang->format('YmdHisv')),
                'periode_awal' => $validated['tanggal'],
                'periode_akhir' => $validated['tanggal'],
                'jumlah_transaksi' => 1,
                'jumlah_detail' => $detail->count(),
                'total_debit' => $totalDebit,
                'total_kredit' => $totalKredit,
                'status' => 'aktif',
                'diimpor_oleh' => auth()->id(),
                'created_at' => $sekarang,
                'updated_at' => $sekarang,
            ]);

            $rows = $detail->map(function ($item, $index) use ($validated, $batchId, $sekarang) {
                return [
                    'id_impor_jurnal_perkiraan' => $batchId,
                    'id_akun_perkiraan' => $item['id_akun_perkiraan'],
                    'tanggal' => $validated['tanggal'],
                    'nomor_transaksi' => $validated['nomor_transaksi'],
                    'tipe_transaksi' => 'Jurnal Umum Manual',
                    'urutan_detail' => $index + 1,
                    'deskripsi' => ($item['deskripsi'] ?? null) ?: ($validated['keterangan'] ?? null),
                    'debit' => $item['debit'],
                    'kredit' => $item['kredit'],
                    'created_at' => $sekarang,
                    'updated_at' => $sekarang,
                ];
            })->all();

            DB::table('jurnal_perkiraan')->insert($rows);
        });

        return redirect()
            ->route('pembukuan-baru.jurnal-umum.index', ['kelompok' => 'manual'])
            ->with('sukses', 'Jurnal umum berhasil disimpan.');
    }

    public function editManual(int $id): View
    {
        $batch = DB::table('impor_jurnal_perkiraan')
            ->where('id_impor_jurnal_perkiraan', $id)
            ->where('nama_file', 'like', 'Jurnal umum manual%')
            ->first();

        abort_unless($batch, 404);

        $rows = DB::table('jurnal_perkiraan')
            ->where('id_impor_jurnal_perkiraan', $id)
            ->orderBy('urutan_detail')
            ->get();

        abort_if($rows->isEmpty(), 404);

        return view('pembukuan_baru.jurnal_umum.create', [
            'title' => 'Edit Jurnal Umum',
            'isEdit' => true,
            'batchManual' => $batch,
            'noTransaksi' => $rows->first()->nomor_transaksi,
            'jurnalTanggal' => $rows->first()->tanggal,
            'detailAwal' => $rows->map(fn($row) => [
                'id_akun_perkiraan' => $row->id_akun_perkiraan,
                'deskripsi' => $row->deskripsi,
                'debit' => (float) $row->debit,
                'kredit' => (float) $row->kredit,
            ])->all(),
            'akun' => DB::table('akun_perkiraan')
                ->where('aktif', 1)
                ->orderBy('kode_perkiraan')
                ->get(['id_akun_perkiraan', 'kode_perkiraan', 'nama']),
        ]);
    }

    public function updateManual(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'tanggal' => ['required', 'date'],
            'nomor_transaksi' => ['required', 'string', 'max:100'],
            'keterangan' => ['nullable', 'string'],
            'detail' => ['required', 'array', 'min:2'],
            'detail.*.id_akun_perkiraan' => ['required', 'exists:akun_perkiraan,id_akun_perkiraan'],
            'detail.*.deskripsi' => ['nullable', 'string'],
            'detail.*.debit' => ['nullable', 'numeric', 'min:0'],
            'detail.*.kredit' => ['nullable', 'numeric', 'min:0'],
        ]);

        $detail = collect($validated['detail'])->values()->map(function ($item) {
            $item['debit'] = round((float) ($item['debit'] ?? 0), 2);
            $item['kredit'] = round((float) ($item['kredit'] ?? 0), 2);
            return $item;
        })->filter(fn($item) => $item['debit'] > 0 || $item['kredit'] > 0)->values();

        if ($detail->count() < 2) {
            return back()->withErrors(['detail' => 'Minimal isi 2 baris jurnal.'])->withInput();
        }

        $totalDebit = $detail->sum('debit');
        $totalKredit = $detail->sum('kredit');

        if (round($totalDebit - $totalKredit, 2) !== 0.0) {
            return back()->withErrors(['detail' => 'Total debit dan kredit harus sama.'])->withInput();
        }

        DB::transaction(function () use ($id, $validated, $detail, $totalDebit, $totalKredit) {
            $batch = DB::table('impor_jurnal_perkiraan')
                ->where('id_impor_jurnal_perkiraan', $id)
                ->where('nama_file', 'like', 'Jurnal umum manual%')
                ->lockForUpdate()
                ->first();

            abort_unless($batch, 404);

            $sekarang = now();
            DB::table('impor_jurnal_perkiraan')
                ->where('id_impor_jurnal_perkiraan', $id)
                ->update([
                    'nama_file' => 'Jurnal umum manual ' . $validated['nomor_transaksi'],
                    'periode_awal' => $validated['tanggal'],
                    'periode_akhir' => $validated['tanggal'],
                    'jumlah_transaksi' => 1,
                    'jumlah_detail' => $detail->count(),
                    'total_debit' => $totalDebit,
                    'total_kredit' => $totalKredit,
                    'updated_at' => $sekarang,
                ]);

            DB::table('jurnal_perkiraan')
                ->where('id_impor_jurnal_perkiraan', $id)
                ->delete();

            DB::table('jurnal_perkiraan')->insert(
                $detail->map(function ($item, $index) use ($id, $validated, $sekarang) {
                    return [
                        'id_impor_jurnal_perkiraan' => $id,
                        'id_akun_perkiraan' => $item['id_akun_perkiraan'],
                        'tanggal' => $validated['tanggal'],
                        'nomor_transaksi' => $validated['nomor_transaksi'],
                        'tipe_transaksi' => 'Jurnal Umum Manual',
                        'urutan_detail' => $index + 1,
                        'deskripsi' => ($item['deskripsi'] ?? null) ?: ($validated['keterangan'] ?? null),
                        'debit' => $item['debit'],
                        'kredit' => $item['kredit'],
                        'created_at' => $sekarang,
                        'updated_at' => $sekarang,
                    ];
                })->all()
            );
        });

        return redirect()
            ->route('pembukuan-baru.jurnal-umum.index', ['kelompok' => 'manual'])
            ->with('sukses', 'Jurnal umum manual berhasil diperbarui.');
    }

    public function destroyManual(int $id): RedirectResponse
    {
        DB::transaction(function () use ($id) {
            $batch = DB::table('impor_jurnal_perkiraan')
                ->where('id_impor_jurnal_perkiraan', $id)
                ->where('nama_file', 'like', 'Jurnal umum manual%')
                ->lockForUpdate()
                ->first();

            abort_unless($batch, 404);

            DB::table('jurnal_perkiraan')
                ->where('id_impor_jurnal_perkiraan', $id)
                ->delete();
            DB::table('impor_jurnal_perkiraan')
                ->where('id_impor_jurnal_perkiraan', $id)
                ->delete();
        });

        return redirect()
            ->route('pembukuan-baru.jurnal-umum.index', ['kelompok' => 'manual'])
            ->with('sukses', 'Jurnal umum manual berhasil dihapus.');
    }

    public function createBiaya(): View
    {
        return view('pembukuan_baru.jurnal_umum.create_biaya', [
            'title' => 'Tambah Jurnal Biaya',
            'noTransaksi' => $this->generateNomorBiaya(),
            'akunBiaya' => $this->akunBiayaAktif(),
            'akunKas' => $this->akunKasBankAktif(),
        ]);
    }

    public function storeBiaya(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'tanggal' => ['required', 'date'],
            'nomor_transaksi' => ['required', 'string', 'max:100'],
            'id_akun_kas' => ['required', 'exists:akun_perkiraan,id_akun_perkiraan'],
            'detail' => ['required', 'array', 'min:1'],
            'detail.*.id_akun_biaya' => ['required', 'exists:akun_perkiraan,id_akun_perkiraan'],
            'detail.*.keterangan' => ['required', 'string', 'max:255'],
            'detail.*.jumlah' => ['required', 'numeric', 'min:0.01'],
        ]);

        $detail = collect($validated['detail'])
            ->map(function ($item) {
                return [
                    'id_akun_biaya' => (int) $item['id_akun_biaya'],
                    'keterangan' => trim($item['keterangan']),
                    'jumlah' => round((float) $item['jumlah'], 2),
                ];
            })
            ->filter(fn($item) => $item['jumlah'] > 0)
            ->values();

        $akunKas = DB::table('akun_perkiraan')
            ->where('id_akun_perkiraan', $validated['id_akun_kas'])
            ->where('aktif', 1)
            ->first();

        $akunBiaya = DB::table('akun_perkiraan')
            ->whereIn('id_akun_perkiraan', $detail->pluck('id_akun_biaya')->unique()->all())
            ->whereIn('tipe_akun', ['EXPS', 'OEXP', 'COGS'])
            ->where('aktif', 1)
            ->get()
            ->keyBy('id_akun_perkiraan');

        if ($detail->isEmpty() || ! $akunKas || $akunBiaya->count() !== $detail->pluck('id_akun_biaya')->unique()->count()) {
            return back()
                ->withErrors(['akun' => 'Akun pembayaran atau salah satu akun biaya belum tersedia/aktif.'])
                ->withInput();
        }

        $total = round($detail->sum('jumlah'), 2);

        DB::transaction(function () use ($validated, $detail, $akunBiaya, $akunKas, $total) {
            $sekarang = now();
            $batchId = DB::table('impor_jurnal_perkiraan')->insertGetId([
                'nama_file' => 'Jurnal biaya ' . $validated['nomor_transaksi'],
                'hash_file' => hash('sha256', 'jurnal-biaya|' . $validated['nomor_transaksi'] . '|' . $sekarang->format('YmdHisv')),
                'periode_awal' => $validated['tanggal'],
                'periode_akhir' => $validated['tanggal'],
                'jumlah_transaksi' => 1,
                'jumlah_detail' => $detail->count() + 1,
                'total_debit' => $total,
                'total_kredit' => $total,
                'status' => 'aktif',
                'diimpor_oleh' => auth()->id(),
                'created_at' => $sekarang,
                'updated_at' => $sekarang,
            ]);

            $rows = $detail->map(function ($item, $index) use ($batchId, $validated, $akunBiaya, $sekarang) {
                return [
                    'id_impor_jurnal_perkiraan' => $batchId,
                    'id_akun_perkiraan' => $akunBiaya[$item['id_akun_biaya']]->id_akun_perkiraan,
                    'tanggal' => $validated['tanggal'],
                    'nomor_transaksi' => $validated['nomor_transaksi'],
                    'tipe_transaksi' => 'Jurnal Biaya',
                    'urutan_detail' => $index + 1,
                    'deskripsi' => $item['keterangan'],
                    'debit' => $item['jumlah'],
                    'kredit' => 0,
                    'created_at' => $sekarang,
                    'updated_at' => $sekarang,
                ];
            })->push([
                'id_impor_jurnal_perkiraan' => $batchId,
                'id_akun_perkiraan' => $akunKas->id_akun_perkiraan,
                'tanggal' => $validated['tanggal'],
                'nomor_transaksi' => $validated['nomor_transaksi'],
                'tipe_transaksi' => 'Jurnal Biaya',
                'urutan_detail' => $detail->count() + 1,
                'deskripsi' => 'Pembayaran biaya dari ' . $akunKas->nama,
                'debit' => 0,
                'kredit' => $total,
                'created_at' => $sekarang,
                'updated_at' => $sekarang,
            ])->all();

            DB::table('jurnal_perkiraan')->insert($rows);
        });

        return redirect()
            ->route('pembukuan-baru.jurnal-umum.index', ['kelompok' => 'biaya'])
            ->with('sukses', 'Jurnal biaya berhasil disimpan.');
    }

    public function editBiaya(string $nomor_transaksi): View
    {
        $rows = DB::table('jurnal_perkiraan as j')
            ->leftJoin('akun_perkiraan as a', 'a.id_akun_perkiraan', '=', 'j.id_akun_perkiraan')
            ->where('j.tipe_transaksi', 'Jurnal Biaya')
            ->where('j.nomor_transaksi', $nomor_transaksi)
            ->orderBy('j.urutan_detail')
            ->get([
                'j.*',
                'a.kode_perkiraan',
                'a.nama as nama_akun',
            ]);

        abort_if($rows->isEmpty(), 404, 'Transaksi biaya tidak ditemukan.');

        $kasRow = $rows->first(fn($r) => (float) $r->kredit > 0 && (float) $r->debit == 0);
        $debitRows = $rows->filter(fn($r) => (float) $r->debit > 0)->values();

        $detail = $debitRows->map(function ($r) {
            return [
                'id_akun_biaya' => $r->id_akun_perkiraan,
                'keterangan' => $r->deskripsi,
                'jumlah' => (float) $r->debit,
            ];
        })->all();

        return view('pembukuan_baru.jurnal_umum.edit_biaya', [
            'title' => 'Edit Jurnal Biaya',
            'nomor_transaksi' => $nomor_transaksi,
            'tanggal' => $rows->first()->tanggal,
            'id_akun_kas' => $kasRow?->id_akun_perkiraan,
            'detail' => $detail,
            'akunBiaya' => $this->akunBiayaAktif(),
            'akunKas' => $this->akunKasBankAktif(),
        ]);
    }

    public function updateBiaya(Request $request, string $nomor_transaksi): RedirectResponse
    {
        $validated = $request->validate([
            'tanggal' => ['required', 'date'],
            'nomor_transaksi' => ['required', 'string', 'max:100'],
            'id_akun_kas' => ['required', 'exists:akun_perkiraan,id_akun_perkiraan'],
            'detail' => ['required', 'array', 'min:1'],
            'detail.*.id_akun_biaya' => ['required', 'exists:akun_perkiraan,id_akun_perkiraan'],
            'detail.*.keterangan' => ['required', 'string', 'max:255'],
            'detail.*.jumlah' => ['required', 'numeric', 'min:0.01'],
        ]);

        $detail = collect($validated['detail'])
            ->map(function ($item) {
                return [
                    'id_akun_biaya' => (int) $item['id_akun_biaya'],
                    'keterangan' => trim($item['keterangan']),
                    'jumlah' => round((float) $item['jumlah'], 2),
                ];
            })
            ->filter(fn($item) => $item['jumlah'] > 0)
            ->values();

        $akunKas = DB::table('akun_perkiraan')
            ->where('id_akun_perkiraan', $validated['id_akun_kas'])
            ->where('aktif', 1)
            ->first();

        $akunBiaya = DB::table('akun_perkiraan')
            ->whereIn('id_akun_perkiraan', $detail->pluck('id_akun_biaya')->unique()->all())
            ->whereIn('tipe_akun', ['EXPS', 'OEXP', 'COGS'])
            ->where('aktif', 1)
            ->get()
            ->keyBy('id_akun_perkiraan');

        if ($detail->isEmpty() || ! $akunKas || $akunBiaya->count() !== $detail->pluck('id_akun_biaya')->unique()->count()) {
            return back()
                ->withErrors(['akun' => 'Akun pembayaran atau salah satu akun biaya belum tersedia/aktif.'])
                ->withInput();
        }

        $total = round($detail->sum('jumlah'), 2);

        DB::transaction(function () use ($validated, $nomor_transaksi, $detail, $akunBiaya, $akunKas, $total) {
            $sekarang = now();

            $batchId = DB::table('jurnal_perkiraan')
                ->where('nomor_transaksi', $nomor_transaksi)
                ->where('tipe_transaksi', 'Jurnal Biaya')
                ->value('id_impor_jurnal_perkiraan');

            if ($batchId) {
                DB::table('jurnal_perkiraan')
                    ->where('id_impor_jurnal_perkiraan', $batchId)
                    ->where('tipe_transaksi', 'Jurnal Biaya')
                    ->delete();

                DB::table('impor_jurnal_perkiraan')
                    ->where('id_impor_jurnal_perkiraan', $batchId)
                    ->update([
                        'nama_file' => 'Jurnal biaya ' . $validated['nomor_transaksi'],
                        'periode_awal' => $validated['tanggal'],
                        'periode_akhir' => $validated['tanggal'],
                        'jumlah_transaksi' => 1,
                        'jumlah_detail' => $detail->count() + 1,
                        'total_debit' => $total,
                        'total_kredit' => $total,
                        'updated_at' => $sekarang,
                    ]);
            } else {
                $batchId = DB::table('impor_jurnal_perkiraan')->insertGetId([
                    'nama_file' => 'Jurnal biaya ' . $validated['nomor_transaksi'],
                    'hash_file' => hash('sha256', 'jurnal-biaya|' . $validated['nomor_transaksi'] . '|' . $sekarang->format('YmdHisv')),
                    'periode_awal' => $validated['tanggal'],
                    'periode_akhir' => $validated['tanggal'],
                    'jumlah_transaksi' => 1,
                    'jumlah_detail' => $detail->count() + 1,
                    'total_debit' => $total,
                    'total_kredit' => $total,
                    'status' => 'aktif',
                    'diimpor_oleh' => auth()->id(),
                    'created_at' => $sekarang,
                    'updated_at' => $sekarang,
                ]);
            }

            $rows = $detail->map(function ($item, $index) use ($batchId, $validated, $akunBiaya, $sekarang) {
                return [
                    'id_impor_jurnal_perkiraan' => $batchId,
                    'id_akun_perkiraan' => $akunBiaya[$item['id_akun_biaya']]->id_akun_perkiraan,
                    'tanggal' => $validated['tanggal'],
                    'nomor_transaksi' => $validated['nomor_transaksi'],
                    'tipe_transaksi' => 'Jurnal Biaya',
                    'urutan_detail' => $index + 1,
                    'deskripsi' => $item['keterangan'],
                    'debit' => $item['jumlah'],
                    'kredit' => 0,
                    'created_at' => $sekarang,
                    'updated_at' => $sekarang,
                ];
            })->push([
                'id_impor_jurnal_perkiraan' => $batchId,
                'id_akun_perkiraan' => $akunKas->id_akun_perkiraan,
                'tanggal' => $validated['tanggal'],
                'nomor_transaksi' => $validated['nomor_transaksi'],
                'tipe_transaksi' => 'Jurnal Biaya',
                'urutan_detail' => $detail->count() + 1,
                'deskripsi' => 'Pembayaran biaya dari ' . $akunKas->nama,
                'debit' => 0,
                'kredit' => $total,
                'created_at' => $sekarang,
                'updated_at' => $sekarang,
            ])->all();

            DB::table('jurnal_perkiraan')->insert($rows);
        });

        return redirect()
            ->route('pembukuan-baru.jurnal-umum.index', ['kelompok' => 'biaya'])
            ->with('sukses', 'Jurnal biaya berhasil diperbarui.');
    }

    public function destroyBiaya(string $nomor_transaksi): RedirectResponse
    {
        DB::transaction(function () use ($nomor_transaksi) {
            $batchIds = DB::table('jurnal_perkiraan')
                ->where('nomor_transaksi', $nomor_transaksi)
                ->where('tipe_transaksi', 'Jurnal Biaya')
                ->pluck('id_impor_jurnal_perkiraan')
                ->filter()
                ->unique();

            DB::table('jurnal_perkiraan')
                ->where('nomor_transaksi', $nomor_transaksi)
                ->where('tipe_transaksi', 'Jurnal Biaya')
                ->delete();

            if ($batchIds->isNotEmpty()) {
                DB::table('impor_jurnal_perkiraan')
                    ->whereIn('id_impor_jurnal_perkiraan', $batchIds)
                    ->delete();
            }
        });

        return redirect()
            ->route('pembukuan-baru.jurnal-umum.index', ['kelompok' => 'biaya'])
            ->with('sukses', 'Jurnal biaya berhasil dihapus.');
    }

    public function createAktivaGantung(): View
    {
        return view('pembukuan_baru.jurnal_umum.create_aktiva_gantung', [
            'title' => 'Tambah Aktiva Gantung',
            'noTransaksi' => $this->generateNomorAktivaGantung(),
            'asetGantung' => DB::table('aktiva_gantung')
                ->where('status', 'gantung')
                ->orderBy('nama_aset')
                ->get(['id', 'kode', 'nama_aset']),
            'akunAktivaGantung' => $this->akunAktivaGantungDefault(),
            'akunKas' => $this->akunKasBankAktif(),
        ]);
    }

    public function storeAktivaGantung(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'tanggal' => ['required', 'date'],
            'nomor_transaksi' => ['required', 'string', 'max:100'],
            'mode_aset' => ['required', 'in:baru,lama'],
            'aktiva_gantung_id' => ['nullable', 'required_if:mode_aset,lama', 'exists:aktiva_gantung,id'],
            'nama_aset' => ['nullable', 'required_if:mode_aset,baru', 'string', 'max:255'],
            'keterangan_aset' => ['nullable', 'string'],
            'id_akun_kas' => ['required', 'exists:akun_perkiraan,id_akun_perkiraan'],
            'detail' => ['required', 'array', 'min:1'],
            'detail.*.keterangan' => ['required', 'string', 'max:255'],
            'detail.*.jumlah' => ['required', 'numeric', 'min:0.01'],
        ]);

        $detail = collect($validated['detail'])
            ->map(function ($item) {
                return [
                    'keterangan' => trim($item['keterangan']),
                    'jumlah' => round((float) $item['jumlah'], 2),
                ];
            })
            ->filter(fn($item) => $item['jumlah'] > 0)
            ->values();

        $akunAktiva = $this->akunAktivaGantungDefault();
        $akunKas = DB::table('akun_perkiraan')
            ->where('id_akun_perkiraan', $validated['id_akun_kas'])
            ->where('aktif', 1)
            ->first();

        if ($detail->isEmpty() || ! $akunAktiva || ! $akunKas) {
            return back()
                ->withErrors(['akun' => 'Akun aktiva gantung atau akun pembayaran belum tersedia/aktif.'])
                ->withInput();
        }

        $total = round($detail->sum('jumlah'), 2);

        DB::transaction(function () use ($validated, $detail, $akunAktiva, $akunKas, $total) {
            $sekarang = now();
            $asetId = $validated['mode_aset'] === 'lama'
                ? (int) $validated['aktiva_gantung_id']
                : DB::table('aktiva_gantung')->insertGetId([
                    'kode' => $this->generateKodeAktivaGantung(),
                    'nama_aset' => trim($validated['nama_aset']),
                    'keterangan' => $validated['keterangan_aset'] ?? null,
                    'status' => 'gantung',
                    'created_by' => auth()->id(),
                    'created_at' => $sekarang,
                    'updated_at' => $sekarang,
                ]);

            $aset = DB::table('aktiva_gantung')->where('id', $asetId)->first();
            $batchId = DB::table('impor_jurnal_perkiraan')->insertGetId([
                'nama_file' => 'Aktiva gantung ' . $aset->nama_aset . ' ' . $validated['nomor_transaksi'],
                'hash_file' => hash('sha256', 'aktiva-gantung|' . $validated['nomor_transaksi'] . '|' . $sekarang->format('YmdHisv')),
                'periode_awal' => $validated['tanggal'],
                'periode_akhir' => $validated['tanggal'],
                'jumlah_transaksi' => 1,
                'jumlah_detail' => $detail->count() + 1,
                'total_debit' => $total,
                'total_kredit' => $total,
                'status' => 'aktif',
                'diimpor_oleh' => auth()->id(),
                'created_at' => $sekarang,
                'updated_at' => $sekarang,
            ]);

            DB::table('aktiva_gantung_transaksi')->insert($detail->map(function ($item) use ($asetId, $validated, $akunAktiva, $akunKas, $batchId, $sekarang) {
                return [
                    'aktiva_gantung_id' => $asetId,
                    'tanggal' => $validated['tanggal'],
                    'nomor_transaksi' => $validated['nomor_transaksi'],
                    'id_akun_aktiva_gantung' => $akunAktiva->id_akun_perkiraan,
                    'id_akun_kas' => $akunKas->id_akun_perkiraan,
                    'jumlah' => $item['jumlah'],
                    'keterangan' => $item['keterangan'],
                    'id_impor_jurnal_perkiraan' => $batchId,
                    'created_by' => auth()->id(),
                    'created_at' => $sekarang,
                    'updated_at' => $sekarang,
                ];
            })->all());

            $rows = $detail->map(function ($item, $index) use ($batchId, $validated, $akunAktiva, $aset, $sekarang) {
                return [
                    'id_impor_jurnal_perkiraan' => $batchId,
                    'id_akun_perkiraan' => $akunAktiva->id_akun_perkiraan,
                    'tanggal' => $validated['tanggal'],
                    'nomor_transaksi' => $validated['nomor_transaksi'],
                    'tipe_transaksi' => 'Aktiva Gantung',
                    'urutan_detail' => $index + 1,
                    'deskripsi' => 'Biaya aktiva gantung ' . $aset->nama_aset . ' - ' . $item['keterangan'],
                    'debit' => $item['jumlah'],
                    'kredit' => 0,
                    'created_at' => $sekarang,
                    'updated_at' => $sekarang,
                ];
            })->push([
                'id_impor_jurnal_perkiraan' => $batchId,
                'id_akun_perkiraan' => $akunKas->id_akun_perkiraan,
                'tanggal' => $validated['tanggal'],
                'nomor_transaksi' => $validated['nomor_transaksi'],
                'tipe_transaksi' => 'Aktiva Gantung',
                'urutan_detail' => $detail->count() + 1,
                'deskripsi' => 'Pembayaran aktiva gantung ' . $aset->nama_aset . ' dari ' . $akunKas->nama,
                'debit' => 0,
                'kredit' => $total,
                'created_at' => $sekarang,
                'updated_at' => $sekarang,
            ])->all();

            DB::table('jurnal_perkiraan')->insert($rows);
        });

        return redirect()
            ->route('pembukuan-baru.jurnal-umum.index', ['kelompok' => 'aktiva-gantung'])
            ->with('sukses', 'Aktiva gantung berhasil ditambahkan.');
    }

    public function editAktivaGantungTransaksi(string $nomor_transaksi): View
    {
        $transaksiList = DB::table('aktiva_gantung_transaksi')
            ->where('nomor_transaksi', $nomor_transaksi)
            ->orderBy('id')
            ->get();

        abort_if($transaksiList->isEmpty(), 404, 'Transaksi aktiva gantung tidak ditemukan.');

        $first = $transaksiList->first();

        $detail = $transaksiList->map(function ($item) {
            return [
                'keterangan' => $item->keterangan,
                'jumlah' => (float) $item->jumlah,
            ];
        })->all();

        return view('pembukuan_baru.jurnal_umum.edit_aktiva_gantung', [
            'title' => 'Edit Biaya Aktiva Gantung',
            'nomor_transaksi' => $nomor_transaksi,
            'tanggal' => $first->tanggal,
            'aktiva_gantung_id' => $first->aktiva_gantung_id,
            'id_akun_kas' => $first->id_akun_kas,
            'detail' => $detail,
            'asetGantung' => DB::table('aktiva_gantung')
                ->orderBy('nama_aset')
                ->get(['id', 'kode', 'nama_aset']),
            'akunAktivaGantung' => $this->akunAktivaGantungDefault(),
            'akunKas' => $this->akunKasBankAktif(),
        ]);
    }

    public function updateAktivaGantungTransaksi(Request $request, string $nomor_transaksi): RedirectResponse
    {
        $validated = $request->validate([
            'tanggal' => ['required', 'date'],
            'nomor_transaksi' => ['required', 'string', 'max:100'],
            'aktiva_gantung_id' => ['required', 'exists:aktiva_gantung,id'],
            'id_akun_kas' => ['required', 'exists:akun_perkiraan,id_akun_perkiraan'],
            'detail' => ['required', 'array', 'min:1'],
            'detail.*.keterangan' => ['required', 'string', 'max:255'],
            'detail.*.jumlah' => ['required', 'numeric', 'min:0.01'],
        ]);

        $detail = collect($validated['detail'])
            ->map(function ($item) {
                return [
                    'keterangan' => trim($item['keterangan']),
                    'jumlah' => round((float) $item['jumlah'], 2),
                ];
            })
            ->filter(fn($item) => $item['jumlah'] > 0)
            ->values();

        $akunAktiva = $this->akunAktivaGantungDefault();
        $akunKas = DB::table('akun_perkiraan')
            ->where('id_akun_perkiraan', $validated['id_akun_kas'])
            ->where('aktif', 1)
            ->first();

        if ($detail->isEmpty() || ! $akunAktiva || ! $akunKas) {
            return back()
                ->withErrors(['akun' => 'Akun aktiva gantung atau akun pembayaran belum tersedia/aktif.'])
                ->withInput();
        }

        $total = round($detail->sum('jumlah'), 2);

        DB::transaction(function () use ($validated, $nomor_transaksi, $detail, $akunAktiva, $akunKas, $total) {
            $sekarang = now();
            $asetId = (int) $validated['aktiva_gantung_id'];
            $aset = DB::table('aktiva_gantung')->where('id', $asetId)->first();

            $batchId = DB::table('aktiva_gantung_transaksi')
                ->where('nomor_transaksi', $nomor_transaksi)
                ->value('id_impor_jurnal_perkiraan');

            if (! $batchId) {
                $batchId = DB::table('jurnal_perkiraan')
                    ->where('nomor_transaksi', $nomor_transaksi)
                    ->where('tipe_transaksi', 'Aktiva Gantung')
                    ->value('id_impor_jurnal_perkiraan');
            }

            if ($batchId) {
                DB::table('impor_jurnal_perkiraan')
                    ->where('id_impor_jurnal_perkiraan', $batchId)
                    ->update([
                        'nama_file' => 'Aktiva gantung ' . $aset->nama_aset . ' ' . $validated['nomor_transaksi'],
                        'periode_awal' => $validated['tanggal'],
                        'periode_akhir' => $validated['tanggal'],
                        'jumlah_transaksi' => 1,
                        'jumlah_detail' => $detail->count() + 1,
                        'total_debit' => $total,
                        'total_kredit' => $total,
                        'updated_at' => $sekarang,
                    ]);

                DB::table('jurnal_perkiraan')
                    ->where('id_impor_jurnal_perkiraan', $batchId)
                    ->where('tipe_transaksi', 'Aktiva Gantung')
                    ->delete();
            } else {
                $batchId = DB::table('impor_jurnal_perkiraan')->insertGetId([
                    'nama_file' => 'Aktiva gantung ' . $aset->nama_aset . ' ' . $validated['nomor_transaksi'],
                    'hash_file' => hash('sha256', 'aktiva-gantung|' . $validated['nomor_transaksi'] . '|' . $sekarang->format('YmdHisv')),
                    'periode_awal' => $validated['tanggal'],
                    'periode_akhir' => $validated['tanggal'],
                    'jumlah_transaksi' => 1,
                    'jumlah_detail' => $detail->count() + 1,
                    'total_debit' => $total,
                    'total_kredit' => $total,
                    'status' => 'aktif',
                    'diimpor_oleh' => auth()->id(),
                    'created_at' => $sekarang,
                    'updated_at' => $sekarang,
                ]);
            }

            DB::table('aktiva_gantung_transaksi')
                ->where('nomor_transaksi', $nomor_transaksi)
                ->delete();

            DB::table('aktiva_gantung_transaksi')->insert($detail->map(function ($item) use ($asetId, $validated, $akunAktiva, $akunKas, $batchId, $sekarang) {
                return [
                    'aktiva_gantung_id' => $asetId,
                    'tanggal' => $validated['tanggal'],
                    'nomor_transaksi' => $validated['nomor_transaksi'],
                    'id_akun_aktiva_gantung' => $akunAktiva->id_akun_perkiraan,
                    'id_akun_kas' => $akunKas->id_akun_perkiraan,
                    'jumlah' => $item['jumlah'],
                    'keterangan' => $item['keterangan'],
                    'id_impor_jurnal_perkiraan' => $batchId,
                    'created_by' => auth()->id(),
                    'created_at' => $sekarang,
                    'updated_at' => $sekarang,
                ];
            })->all());

            $rows = $detail->map(function ($item, $index) use ($batchId, $validated, $akunAktiva, $aset, $sekarang) {
                return [
                    'id_impor_jurnal_perkiraan' => $batchId,
                    'id_akun_perkiraan' => $akunAktiva->id_akun_perkiraan,
                    'tanggal' => $validated['tanggal'],
                    'nomor_transaksi' => $validated['nomor_transaksi'],
                    'tipe_transaksi' => 'Aktiva Gantung',
                    'urutan_detail' => $index + 1,
                    'deskripsi' => 'Biaya aktiva gantung ' . $aset->nama_aset . ' - ' . $item['keterangan'],
                    'debit' => $item['jumlah'],
                    'kredit' => 0,
                    'created_at' => $sekarang,
                    'updated_at' => $sekarang,
                ];
            })->push([
                'id_impor_jurnal_perkiraan' => $batchId,
                'id_akun_perkiraan' => $akunKas->id_akun_perkiraan,
                'tanggal' => $validated['tanggal'],
                'nomor_transaksi' => $validated['nomor_transaksi'],
                'tipe_transaksi' => 'Aktiva Gantung',
                'urutan_detail' => $detail->count() + 1,
                'deskripsi' => 'Pembayaran aktiva gantung ' . $aset->nama_aset . ' dari ' . $akunKas->nama,
                'debit' => 0,
                'kredit' => $total,
                'created_at' => $sekarang,
                'updated_at' => $sekarang,
            ])->all();

            DB::table('jurnal_perkiraan')->insert($rows);
        });

        return redirect()
            ->route('pembukuan-baru.jurnal-umum.index', ['kelompok' => 'aktiva-gantung'])
            ->with('sukses', 'Biaya aktiva gantung berhasil diperbarui.');
    }

    public function destroyAktivaGantungTransaksi(string $nomor_transaksi): RedirectResponse
    {
        DB::transaction(function () use ($nomor_transaksi) {
            $batchIds = DB::table('aktiva_gantung_transaksi')
                ->where('nomor_transaksi', $nomor_transaksi)
                ->pluck('id_impor_jurnal_perkiraan')
                ->concat(
                    DB::table('jurnal_perkiraan')
                        ->where('nomor_transaksi', $nomor_transaksi)
                        ->where('tipe_transaksi', 'Aktiva Gantung')
                        ->pluck('id_impor_jurnal_perkiraan')
                )
                ->filter()
                ->unique();

            DB::table('aktiva_gantung_transaksi')
                ->where('nomor_transaksi', $nomor_transaksi)
                ->delete();

            DB::table('jurnal_perkiraan')
                ->where('nomor_transaksi', $nomor_transaksi)
                ->where('tipe_transaksi', 'Aktiva Gantung')
                ->delete();

            if ($batchIds->isNotEmpty()) {
                DB::table('impor_jurnal_perkiraan')
                    ->whereIn('id_impor_jurnal_perkiraan', $batchIds)
                    ->delete();
            }
        });

        return redirect()
            ->route('pembukuan-baru.jurnal-umum.index', ['kelompok' => 'aktiva-gantung'])
            ->with('sukses', 'Transaksi aktiva gantung berhasil dihapus.');
    }

    public function updateAktivaGantungAset(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'nama_aset' => ['required', 'string', 'max:255'],
            'keterangan' => ['nullable', 'string'],
            'status' => ['required', 'in:gantung,selesai,dibatalkan'],
        ]);

        DB::table('aktiva_gantung')
            ->where('id', $id)
            ->update([
                'nama_aset' => trim($validated['nama_aset']),
                'keterangan' => $validated['keterangan'] ?? null,
                'status' => $validated['status'],
                'updated_at' => now(),
            ]);

        return redirect()
            ->route('pembukuan-baru.jurnal-umum.index', ['kelompok' => 'aktiva-gantung'])
            ->with('sukses', 'Data aset aktiva gantung berhasil diperbarui.');
    }

    public function destroyAktivaGantungAset(int $id): RedirectResponse
    {
        DB::transaction(function () use ($id) {
            $transaksi = DB::table('aktiva_gantung_transaksi')
                ->where('aktiva_gantung_id', $id)
                ->get();

            $nomorTransaksi = $transaksi->pluck('nomor_transaksi')->unique();
            $batchIds = $transaksi->pluck('id_impor_jurnal_perkiraan')->filter()->unique();

            if ($nomorTransaksi->isNotEmpty()) {
                DB::table('jurnal_perkiraan')
                    ->whereIn('nomor_transaksi', $nomorTransaksi)
                    ->where('tipe_transaksi', 'Aktiva Gantung')
                    ->delete();
            }

            if ($batchIds->isNotEmpty()) {
                DB::table('impor_jurnal_perkiraan')
                    ->whereIn('id_impor_jurnal_perkiraan', $batchIds)
                    ->delete();
            }

            DB::table('aktiva_gantung_transaksi')
                ->where('aktiva_gantung_id', $id)
                ->delete();

            DB::table('aktiva_gantung')
                ->where('id', $id)
                ->delete();
        });

        return redirect()
            ->route('pembukuan-baru.jurnal-umum.index', ['kelompok' => 'aktiva-gantung'])
            ->with('sukses', 'Aset aktiva gantung beserta transaksinya berhasil dihapus.');
    }

    public function createPembalikAktivaGantung(): View
    {
        return view('pembukuan_baru.jurnal_umum.create_pembalik_aktiva_gantung', [
            'title' => 'Buat Pembalik Aktiva Gantung',
            'noTransaksi' => $this->generateNomorPembalikAktivaGantung(),
            'asetGantung' => $this->asetGantungDenganSaldo(),
            'akunAktivaGantungDefault' => $this->akunAktivaGantungDefault(),
            'akunAktivaGantung' => $this->akunAktivaGantungSemua(),
            'akunAset' => $this->akunAsetTetapAktif(),
            'kelompokAktiva' => DB::table('kelompok_aktiva')->orderBy('id_kelompok')->get(),
        ]);
    }

    public function storePembalikAktivaGantung(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'tanggal' => ['required', 'date'],
            'nomor_transaksi' => ['required', 'string', 'max:100'],
            'aktiva_gantung_id' => ['required', 'exists:aktiva_gantung,id'],
            'id_akun_aktiva_gantung' => ['required', 'exists:akun_perkiraan,id_akun_perkiraan'],
            'id_akun_aset' => ['required', 'exists:akun_perkiraan,id_akun_perkiraan'],
            'nominal' => ['required', 'numeric', 'min:0.01'],
            'keterangan' => ['nullable', 'string'],
            'status_aset_gantung' => ['nullable', 'string', 'in:selesai,gantung'],
            'simpan_ke_master_aktiva' => ['nullable', 'in:0,1'],
            'id_kelompok_aktiva' => ['nullable', 'required_if:simpan_ke_master_aktiva,1', 'exists:kelompok_aktiva,id_kelompok'],
        ]);

        $nominal = round((float) $validated['nominal'], 2);
        $aset = DB::table('aktiva_gantung')->where('id', $validated['aktiva_gantung_id'])->first();
        $akunAset = DB::table('akun_perkiraan')->where('id_akun_perkiraan', $validated['id_akun_aset'])->where('aktif', 1)->first();
        $akunAktivaGantung = DB::table('akun_perkiraan')->where('id_akun_perkiraan', $validated['id_akun_aktiva_gantung'])->where('aktif', 1)->first();

        if (! $aset || ! $akunAset || ! $akunAktivaGantung) {
            return back()
                ->withErrors(['akun' => 'Aset gantung atau salah satu akun perkiraan belum valid/aktif.'])
                ->withInput();
        }

        DB::transaction(function () use ($validated, $aset, $akunAset, $akunAktivaGantung, $nominal) {
            $sekarang = now();

            if (($validated['status_aset_gantung'] ?? 'selesai') === 'selesai') {
                DB::table('aktiva_gantung')
                    ->where('id', $aset->id)
                    ->update([
                        'status' => 'selesai',
                        'updated_at' => $sekarang,
                    ]);
            }

            $batchId = DB::table('impor_jurnal_perkiraan')->insertGetId([
                'nama_file' => 'Pembalik aktiva gantung ' . $aset->nama_aset . ' ' . $validated['nomor_transaksi'],
                'hash_file' => hash('sha256', 'pembalik-aktiva-gantung|' . $validated['nomor_transaksi'] . '|' . $sekarang->format('YmdHisv')),
                'periode_awal' => $validated['tanggal'],
                'periode_akhir' => $validated['tanggal'],
                'jumlah_transaksi' => 1,
                'jumlah_detail' => 2,
                'total_debit' => $nominal,
                'total_kredit' => $nominal,
                'status' => 'aktif',
                'diimpor_oleh' => auth()->id(),
                'created_at' => $sekarang,
                'updated_at' => $sekarang,
            ]);

            $deskripsiDebit = $validated['keterangan'] ?: "Pembalikan aktiva gantung {$aset->nama_aset} ke aset {$akunAset->nama}";
            $deskripsiKredit = $validated['keterangan'] ?: "Pembalikan aktiva gantung {$aset->nama_aset}";

            $rows = [
                [
                    'id_impor_jurnal_perkiraan' => $batchId,
                    'id_akun_perkiraan' => $akunAset->id_akun_perkiraan,
                    'tanggal' => $validated['tanggal'],
                    'nomor_transaksi' => $validated['nomor_transaksi'],
                    'tipe_transaksi' => 'Pembalik Aktiva Gantung',
                    'urutan_detail' => 1,
                    'deskripsi' => $deskripsiDebit,
                    'debit' => $nominal,
                    'kredit' => 0,
                    'created_at' => $sekarang,
                    'updated_at' => $sekarang,
                ],
                [
                    'id_impor_jurnal_perkiraan' => $batchId,
                    'id_akun_perkiraan' => $akunAktivaGantung->id_akun_perkiraan,
                    'tanggal' => $validated['tanggal'],
                    'nomor_transaksi' => $validated['nomor_transaksi'],
                    'tipe_transaksi' => 'Pembalik Aktiva Gantung',
                    'urutan_detail' => 2,
                    'deskripsi' => $deskripsiKredit,
                    'debit' => 0,
                    'kredit' => $nominal,
                    'created_at' => $sekarang,
                    'updated_at' => $sekarang,
                ],
            ];

            DB::table('jurnal_perkiraan')->insert($rows);

            if (! empty($validated['simpan_ke_master_aktiva']) && ! empty($validated['id_kelompok_aktiva'])) {
                $kelompok = DB::table('kelompok_aktiva')->where('id_kelompok', $validated['id_kelompok_aktiva'])->first();
                $tarif = (float) ($kelompok?->tarif ?? 0);
                $biayaDepresiasi = round($nominal * $tarif, 2);

                DB::table('aktiva_pembukuan_baru')->insert([
                    'id_akun_aset' => $validated['id_akun_aset'],
                    'id_kelompok' => $validated['id_kelompok_aktiva'],
                    'nm_aktiva' => $aset->nama_aset,
                    'tgl' => $validated['tanggal'],
                    'h_perolehan' => $nominal,
                    'biaya_depresiasi' => $biayaDepresiasi,
                    'admin' => auth()->user()->name ?? 'Admin',
                    'created_at' => $sekarang,
                    'updated_at' => $sekarang,
                ]);
            }
        });

        return redirect()
            ->route('pembukuan-baru.jurnal-umum.index', ['kelompok' => 'pembalik-aktiva-gantung'])
            ->with('sukses', 'Pembalik aktiva gantung berhasil disimpan.');
    }

    public function editPembalikAktivaGantung(string $nomor_transaksi): View
    {
        $rows = DB::table('jurnal_perkiraan as j')
            ->leftJoin('akun_perkiraan as a', 'a.id_akun_perkiraan', '=', 'j.id_akun_perkiraan')
            ->where('j.tipe_transaksi', 'Pembalik Aktiva Gantung')
            ->where('j.nomor_transaksi', $nomor_transaksi)
            ->orderBy('j.urutan_detail')
            ->get([
                'j.*',
                'a.kode_perkiraan',
                'a.nama as nama_akun',
            ]);

        abort_if($rows->isEmpty(), 404, 'Transaksi pembalik aktiva gantung tidak ditemukan.');

        $debitRow = $rows->first(fn($r) => (float) $r->debit > 0);
        $kreditRow = $rows->first(fn($r) => (float) $r->kredit > 0);

        return view('pembukuan_baru.jurnal_umum.edit_pembalik_aktiva_gantung', [
            'title' => 'Edit Pembalik Aktiva Gantung',
            'nomor_transaksi' => $nomor_transaksi,
            'tanggal' => $rows->first()->tanggal,
            'id_akun_aset' => $debitRow?->id_akun_perkiraan,
            'id_akun_aktiva_gantung' => $kreditRow?->id_akun_perkiraan,
            'nominal' => (float) ($debitRow?->debit ?? $kreditRow?->kredit ?? 0),
            'keterangan' => $debitRow?->deskripsi ?? $kreditRow?->deskripsi ?? '',
            'akunAktivaGantung' => $this->akunAktivaGantungSemua(),
            'akunAset' => $this->akunAsetTetapAktif(),
        ]);
    }

    public function updatePembalikAktivaGantung(Request $request, string $nomor_transaksi): RedirectResponse
    {
        $validated = $request->validate([
            'tanggal' => ['required', 'date'],
            'nomor_transaksi' => ['required', 'string', 'max:100'],
            'id_akun_aktiva_gantung' => ['required', 'exists:akun_perkiraan,id_akun_perkiraan'],
            'id_akun_aset' => ['required', 'exists:akun_perkiraan,id_akun_perkiraan'],
            'nominal' => ['required', 'numeric', 'min:0.01'],
            'keterangan' => ['nullable', 'string'],
        ]);

        $nominal = round((float) $validated['nominal'], 2);
        $akunAset = DB::table('akun_perkiraan')->where('id_akun_perkiraan', $validated['id_akun_aset'])->where('aktif', 1)->first();
        $akunAktivaGantung = DB::table('akun_perkiraan')->where('id_akun_perkiraan', $validated['id_akun_aktiva_gantung'])->where('aktif', 1)->first();

        if (! $akunAset || ! $akunAktivaGantung) {
            return back()
                ->withErrors(['akun' => 'Akun aset atau akun aktiva gantung belum valid/aktif.'])
                ->withInput();
        }

        DB::transaction(function () use ($validated, $nomor_transaksi, $akunAset, $akunAktivaGantung, $nominal) {
            $sekarang = now();

            $batchId = DB::table('jurnal_perkiraan')
                ->where('nomor_transaksi', $nomor_transaksi)
                ->where('tipe_transaksi', 'Pembalik Aktiva Gantung')
                ->value('id_impor_jurnal_perkiraan');

            if ($batchId) {
                DB::table('jurnal_perkiraan')
                    ->where('id_impor_jurnal_perkiraan', $batchId)
                    ->where('tipe_transaksi', 'Pembalik Aktiva Gantung')
                    ->delete();

                DB::table('impor_jurnal_perkiraan')
                    ->where('id_impor_jurnal_perkiraan', $batchId)
                    ->update([
                        'nama_file' => 'Pembalik aktiva gantung ' . $validated['nomor_transaksi'],
                        'periode_awal' => $validated['tanggal'],
                        'periode_akhir' => $validated['tanggal'],
                        'jumlah_transaksi' => 1,
                        'jumlah_detail' => 2,
                        'total_debit' => $nominal,
                        'total_kredit' => $nominal,
                        'updated_at' => $sekarang,
                    ]);
            } else {
                $batchId = DB::table('impor_jurnal_perkiraan')->insertGetId([
                    'nama_file' => 'Pembalik aktiva gantung ' . $validated['nomor_transaksi'],
                    'hash_file' => hash('sha256', 'pembalik-aktiva-gantung|' . $validated['nomor_transaksi'] . '|' . $sekarang->format('YmdHisv')),
                    'periode_awal' => $validated['tanggal'],
                    'periode_akhir' => $validated['tanggal'],
                    'jumlah_transaksi' => 1,
                    'jumlah_detail' => 2,
                    'total_debit' => $nominal,
                    'total_kredit' => $nominal,
                    'status' => 'aktif',
                    'diimpor_oleh' => auth()->id(),
                    'created_at' => $sekarang,
                    'updated_at' => $sekarang,
                ]);
            }

            $deskripsiDebit = $validated['keterangan'] ?: "Pembalikan aktiva gantung ke aset {$akunAset->nama}";
            $deskripsiKredit = $validated['keterangan'] ?: "Pembalikan aktiva gantung";

            $rows = [
                [
                    'id_impor_jurnal_perkiraan' => $batchId,
                    'id_akun_perkiraan' => $akunAset->id_akun_perkiraan,
                    'tanggal' => $validated['tanggal'],
                    'nomor_transaksi' => $validated['nomor_transaksi'],
                    'tipe_transaksi' => 'Pembalik Aktiva Gantung',
                    'urutan_detail' => 1,
                    'deskripsi' => $deskripsiDebit,
                    'debit' => $nominal,
                    'kredit' => 0,
                    'created_at' => $sekarang,
                    'updated_at' => $sekarang,
                ],
                [
                    'id_impor_jurnal_perkiraan' => $batchId,
                    'id_akun_perkiraan' => $akunAktivaGantung->id_akun_perkiraan,
                    'tanggal' => $validated['tanggal'],
                    'nomor_transaksi' => $validated['nomor_transaksi'],
                    'tipe_transaksi' => 'Pembalik Aktiva Gantung',
                    'urutan_detail' => 2,
                    'deskripsi' => $deskripsiKredit,
                    'debit' => 0,
                    'kredit' => $nominal,
                    'created_at' => $sekarang,
                    'updated_at' => $sekarang,
                ],
            ];

            DB::table('jurnal_perkiraan')->insert($rows);
        });

        return redirect()
            ->route('pembukuan-baru.jurnal-umum.index', ['kelompok' => 'pembalik-aktiva-gantung'])
            ->with('sukses', 'Pembalik aktiva gantung berhasil diperbarui.');
    }

    public function destroyPembalikAktivaGantung(string $nomor_transaksi): RedirectResponse
    {
        DB::transaction(function () use ($nomor_transaksi) {
            $batchIds = DB::table('jurnal_perkiraan')
                ->where('nomor_transaksi', $nomor_transaksi)
                ->where('tipe_transaksi', 'Pembalik Aktiva Gantung')
                ->pluck('id_impor_jurnal_perkiraan')
                ->filter()
                ->unique();

            DB::table('jurnal_perkiraan')
                ->where('nomor_transaksi', $nomor_transaksi)
                ->where('tipe_transaksi', 'Pembalik Aktiva Gantung')
                ->delete();

            if ($batchIds->isNotEmpty()) {
                DB::table('impor_jurnal_perkiraan')
                    ->whereIn('id_impor_jurnal_perkiraan', $batchIds)
                    ->delete();
            }
        });

        return redirect()
            ->route('pembukuan-baru.jurnal-umum.index', ['kelompok' => 'pembalik-aktiva-gantung'])
            ->with('sukses', 'Jurnal pembalik aktiva gantung berhasil dihapus.');
    }

    private function generateNomorTransaksi(): string
    {
        $prefix = 'JU-' . now()->format('Ymd') . '-';
        $last = DB::table('jurnal_perkiraan')
            ->where('nomor_transaksi', 'like', $prefix . '%')
            ->orderByDesc('nomor_transaksi')
            ->value('nomor_transaksi');

        $next = $last ? ((int) substr($last, -3)) + 1 : 1;

        return $prefix . str_pad((string) $next, 3, '0', STR_PAD_LEFT);
    }

    private function generateNomorBiaya(): string
    {
        $prefix = 'JB-' . now()->format('Ymd') . '-';
        $last = DB::table('jurnal_perkiraan')
            ->where('nomor_transaksi', 'like', $prefix . '%')
            ->orderByDesc('nomor_transaksi')
            ->value('nomor_transaksi');

        $next = $last ? ((int) substr($last, -3)) + 1 : 1;

        return $prefix . str_pad((string) $next, 3, '0', STR_PAD_LEFT);
    }

    private function generateNomorAktivaGantung(): string
    {
        $prefix = 'AG-' . now()->format('Ymd') . '-';
        $last = DB::table('jurnal_perkiraan')
            ->where('nomor_transaksi', 'like', $prefix . '%')
            ->orderByDesc('nomor_transaksi')
            ->value('nomor_transaksi');

        $next = $last ? ((int) substr($last, -3)) + 1 : 1;

        return $prefix . str_pad((string) $next, 3, '0', STR_PAD_LEFT);
    }

    private function generateKodeAktivaGantung(): string
    {
        $prefix = 'AG-' . now()->format('Ymd') . '-';
        $last = DB::table('aktiva_gantung')
            ->where('kode', 'like', $prefix . '%')
            ->orderByDesc('kode')
            ->value('kode');

        $next = $last ? ((int) substr($last, -3)) + 1 : 1;

        return $prefix . str_pad((string) $next, 3, '0', STR_PAD_LEFT);
    }

    private function akunBiayaAktif()
    {
        return DB::table('akun_perkiraan')
            ->where('aktif', 1)
            ->whereIn('tipe_akun', ['EXPS', 'OEXP', 'COGS'])
            ->whereNotNull('id_akun_induk')
            ->orderBy('kode_perkiraan')
            ->get(['id_akun_perkiraan', 'kode_perkiraan', 'nama', 'tipe_akun']);
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

    private function akunAktivaGantungDefault(): ?object
    {
        return DB::table('akun_perkiraan')
            ->where('aktif', 1)
            ->where('kode_perkiraan', '110506')
            ->first(['id_akun_perkiraan', 'kode_perkiraan', 'nama']);
    }

    private function aktivaGantungData(
        string $tanggalAwal,
        string $tanggalAkhir,
        ?string $cari,
        bool $aktif
    ): array {
        $transaksiPeriode = DB::table('aktiva_gantung_transaksi')
            ->whereBetween('tanggal', [$tanggalAwal, $tanggalAkhir])
            ->select('aktiva_gantung_id')
            ->selectRaw('COUNT(*) as jumlah_transaksi')
            ->selectRaw('COALESCE(SUM(jumlah), 0) as total_periode')
            ->groupBy('aktiva_gantung_id');

        $transaksiSemua = DB::table('aktiva_gantung_transaksi')
            ->select('aktiva_gantung_id')
            ->selectRaw('COALESCE(SUM(jumlah), 0) as total_terkumpul')
            ->groupBy('aktiva_gantung_id');

        $summary = DB::table('aktiva_gantung as ag')
            ->leftJoinSub($transaksiPeriode, 'tp', 'tp.aktiva_gantung_id', '=', 'ag.id')
            ->when($cari, function ($query) use ($cari) {
                $query->where(function ($q) use ($cari) {
                    $q->where('ag.nama_aset', 'like', "%{$cari}%")
                        ->orWhere('ag.kode', 'like', "%{$cari}%");
                });
            })
            ->selectRaw('COUNT(DISTINCT ag.id) as jumlah_aset')
            ->selectRaw('COALESCE(SUM(tp.jumlah_transaksi), 0) as jumlah_detail')
            ->selectRaw('COALESCE(SUM(tp.total_periode), 0) as total_debit')
            ->first();

        $summary->total_kredit = $summary->total_debit ?? 0;

        if (! $aktif) {
            return [collect(), collect(), $summary];
        }

        $aktivaGantung = DB::table('aktiva_gantung as ag')
            ->leftJoinSub($transaksiPeriode, 'tp', 'tp.aktiva_gantung_id', '=', 'ag.id')
            ->leftJoinSub($transaksiSemua, 'ts', 'ts.aktiva_gantung_id', '=', 'ag.id')
            ->when($cari, function ($query) use ($cari) {
                $query->where(function ($q) use ($cari) {
                    $q->where('ag.nama_aset', 'like', "%{$cari}%")
                        ->orWhere('ag.kode', 'like', "%{$cari}%")
                        ->orWhereExists(function ($sq) use ($cari) {
                            $sq->selectRaw(1)
                                ->from('aktiva_gantung_transaksi as t')
                                ->whereColumn('t.aktiva_gantung_id', 'ag.id')
                                ->where(function ($tq) use ($cari) {
                                    $tq->where('t.nomor_transaksi', 'like', "%{$cari}%")
                                        ->orWhere('t.keterangan', 'like', "%{$cari}%");
                                });
                        });
                });
            })
            ->select('ag.*')
            ->selectRaw('COALESCE(tp.jumlah_transaksi, 0) as jumlah_transaksi')
            ->selectRaw('COALESCE(tp.total_periode, 0) as total_periode')
            ->selectRaw('COALESCE(ts.total_terkumpul, 0) as total_terkumpul')
            ->orderByDesc('ag.id')
            ->paginate(15)
            ->withQueryString();

        $detail = $aktivaGantung->count()
            ? DB::table('aktiva_gantung_transaksi as t')
            ->leftJoin('akun_perkiraan as aa', 'aa.id_akun_perkiraan', '=', 't.id_akun_aktiva_gantung')
            ->leftJoin('akun_perkiraan as ak', 'ak.id_akun_perkiraan', '=', 't.id_akun_kas')
            ->whereIn('t.aktiva_gantung_id', $aktivaGantung->getCollection()->pluck('id'))
            ->whereBetween('t.tanggal', [$tanggalAwal, $tanggalAkhir])
            ->when($cari, function ($query) use ($cari) {
                $query->where(function ($q) use ($cari) {
                    $q->where('t.nomor_transaksi', 'like', "%{$cari}%")
                        ->orWhere('t.keterangan', 'like', "%{$cari}%");
                });
            })
            ->orderByDesc('t.tanggal')
            ->orderByDesc('t.id')
            ->get([
                't.*',
                'aa.kode_perkiraan as kode_akun_aktiva',
                'aa.nama as nama_akun_aktiva',
                'ak.kode_perkiraan as kode_akun_kas',
                'ak.nama as nama_akun_kas',
            ])
            ->groupBy('aktiva_gantung_id')
            : collect();

        return [$aktivaGantung, $detail, $summary];
    }

    private function jurnalGroupedByTransaction(
        string|array $tipeTransaksi,
        string $tanggalAwal,
        string $tanggalAkhir,
        ?string $cari,
        bool $aktif
    ): array {
        $filter = function ($query) use ($tipeTransaksi, $tanggalAwal, $tanggalAkhir, $cari) {
            $query->whereIn('j.tipe_transaksi', (array) $tipeTransaksi)
                ->whereBetween('j.tanggal', [$tanggalAwal, $tanggalAkhir])
                ->when($cari, function ($query) use ($cari) {
                    $query->where(function ($q) use ($cari) {
                        $q->where('j.nomor_transaksi', 'like', "%{$cari}%")
                            ->orWhere('j.deskripsi', 'like', "%{$cari}%")
                            ->orWhere('a.kode_perkiraan', 'like', "%{$cari}%")
                            ->orWhere('a.nama', 'like', "%{$cari}%");
                    });
                });
        };

        $summary = DB::table('jurnal_perkiraan as j')
            ->leftJoin('akun_perkiraan as a', 'a.id_akun_perkiraan', '=', 'j.id_akun_perkiraan')
            ->tap($filter)
            ->selectRaw('COALESCE(SUM(j.debit), 0) as total_debit, COALESCE(SUM(j.kredit), 0) as total_kredit, COUNT(*) as jumlah_detail')
            ->first();

        if (! $aktif) {
            return [collect(), collect(), $summary];
        }

        $jurnal = DB::table('jurnal_perkiraan as j')
            ->leftJoin('akun_perkiraan as a', 'a.id_akun_perkiraan', '=', 'j.id_akun_perkiraan')
            ->tap($filter)
            ->select('j.nomor_transaksi')
            ->selectRaw('MIN(j.tanggal) as tanggal')
            ->selectRaw('MAX(j.tipe_transaksi) as tipe_transaksi')
            ->selectRaw('COUNT(*) as jumlah_detail')
            ->selectRaw('COALESCE(SUM(j.debit), 0) as total_debit')
            ->selectRaw('COALESCE(SUM(j.kredit), 0) as total_kredit')
            ->groupBy('j.nomor_transaksi')
            ->orderByDesc('tanggal')
            ->orderByDesc('j.nomor_transaksi')
            ->paginate(15)
            ->withQueryString();

        $detail = $jurnal->count()
            ? DB::table('jurnal_perkiraan as j')
            ->leftJoin('akun_perkiraan as a', 'a.id_akun_perkiraan', '=', 'j.id_akun_perkiraan')
            ->tap($filter)
            ->whereIn('j.nomor_transaksi', $jurnal->getCollection()->pluck('nomor_transaksi'))
            ->orderBy('j.tanggal')
            ->orderBy('j.nomor_transaksi')
            ->orderBy('j.urutan_detail')
            ->get([
                'j.id_jurnal_perkiraan',
                'j.tanggal',
                'j.nomor_transaksi',
                'j.tipe_transaksi',
                'j.deskripsi',
                'j.debit',
                'j.kredit',
                'a.kode_perkiraan',
                'a.nama as nama_akun',
            ])
            ->groupBy('nomor_transaksi')
            : collect();

        return [$jurnal, $detail, $summary];
    }

    private function generateNomorPembalikAktivaGantung(): string
    {
        $prefix = 'PAG-' . now()->format('Ymd') . '-';
        $last = DB::table('jurnal_perkiraan')
            ->where('nomor_transaksi', 'like', $prefix . '%')
            ->orderByDesc('nomor_transaksi')
            ->value('nomor_transaksi');

        $next = $last ? ((int) substr($last, -3)) + 1 : 1;

        return $prefix . str_pad((string) $next, 3, '0', STR_PAD_LEFT);
    }

    private function akunAsetTetapAktif()
    {
        return DB::table('akun_perkiraan')
            ->where('aktif', 1)
            ->where(function ($query) {
                $query->where('tipe_akun', 'FASS')
                    ->orWhere('kode_perkiraan', 'like', '1200%')
                    ->orWhere('kode_perkiraan', 'like', '12%');
            })
            ->where('tipe_akun', '!=', 'DEPR')
            ->whereNotNull('id_akun_induk')
            ->orderBy('kode_perkiraan')
            ->get(['id_akun_perkiraan', 'kode_perkiraan', 'nama', 'tipe_akun']);
    }

    private function akunAktivaGantungSemua()
    {
        return DB::table('akun_perkiraan')
            ->where('aktif', 1)
            ->where(function ($query) {
                $query->where('kode_perkiraan', 'like', '110506%')
                    ->orWhere('kode_perkiraan', 'like', '110507%')
                    ->orWhere('kode_perkiraan', 'like', '110508%')
                    ->orWhere('kode_perkiraan', 'like', '110509%')
                    ->orWhere('kode_perkiraan', 'like', '110510%')
                    ->orWhere('kode_perkiraan', 'like', '1105%');
            })
            ->orderBy('kode_perkiraan')
            ->get(['id_akun_perkiraan', 'kode_perkiraan', 'nama']);
    }

    private function asetGantungDenganSaldo()
    {
        $transaksiSemua = DB::table('aktiva_gantung_transaksi')
            ->select('aktiva_gantung_id')
            ->selectRaw('COALESCE(SUM(jumlah), 0) as total_terkumpul')
            ->selectRaw('COUNT(*) as jumlah_transaksi')
            ->groupBy('aktiva_gantung_id');

        return DB::table('aktiva_gantung as ag')
            ->leftJoinSub($transaksiSemua, 'ts', 'ts.aktiva_gantung_id', '=', 'ag.id')
            ->select('ag.*')
            ->selectRaw('COALESCE(ts.total_terkumpul, 0) as total_terkumpul')
            ->selectRaw('COALESCE(ts.jumlah_transaksi, 0) as jumlah_transaksi')
            ->orderByRaw("FIELD(ag.status, 'gantung', 'selesai', 'dibatalkan')")
            ->orderBy('ag.nama_aset')
            ->get();
    }

    public function createPembelianUmum(): View
    {
        return view('pembukuan_baru.jurnal_umum.create_pembelian_umum', [
            'title' => 'Tambah Pembelian Umum',
            'noTransaksi' => $this->generateNomorPembelianUmum(),
            'produkList' => $this->produkTbProdukKategori1(),
            'akunDebitDefault' => $this->akunPersediaanUmumDefault(),
            'akunPembayaran' => $this->akunPembayaranUmumAktif(),
        ]);
    }

    public function storePembelianUmum(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'tanggal' => ['required', 'date'],
            'nomor_transaksi' => ['required', 'string', 'max:100'],
            'id_akun_pembayaran' => ['required', 'exists:akun_perkiraan,id_akun_perkiraan'],
            'keterangan_global' => ['nullable', 'string', 'max:255'],
            'detail' => ['required', 'array', 'min:1'],
            'detail.*.id_produk' => ['required', 'exists:tb_produk,id_produk'],
            'detail.*.qty' => ['required', 'numeric', 'min:0.001'],
            'detail.*.satuan' => ['nullable', 'string', 'max:50'],
            'detail.*.harga_satuan' => ['required', 'numeric', 'min:0'],
            'detail.*.keterangan' => ['nullable', 'string', 'max:255'],
        ]);

        $akunDebit = $this->akunPersediaanUmumDefault();
        if (! $akunDebit) {
            return back()
                ->withErrors(['detail' => 'Akun Persediaan Umum (110406) tidak ditemukan / tidak aktif.'])
                ->withInput();
        }

        $detail = collect($validated['detail'])
            ->map(function ($item) {
                $qty = (float) $item['qty'];
                $harga = (float) $item['harga_satuan'];
                $subtotal = round($qty * $harga, 2);
                return [
                    'id_produk' => (int) $item['id_produk'],
                    'qty' => $qty,
                    'satuan' => trim($item['satuan'] ?? ''),
                    'harga_satuan' => $harga,
                    'subtotal' => $subtotal,
                    'keterangan' => trim($item['keterangan'] ?? ''),
                ];
            })
            ->filter(fn($item) => $item['subtotal'] > 0)
            ->values();

        $akunPembayaran = DB::table('akun_perkiraan')
            ->where('id_akun_perkiraan', $validated['id_akun_pembayaran'])
            ->where('aktif', 1)
            ->first();

        if ($detail->isEmpty() || ! $akunPembayaran) {
            return back()
                ->withErrors(['detail' => 'Item pembelian atau akun pembayaran belum valid / aktif.'])
                ->withInput();
        }

        $produkIds = $detail->pluck('id_produk')->unique()->values();
        $produkMap = DB::table('tb_produk')
            ->whereIn('id_produk', $produkIds)
            ->get()
            ->keyBy('id_produk');

        $total = round($detail->sum('subtotal'), 2);

        DB::transaction(function () use ($validated, $detail, $akunPembayaran, $akunDebit, $produkMap, $total) {
            $sekarang = now();

            $batchId = DB::table('impor_jurnal_perkiraan')->insertGetId([
                'nama_file' => 'Pembelian umum ' . $validated['nomor_transaksi'],
                'hash_file' => hash('sha256', 'pembelian-umum|' . $validated['nomor_transaksi'] . '|' . $sekarang->format('YmdHisv')),
                'periode_awal' => $validated['tanggal'],
                'periode_akhir' => $validated['tanggal'],
                'jumlah_transaksi' => 1,
                'jumlah_detail' => $detail->count() + 1,
                'total_debit' => $total,
                'total_kredit' => $total,
                'status' => 'aktif',
                'diimpor_oleh' => auth()->id(),
                'created_at' => $sekarang,
                'updated_at' => $sekarang,
            ]);

            $rows = $detail->map(function ($item, $index) use ($batchId, $validated, $produkMap, $akunDebit, $sekarang) {
                $p = $produkMap[$item['id_produk']] ?? null;
                $nmProduk = $p ? $p->nm_produk : 'Produk #' . $item['id_produk'];
                $deskripsi = 'Pembelian ' . $nmProduk . ' (' . number_format($item['qty'], 2) . ' ' . $item['satuan'] . ' @ Rp ' . number_format($item['harga_satuan'], 0) . ')';
                if (! empty($item['keterangan'])) {
                    $deskripsi .= ' - ' . $item['keterangan'];
                }

                return [
                    'id_impor_jurnal_perkiraan' => $batchId,
                    'id_akun_perkiraan' => $akunDebit->id_akun_perkiraan,
                    'tanggal' => $validated['tanggal'],
                    'nomor_transaksi' => $validated['nomor_transaksi'],
                    'tipe_transaksi' => 'Pembelian Umum',
                    'urutan_detail' => $index + 1,
                    'deskripsi' => $deskripsi,
                    'debit' => $item['subtotal'],
                    'kredit' => 0,
                    'created_at' => $sekarang,
                    'updated_at' => $sekarang,
                ];
            })->push([
                'id_impor_jurnal_perkiraan' => $batchId,
                'id_akun_perkiraan' => $akunPembayaran->id_akun_perkiraan,
                'tanggal' => $validated['tanggal'],
                'nomor_transaksi' => $validated['nomor_transaksi'],
                'tipe_transaksi' => 'Pembelian Umum',
                'urutan_detail' => $detail->count() + 1,
                'deskripsi' => 'Pembayaran pembelian umum' . ($validated['keterangan_global'] ? ' (' . $validated['keterangan_global'] . ')' : '') . ' via ' . $akunPembayaran->nama,
                'debit' => 0,
                'kredit' => $total,
                'created_at' => $sekarang,
                'updated_at' => $sekarang,
            ])->all();

            DB::table('jurnal_perkiraan')->insert($rows);
        });

        return redirect()
            ->route('pembukuan-baru.jurnal-umum.index', ['kelompok' => 'pembelian-umum'])
            ->with('sukses', 'Pembelian umum berhasil disimpan.');
    }

    public function editPembelianUmum(string $nomor_transaksi): View|RedirectResponse
    {
        $jurnal = DB::table('jurnal_perkiraan as j')
            ->leftJoin('akun_perkiraan as a', 'a.id_akun_perkiraan', '=', 'j.id_akun_perkiraan')
            ->where('j.nomor_transaksi', $nomor_transaksi)
            ->where('j.tipe_transaksi', 'Pembelian Umum')
            ->orderBy('j.urutan_detail')
            ->get([
                'j.id_jurnal_perkiraan',
                'j.tanggal',
                'j.nomor_transaksi',
                'j.tipe_transaksi',
                'j.deskripsi',
                'j.debit',
                'j.kredit',
                'j.id_akun_perkiraan',
                'a.kode_perkiraan',
                'a.nama as nama_akun',
            ]);

        if ($jurnal->isEmpty()) {
            return redirect()
                ->route('pembukuan-baru.jurnal-umum.index', ['kelompok' => 'pembelian-umum'])
                ->with('error', 'Transaksi pembelian umum tidak ditemukan.');
        }

        $rowKredit = $jurnal->firstWhere('kredit', '>', 0);
        $rowsDebit = $jurnal->where('debit', '>', 0)->values();

        return view('pembukuan_baru.jurnal_umum.edit_pembelian_umum', [
            'title' => 'Edit Pembelian Umum',
            'nomorTransaksi' => $nomor_transaksi,
            'tanggal' => $jurnal->first()->tanggal,
            'keteranganGlobal' => $rowKredit ? $rowKredit->deskripsi : '',
            'idAkunPembayaran' => $rowKredit ? $rowKredit->id_akun_perkiraan : null,
            'rowsDebit' => $rowsDebit,
            'produkList' => $this->produkTbProdukKategori1(),
            'akunDebitDefault' => $this->akunPersediaanUmumDefault(),
            'akunPembayaran' => $this->akunPembayaranUmumAktif(),
        ]);
    }

    public function updatePembelianUmum(Request $request, string $nomor_transaksi): RedirectResponse
    {
        $validated = $request->validate([
            'tanggal' => ['required', 'date'],
            'id_akun_pembayaran' => ['required', 'exists:akun_perkiraan,id_akun_perkiraan'],
            'keterangan_global' => ['nullable', 'string', 'max:255'],
            'detail' => ['required', 'array', 'min:1'],
            'detail.*.id_produk' => ['required', 'exists:tb_produk,id_produk'],
            'detail.*.qty' => ['required', 'numeric', 'min:0.001'],
            'detail.*.satuan' => ['nullable', 'string', 'max:50'],
            'detail.*.harga_satuan' => ['required', 'numeric', 'min:0'],
            'detail.*.keterangan' => ['nullable', 'string', 'max:255'],
        ]);

        $akunDebit = $this->akunPersediaanUmumDefault();
        if (! $akunDebit) {
            return back()
                ->withErrors(['detail' => 'Akun Persediaan Umum (110406) tidak ditemukan / tidak aktif.'])
                ->withInput();
        }

        $detail = collect($validated['detail'])
            ->map(function ($item) {
                $qty = (float) $item['qty'];
                $harga = (float) $item['harga_satuan'];
                $subtotal = round($qty * $harga, 2);
                return [
                    'id_produk' => (int) $item['id_produk'],
                    'qty' => $qty,
                    'satuan' => trim($item['satuan'] ?? ''),
                    'harga_satuan' => $harga,
                    'subtotal' => $subtotal,
                    'keterangan' => trim($item['keterangan'] ?? ''),
                ];
            })
            ->filter(fn($item) => $item['subtotal'] > 0)
            ->values();

        $akunPembayaran = DB::table('akun_perkiraan')
            ->where('id_akun_perkiraan', $validated['id_akun_pembayaran'])
            ->where('aktif', 1)
            ->first();

        if ($detail->isEmpty() || ! $akunPembayaran) {
            return back()
                ->withErrors(['detail' => 'Item pembelian atau akun pembayaran belum valid / aktif.'])
                ->withInput();
        }

        $produkIds = $detail->pluck('id_produk')->unique()->values();
        $produkMap = DB::table('tb_produk')
            ->whereIn('id_produk', $produkIds)
            ->get()
            ->keyBy('id_produk');

        $total = round($detail->sum('subtotal'), 2);

        DB::transaction(function () use ($validated, $nomor_transaksi, $detail, $akunPembayaran, $akunDebit, $produkMap, $total) {
            $sekarang = now();

            $batchId = DB::table('jurnal_perkiraan')
                ->where('nomor_transaksi', $nomor_transaksi)
                ->where('tipe_transaksi', 'Pembelian Umum')
                ->value('id_impor_jurnal_perkiraan');

            if ($batchId) {
                DB::table('impor_jurnal_perkiraan')
                    ->where('id_impor_jurnal_perkiraan', $batchId)
                    ->update([
                        'periode_awal' => $validated['tanggal'],
                        'periode_akhir' => $validated['tanggal'],
                        'jumlah_detail' => $detail->count() + 1,
                        'total_debit' => $total,
                        'total_kredit' => $total,
                        'updated_at' => $sekarang,
                    ]);
            } else {
                $batchId = DB::table('impor_jurnal_perkiraan')->insertGetId([
                    'nama_file' => 'Pembelian umum ' . $nomor_transaksi,
                    'hash_file' => hash('sha256', 'pembelian-umum|' . $nomor_transaksi . '|' . $sekarang->format('YmdHisv')),
                    'periode_awal' => $validated['tanggal'],
                    'periode_akhir' => $validated['tanggal'],
                    'jumlah_transaksi' => 1,
                    'jumlah_detail' => $detail->count() + 1,
                    'total_debit' => $total,
                    'total_kredit' => $total,
                    'status' => 'aktif',
                    'diimpor_oleh' => auth()->id(),
                    'created_at' => $sekarang,
                    'updated_at' => $sekarang,
                ]);
            }

            DB::table('jurnal_perkiraan')
                ->where('nomor_transaksi', $nomor_transaksi)
                ->where('tipe_transaksi', 'Pembelian Umum')
                ->delete();

            $rows = $detail->map(function ($item, $index) use ($batchId, $validated, $nomor_transaksi, $produkMap, $akunDebit, $sekarang) {
                $p = $produkMap[$item['id_produk']] ?? null;
                $nmProduk = $p ? $p->nm_produk : 'Produk #' . $item['id_produk'];
                $deskripsi = 'Pembelian ' . $nmProduk . ' (' . number_format($item['qty'], 2) . ' ' . $item['satuan'] . ' @ Rp ' . number_format($item['harga_satuan'], 0) . ')';
                if (! empty($item['keterangan'])) {
                    $deskripsi .= ' - ' . $item['keterangan'];
                }

                return [
                    'id_impor_jurnal_perkiraan' => $batchId,
                    'id_akun_perkiraan' => $akunDebit->id_akun_perkiraan,
                    'tanggal' => $validated['tanggal'],
                    'nomor_transaksi' => $nomor_transaksi,
                    'tipe_transaksi' => 'Pembelian Umum',
                    'urutan_detail' => $index + 1,
                    'deskripsi' => $deskripsi,
                    'debit' => $item['subtotal'],
                    'kredit' => 0,
                    'created_at' => $sekarang,
                    'updated_at' => $sekarang,
                ];
            })->push([
                'id_impor_jurnal_perkiraan' => $batchId,
                'id_akun_perkiraan' => $akunPembayaran->id_akun_perkiraan,
                'tanggal' => $validated['tanggal'],
                'nomor_transaksi' => $nomor_transaksi,
                'tipe_transaksi' => 'Pembelian Umum',
                'urutan_detail' => $detail->count() + 1,
                'deskripsi' => 'Pembayaran pembelian umum' . ($validated['keterangan_global'] ? ' (' . $validated['keterangan_global'] . ')' : '') . ' via ' . $akunPembayaran->nama,
                'debit' => 0,
                'kredit' => $total,
                'created_at' => $sekarang,
                'updated_at' => $sekarang,
            ])->all();

            DB::table('jurnal_perkiraan')->insert($rows);
        });

        return redirect()
            ->route('pembukuan-baru.jurnal-umum.index', ['kelompok' => 'pembelian-umum'])
            ->with('sukses', 'Pembelian umum berhasil diperbarui.');
    }

    public function destroyPembelianUmum(string $nomor_transaksi): RedirectResponse
    {
        DB::transaction(function () use ($nomor_transaksi) {
            $batchIds = DB::table('jurnal_perkiraan')
                ->where('nomor_transaksi', $nomor_transaksi)
                ->where('tipe_transaksi', 'Pembelian Umum')
                ->pluck('id_impor_jurnal_perkiraan')
                ->filter()
                ->unique();

            DB::table('jurnal_perkiraan')
                ->where('nomor_transaksi', $nomor_transaksi)
                ->where('tipe_transaksi', 'Pembelian Umum')
                ->delete();

            if ($batchIds->isNotEmpty()) {
                DB::table('impor_jurnal_perkiraan')
                    ->whereIn('id_impor_jurnal_perkiraan', $batchIds)
                    ->delete();
            }
        });

        return redirect()
            ->route('pembukuan-baru.jurnal-umum.index', ['kelompok' => 'pembelian-umum'])
            ->with('sukses', 'Transaksi pembelian umum berhasil dihapus.');
    }

    private function generateNomorPembelianUmum(): string
    {
        $prefix = 'PU-' . now()->format('Ymd') . '-';
        $last = DB::table('jurnal_perkiraan')
            ->where('nomor_transaksi', 'like', $prefix . '%')
            ->orderByDesc('nomor_transaksi')
            ->value('nomor_transaksi');

        $next = $last ? ((int) substr($last, -3)) + 1 : 1;

        return $prefix . str_pad((string) $next, 3, '0', STR_PAD_LEFT);
    }

    private function produkTbProdukKategori1()
    {
        return DB::table('tb_produk as p')
            ->leftJoin('tb_satuan as s', 's.id_satuan', '=', 'p.satuan_id')
            ->where('p.kategori_id', 1)
            ->select('p.id_produk', 'p.kd_produk', 'p.nm_produk', 'p.satuan_id', 's.nm_satuan')
            ->orderBy('p.nm_produk')
            ->get();
    }

    private function akunBebanUmumAktif()
    {
        return DB::table('akun_perkiraan')
            ->where('aktif', 1)
            ->whereIn('tipe_akun', ['EXPS', 'OEXP', 'COGS', 'INVT', 'OCAS', 'FASS'])
            ->whereNotNull('id_akun_induk')
            ->orderBy('kode_perkiraan')
            ->get(['id_akun_perkiraan', 'kode_perkiraan', 'nama', 'tipe_akun']);
    }

    private function akunPembayaranUmumAktif()
    {
        return DB::table('akun_perkiraan')
            ->where('aktif', 1)
            ->where(function ($query) {
                $query->whereIn('tipe_akun', ['BANK', 'APAY', 'OCLI'])
                    ->orWhere('kode_perkiraan', 'like', '1101%')
                    ->orWhere('kode_perkiraan', 'like', '2101%')
                    ->orWhere('nama', 'like', '%kas%')
                    ->orWhere('nama', 'like', '%bank%')
                    ->orWhere('nama', 'like', '%hutang%');
            })
            ->whereNotNull('id_akun_induk')
            ->orderBy('kode_perkiraan')
            ->get(['id_akun_perkiraan', 'kode_perkiraan', 'nama', 'tipe_akun']);
    }

    private function akunPersediaanUmumDefault(): ?object
    {
        return DB::table('akun_perkiraan')
            ->where('aktif', 1)
            ->where('kode_perkiraan', '110406')
            ->first(['id_akun_perkiraan', 'kode_perkiraan', 'nama']);
    }
}
