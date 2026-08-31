<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PembelianPulletController extends Controller
{
    private const UANG_MUKA_ID = 19;
    private const PERSEDIAAN_AYAM_ID = 24;

    private function accounts()
    {
        return DB::table('akun_perkiraan')->where('aktif', 1)->orderBy('kode_perkiraan')->get();
    }

    private function nomor(string $prefix): string
    {
        return $prefix . '-' . now()->format('YmdHis') . '-' . strtoupper(substr(uniqid(), -4));
    }

    private function batch(string $tanggal, string $nomor, float $nilai): int
    {
        return DB::table('impor_jurnal_perkiraan')->insertGetId([
            'nama_file' => 'Pembelian Pullet ' . $nomor,
            'hash_file' => hash('sha256', $nomor . microtime(true)),
            'periode_awal' => $tanggal,
            'periode_akhir' => $tanggal,
            'jumlah_transaksi' => 1,
            'jumlah_detail' => 2,
            'total_debit' => $nilai,
            'total_kredit' => $nilai,
            'status' => 'aktif',
            'diimpor_oleh' => auth()->id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function journal(int $batchId, int $urutan, string $tanggal, string $nomor, int $akunId, float $debit, float $kredit, string $deskripsi): void
    {
        DB::table('jurnal_perkiraan')->insert([
            'id_impor_jurnal_perkiraan' => $batchId,
            'id_akun_perkiraan' => $akunId,
            'tanggal' => $tanggal,
            'nomor_transaksi' => $nomor,
            'tipe_transaksi' => 'Faktur Pembelian Pullet',
            'urutan_detail' => $urutan,
            'deskripsi' => $deskripsi,
            'debit' => $debit,
            'kredit' => $kredit,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function index(Request $request)
    {
        $q = trim($request->get('q', ''));
        $tanggalAwal = $request->input('tanggal_awal', now()->startOfMonth()->toDateString());
        $tanggalAkhir = $request->input('tanggal_akhir', now()->toDateString());
        $items = DB::table('pembelian_pullet as p')
            ->leftJoin('kandang as k', 'k.id_kandang', '=', 'p.id_kandang')
            ->leftJoin('tb_suplier as s', 's.id_suplier', '=', 'p.id_suplier')
            ->select('p.*', 'k.nm_kandang', 's.nm_suplier')
            ->whereBetween('p.tanggal', [$tanggalAwal, $tanggalAkhir])
            ->when($q, fn ($query) => $query->where(fn ($sub) => $sub
                ->where('p.nomor', 'like', "%{$q}%")
                ->orWhere('p.nama_pullet', 'like', "%{$q}%")
                ->orWhere('s.nm_suplier', 'like', "%{$q}%")))
            ->orderByDesc('p.tanggal')->paginate(10)->withQueryString();

        return view('pembelian_pullet.index', compact('items', 'q', 'tanggalAwal', 'tanggalAkhir'));
    }

    public function create(Request $request)
    {
        return view('pembelian_pullet.create', [
            'accounts' => $this->accounts(),
            'suppliers' => DB::table('tb_suplier')->orderBy('nm_suplier')->get(),
            'pulletBerjalan' => DB::table('pembelian_pullet as p')
                ->leftJoin('tb_suplier as s', 's.id_suplier', '=', 'p.id_suplier')
                ->where('p.status', 'berjalan')
                ->orderBy('p.nama_pullet')
                ->get(['p.id', 'p.nomor', 'p.nama_pullet', 'p.total_nilai', 'p.total_dibayar', 's.nm_suplier']),
            'modeAwal' => $request->input('mode', 'baru'),
            'pulletTerpilih' => $request->input('pullet'),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'mode_pullet' => 'required|in:baru,lama',
            'tanggal' => 'required|date',
            'pembelian_pullet_id' => 'required_if:mode_pullet,lama|nullable|integer|exists:pembelian_pullet,id',
            'id_suplier' => 'required_if:mode_pullet,baru|nullable|integer|exists:tb_suplier,id_suplier',
            'nama_pullet' => 'required_if:mode_pullet,baru|nullable|string|max:150',
            'nilai_pembelian' => 'required_if:mode_pullet,baru|nullable|numeric|min:0.01',
            'nominal' => 'required|numeric|min:0.01',
            'keterangan' => 'nullable|string|max:1000',
            'id_akun_pembayaran' => 'required|integer|exists:akun_perkiraan,id_akun_perkiraan',
        ]);
        if ($data['mode_pullet'] === 'baru' && (float) $data['nominal'] > (float) $data['nilai_pembelian']) {
            return back()->withErrors(['nominal' => 'Pembayaran awal melebihi total nilai pembelian pullet.'])->withInput();
        }

        DB::transaction(function () use ($data) {
            $nomor = $this->nomor('PP');
            $catatan = !empty($data['keterangan']) ? ' - ' . trim($data['keterangan']) : '';
            if ($data['mode_pullet'] === 'lama') {
                $pullet = DB::table('pembelian_pullet')
                    ->where('id', $data['pembelian_pullet_id'])
                    ->where('status', 'berjalan')->lockForUpdate()->first();
                abort_unless($pullet, 422, 'Uang muka pullet sudah ditutup atau tidak ditemukan.');
                $sisaHutang = max((float) $pullet->total_nilai - (float) ($pullet->total_dibayar ?? 0), 0);
                abort_if((float) $data['nominal'] > $sisaHutang, 422, 'Pembayaran melebihi sisa hutang pullet.');
                $batch = $this->batch($data['tanggal'], $nomor, (float) $data['nominal']);
                $this->journal($batch, 1, $data['tanggal'], $nomor, self::UANG_MUKA_ID, $data['nominal'], 0, 'Tambahan uang muka pembelian pullet - ' . $pullet->nama_pullet . $catatan);
                $this->journal($batch, 2, $data['tanggal'], $nomor, $data['id_akun_pembayaran'], 0, $data['nominal'], 'Pembayaran uang muka pullet - ' . $pullet->nama_pullet . $catatan);
                DB::table('pembelian_pullet')->where('id', $pullet->id)->increment('total_dibayar', $data['nominal']);
                DB::table('pembelian_pullet_cicilan')->insert([
                    'pembelian_pullet_id' => $pullet->id, 'tanggal' => $data['tanggal'],
                    'nominal' => $data['nominal'], 'id_akun_pembayaran' => $data['id_akun_pembayaran'],
                    'nomor_transaksi' => $nomor, 'keterangan' => $data['keterangan'] ?? null,
                    'created_at' => now(), 'updated_at' => now(),
                ]);
                return;
            }
            $id = DB::table('pembelian_pullet')->insertGetId([
                'nomor' => $nomor,
                'tanggal' => $data['tanggal'],
                'id_suplier' => $data['id_suplier'],
                'nama_pullet' => $data['nama_pullet'],
                'keterangan' => $data['keterangan'] ?? null,
                'qty' => 0,
                'total_nilai' => $data['nilai_pembelian'],
                'total_dibayar' => $data['nominal'],
                'id_akun_proses' => self::UANG_MUKA_ID,
                'id_akun_hutang' => null,
                'skema_hutang' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $batch = $this->batch($data['tanggal'], $nomor, (float) $data['nominal']);
            $this->journal($batch, 1, $data['tanggal'], $nomor, self::UANG_MUKA_ID, $data['nominal'], 0, 'Uang muka pembelian pullet - ' . $data['nama_pullet'] . $catatan);
            $this->journal($batch, 2, $data['tanggal'], $nomor, $data['id_akun_pembayaran'], 0, $data['nominal'], 'Pembayaran uang muka pullet - ' . $data['nama_pullet'] . $catatan);
            DB::table('pembelian_pullet_cicilan')->insert([
                'pembelian_pullet_id' => $id, 'tanggal' => $data['tanggal'],
                'nominal' => $data['nominal'], 'id_akun_pembayaran' => $data['id_akun_pembayaran'],
                'nomor_transaksi' => $nomor, 'keterangan' => $data['keterangan'] ?? null,
                'created_at' => now(), 'updated_at' => now(),
            ]);
        });

        return redirect()->route('pembelian-pullet.index')->with('success', 'Pembelian pullet tersimpan.');
    }

    public function cicilan($id)
    {
        $p = DB::table('pembelian_pullet')->find($id);
        abort_unless($p && $p->status === 'berjalan', 404);
        $pembayaran = DB::table('pembelian_pullet_cicilan as c')
            ->leftJoin('akun_perkiraan as a', 'a.id_akun_perkiraan', '=', 'c.id_akun_pembayaran')
            ->where('c.pembelian_pullet_id', $id)->orderBy('c.tanggal')->orderBy('c.id')
            ->get(['c.*', 'a.kode_perkiraan', 'a.nama as nama_akun']);
        return view('pembelian_pullet.cicilan', ['p' => $p, 'accounts' => $this->accounts(), 'pembayaran' => $pembayaran]);
    }

    public function storeCicilan(Request $request, $id)
    {
        $p = DB::table('pembelian_pullet')->find($id);
        abort_unless($p && $p->status === 'berjalan', 404);
        $data = $request->validate(['tanggal' => 'required|date', 'nominal' => 'required|numeric|min:0.01', 'id_akun_pembayaran' => 'required|integer', 'keterangan' => 'nullable|string|max:1000']);
        DB::transaction(function () use ($data, $p) {
            $sisaHutang = max((float) $p->total_nilai - (float) ($p->total_dibayar ?? 0), 0);
            abort_if((float) $data['nominal'] > $sisaHutang, 422, 'Pembayaran melebihi sisa hutang pullet.');
            $nomor = $this->nomor('PP');
            $catatan = !empty($data['keterangan']) ? ' - ' . trim($data['keterangan']) : '';
            $batch = $this->batch($data['tanggal'], $nomor, (float) $data['nominal']);
            $this->journal($batch, 1, $data['tanggal'], $nomor, self::UANG_MUKA_ID, $data['nominal'], 0, 'Tambahan uang muka pembelian pullet - ' . $p->nama_pullet . $catatan);
            $this->journal($batch, 2, $data['tanggal'], $nomor, $data['id_akun_pembayaran'], 0, $data['nominal'], 'Pembayaran uang muka pullet - ' . $p->nama_pullet . $catatan);
            DB::table('pembelian_pullet')->where('id', $p->id)->increment('total_dibayar', $data['nominal']);
            DB::table('pembelian_pullet_cicilan')->insert(['pembelian_pullet_id' => $p->id, 'tanggal' => $data['tanggal'], 'nominal' => $data['nominal'], 'id_akun_pembayaran' => $data['id_akun_pembayaran'], 'nomor_transaksi' => $nomor, 'keterangan' => $data['keterangan'] ?? null, 'created_at' => now(), 'updated_at' => now()]);
        });
        return redirect()->route('pembelian-pullet.index')->with('success', 'Cicilan tersimpan.');
    }

    public function editPembayaran($id, $pembayaran)
    {
        $p = DB::table('pembelian_pullet')->find($id);
        abort_unless($p && $p->status === 'berjalan', 404);
        $payment = DB::table('pembelian_pullet_cicilan')
            ->where('id', $pembayaran)->where('pembelian_pullet_id', $id)->first();
        abort_unless($payment, 404);
        $maksimal = max((float) $p->total_nilai - ((float) $p->total_dibayar - (float) $payment->nominal), 0);
        return view('pembelian_pullet.edit_pembayaran', [
            'p' => $p, 'payment' => $payment, 'maksimal' => $maksimal, 'accounts' => $this->accounts(),
        ]);
    }

    public function updatePembayaran(Request $request, $id, $pembayaran)
    {
        $data = $request->validate([
            'tanggal' => 'required|date', 'nominal' => 'required|numeric|min:0.01',
            'id_akun_pembayaran' => 'required|integer|exists:akun_perkiraan,id_akun_perkiraan',
            'keterangan' => 'nullable|string|max:1000',
        ]);

        DB::transaction(function () use ($data, $id, $pembayaran) {
            $p = DB::table('pembelian_pullet')->where('id', $id)->where('status', 'berjalan')->lockForUpdate()->first();
            $payment = DB::table('pembelian_pullet_cicilan')->where('id', $pembayaran)
                ->where('pembelian_pullet_id', $id)->lockForUpdate()->first();
            abort_unless($p && $payment, 404);

            $totalBaru = (float) $p->total_dibayar - (float) $payment->nominal + (float) $data['nominal'];
            abort_if($totalBaru > (float) $p->total_nilai, 422, 'Total uang muka melebihi nilai pembelian pullet.');

            DB::table('pembelian_pullet_cicilan')->where('id', $payment->id)->update([
                'tanggal' => $data['tanggal'], 'nominal' => $data['nominal'],
                'id_akun_pembayaran' => $data['id_akun_pembayaran'],
                'keterangan' => $data['keterangan'] ?? null, 'updated_at' => now(),
            ]);
            DB::table('pembelian_pullet')->where('id', $p->id)->update(['total_dibayar' => $totalBaru, 'updated_at' => now()]);

            $journal = DB::table('jurnal_perkiraan')->where('nomor_transaksi', $payment->nomor_transaksi)
                ->whereIn('tipe_transaksi', ['Faktur Pembelian Pullet', 'Pembelian Pullet'])->get();
            if ($journal->isNotEmpty()) {
                DB::table('jurnal_perkiraan')->whereIn('id_jurnal_perkiraan', $journal->where('debit', '>', 0)->pluck('id_jurnal_perkiraan'))
                    ->update(['tanggal' => $data['tanggal'], 'id_akun_perkiraan' => self::UANG_MUKA_ID, 'debit' => $data['nominal'], 'kredit' => 0, 'deskripsi' => 'Uang muka pembelian pullet' . (!empty($data['keterangan']) ? ' - '.$data['keterangan'] : ''), 'updated_at' => now()]);
                DB::table('jurnal_perkiraan')->whereIn('id_jurnal_perkiraan', $journal->where('kredit', '>', 0)->pluck('id_jurnal_perkiraan'))
                    ->update(['tanggal' => $data['tanggal'], 'id_akun_perkiraan' => $data['id_akun_pembayaran'], 'debit' => 0, 'kredit' => $data['nominal'], 'deskripsi' => 'Pembayaran uang muka pullet' . (!empty($data['keterangan']) ? ' - '.$data['keterangan'] : ''), 'updated_at' => now()]);
                $batchId = $journal->first()->id_impor_jurnal_perkiraan;
                DB::table('impor_jurnal_perkiraan')->where('id_impor_jurnal_perkiraan', $batchId)->update([
                    'periode_awal' => $data['tanggal'], 'periode_akhir' => $data['tanggal'],
                    'total_debit' => $data['nominal'], 'total_kredit' => $data['nominal'], 'updated_at' => now(),
                ]);
            }

            $pertama = DB::table('pembelian_pullet_cicilan')->where('pembelian_pullet_id', $p->id)->orderBy('tanggal')->orderBy('id')->value('id');
            if ((int) $pertama === (int) $payment->id) {
                DB::table('pembelian_pullet')->where('id', $p->id)->update(['tanggal' => $data['tanggal'], 'keterangan' => $data['keterangan'] ?? null]);
            }
        });

        return redirect()->route('pembelian-pullet.cicilan', $id)->with('success', 'Pembayaran berhasil diperbarui.');
    }

    public function finalize($id)
    {
        $p = DB::table('pembelian_pullet')->find($id);
        abort_unless($p && $p->status === 'berjalan', 404);
        return view('pembelian_pullet.finalize', [
            'p' => $p,
            'strains' => DB::table('strain')->orderBy('nm_strain')->get(['id_strain', 'nm_strain']),
        ]);
    }

    public function storeFinalize(Request $request, $id)
    {
        $p = DB::table('pembelian_pullet')->find($id);
        abort_unless($p && $p->status === 'berjalan', 404);
        $sisaPembayaran = max((float) $p->total_nilai - (float) ($p->total_dibayar ?? 0), 0);
        if ($sisaPembayaran > 0) {
            return back()->withErrors(['pembayaran' => 'Uang muka pullet harus dilunasi sebelum masuk kandang. Sisa Rp ' . number_format($sisaPembayaran, 0, ',', '.')]);
        }
        $data = $request->validate([
            'tanggal' => 'required|date', 'qty' => 'required|numeric|min:0.001',
            'id_strain' => 'required|integer|exists:strain,id_strain',
        ]);
        DB::transaction(function () use ($data, $p) {
            $kandangId = DB::table('kandang')->insertGetId([
                'nm_kandang' => $p->nama_pullet, 'chick_in' => $data['tanggal'],
                'tgl_masuk' => $data['tanggal'], 'stok_awal' => $data['qty'],
                'id_strain' => $data['id_strain'], 'selesai' => 'T', 'rupiah' => $p->total_nilai,
                'tgl_masuk_kandang' => $data['tanggal'], 'id_post' => 0,
            ]);
            $nomor = $this->nomor('PPM');
            $batch = $this->batch($data['tanggal'], $nomor, (float) $p->total_nilai);
            $this->journal($batch, 1, $data['tanggal'], $nomor, self::PERSEDIAAN_AYAM_ID, $p->total_nilai, 0, 'Pullet masuk kandang menjadi Persediaan Ayam');
            $this->journal($batch, 2, $data['tanggal'], $nomor, self::UANG_MUKA_ID, 0, $p->total_nilai, 'Penutupan Uang Muka Pembelian Pullet');
            DB::table('pembelian_pullet')->where('id', $p->id)->update(['qty' => $data['qty'], 'status' => 'selesai', 'id_kandang' => $kandangId, 'tanggal_masuk_kandang' => $data['tanggal'], 'updated_at' => now()]);
        });
        return redirect()->route('pembelian-pullet.index')->with('success', 'Pullet berhasil masuk kandang dan transaksi dikunci.');
    }
}
