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
        $id_user = auth()->id();
        $data = [
            'title' => 'Data Customer',
            'customer' => DB::table('customer')->where('active', 'Y')->orderBy('nm_customer')->get(),
            'format' => \SettingHal::btnHal(116, $id_user),
            'import' => \SettingHal::btnHal(117, $id_user),
            'tambah' => \SettingHal::btnHal(118, $id_user),
            'edit' => \SettingHal::btnHal(119, $id_user),
            'hapus' => \SettingHal::btnHal(120, $id_user),
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
            'id_customer' => $id_customer,
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

    public function export()
    {
        $customers = DB::table('customer')->where('active', 'Y')->orderBy('nm_customer')->get([
            'kode_customer', 'nm_customer', 'alamat', 'no_telp', 'npwp', 'ktp', 'active',
        ]);

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data Customer');
        $sheet->fromArray(
            ['kode_customer', 'nama_customer', 'alamat', 'telepon', 'npwp', 'ktp', 'status_aktif'],
            null, 'A1'
        );
        $row = 2;
        foreach ($customers as $c) {
            $sheet->setCellValueExplicit('A'.$row, (string) ($c->kode_customer ?? ''), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('B'.$row, (string) ($c->nm_customer ?? ''));
            $sheet->setCellValue('C'.$row, (string) ($c->alamat ?? ''));
            $sheet->setCellValueExplicit('D'.$row, (string) ($c->no_telp ?? ''), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('E'.$row, (string) ($c->npwp ?? ''), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('F'.$row, (string) ($c->ktp ?? ''), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('G'.$row, (string) ($c->active ?? ''));
            $row++;
        }
        foreach (['A' => 20, 'B' => 30, 'C' => 38, 'D' => 20, 'E' => 24, 'F' => 24, 'G' => 16] as $kol => $lebar) {
            $sheet->getColumnDimension($kol)->setWidth($lebar);
        }
        $sheet->getStyle('A1:G1')->getFont()->setBold(true);
        $sheet->setAutoFilter('A1:G'.($row - 1));

        return response()->streamDownload(function () use ($spreadsheet) {
            (new Xlsx($spreadsheet))->save('php://output');
            $spreadsheet->disconnectWorksheets();
        }, 'export-data-customer-'.date('Ymd').'.xlsx', [
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
            return back()->withErrors(['file_customer' => 'Kolom tidak ditemukan: '.implode(', ', $missing).'. Silakan unduh Format Import terbaru.']);
        }

        $indexes = array_flip($headers);
        // Kode '0'/kosong dianggap tidak ada (peninggalan lama) agar tidak
        // menimpa ratusan baris sekaligus.
        $existingByCode = DB::table('customer')
            ->whereNotNull('kode_customer')->whereNotIn('kode_customer', ['', '0'])
            ->get(['id_customer', 'kode_customer'])
            ->mapWithKeys(fn ($r) => [mb_strtolower(trim((string) $r->kode_customer)) => (int) $r->id_customer]);
        $existingByName = DB::table('customer')->get(['id_customer', 'nm_customer'])
            ->mapToGroups(fn ($r) => [mb_strtolower(trim((string) $r->nm_customer)) => (int) $r->id_customer]);
        $fileCodes = [];
        $claimedIds = [];
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
                $errors[] = "Baris {$rowNumber}: ".implode(' ', $validator->errors()->all());

                continue;
            }

            $kodeEfektif = ($raw['kode_customer'] !== '' && $raw['kode_customer'] !== '0') ? $raw['kode_customer'] : '';
            $kodeKey = mb_strtolower($kodeEfektif);
            $targetId = null;
            if ($kodeEfektif !== '') {
                if (isset($fileCodes[$kodeKey])) {
                    $errors[] = "Baris {$rowNumber}: kode customer duplikat dengan baris {$fileCodes[$kodeKey]}.";

                    continue;
                }
                $fileCodes[$kodeKey] = $rowNumber;
                // Kode yang sudah ada di database = EDIT, bukan tambah baru.
                if ($existingByCode->has($kodeKey)) {
                    $targetId = $existingByCode->get($kodeKey);
                }
            }
            if ($targetId === null) {
                // Tanpa kode yang cocok: cocokkan nama (tepat satu) agar baris
                // lama berkode '0'/kosong tetap bisa diperbarui, bukan diduplikat.
                $namaKey = mb_strtolower($raw['nama_customer']);
                $calon = $existingByName->get($namaKey, collect())->unique()->values();
                if ($calon->count() > 1) {
                    $errors[] = "Baris {$rowNumber}: nama customer dipakai beberapa data, isi kode_customer untuk memilih yang benar.";

                    continue;
                }
                if ($calon->count() === 1) {
                    $targetId = $calon->first();
                }
            }
            if ($targetId !== null) {
                if (isset($claimedIds[$targetId])) {
                    $errors[] = "Baris {$rowNumber}: data yang sama sudah diubah pada baris {$claimedIds[$targetId]}.";

                    continue;
                }
                $claimedIds[$targetId] = $rowNumber;
            }

            $dataImport[] = [
                'id_customer' => $targetId,
                'kode_customer' => $kodeEfektif !== '' ? $kodeEfektif : null,
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

        $tambah = 0;
        $ubah = 0;
        DB::transaction(function () use (&$dataImport, &$tambah, &$ubah) {
            $nextNumber = $this->maxCustomerCodeNumber() + 1;
            $usedCodes = DB::table('customer')->whereNotNull('kode_customer')->pluck('kode_customer')
                ->mapWithKeys(fn ($code) => [mb_strtolower(trim((string) $code)) => true]);

            foreach ($dataImport as &$row) {
                if ($row['id_customer'] !== null) {
                    $id = $row['id_customer'];
                    unset($row['id_customer']);
                    DB::table('customer')->where('id_customer', $id)->update($row);
                    $ubah++;

                    continue;
                }
                unset($row['id_customer']);
                if ($row['kode_customer'] !== null) {
                    $usedCodes->put(mb_strtolower($row['kode_customer']), true);
                } else {
                    do {
                        $generatedCode = 'C.'.str_pad((string) $nextNumber++, 5, '0', STR_PAD_LEFT);
                    } while ($usedCodes->has(mb_strtolower($generatedCode)));
                    $row['kode_customer'] = $generatedCode;
                    $usedCodes->put(mb_strtolower($generatedCode), true);
                }
                DB::table('customer')->insert($row);
                $tambah++;
            }
            unset($row);
        });

        return redirect()->route('customer.index')->with('sukses', $tambah.' customer ditambah, '.$ubah.' customer diperbarui.');
    }

    private function nextCustomerCode(): string
    {
        return 'C.'.str_pad((string) ($this->maxCustomerCodeNumber() + 1), 5, '0', STR_PAD_LEFT);
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
