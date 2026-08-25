<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PiutangTransaksiController extends Controller
{
    public function index(Request $request)
    {
        $jenis = in_array($request->input('jenis'), ['telur', 'ayam', 'umum'], true) ? $request->input('jenis') : 'telur';
        $awal = $request->input('tanggal_awal', date('Y-m-01'));
        $akhir = $request->input('tanggal_akhir', date('Y-m-d'));
        $cari = trim((string) $request->input('cari', ''));

        if ($jenis === 'ayam') {
            $piutang = DB::table('invoice_ayam as i')
                ->leftJoin('customer as c', 'c.id_customer', '=', 'i.id_customer')
                ->where('i.lokasi', 'alpa')->where('i.status', 'unpaid')->whereBetween('i.tgl', [$awal, $akhir])
                ->when($cari !== '', fn ($q) => $q->where(fn ($s) => $s->where('i.no_nota', 'like', "%{$cari}%")->orWhere('c.nm_customer', 'like', "%{$cari}%")))
                ->select('i.no_nota', 'i.tgl', 'i.id_customer', 'i.qty', 'i.h_satuan', 'c.nm_customer', DB::raw('i.qty * i.h_satuan as total_rp'))
                ->orderByDesc('i.tgl')->orderByDesc('i.urutan')->get();
        } elseif ($jenis === 'umum') {
            $piutang = DB::table('penjualan_agl as i')
                ->leftJoin('customer as c', 'c.id_customer', '=', 'i.id_customer')
                ->where('i.lokasi', 'alpa')->where('i.status', 'unpaid')->whereBetween('i.tgl', [$awal, $akhir])
                ->when($cari !== '', fn ($q) => $q->where(fn ($s) => $s->where('i.urutan', 'like', "%{$cari}%")->orWhere('c.nm_customer', 'like', "%{$cari}%")))
                ->select(DB::raw("CONCAT('PU-', i.urutan) as no_nota"), 'i.tgl', 'i.id_customer', 'c.nm_customer', DB::raw('SUM(i.total_rp) as total_rp'), DB::raw('SUM(i.qty) as qty'))
                ->groupBy('i.urutan', 'i.tgl', 'i.id_customer', 'c.nm_customer')
                ->orderByDesc('i.tgl')->orderByDesc('i.urutan')->get();
        } else {
            $piutang = DB::table('invoice_telur as i')
                ->leftJoin('customer as c', 'c.id_customer', '=', 'i.id_customer')
                ->whereIn('i.lokasi', ['alpa', 'mtd'])->where('i.status', 'unpaid')->whereBetween('i.tgl', [$awal, $akhir])
                ->when($cari !== '', fn ($q) => $q->where(fn ($s) => $s->where('i.no_nota', 'like', "%{$cari}%")->orWhere('c.nm_customer', 'like', "%{$cari}%")))
                ->select('i.no_nota', 'i.tgl', 'i.id_customer', 'i.tipe', 'c.nm_customer', DB::raw('SUM(i.total_rp) as total_rp'))
                ->groupBy('i.no_nota', 'i.tgl', 'i.id_customer', 'i.tipe', 'c.nm_customer')
                ->orderByDesc('i.tgl')->orderByDesc('i.no_nota')->get();
        }

        return view('transaksi.piutang.index', compact('jenis', 'awal', 'akhir', 'cari', 'piutang'));
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

        $total = $jenis === 'ayam' ? $rows->sum(fn ($row) => (float) $row->qty * (float) $row->h_satuan) : $rows->sum(fn ($row) => (float) $row->total_rp);
        $akunPembayaran = DB::table('akun_perkiraan')->where('aktif', 1)->where('tipe_akun', 'BANK')->orderBy('kode_perkiraan')->get(['id_akun_perkiraan', 'kode_perkiraan', 'nama']);

        return view('transaksi.piutang.pelunasan', compact('jenis', 'nota', 'rows', 'total', 'akunPembayaran'));
    }

    public function storePelunasan(Request $request)
    {
        $validated = $request->validate([
            'jenis' => ['required', 'in:telur,ayam,umum'],
            'tanggal_bayar' => ['required', 'date'],
            'id_akun_pembayaran' => ['required', 'exists:akun_perkiraan,id_akun_perkiraan'],
            'nota' => ['required', 'array', 'min:1'],
            'nota.*' => ['required', 'string', 'max:100'],
        ]);

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

        $total = $validated['jenis'] === 'ayam' ? $rows->sum(fn ($row) => (float) $row->qty * (float) $row->h_satuan) : $rows->sum(fn ($row) => (float) $row->total_rp);
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

        DB::transaction(function () use ($validated, $rows, $table, $akunPembayaran, $akunPiutang, $total, $tipeJurnal, $nota) {
            $now = now();
            $nomorTransaksi = 'PL-' . strtoupper($validated['jenis']) . '-' . $now->format('YmdHis');
            $batchId = DB::table('impor_jurnal_perkiraan')->insertGetId([
                'nama_file' => 'Pelunasan piutang ' . strtoupper($validated['jenis']),
                'hash_file' => hash('sha256', 'pelunasan-piutang|' . $nomorTransaksi),
                'periode_awal' => $validated['tanggal_bayar'],
                'periode_akhir' => $validated['tanggal_bayar'],
                'jumlah_transaksi' => count($nota),
                'jumlah_detail' => 2,
                'total_debit' => $total,
                'total_kredit' => $total,
                'status' => 'aktif',
                'diimpor_oleh' => auth()->id(),
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('jurnal_perkiraan')->insert([
                ['id_impor_jurnal_perkiraan' => $batchId, 'id_akun_perkiraan' => $akunPembayaran->id_akun_perkiraan, 'tanggal' => $validated['tanggal_bayar'], 'nomor_transaksi' => $nomorTransaksi, 'tipe_transaksi' => $tipeJurnal, 'urutan_detail' => 1, 'deskripsi' => 'Penerimaan pelunasan piutang ' . implode(', ', $nota), 'debit' => $total, 'kredit' => 0, 'created_at' => $now, 'updated_at' => $now],
                ['id_impor_jurnal_perkiraan' => $batchId, 'id_akun_perkiraan' => $akunPiutang->id_akun_perkiraan, 'tanggal' => $validated['tanggal_bayar'], 'nomor_transaksi' => $nomorTransaksi, 'tipe_transaksi' => $tipeJurnal, 'urutan_detail' => 2, 'deskripsi' => 'Pelunasan piutang ' . implode(', ', $nota), 'debit' => 0, 'kredit' => $total, 'created_at' => $now, 'updated_at' => $now],
            ]);

            $paymentRows = $rows->groupBy('no_nota')->map(function ($items, $noNota) use ($validated, $table, $akunPembayaran, $batchId) {
                $jumlah = $table === 'invoice_ayam'
                    ? $items->sum(fn ($row) => (float) $row->qty * (float) $row->h_satuan)
                    : $items->sum(fn ($row) => (float) $row->total_rp);

                return [
                    'jenis' => $validated['jenis'],
                    'no_nota' => $noNota,
                    'id_customer' => $items->first()->id_customer,
                    'tanggal_bayar' => $validated['tanggal_bayar'],
                    'jumlah_bayar' => $jumlah,
                    'id_akun_pembayaran' => $akunPembayaran->id_akun_perkiraan,
                    'id_impor_jurnal_perkiraan' => $batchId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })->values()->all();
            DB::table('pelunasan_piutang_penjualan')->insert($paymentRows);

            if ($validated['jenis'] === 'umum') {
                DB::table($table)->where('lokasi', 'alpa')->whereIn('urutan', array_map(fn ($value) => (int) str_replace('PU-', '', $value), $nota))->update(['status' => 'paid']);
            } else {
                $update = DB::table($table)->whereIn('no_nota', $nota);
                $validated['jenis'] === 'telur'
                    ? $update->whereIn('lokasi', ['alpa', 'mtd'])
                    : $update->where('lokasi', 'alpa');
                $update->update(['status' => 'paid']);
            }
        });

        return redirect()->route('transaksi.piutang.index', ['jenis' => $validated['jenis']])->with('sukses', 'Pelunasan piutang berhasil disimpan.');
    }
}
