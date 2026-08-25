<?php

namespace App\Http\Controllers;

use App\Services\MasterDataSpreadsheetService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class CustomerController extends Controller
{
    public function index()
    {
        $data = [
            'title' => 'Data Customer',
            'customer' => DB::table('customer')->where('active', 'Y')->orderBy('nm_customer')->get()
        ];
        return view('customer.customer', $data);
    }

    public function create(Request $r)
    {
        $validated = $r->validate([
            'nm_customer' => ['required', 'string', 'max:225'],
            'alamat' => ['nullable', 'string', 'max:225'],
            'telepon' => ['nullable', 'string', 'max:225'],
            'npwp' => ['nullable', 'string', 'max:225'],
            'ktp' => ['nullable', 'string', 'max:50'],
        ]);

        DB::table('customer')->insert([
            'kode_customer' => $this->nextCustomerCode(),
            'nm_customer' => $validated['nm_customer'],
            'alamat' => $validated['alamat'] ?? null,
            'no_telp' => $validated['telepon'] ?? null,
            'npwp' => $validated['npwp'] ?? null,
            'ktp' => $validated['ktp'] ?? null,
            'active' => 'Y',
        ]);

        return redirect()->route('customer.index')->with('sukses', 'Data Berhasil Ditambahkan');
    }

    public function edit($id_customer)
    {
        $data = [
            'customer' => DB::table('customer')->where('id_customer', $id_customer)->first(),
            'id_customer' => $id_customer
        ];
        return view('customer.edit', $data);
    }

    public function update(Request $r)
    {
        $validated = $r->validate([
            'id_customer' => ['required', 'integer', 'exists:customer,id_customer'],
            'nm_customer' => ['required', 'string', 'max:225'],
            'alamat' => ['nullable', 'string', 'max:225'],
            'telepon' => ['nullable', 'string', 'max:225'],
            'npwp' => ['nullable', 'string', 'max:225'],
            'ktp' => ['nullable', 'string', 'max:50'],
        ]);
        $data = [
            'nm_customer' => $validated['nm_customer'],
            'alamat' => $validated['alamat'] ?? null,
            'no_telp' => $validated['telepon'] ?? null,
            'npwp' => $validated['npwp'] ?? null,
            'ktp' => $validated['ktp'] ?? null,
        ];
        DB::table('customer')->where('id_customer', $validated['id_customer'])->update($data);

        return redirect()->route('customer.index')->with('sukses', 'Data Berhasil Diedit');
    }

    public function delete($id_customer)
    {
        DB::table('customer')->where('id_customer', $id_customer)->delete();
        return redirect()->route('customer.index')->with('sukses', 'Data Berhasil Dihapus');
    }

    public function templateImport(MasterDataSpreadsheetService $spreadsheetService)
    {
        $spreadsheet = $spreadsheetService->customerTemplate();

        return response()->streamDownload(function () use ($spreadsheet) {
            (new Xlsx($spreadsheet))->save('php://output');
            $spreadsheet->disconnectWorksheets();
        }, 'format-import-master-customer.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function import(Request $request, MasterDataSpreadsheetService $spreadsheetService)
    {
        $request->validate([
            'file_customer' => ['required', 'file', 'mimes:xlsx,xls,csv,txt', 'max:5120'],
        ]);

        try {
            [$headers, $rows] = $spreadsheetService->readRows($request->file('file_customer'));
        } catch (\Throwable $e) {
            return back()->withErrors(['file_customer' => 'File tidak dapat dibaca. Pastikan menggunakan format import customer terbaru.']);
        }

        $requiredHeaders = ['kode_customer', 'nama_customer', 'alamat', 'telepon', 'npwp', 'ktp', 'status_aktif'];
        $missing = array_values(array_diff($requiredHeaders, $headers));
        if ($missing !== []) {
            return back()->withErrors(['file_customer' => 'Kolom tidak ditemukan: ' . implode(', ', $missing) . '. Silakan unduh Format Import terbaru.']);
        }

        $indexes = array_flip($headers);
        $existingCodes = DB::table('customer')->whereNotNull('kode_customer')->pluck('kode_customer')
            ->mapWithKeys(fn ($code) => [mb_strtolower(trim((string) $code)) => true]);
        $fileCodes = [];
        $dataImport = [];
        $errors = [];

        foreach ($rows as $offset => $row) {
            $rowNumber = $offset + 2;
            if (collect($row)->filter(fn ($value) => $value !== null && trim((string) $value) !== '')->isEmpty()) {
                continue;
            }

            $raw = [];
            foreach ($requiredHeaders as $header) {
                $raw[$header] = trim((string) ($row[$indexes[$header]] ?? ''));
            }
            $raw['status_aktif'] = strtoupper($raw['status_aktif'] ?: 'Y');

            $validator = Validator::make($raw, [
                'kode_customer' => ['nullable', 'string', 'max:50'],
                'nama_customer' => ['required', 'string', 'max:225'],
                'alamat' => ['nullable', 'string', 'max:225'],
                'telepon' => ['nullable', 'string', 'max:225'],
                'npwp' => ['nullable', 'string', 'max:225'],
                'ktp' => ['nullable', 'string', 'max:50'],
                'status_aktif' => ['required', 'in:T,Y'],
            ]);

            if ($validator->fails()) {
                $errors[] = "Baris {$rowNumber}: " . implode(' ', $validator->errors()->all());
                continue;
            }

            if ($raw['kode_customer'] !== '') {
                $key = mb_strtolower($raw['kode_customer']);
                if ($existingCodes->has($key)) {
                    $errors[] = "Baris {$rowNumber}: kode customer {$raw['kode_customer']} sudah digunakan.";
                    continue;
                }
                if (isset($fileCodes[$key])) {
                    $errors[] = "Baris {$rowNumber}: kode customer duplikat dengan baris {$fileCodes[$key]}.";
                    continue;
                }
                $fileCodes[$key] = $rowNumber;
            }

            $dataImport[] = [
                'kode_customer' => $raw['kode_customer'] ?: null,
                'nm_customer' => $raw['nama_customer'],
                'alamat' => $raw['alamat'] ?: null,
                'no_telp' => $raw['telepon'] ?: null,
                'npwp' => $raw['npwp'] ?: null,
                'ktp' => $raw['ktp'] ?: null,
                'active' => $raw['status_aktif'],
            ];
        }

        if ($errors !== []) {
            return back()->withErrors(['file_customer' => implode(' | ', array_slice($errors, 0, 20))]);
        }
        if ($dataImport === []) {
            return back()->withErrors(['file_customer' => 'Tidak ada baris customer yang dapat diimport.']);
        }

        DB::transaction(function () use (&$dataImport) {
            $nextNumber = $this->maxCustomerCodeNumber() + 1;
            $usedCodes = DB::table('customer')->whereNotNull('kode_customer')->pluck('kode_customer')
                ->mapWithKeys(fn ($code) => [mb_strtolower(trim((string) $code)) => true]);

            foreach ($dataImport as &$row) {
                if ($row['kode_customer'] !== null) {
                    $usedCodes->put(mb_strtolower($row['kode_customer']), true);
                    continue;
                }
                do {
                    $generatedCode = 'C.' . str_pad((string) $nextNumber++, 5, '0', STR_PAD_LEFT);
                } while ($usedCodes->has(mb_strtolower($generatedCode)));
                $row['kode_customer'] = $generatedCode;
                $usedCodes->put(mb_strtolower($generatedCode), true);
            }
            unset($row);

            DB::table('customer')->insert($dataImport);
        });

        return redirect()->route('customer.index')->with('sukses', count($dataImport) . ' customer berhasil diimport.');
    }

    private function nextCustomerCode(): string
    {
        return 'C.' . str_pad((string) ($this->maxCustomerCodeNumber() + 1), 5, '0', STR_PAD_LEFT);
    }

    private function maxCustomerCodeNumber(): int
    {
        $maximum = 0;
        foreach (DB::table('customer')->whereNotNull('kode_customer')->pluck('kode_customer') as $code) {
            if (preg_match('/^C\.(\d+)$/i', trim((string) $code), $matches)) {
                $maximum = max($maximum, (int) $matches[1]);
            }
        }
        return $maximum;
    }
}
