<?php

namespace App\Http\Controllers;

use App\Exports\TemplateJurnalPerkiraanExport;
use App\Exports\LaporanLabaRugiPerkiraanExport;
use App\Http\Requests\PratinjauJurnalPerkiraanRequest;
use App\Models\AkunPerkiraan;
use App\Models\ImporJurnalPerkiraan;
use App\Models\JurnalPerkiraan;
use App\Services\ImporJurnalPerkiraanService;
use App\Services\LaporanLabaRugiPerkiraanService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class JurnalPerkiraanController extends Controller
{
    public function index(): View
    {
        return view('jurnal_perkiraan.index', [
            'title' => 'Jurnal Perkiraan',
            'batch' => ImporJurnalPerkiraan::latest('id_impor_jurnal_perkiraan')->get(),
            'preview' => session('preview_jurnal_perkiraan'),
        ]);
    }

    public function pratinjau(PratinjauJurnalPerkiraanRequest $request, ImporJurnalPerkiraanService $service): RedirectResponse
    {
        $preview = $service->pratinjau($request->file('file'));
        $token = (string) Str::uuid();
        Cache::put($this->cacheKey($token), $preview, now()->addMinutes(30));

        return back()->with('preview_jurnal_perkiraan', [
            'token' => $token,
            'ringkasan' => collect($preview)->except('detail')->all(),
        ]);
    }

    public function simpan(Request $request, ImporJurnalPerkiraanService $service): RedirectResponse
    {
        $request->validate(['token' => ['required', 'uuid']]);
        $preview = Cache::pull($this->cacheKey($request->token));
        abort_unless(is_array($preview), 419, 'Preview sudah kedaluwarsa. Silakan upload ulang.');

        $batch = $service->simpan($preview, auth()->id());

        return redirect()->route('jurnal-perkiraan.detail-batch', $batch)
            ->with('sukses', "{$batch->jumlah_detail} detail jurnal berhasil diimport.");
    }

    public function batalkan(ImporJurnalPerkiraan $impor_jurnal_perkiraan): RedirectResponse
    {
        DB::transaction(function () use ($impor_jurnal_perkiraan) {
            DB::update(
                '
                    UPDATE stok_produk_perencanaan AS s
                    INNER JOIN jurnal_perkiraan_stok_perencanaan AS m
                        ON m.id_stok_telur = s.id_stok_telur
                    SET s.`check` = m.check_sebelum,
                        s.cek_admin = m.cek_admin_sebelum
                    WHERE m.id_impor_jurnal_perkiraan = ?
                ',
                [$impor_jurnal_perkiraan->getKey()]
            );

            $impor_jurnal_perkiraan->detail()->delete();
            $impor_jurnal_perkiraan->delete();
        });

        return redirect()->route('jurnal-perkiraan.index')
            ->with('sukses', 'Batch import dan seluruh detail jurnal berhasil dihapus permanen.');
    }

    public function detailBatch(Request $request, ImporJurnalPerkiraan $impor_jurnal_perkiraan): View
    {
        $filters = $request->validate([
            'cari' => ['nullable', 'string', 'max:100'],
            'tipe' => ['nullable', 'string', 'max:100'],
            'per_page' => ['nullable', 'integer', 'in:25,50,100'],
        ]);
        $query = $impor_jurnal_perkiraan->detail()
            ->with('akun:id_akun_perkiraan,kode_perkiraan,nama')
            ->when($filters['tipe'] ?? null, fn ($query, $tipe) => $query->where('tipe_transaksi', $tipe))
            ->when($filters['cari'] ?? null, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('nomor_transaksi', 'like', "%{$search}%")
                        ->orWhere('tipe_transaksi', 'like', "%{$search}%")
                        ->orWhere('deskripsi', 'like', "%{$search}%")
                        ->orWhereHas('akun', fn ($account) => $account
                            ->where('kode_perkiraan', 'like', "%{$search}%")
                            ->orWhere('nama', 'like', "%{$search}%"));
                });
            })
            ->orderBy('tanggal')->orderBy('nomor_transaksi')->orderBy('urutan_detail');

        return view('jurnal_perkiraan.detail_batch', [
            'title' => 'Detail Import Jurnal',
            'batch' => $impor_jurnal_perkiraan,
            'detail' => $query->paginate($filters['per_page'] ?? 50)->withQueryString(),
            'tipeOptions' => $impor_jurnal_perkiraan->detail()->whereNotNull('tipe_transaksi')
                ->distinct()->orderBy('tipe_transaksi')->pluck('tipe_transaksi'),
        ]);
    }

    public function labaRugi(Request $request, LaporanLabaRugiPerkiraanService $service): View
    {
        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
            7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];
        $years = range(now()->year - 5, now()->year + 1);
        $result = null;
        $start = null;
        $end = null;

        if ($request->filled('bulan_dari')) {
            $data = $request->validate([
                'bulan_dari' => ['required', 'integer', 'between:1,12'],
                'tahun_dari' => ['required', 'integer', 'between:2000,2100'],
                'bulan_sampai' => ['required', 'integer', 'between:1,12'],
                'tahun_sampai' => ['required', 'integer', 'between:2000,2100'],
            ]);
            $start = Carbon::create($data['tahun_dari'], $data['bulan_dari'], 1)->startOfMonth();
            $end = Carbon::create($data['tahun_sampai'], $data['bulan_sampai'], 1)->startOfMonth();
            if ($start->gt($end)) {
                throw ValidationException::withMessages(['bulan_sampai' => 'Periode akhir harus setelah periode awal.']);
            }
            if ($start->diffInMonths($end) > 23) {
                throw ValidationException::withMessages(['bulan_sampai' => 'Maksimal laporan 24 bulan.']);
            }
            $result = $service->buat($start, $end);
        }

        return view('jurnal_perkiraan.laba_rugi', [
            'title' => 'Laba/Rugi (Multi Periode)',
            'months' => $months,
            'years' => $years,
            'result' => $result,
            'start' => $start,
            'end' => $end,
        ]);
    }

    public function detailAkun(Request $request, AkunPerkiraan $akun_perkiraan): View
    {
        $request->validate(['tanggal_awal' => ['required', 'date'], 'tanggal_akhir' => ['required', 'date', 'after_or_equal:tanggal_awal']]);

        $accountIds = $this->accountSubtreeIds($akun_perkiraan);
        $detail = JurnalPerkiraan::with(['impor', 'akun'])
            ->whereIn('id_akun_perkiraan', $accountIds)
            ->whereBetween('tanggal', [$request->tanggal_awal, $request->tanggal_akhir])
            ->whereHas('impor', fn ($query) => $query->where('status', 'aktif'))
            ->orderBy('tanggal')->orderBy('nomor_transaksi')->orderBy('urutan_detail')->get();
        $totalDebit = $detail->reduce(fn ($total, $item) => bcadd($total, $item->debit, 12), '0.000000000000');
        $totalKredit = $detail->reduce(fn ($total, $item) => bcadd($total, $item->kredit, 12), '0.000000000000');
        $nilaiLaporan = in_array($akun_perkiraan->tipe_akun, ['REVE', 'OINC'], true)
            ? bcsub($totalKredit, $totalDebit, 12)
            : bcsub($totalDebit, $totalKredit, 12);

        return view('jurnal_perkiraan.detail_akun', [
            'title' => 'Detail Jurnal '.$akun_perkiraan->nama,
            'akun' => $akun_perkiraan,
            'tanggalAwal' => $request->tanggal_awal,
            'tanggalAkhir' => $request->tanggal_akhir,
            'detail' => $detail,
            'totalDebit' => $totalDebit,
            'totalKredit' => $totalKredit,
            'nilaiLaporan' => $nilaiLaporan,
            'jumlahAkun' => count($accountIds),
        ]);
    }

    public function exportLabaRugi(Request $request, LaporanLabaRugiPerkiraanService $service): BinaryFileResponse
    {
        $data = $request->validate([
            'bulan_dari' => ['required', 'integer', 'between:1,12'],
            'tahun_dari' => ['required', 'integer', 'between:2000,2100'],
            'bulan_sampai' => ['required', 'integer', 'between:1,12'],
            'tahun_sampai' => ['required', 'integer', 'between:2000,2100'],
        ]);

        $start = Carbon::create($data['tahun_dari'], $data['bulan_dari'], 1)->startOfMonth();
        $end = Carbon::create($data['tahun_sampai'], $data['bulan_sampai'], 1)->startOfMonth();
        if ($start->gt($end)) {
            throw ValidationException::withMessages(['bulan_sampai' => 'Periode akhir harus setelah periode awal.']);
        }
        if ($start->diffInMonths($end) > 23) {
            throw ValidationException::withMessages(['bulan_sampai' => 'Maksimal laporan 24 bulan.']);
        }

        $filename = "laba-rugi-{$start->format('Y-m')}-sampai-{$end->format('Y-m')}.xlsx";

        return Excel::download(new LaporanLabaRugiPerkiraanExport($service->buat($start, $end)), $filename);
    }

    public function template(): BinaryFileResponse
    {
        return Excel::download(new TemplateJurnalPerkiraanExport(), 'template-jurnal-perkiraan.xlsx');
    }

    private function cacheKey(string $token): string
    {
        return 'preview_jurnal_perkiraan:'.auth()->id().':'.$token;
    }

    private function accountSubtreeIds(AkunPerkiraan $account): array
    {
        $ids = [$account->getKey()];
        foreach ($account->akunAnak as $child) {
            $ids = array_merge($ids, $this->accountSubtreeIds($child));
        }

        return $ids;
    }
}
