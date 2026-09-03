<?php

namespace App\Http\Controllers;

use App\Models\SetorKas;
use App\Models\SetorKasDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SetorKasController extends Controller
{
    private const DEFAULT_SOURCE_ACCOUNT_CODES = [
        '110103',
        '110105',
        '110107',
        '110108',
        '110109',
        '110110',
        '110111',
    ];

    public function index(Request $request)
    {
        $tanggalAwal = $request->input('tanggal_awal', now()->startOfMonth()->toDateString());
        $tanggalAkhir = $request->input('tanggal_akhir', now()->toDateString());
        $cari = trim((string) $request->input('cari', ''));

        $setoranKas = SetorKas::with('akunTujuan', 'detail')
            ->whereBetween('tanggal_setoran', [$tanggalAwal, $tanggalAkhir])
            ->when($cari !== '', function ($query) use ($cari) {
                $query->where(function ($query) use ($cari) {
                    $query->where('nomor_setoran', 'like', "%{$cari}%")
                        ->orWhere('nomor_referensi', 'like', "%{$cari}%")
                        ->orWhere('keterangan', 'like', "%{$cari}%")
                        ->orWhereHas('akunTujuan', function ($akunQuery) use ($cari) {
                            $akunQuery->where('kode_perkiraan', 'like', "%{$cari}%")
                                ->orWhere('nama', 'like', "%{$cari}%");
                        });
                });
            })
            ->orderByDesc('tanggal_setoran')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('transaksi.setoran_kas.index', [
            'title' => 'Setoran Kas/Bank',
            'setoranKas' => $setoranKas,
            'tanggalAwal' => $tanggalAwal,
            'tanggalAkhir' => $tanggalAkhir,
            'cari' => $cari,
        ]);
    }

    public function create()
    {
        // Ambil jurnal kas penjualan yang belum disetorkan
        $akunSumberTersedia = $this->sourceAccountCandidates();
        $akunSumberTerpilih = $this->configuredSourceAccountIds($akunSumberTersedia);
        
        $jurnalBelumDisetorkan = DB::table('jurnal_perkiraan as j')
            ->join('akun_perkiraan as a', 'a.id_akun_perkiraan', '=', 'j.id_akun_perkiraan')
            ->join('impor_jurnal_perkiraan as i', 'i.id_impor_jurnal_perkiraan', '=', 'j.id_impor_jurnal_perkiraan')
            ->leftJoinSub($this->customerTransactionSubquery(), 'customer_transaksi', function ($join) {
                $join->on('customer_transaksi.nomor_transaksi', '=', 'j.nomor_transaksi');
            })
            ->where('i.status', 'aktif')
            ->where('i.sumber_data', 'sistem')
            ->whereIn('j.id_akun_perkiraan', $akunSumberTerpilih)
            ->where('j.debit', '>', 0) // Hanya yang masuk (debit)
            ->where(function ($query) {
                $query->whereNotNull('customer_transaksi.nama_customer')
                    ->orWhere('j.tipe_transaksi', 'like', '%Setoran Kas%');
            })
            ->whereNotIn('j.id_jurnal_perkiraan', function($query) {
                $query->select('jurnal_perkiraan_id')->from('setoran_kas_detail');
            })
            ->select(
                'j.id_jurnal_perkiraan',
                'j.tanggal',
                'j.nomor_transaksi',
                'j.tipe_transaksi',
                'j.debit',
                'j.id_akun_perkiraan',
                'a.kode_perkiraan',
                'a.nama',
                DB::raw("COALESCE(customer_transaksi.nama_customer, '0') as nama_customer"),
                DB::raw("CASE
                    WHEN LOWER(j.tipe_transaksi) LIKE '%telur%' THEN 'Penjualan Telur'
                    WHEN LOWER(j.tipe_transaksi) LIKE '%ayam%' THEN 'Penjualan Ayam'
                    ELSE 'Setoran Kas'
                END as asal")
            )
            ->orderByDesc('j.tanggal')
            ->get();

        // Ambil akun bank/kas untuk tujuan setoran
        $akunBank = DB::table('akun_perkiraan')
            ->where('aktif', 1)
            ->where('kode_perkiraan', 'like', '110%') // Akun kas/bank
            ->orderBy('kode_perkiraan')
            ->get(['id_akun_perkiraan', 'kode_perkiraan', 'nama']);

        return view('transaksi.setoran_kas.create', [
            'title' => 'Buat Setoran Kas/Bank',
            'jurnalBelumDisetorkan' => $jurnalBelumDisetorkan,
            'akunBank' => $akunBank,
            'akunSumberTersedia' => $akunSumberTersedia,
            'akunSumberTerpilih' => $akunSumberTerpilih,
        ]);
    }

    public function saveSourceAccountSettings(Request $request)
    {
        $validated = $request->validateWithBag('sourceAccountSetting', [
            'akun_sumber' => ['required', 'array', 'min:1'],
            'akun_sumber.*' => ['required', 'integer', 'distinct', 'exists:akun_perkiraan,id_akun_perkiraan'],
        ], [
            'akun_sumber.required' => 'Pilih minimal satu akun kas sumber.',
            'akun_sumber.min' => 'Pilih minimal satu akun kas sumber.',
        ]);

        $akunIds = collect($validated['akun_sumber'])
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $allowedIds = $this->sourceAccountCandidates()
            ->pluck('id_akun_perkiraan')
            ->map(fn ($id) => (int) $id);

        if ($akunIds->diff($allowedIds)->isNotEmpty()) {
            return back()
                ->withErrors(['akun_sumber' => 'Pilihan hanya boleh menggunakan akun kas/bank aktif.'], 'sourceAccountSetting')
                ->withInput();
        }

        $userId = auth()->id();
        $existing = DB::table('setoran_kas_setting')
            ->where('id_user', $userId)
            ->first();

        if ($existing) {
            DB::table('setoran_kas_setting')
                ->where('id', $existing->id)
                ->update([
                    'akun_sumber_ids' => json_encode($akunIds->all()),
                    'updated_at' => now(),
                ]);
        } else {
            DB::table('setoran_kas_setting')->insert([
                'id_user' => $userId,
                'akun_sumber_ids' => json_encode($akunIds->all()),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return redirect()
            ->route('transaksi.setoran-kas.create')
            ->with('sukses', 'Pengaturan akun kas sumber berhasil disimpan.');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tanggal_setoran'  => ['required', 'date'],
            'akun_tujuan_id'   => ['required', 'exists:akun_perkiraan,id_akun_perkiraan'],
            'keterangan'       => ['nullable', 'string', 'max:255'],
            'nomor_referensi'  => ['nullable', 'string', 'max:50'],
            'jurnal_terpilih'  => ['required', 'array', 'min:1'],
            'jurnal_terpilih.*' => ['required', 'integer'],
            'nominal_aktual'   => ['nullable', 'array'],
            'nominal_aktual.*' => ['nullable', 'numeric', 'min:0'],
        ]);

        $akunSumberTerpilih = $this->configuredSourceAccountIds($this->sourceAccountCandidates());
        $idJurnalDiminta = collect($validated['jurnal_terpilih'])->map(fn ($id) => (int) $id)->unique()->values();
        $jurnalTerpilih = DB::table('jurnal_perkiraan as j')
            ->join('akun_perkiraan as a', 'a.id_akun_perkiraan', '=', 'j.id_akun_perkiraan')
            ->join('impor_jurnal_perkiraan as i', 'i.id_impor_jurnal_perkiraan', '=', 'j.id_impor_jurnal_perkiraan')
            ->leftJoinSub($this->customerTransactionSubquery(), 'customer_transaksi', function ($join) {
                $join->on('customer_transaksi.nomor_transaksi', '=', 'j.nomor_transaksi');
            })
            ->whereIn('j.id_jurnal_perkiraan', $validated['jurnal_terpilih'])
            ->where('i.status', 'aktif')
            ->where('i.sumber_data', 'sistem')
            ->whereIn('j.id_akun_perkiraan', $akunSumberTerpilih)
            ->where('j.debit', '>', 0)
            ->where(function ($query) {
                $query->whereNotNull('customer_transaksi.nama_customer')
                    ->orWhere('j.tipe_transaksi', 'like', '%Setoran Kas%');
            })
            ->whereNotIn('j.id_jurnal_perkiraan', function ($query) {
                $query->select('jurnal_perkiraan_id')->from('setoran_kas_detail');
            })
            ->select('j.id_jurnal_perkiraan', 'j.debit', 'j.id_akun_perkiraan')
            ->get();

        if ($jurnalTerpilih->count() !== $idJurnalDiminta->count()) {
            return back()
                ->withErrors(['jurnal_terpilih' => 'Ada jurnal impor, jurnal yang sudah disetorkan, atau jurnal yang tidak valid dalam pilihan. Silakan pilih ulang.'])
                ->withInput();
        }

        if ($jurnalTerpilih->pluck('id_akun_perkiraan')->unique()->count() > 1) {
            return back()
                ->withErrors(['jurnal_terpilih' => 'Transaksi yang disetorkan harus berasal dari satu akun kas sumber yang sama agar setoran tidak tercampur.'])
                ->withInput();
        }

        // Map nominal aktual: keyed by jurnal_perkiraan_id
        $nominalAktualMap = collect($validated['nominal_aktual'] ?? [])
            ->mapWithKeys(fn ($val, $id) => [(int)$id => (float)$val]);

        // Total based on nominal aktual (for the actual setoran amount)
        $totalNominalJurnal = $jurnalTerpilih->sum('debit');
        $totalNominalAktual = $jurnalTerpilih->sum(function ($j) use ($nominalAktualMap) {
            return $nominalAktualMap->get($j->id_jurnal_perkiraan, $j->debit);
        });
        $totalSelisih = $totalNominalAktual - $totalNominalJurnal; // + = lebih, - = kurang

        $setorKas = DB::transaction(function () use ($validated, $jurnalTerpilih, $totalNominalJurnal, $totalNominalAktual, $totalSelisih, $nominalAktualMap) {
            $sekarang      = now();
            $tanggalClean  = date('Ymd', strtotime($validated['tanggal_setoran']));

            $countHariIni  = DB::table('setoran_kas')
                ->whereDate('tanggal_setoran', $validated['tanggal_setoran'])
                ->count();
            $nomorSetoran  = 'SK-' . $tanggalClean . '-' . sprintf('%03d', $countHariIni + 1);

            $setor = SetorKas::create([
                'nomor_setoran'   => $nomorSetoran,
                'tanggal_setoran' => $validated['tanggal_setoran'],
                'akun_tujuan_id'  => $validated['akun_tujuan_id'],
                'nominal_total'   => $totalNominalAktual, // simpan nominal aktual yg benar-benar disetor
                'keterangan'      => $validated['keterangan'],
                'nomor_referensi' => $validated['nomor_referensi'],
            ]);

            foreach ($jurnalTerpilih as $jurnal) {
                $aktual = $nominalAktualMap->get($jurnal->id_jurnal_perkiraan, $jurnal->debit);
                SetorKasDetail::create([
                    'setoran_kas_id'     => $setor->id,
                    'jurnal_perkiraan_id' => $jurnal->id_jurnal_perkiraan,
                    'akun_sumber_id'     => $jurnal->id_akun_perkiraan,
                    'nominal'            => $aktual,
                ]);
            }

            // Ambil data akun tujuan
            $akunTujuan = DB::table('akun_perkiraan')->where('id_akun_perkiraan', $validated['akun_tujuan_id'])->first();

            // Kelompokkan kredit per akun kas sumber (menggunakan nominal jurnal asli di kas sumber)
            $kreditPerAkun = $jurnalTerpilih->groupBy('id_akun_perkiraan')->map(function ($rows) {
                return $rows->sum('debit');
            });

            // Hitung jumlah detail jurnal: 1 debit bank + n kredit kas sumber + selisih (jika ada)
            $jumlahDetail = 1 + $kreditPerAkun->count() + ($totalSelisih != 0 ? 1 : 0);
            $totalBatchDebitKredit = max($totalNominalAktual, $totalNominalJurnal);

            // 1. Buat batch di impor_jurnal_perkiraan
            $batchId = DB::table('impor_jurnal_perkiraan')->insertGetId([
                'nama_file'          => 'Setoran Kas ' . $nomorSetoran,
                'sumber_data'        => 'sistem',
                'hash_file'          => hash('sha256', 'setoran-kas|' . $setor->id . '|' . $sekarang->timestamp),
                'periode_awal'       => $validated['tanggal_setoran'],
                'periode_akhir'      => $validated['tanggal_setoran'],
                'jumlah_transaksi'   => 1,
                'jumlah_detail'      => $jumlahDetail,
                'total_debit'        => $totalBatchDebitKredit,
                'total_kredit'       => $totalBatchDebitKredit,
                'status'             => 'aktif',
                'diimpor_oleh'       => auth()->id() ?? 1,
                'created_at'         => $sekarang,
                'updated_at'         => $sekarang,
            ]);

            $setor->update(['id_impor_jurnal_perkiraan' => $batchId]);

            // 2. Susun detail jurnal perkiraan
            $detailJurnal = [];
            $urutan = 1;

            // Baris DEBIT: Kas/Bank Tujuan sebesar nominal aktual
            $detailJurnal[] = [
                'id_impor_jurnal_perkiraan' => $batchId,
                'id_akun_perkiraan'         => $akunTujuan->id_akun_perkiraan,
                'tanggal'                   => $validated['tanggal_setoran'],
                'nomor_transaksi'           => $nomorSetoran,
                'tipe_transaksi'            => 'Setoran Kas Penjualan',
                'urutan_detail'             => $urutan++,
                'deskripsi'                 => 'Penerimaan setoran kas ke ' . $akunTujuan->nama . ($validated['keterangan'] ? ' (' . $validated['keterangan'] . ')' : ''),
                'debit'                     => $totalNominalAktual,
                'kredit'                    => 0,
                'created_at'                => $sekarang,
                'updated_at'                => $sekarang,
            ];

            // Baris KREDIT: per akun kas sumber (nominal aktual)
            foreach ($kreditPerAkun as $idAkunSumber => $nominalSumber) {
                $akunSumber = DB::table('akun_perkiraan')->where('id_akun_perkiraan', $idAkunSumber)->first();
                $detailJurnal[] = [
                    'id_impor_jurnal_perkiraan' => $batchId,
                    'id_akun_perkiraan'         => $idAkunSumber,
                    'tanggal'                   => $validated['tanggal_setoran'],
                    'nomor_transaksi'           => $nomorSetoran,
                    'tipe_transaksi'            => 'Setoran Kas Penjualan',
                    'urutan_detail'             => $urutan++,
                    'deskripsi'                 => 'Penyetoran kas dari ' . ($akunSumber->nama ?? 'Kas Sumber') . ($validated['keterangan'] ? ' (' . $validated['keterangan'] . ')' : ''),
                    'debit'                     => 0,
                    'kredit'                    => $nominalSumber,
                    'created_at'                => $sekarang,
                    'updated_at'                => $sekarang,
                ];
            }

            // Baris SELISIH: jika ada perbedaan antara nominal jurnal dan nominal aktual
            if ($totalSelisih != 0) {
                $akunSelisih = DB::table('akun_perkiraan')->where('kode_perkiraan', '710001')->first();
                if ($akunSelisih) {
                    if ($totalSelisih > 0) {
                        // Lebih bayar → Pendapatan (kredit akun 710001)
                        // Debit sudah ter-cover di baris akun tujuan (lebih besar)
                        // Tambah 1 baris kredit ke 710001
                        $detailJurnal[] = [
                            'id_impor_jurnal_perkiraan' => $batchId,
                            'id_akun_perkiraan'         => $akunSelisih->id_akun_perkiraan,
                            'tanggal'                   => $validated['tanggal_setoran'],
                            'nomor_transaksi'           => $nomorSetoran,
                            'tipe_transaksi'            => 'Setoran Kas Penjualan',
                            'urutan_detail'             => $urutan++,
                            'deskripsi'                 => 'Selisih lebih setoran kas ' . $nomorSetoran,
                            'debit'                     => 0,
                            'kredit'                    => $totalSelisih,
                            'created_at'                => $sekarang,
                            'updated_at'                => $sekarang,
                        ];
                    } else {
                        // Kurang bayar → Kerugian (debit akun 710001)
                        // Kredit sudah ter-cover (lebih besar dari debit akun tujuan)
                        $detailJurnal[] = [
                            'id_impor_jurnal_perkiraan' => $batchId,
                            'id_akun_perkiraan'         => $akunSelisih->id_akun_perkiraan,
                            'tanggal'                   => $validated['tanggal_setoran'],
                            'nomor_transaksi'           => $nomorSetoran,
                            'tipe_transaksi'            => 'Setoran Kas Penjualan',
                            'urutan_detail'             => $urutan++,
                            'deskripsi'                 => 'Selisih kurang setoran kas ' . $nomorSetoran,
                            'debit'                     => abs($totalSelisih),
                            'kredit'                    => 0,
                            'created_at'                => $sekarang,
                            'updated_at'                => $sekarang,
                        ];
                    }
                }
            }

            DB::table('jurnal_perkiraan')->insert($detailJurnal);

            return $setor;
        });

        return redirect()
            ->route('transaksi.setoran-kas.index')
            ->with('sukses', 'Setoran kas berhasil dibuat dan jurnal perkiraan telah tercatat.');
    }

    public function show(SetorKas $setorKas)
    {
        $setorKas->load('akunTujuan', 'detail.jurnalPerkiraan', 'detail.akunSumber');

        $jurnalHasil = collect();
        if ($setorKas->id_impor_jurnal_perkiraan) {
            $jurnalHasil = DB::table('jurnal_perkiraan as j')
                ->join('akun_perkiraan as a', 'a.id_akun_perkiraan', '=', 'j.id_akun_perkiraan')
                ->where('j.id_impor_jurnal_perkiraan', $setorKas->id_impor_jurnal_perkiraan)
                ->orderBy('j.urutan_detail')
                ->select('j.*', 'a.kode_perkiraan', 'a.nama as nama_akun')
                ->get();
        } elseif ($setorKas->nomor_setoran) {
            $jurnalHasil = DB::table('jurnal_perkiraan as j')
                ->join('akun_perkiraan as a', 'a.id_akun_perkiraan', '=', 'j.id_akun_perkiraan')
                ->where('j.nomor_transaksi', $setorKas->nomor_setoran)
                ->orderBy('j.urutan_detail')
                ->select('j.*', 'a.kode_perkiraan', 'a.nama as nama_akun')
                ->get();
        }

        return view('transaksi.setoran_kas.detail', [
            'title' => 'Detail Setoran Kas/Bank',
            'setorKas' => $setorKas,
            'jurnalHasil' => $jurnalHasil,
        ]);
    }

    public function cetak(SetorKas $setorKas)
    {
        $setorKas->load('akunTujuan', 'detail.jurnalPerkiraan', 'detail.akunSumber');

        $jurnalHasil = collect();
        if ($setorKas->id_impor_jurnal_perkiraan) {
            $jurnalHasil = DB::table('jurnal_perkiraan as j')
                ->join('akun_perkiraan as a', 'a.id_akun_perkiraan', '=', 'j.id_akun_perkiraan')
                ->where('j.id_impor_jurnal_perkiraan', $setorKas->id_impor_jurnal_perkiraan)
                ->orderBy('j.urutan_detail')
                ->select('j.*', 'a.kode_perkiraan', 'a.nama as nama_akun')
                ->get();
        } elseif ($setorKas->nomor_setoran) {
            $jurnalHasil = DB::table('jurnal_perkiraan as j')
                ->join('akun_perkiraan as a', 'a.id_akun_perkiraan', '=', 'j.id_akun_perkiraan')
                ->where('j.nomor_transaksi', $setorKas->nomor_setoran)
                ->orderBy('j.urutan_detail')
                ->select('j.*', 'a.kode_perkiraan', 'a.nama as nama_akun')
                ->get();
        }

        return view('transaksi.setoran_kas.cetak', [
            'title' => 'Bukti Setoran Kas/Bank',
            'setorKas' => $setorKas,
            'jurnalHasil' => $jurnalHasil,
        ]);
    }

    public function destroy(SetorKas $setorKas)
    {
        DB::transaction(function () use ($setorKas) {
            $batchId = $setorKas->id_impor_jurnal_perkiraan;

            if ($batchId) {
                DB::table('jurnal_perkiraan')->where('id_impor_jurnal_perkiraan', $batchId)->delete();
                DB::table('impor_jurnal_perkiraan')->where('id_impor_jurnal_perkiraan', $batchId)->delete();
            } elseif ($setorKas->nomor_setoran) {
                DB::table('jurnal_perkiraan')->where('nomor_transaksi', $setorKas->nomor_setoran)->delete();
            }

            $setorKas->detail()->delete();
            $setorKas->delete();
        });

        return redirect()
            ->route('transaksi.setoran-kas.index')
            ->with('sukses', 'Setoran kas beserta jurnal perkiraannya berhasil dihapus.');
    }

    private function sourceAccountCandidates()
    {
        return DB::table('akun_perkiraan')
            ->where('aktif', 1)
            ->where('tipe_akun', 'BANK')
            ->whereNotNull('id_akun_induk')
            ->orderBy('kode_perkiraan')
            ->get(['id_akun_perkiraan', 'kode_perkiraan', 'nama']);
    }

    private function customerTransactionSubquery()
    {
        $penjualanTelur = DB::table('invoice_telur as transaksi')
            ->leftJoin('customer as customer_utama', 'customer_utama.id_customer', '=', 'transaksi.id_customer')
            ->leftJoin('customer as customer_kedua', 'customer_kedua.id_customer', '=', 'transaksi.id_customer2')
            ->selectRaw("transaksi.no_nota as nomor_transaksi, MAX(COALESCE(NULLIF(TRIM(customer_utama.nm_customer), ''), NULLIF(TRIM(customer_kedua.nm_customer), ''), NULLIF(TRIM(transaksi.customer), ''))) as nama_customer")
            ->groupBy('transaksi.no_nota');

        $penjualanAyam = DB::table('invoice_ayam as transaksi')
            ->leftJoin('customer as customer_utama', 'customer_utama.id_customer', '=', 'transaksi.id_customer')
            ->leftJoin('customer as customer_kedua', 'customer_kedua.id_customer', '=', 'transaksi.id_customer2')
            ->selectRaw("transaksi.no_nota as nomor_transaksi, MAX(COALESCE(NULLIF(TRIM(customer_utama.nm_customer), ''), NULLIF(TRIM(customer_kedua.nm_customer), ''), NULLIF(TRIM(transaksi.customer), ''))) as nama_customer")
            ->groupBy('transaksi.no_nota');

        $penjualanUmum = DB::table('penjualan_agl as transaksi')
            ->leftJoin('customer as customer_utama', 'customer_utama.id_customer', '=', 'transaksi.id_customer')
            ->selectRaw("CASE
                WHEN NULLIF(TRIM(transaksi.nota_manual), '') IS NOT NULL THEN transaksi.nota_manual
                WHEN transaksi.lokasi = 'alpa' THEN CONCAT('PU-', transaksi.urutan)
                ELSE CONCAT('PUM-', transaksi.urutan)
            END as nomor_transaksi")
            ->selectRaw("MAX(COALESCE(NULLIF(TRIM(customer_utama.nm_customer), ''), NULLIF(NULLIF(TRIM(CAST(transaksi.id_customer AS CHAR)), ''), '0'))) as nama_customer")
            ->groupBy('nomor_transaksi');

        $semuaCustomer = $penjualanTelur
            ->unionAll($penjualanAyam)
            ->unionAll($penjualanUmum);

        return DB::query()
            ->fromSub($semuaCustomer, 'sumber_customer')
            ->select('nomor_transaksi')
            ->selectRaw('MAX(nama_customer) as nama_customer')
            ->whereNotNull('nama_customer')
            ->whereRaw("TRIM(nama_customer) <> ''")
            ->groupBy('nomor_transaksi');
    }

    private function configuredSourceAccountIds($availableAccounts): array
    {
        $availableIds = $availableAccounts
            ->pluck('id_akun_perkiraan')
            ->map(fn ($id) => (int) $id);

        $setting = DB::table('setoran_kas_setting')
            ->where('id_user', auth()->id())
            ->first();

        if ($setting && $setting->akun_sumber_ids) {
            $savedIds = collect(json_decode($setting->akun_sumber_ids, true) ?: [])
                ->map(fn ($id) => (int) $id)
                ->intersect($availableIds)
                ->values();

            if ($savedIds->isNotEmpty()) {
                return $savedIds->all();
            }
        }

        return $availableAccounts
            ->whereIn('kode_perkiraan', self::DEFAULT_SOURCE_ACCOUNT_CODES)
            ->pluck('id_akun_perkiraan')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
    }
}
