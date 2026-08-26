<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AktivaGantungController extends Controller
{
    public function index(): View
    {
        $saldo = DB::table('aktiva_gantung_transaksi')
            ->select('aktiva_gantung_id')
            ->selectRaw('SUM(jumlah) as total_saldo')
            ->groupBy('aktiva_gantung_id');

        return view('pembukuan_baru.aktiva_gantung.index', [
            'title' => 'Aktiva Gantung',
            'aktivaGantung' => DB::table('aktiva_gantung as ag')
                ->leftJoinSub($saldo, 's', 's.aktiva_gantung_id', '=', 'ag.id')
                ->select('ag.*')
                ->selectRaw('COALESCE(s.total_saldo, 0) as total_saldo')
                ->orderByDesc('ag.id')
                ->paginate(15),
            'totalSaldo' => DB::table('aktiva_gantung_transaksi')->sum('jumlah'),
            'akunAktivaGantung' => DB::table('akun_perkiraan')
                ->where('aktif', 1)
                ->where('kode_perkiraan', 'like', '1105%')
                ->orderBy('kode_perkiraan')
                ->get(['id_akun_perkiraan', 'kode_perkiraan', 'nama']),
        ]);
    }

    public function storeSaldoAwal(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'tanggal' => ['required', 'date'],
            'detail' => ['required', 'array', 'min:1'],
            'detail.*.nama_aset' => ['required', 'string', 'max:255'],
            'detail.*.id_akun_aktiva_gantung' => ['required', 'integer', 'exists:akun_perkiraan,id_akun_perkiraan'],
            'detail.*.jumlah' => ['required', 'numeric', 'min:0.01'],
            'detail.*.keterangan' => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($validated) {
            $sekarang = now();
            foreach ($validated['detail'] as $index => $detail) {
                $kode = $this->generateKode();
                $asetId = DB::table('aktiva_gantung')->insertGetId([
                    'kode' => $kode,
                    'nama_aset' => trim($detail['nama_aset']),
                    'keterangan' => $detail['keterangan'] ?? 'Saldo awal dari jurnal perkiraan yang telah diimpor',
                    'status' => 'gantung',
                    'created_by' => auth()->id(),
                    'created_at' => $sekarang,
                    'updated_at' => $sekarang,
                ]);

                DB::table('aktiva_gantung_transaksi')->insert([
                    'aktiva_gantung_id' => $asetId,
                    'tanggal' => $validated['tanggal'],
                    'nomor_transaksi' => 'SA-AG-' . $sekarang->format('YmdHis') . '-' . str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT),
                    'id_akun_aktiva_gantung' => $detail['id_akun_aktiva_gantung'],
                    'id_akun_kas' => null,
                    'jumlah' => round((float) $detail['jumlah'], 2),
                    'keterangan' => $detail['keterangan'] ?? 'Saldo awal aktiva gantung; jurnal sudah diimpor sebelumnya',
                    'sumber' => 'saldo_awal',
                    'id_impor_jurnal_perkiraan' => null,
                    'created_by' => auth()->id(),
                    'created_at' => $sekarang,
                    'updated_at' => $sekarang,
                ]);
            }
        });

        return redirect()->route('pembukuan-baru.aktiva-gantung.index')
            ->with('sukses', 'Saldo awal aktiva gantung berhasil disimpan tanpa membuat jurnal baru.');
    }

    private function generateKode(): string
    {
        $prefix = 'AG-' . now()->format('Ymd') . '-';
        $last = DB::table('aktiva_gantung')->where('kode', 'like', $prefix . '%')
            ->orderByDesc('kode')->value('kode');
        $nomor = ($last ? (int) substr($last, -3) : 0) + 1;

        return $prefix . str_pad((string) $nomor, 3, '0', STR_PAD_LEFT);
    }
}
