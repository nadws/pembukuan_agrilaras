<?php

namespace App\Http\Controllers;

use App\Models\Suplier;
use App\Services\MasterDataSpreadsheetService;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Nonaktif;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class SuplierController extends Controller
{
    public function index()
    {
        $data = [
            'title' => 'Data Suplier',
            'suplier' => Suplier::where('nonaktif', 'T')->orderBy('nm_suplier')->get(),
            'kategoriSupplier' => $this->supplierCategories(),
        ];
        return view('suplier.suplier', $data);
    }

    public function create(Request $r)
    {
        $validated = $r->validate([
            'kategori' => ['required', 'string', 'max:150'],
            'nm_suplier' => ['required', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:100'],
            'alamat' => ['nullable', 'string', 'max:225'],
            'telepon' => ['nullable', 'string', 'max:20'],
            'npwp' => ['nullable', 'string', 'max:50'],
        ]);

        if (!empty($r->file('img'))) {

            $file = $r->file('img');
            $fileDiterima = ['jpg', 'png', 'jpeg'];
            $cek = in_array($file->getClientOriginalExtension(), $fileDiterima);
            if ($cek) {
                $maxFileSize = 1024 * 1024; // 1MB
                if ($file instanceof UploadedFile && $file->getSize() > $maxFileSize) {
                    return redirect()->route('suplier.index')->with('error', 'File lebih dari 1MB');
                }
                $fileName = "S-$r->kd_produk" . $file->getClientOriginalName();
                $path = $file->move('upload/suplier', $fileName);
                Suplier::create([
                    'kategori' => $validated['kategori'],
                    'nm_suplier' => $validated['nm_suplier'],
                    'email' => $validated['email'] ?? null,
                    'alamat' => $validated['alamat'] ?? null,
                    'telepon' => $validated['telepon'] ?? null,
                    'npwp' => $validated['npwp'] ?? null,
                    'dokumen' => $fileName,
                    'admin' => auth()->user()->name,
                    'nonaktif' => 'T',
                ]);

                return redirect()->route('suplier.index')->with('sukses', 'Data Berhasil Ditambahkan');
            } else {
                return redirect()->route('suplier.index')->with('error', 'File tidak didukung');
            }
        } else {
            Suplier::create([
                'kategori' => $validated['kategori'],
                'nm_suplier' => $validated['nm_suplier'],
                'email' => $validated['email'] ?? null,
                'alamat' => $validated['alamat'] ?? null,
                'telepon' => $validated['telepon'] ?? null,
                'npwp' => $validated['npwp'] ?? null,
                'admin' => auth()->user()->name,
                'nonaktif' => 'T',
            ]);

            return redirect()->route('suplier.index')->with('sukses', 'Data Berhasil Ditambahkan');
        }
    }

    public function edit($id_suplier)
    {
        $data = [
            'suplier' => Suplier::where('id_suplier', $id_suplier)->first(),
            'id_suplier' => $id_suplier
        ];
        return view('suplier.edit', $data);
    }

    public function update(Request $r)
    {
        if (!empty($r->file('img'))) {
            $file = $r->file('img');
            $fileDiterima = ['jpg', 'png', 'jpeg'];
            $cek = in_array($file->getClientOriginalExtension(), $fileDiterima);
            if ($cek) {

                $maxFileSize = 1024 * 1024; // 1MB
                if ($file instanceof UploadedFile && $file->getSize() > $maxFileSize) {
                    return redirect()->route('suplier.index')->with('error', 'File lebih dari 1MB');
                }

                $fileName = "S-$r->kd_produk" . $file->getClientOriginalName();

                if ($fileName != $r->img_edit) {
                    unlink(public_path('/upload/suplier/' . $fileName));
                } else {
                    $path = $file->move('upload/suplier', $fileName);
                }
                $data = [
                    'nm_suplier' => $r->nm_suplier,
                    'email' => $r->email,
                    'alamat' => $r->alamat,
                    'telepon' => $r->telepon,
                    'npwp' => $r->npwp,
                    'dokumen' => $fileName,
                    'admin' => auth()->user()->name,
                ];

                Nonaktif::edit('tb_suplier', 'id_suplier', $r->id_suplier, $data);


                return redirect()->route('suplier.index')->with('sukses', 'Data Berhasil Diedit');
            } else {
                return redirect()->route('suplier.index')->with('error', 'File tidak didukung');
            }
        }

        $data = [
            'nm_suplier' => $r->nm_suplier,
            'email' => $r->email,
            'alamat' => $r->alamat,
            'telepon' => $r->telepon,
            'npwp' => $r->npwp,
            'dokumen' => $r->img_edit,
            'admin' => auth()->user()->name,
        ];
        Nonaktif::edit('tb_suplier', 'id_suplier', $r->id_suplier, $data);

        return redirect()->route('suplier.index')->with('sukses', 'Data Berhasil Diedit');
    }

    public function delete($id_suplier)
    {
        Nonaktif::delete('tb_suplier', 'id_suplier', $id_suplier);
        return redirect()->route('suplier.index')->with('sukses', 'Data Berhasil Dihapus');
    }

    public function templateImport(MasterDataSpreadsheetService $spreadsheetService)
    {
        $spreadsheet = $spreadsheetService->supplierTemplate($this->supplierCategories());

        return response()->streamDownload(function () use ($spreadsheet) {
            (new Xlsx($spreadsheet))->save('php://output');
            $spreadsheet->disconnectWorksheets();
        }, 'format-import-master-supplier.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function import(Request $request, MasterDataSpreadsheetService $spreadsheetService)
    {
        $request->validate([
            'file_supplier' => ['required', 'file', 'mimes:xlsx,xls,csv,txt', 'max:5120'],
        ]);

        try {
            [$headers, $rows] = $spreadsheetService->readRows($request->file('file_supplier'));
        } catch (\Throwable $e) {
            return back()->withErrors(['file_supplier' => 'File tidak dapat dibaca. Pastikan menggunakan format import supplier terbaru.']);
        }

        $requiredHeaders = ['kategori', 'nama_supplier', 'email', 'alamat', 'telepon', 'npwp'];
        $missing = array_values(array_diff($requiredHeaders, $headers));
        if ($missing !== []) {
            return back()->withErrors(['file_supplier' => 'Kolom tidak ditemukan: ' . implode(', ', $missing) . '. Silakan unduh Format Import terbaru.']);
        }

        $indexes = array_flip($headers);
        $categories = $this->supplierCategories();
        $existingKeys = Suplier::where('nonaktif', 'T')->get(['kategori', 'nm_suplier'])
            ->mapWithKeys(fn ($row) => [$this->supplierKey($row->kategori, $row->nm_suplier) => true]);
        $fileKeys = [];
        $dataImport = [];
        $errors = [];

        foreach ($rows as $offset => $row) {
            $rowNumber = $offset + 2;
            if (collect($row)->filter(fn ($value) => $value !== null && trim((string) $value) !== '')->isEmpty()) {
                continue;
            }

            $raw = [];
            foreach ($requiredHeaders as $header) {
                $raw[$header] = $row[$indexes[$header]] ?? null;
            }
            $raw = array_map(fn ($value) => trim((string) ($value ?? '')), $raw);

            $validator = Validator::make($raw, [
                'kategori' => ['required', 'string', 'max:150', 'in:' . implode(',', $categories)],
                'nama_supplier' => ['required', 'string', 'max:100'],
                'email' => ['nullable', 'email', 'max:100'],
                'alamat' => ['nullable', 'string', 'max:225'],
                'telepon' => ['nullable', 'string', 'max:20'],
                'npwp' => ['nullable', 'string', 'max:50'],
            ]);

            if ($validator->fails()) {
                $errors[] = "Baris {$rowNumber}: " . implode(' ', $validator->errors()->all());
                continue;
            }

            $key = $this->supplierKey($raw['kategori'], $raw['nama_supplier']);
            if ($existingKeys->has($key)) {
                $errors[] = "Baris {$rowNumber}: supplier {$raw['nama_supplier']} pada kategori {$raw['kategori']} sudah ada.";
                continue;
            }
            if (isset($fileKeys[$key])) {
                $errors[] = "Baris {$rowNumber}: supplier duplikat dengan baris {$fileKeys[$key]}.";
                continue;
            }
            $fileKeys[$key] = $rowNumber;

            $dataImport[] = [
                'kategori' => $raw['kategori'],
                'nm_suplier' => $raw['nama_supplier'],
                'email' => $raw['email'] ?: null,
                'alamat' => $raw['alamat'] ?: null,
                'telepon' => $raw['telepon'] ?: null,
                'npwp' => $raw['npwp'] ?: null,
                'dokumen' => null,
                'admin' => auth()->user()->name,
                'nonaktif' => 'T',
            ];
        }

        if ($errors !== []) {
            return back()->withErrors(['file_supplier' => implode(' | ', array_slice($errors, 0, 20))]);
        }
        if ($dataImport === []) {
            return back()->withErrors(['file_supplier' => 'Tidak ada baris supplier yang dapat diimport.']);
        }

        DB::transaction(fn () => Suplier::insert($dataImport));
        return redirect()->route('suplier.index')->with('sukses', count($dataImport) . ' supplier berhasil diimport.');
    }

    private function supplierCategories(): array
    {
        $defaults = ['Pemasok Umum', 'Pemasok Ayam', 'Pemasok Rak Telur', 'Pemasok Pakan', 'Pemasok Vitamin'];
        return Suplier::query()->whereNotNull('kategori')->distinct()->orderBy('kategori')->pluck('kategori')
            ->merge($defaults)->filter()->unique()->sort()->values()->all();
    }

    private function supplierKey(?string $category, ?string $name): string
    {
        return mb_strtolower(trim((string) $category) . '|' . trim((string) $name));
    }
}
