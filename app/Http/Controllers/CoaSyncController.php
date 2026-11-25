<?php

namespace App\Http\Controllers;

use App\Models\AkunPerkiraan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

/**
 * Halaman Sinkron COA: lihat status koneksi ke aplikasi pusat, dan tarik COA sekarang.
 * Token dan secret tidak pernah ditampilkan, hanya ada atau tidaknya.
 */
class CoaSyncController extends Controller
{
    public function index(): View
    {
        $cursorFile = $this->cursorFile();
        $cursor = Storage::exists($cursorFile) ? (int) Storage::get($cursorFile) : null;

        return view('master-coa-sync.index', [
            'title' => 'Sinkron COA',
            'apiUrl' => (string) config('services.coa.api_url'),
            'hasToken' => config('services.coa.api_token') !== null && config('services.coa.api_token') !== '',
            'cursor' => $cursor,
            'lastSync' => Storage::exists($cursorFile) ? date('d-m-Y H:i', Storage::lastModified($cursorFile)) : null,
            'totalAccounts' => AkunPerkiraan::query()->count(),
            'activeAccounts' => AkunPerkiraan::query()->where('aktif', true)->count(),
            'recent' => AkunPerkiraan::query()->orderByDesc('updated_at')->limit(10)->get(['kode_perkiraan', 'nama', 'aktif', 'updated_at']),
        ]);
    }

    public function tarik(): RedirectResponse
    {
        $exitCode = Artisan::call('coa:pull');
        $output = trim(Artisan::output());

        return redirect()->route('master.coa-sync.index')
            ->with($exitCode === 0 ? 'success' : 'error', $output !== '' ? $output : 'Penarikan selesai.');
    }

    /**
     * Same file name as the coa:pull command uses for this database.
     */
    private function cursorFile(): string
    {
        return 'coa-sync-cursor-'.config('database.connections.'.config('database.default').'.database').'.txt';
    }
}
