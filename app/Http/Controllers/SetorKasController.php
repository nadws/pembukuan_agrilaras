<?php

namespace App\Http\Controllers;

use App\Models\SetorKas;
use App\Models\SetorKasDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SetorKasController extends Controller
{
    public function index(Request $request)
    {
        $tanggalAwal = $request->input('tanggal_awal', now()->startOfMonth()->toDateString());
        $tanggalAkhir = $request->input('tanggal_akhir', now()->toDateString());

        $setoranKas = SetorKas::with('akunTujuan', 'detail')
            ->whereBetween('tanggal_setoran', [$tanggalAwal, $tanggalAkhir])
            ->orderByDesc('tanggal_setoran')
            ->paginate(15)
            ->withQueryString();

        return view('transaksi.setoran_kas.index', [
            'title' => 'Setoran Kas/Bank',
            'setoranKas' => $setoranKas,
            'tanggalAwal' => $tanggalAwal,
            'tanggalAkhir' => $tanggalAkhir,
        ]);
    }

    public function create()
    {
        // Ambil jurnal kas penjualan yang belum disetorkan
        $akunKasPenjualan = ['110108', '110109', '110110', '110111', '110107', '110105', '110103'];
        
        $jurnalBelumDisetorkan = DB::table('jurnal_perkiraan as j')
            ->join('akun_perkiraan as a', 'a.id_akun_perkiraan', '=', 'j.id_akun_perkiraan')
            ->join('impor_jurnal_perkiraan as i', 'i.id_impor_jurnal_perkiraan', '=', 'j.id_impor_jurnal_perkiraan')
            ->where('i.status', 'aktif')
            ->where('i.sumber_data', 'sistem')
            ->whereIn('a.kode_perkiraan', $akunKasPenjualan)
            ->where('j.debit', '>', 0) // Hanya yang masuk (debit)
            ->whereNotIn('j.id_jurnal_perkiraan', function($query) {
                $query->select('jurnal_perkiraan_id')->from('setoran_kas_detail');
            })
            ->select('j.id_jurnal_perkiraan', 'j.tanggal', 'j.nomor_transaksi', 'j.deskripsi', 'j.debit', 'j.id_akun_perkiraan', 'a.kode_perkiraan', 'a.nama')
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
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tanggal_setoran' => ['required', 'date'],
            'akun_tujuan_id' => ['required', 'exists:akun_perkiraan,id_akun_perkiraan'],
            'keterangan' => ['nullable', 'string', 'max:255'],
            'nomor_referensi' => ['nullable', 'string', 'max:50'],
            'jurnal_terpilih' => ['required', 'array', 'min:1'],
            'jurnal_terpilih.*' => ['required', 'integer'],
        ]);

        $akunKasPenjualan = ['110108', '110109', '110110', '110111', '110107', '110105', '110103'];
        $idJurnalDiminta = collect($validated['jurnal_terpilih'])->map(fn ($id) => (int) $id)->unique()->values();
        $jurnalTerpilih = DB::table('jurnal_perkiraan as j')
            ->join('akun_perkiraan as a', 'a.id_akun_perkiraan', '=', 'j.id_akun_perkiraan')
            ->join('impor_jurnal_perkiraan as i', 'i.id_impor_jurnal_perkiraan', '=', 'j.id_impor_jurnal_perkiraan')
            ->whereIn('j.id_jurnal_perkiraan', $validated['jurnal_terpilih'])
            ->where('i.status', 'aktif')
            ->where('i.sumber_data', 'sistem')
            ->whereIn('a.kode_perkiraan', $akunKasPenjualan)
            ->where('j.debit', '>', 0)
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

        $totalNominal = $jurnalTerpilih->sum('debit');

        $setorKas = DB::transaction(function () use ($validated, $jurnalTerpilih, $totalNominal) {
            $sekarang = now();
            $tanggalClean = date('Ymd', strtotime($validated['tanggal_setoran']));
            
            $countHariIni = DB::table('setoran_kas')
                ->whereDate('tanggal_setoran', $validated['tanggal_setoran'])
                ->count();
            $nomorSetoran = 'SK-' . $tanggalClean . '-' . sprintf('%03d', $countHariIni + 1);

            $setor = SetorKas::create([
                'nomor_setoran' => $nomorSetoran,
                'tanggal_setoran' => $validated['tanggal_setoran'],
                'akun_tujuan_id' => $validated['akun_tujuan_id'],
                'nominal_total' => $totalNominal,
                'keterangan' => $validated['keterangan'],
                'nomor_referensi' => $validated['nomor_referensi'],
            ]);

            foreach ($jurnalTerpilih as $jurnal) {
                SetorKasDetail::create([
                    'setoran_kas_id' => $setor->id,
                    'jurnal_perkiraan_id' => $jurnal->id_jurnal_perkiraan,
                    'akun_sumber_id' => $jurnal->id_akun_perkiraan,
                    'nominal' => $jurnal->debit,
                ]);
            }

            // Ambil data akun tujuan
            $akunTujuan = DB::table('akun_perkiraan')->where('id_akun_perkiraan', $validated['akun_tujuan_id'])->first();

            // Kelompokkan total kredit per akun kas sumber
            $kreditPerAkun = $jurnalTerpilih->groupBy('id_akun_perkiraan')->map->sum('debit');
            $jumlahDetail = 1 + $kreditPerAkun->count();

            // 1. Buat batch di impor_jurnal_perkiraan
            $batchId = DB::table('impor_jurnal_perkiraan')->insertGetId([
                'nama_file' => 'Setoran Kas ' . $nomorSetoran,
                'sumber_data' => 'sistem',
                'hash_file' => hash('sha256', 'setoran-kas|' . $setor->id . '|' . $sekarang->timestamp),
                'periode_awal' => $validated['tanggal_setoran'],
                'periode_akhir' => $validated['tanggal_setoran'],
                'jumlah_transaksi' => 1,
                'jumlah_detail' => $jumlahDetail,
                'total_debit' => $totalNominal,
                'total_kredit' => $totalNominal,
                'status' => 'aktif',
                'diimpor_oleh' => auth()->id() ?? 1,
                'created_at' => $sekarang,
                'updated_at' => $sekarang,
            ]);

            $setor->update(['id_impor_jurnal_perkiraan' => $batchId]);

            // 2. Buat detail jurnal perkiraan (Debit: Akun Tujuan, Kredit: Akun Sumber)
            $detailJurnal = [];
            $urutan = 1;

            // Baris DEBIT (Kas/Bank Tujuan Masuk)
            $detailJurnal[] = [
                'id_impor_jurnal_perkiraan' => $batchId,
                'id_akun_perkiraan' => $akunTujuan->id_akun_perkiraan,
                'tanggal' => $validated['tanggal_setoran'],
                'nomor_transaksi' => $nomorSetoran,
                'tipe_transaksi' => 'Setoran Kas Penjualan',
                'urutan_detail' => $urutan++,
                'deskripsi' => 'Penerimaan setoran kas ke ' . $akunTujuan->nama . ($validated['keterangan'] ? ' (' . $validated['keterangan'] . ')' : ''),
                'debit' => $totalNominal,
                'kredit' => 0,
                'created_at' => $sekarang,
                'updated_at' => $sekarang,
            ];

            // Baris KREDIT (Kas Sumber Berkurang)
            foreach ($kreditPerAkun as $idAkunSumber => $nominalSumber) {
                $akunSumber = DB::table('akun_perkiraan')->where('id_akun_perkiraan', $idAkunSumber)->first();
                $detailJurnal[] = [
                    'id_impor_jurnal_perkiraan' => $batchId,
                    'id_akun_perkiraan' => $idAkunSumber,
                    'tanggal' => $validated['tanggal_setoran'],
                    'nomor_transaksi' => $nomorSetoran,
                    'tipe_transaksi' => 'Setoran Kas Penjualan',
                    'urutan_detail' => $urutan++,
                    'deskripsi' => 'Penyetoran kas penjualan dari ' . ($akunSumber->nama ?? 'Kas Sumber') . ($validated['keterangan'] ? ' (' . $validated['keterangan'] . ')' : ''),
                    'debit' => 0,
                    'kredit' => $nominalSumber,
                    'created_at' => $sekarang,
                    'updated_at' => $sekarang,
                ];
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

        return view('transaksi.setoran_kas.detail', [
            'title' => 'Detail Setoran Kas/Bank',
            'setorKas' => $setorKas,
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
}
