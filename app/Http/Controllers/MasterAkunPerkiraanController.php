<?php

namespace App\Http\Controllers;

use App\Exports\AkunPerkiraanBaruExport;
use App\Http\Requests\ImportAkunPerkiraanRequest;
use App\Http\Requests\SimpanAkunPerkiraanRequest;
use App\Models\AkunPerkiraan;
use App\Services\ImportAkunPerkiraanService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class MasterAkunPerkiraanController extends Controller
{
    public function index(Request $request): View
    {
        $query = AkunPerkiraan::with('akunInduk')->orderBy('kode_perkiraan');

        $query->when($request->filled('tipe_akun'), fn ($query) => $query->where('tipe_akun', $request->tipe_akun));
        $query->when($request->status === 'aktif', fn ($query) => $query->where('aktif', true));
        $query->when($request->status === 'nonaktif', fn ($query) => $query->where('aktif', false));

        return view('data_master.akun_perkiraan.index', [
            'title' => 'Akun Perkiraan',
            'akun' => $query->get(),
            'akunInduk' => AkunPerkiraan::where('aktif', true)->orderBy('kode_perkiraan')->get(),
            'tipeAkun' => AkunPerkiraan::query()->distinct()->orderBy('tipe_akun')->pluck('tipe_akun'),
            'preview' => session('preview_akun_perkiraan'),
        ]);
    }

    public function store(SimpanAkunPerkiraanRequest $request): RedirectResponse
    {
        AkunPerkiraan::create($request->validated());

        return back()->with('sukses', 'Akun perkiraan berhasil ditambahkan.');
    }

    public function update(SimpanAkunPerkiraanRequest $request, AkunPerkiraan $akun_perkiraan_baru): RedirectResponse
    {
        $akun_perkiraan_baru->update($request->validated());

        return back()->with('sukses', 'Akun perkiraan berhasil diperbarui.');
    }

    public function toggle(AkunPerkiraan $akun_perkiraan_baru): RedirectResponse
    {
        $akun_perkiraan_baru->update(['aktif' => ! $akun_perkiraan_baru->aktif]);

        return back()->with('sukses', 'Status akun perkiraan berhasil diubah.');
    }

    public function preview(ImportAkunPerkiraanRequest $request, ImportAkunPerkiraanService $service): RedirectResponse
    {
        $rows = $service->preview($request->file('file'));
        $token = (string) Str::uuid();

        session()->put("import_akun_perkiraan.{$token}", $rows);

        return back()->with('preview_akun_perkiraan', ['token' => $token, 'rows' => $rows]);
    }

    public function import(Request $request, ImportAkunPerkiraanService $service): RedirectResponse
    {
        $request->validate(['token' => ['required', 'uuid']]);
        $key = "import_akun_perkiraan.{$request->token}";
        $rows = session()->pull($key);

        abort_unless(is_array($rows), 419, 'Preview import sudah kedaluwarsa. Silakan upload ulang.');
        $result = $service->simpan($rows);

        return redirect()->route('master.akun-perkiraan.index')
            ->with('sukses', "{$result['tersimpan']} akun berhasil diimport.");
    }

    public function export(): BinaryFileResponse
    {
        return Excel::download(new AkunPerkiraanBaruExport(), 'akun-perkiraan.xlsx');
    }

    public function template(): BinaryFileResponse
    {
        return Excel::download(new AkunPerkiraanBaruExport(true), 'template-akun-perkiraan.xlsx');
    }
}
