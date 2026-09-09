<?php

namespace App\Services;

use App\Models\AkunPerkiraan;
use App\Models\ImporJurnalPerkiraan;
use App\Models\JurnalPerkiraan;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class ImporJurnalPerkiraanService
{
    private const HEADER_ALIASES = [
        'tanggal' => ['tanggal'],
        'nomor' => ['no transaksi', 'nomor transaksi', 'no nota', 'nomor nota'],
        'tipe' => ['tipe transaksi', 'tipe jurnal'],
        'kode' => ['kode perkiraan', 'kode akun'],
        'nama' => ['nama perkiraan', 'nama akun'],
        'deskripsi' => ['deskripsi', 'keterangan'],
        'debit' => ['debit'],
        'kredit' => ['kredit'],
    ];

    public function pratinjau(UploadedFile $file): array
    {
        $hash = hash_file('sha256', $file->getPathname());
        if (ImporJurnalPerkiraan::where('hash_file', $hash)->exists()) {
            throw ValidationException::withMessages(['file' => 'File yang sama sudah pernah diimport.']);
        }

        $sheet = IOFactory::load($file->getPathname())->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, false);
        $headers = array_map(fn ($value) => $this->normalizeHeader($value), array_shift($rows) ?? []);

        $columns = [];
        foreach (self::HEADER_ALIASES as $key => $aliases) {
            foreach ($aliases as $alias) {
                $index = array_search($alias, $headers, true);
                if ($index !== false) {
                    $columns[$key] = $index;
                    break;
                }
            }
        }

        if (count($columns) !== count(self::HEADER_ALIASES)) {
            throw ValidationException::withMessages([
                'file' => 'Header wajib: Tanggal | No Nota | Kode Akun | Nama Akun | Keterangan | Debit | Kredit | Tipe Jurnal.',
            ]);
        }

        $akun = AkunPerkiraan::query()->get()->keyBy('kode_perkiraan');
        $detail = [];
        $errors = [];
        $transaksi = [];
        $dates = [];
        $totalDebit = '0.000000000000';
        $totalKredit = '0.000000000000';

        foreach ($rows as $index => $row) {
            $line = $index + 2;
            if (collect($columns)->every(fn ($column) => ($row[$column] ?? null) === null || ($row[$column] ?? '') === '')) {
                continue;
            }

            $rowErrors = [];
            $tanggal = $this->parseDate($row[$columns['tanggal']] ?? null);
            $nomor = trim((string) ($row[$columns['nomor']] ?? ''));
            $tipe = Str::squish((string) ($row[$columns['tipe']] ?? ''));
            $kode = trim((string) ($row[$columns['kode']] ?? ''));
            $nama = Str::squish((string) ($row[$columns['nama']] ?? ''));
            $deskripsi = trim((string) ($row[$columns['deskripsi']] ?? '')) ?: null;
            $debit = $this->parseDecimal($row[$columns['debit']] ?? null);
            $kredit = $this->parseDecimal($row[$columns['kredit']] ?? null);

            if (! $tanggal) {
            $rowErrors[] = 'Tanggal tidak valid.';
            }
            if ($nomor === '') {
            $rowErrors[] = 'Nomor transaksi wajib diisi.';
            }
            if ($tipe === '') {
            $rowErrors[] = 'Tipe transaksi wajib diisi.';
            } elseif (Str::length($tipe) > 100) {
            $rowErrors[] = 'Tipe transaksi maksimal 100 karakter.';
            }
            if ($kode === '') {
            $rowErrors[] = 'Kode perkiraan wajib diisi.';
            }
            if ($debit === null) {
            $rowErrors[] = 'Debit harus berupa angka nonnegatif.';
            }
            if ($kredit === null) {
            $rowErrors[] = 'Kredit harus berupa angka nonnegatif.';
            }

            $account = $akun->get($kode);
            if ($kode !== '' && ! $account) {
                $rowErrors[] = 'Kode akun tidak ditemukan.';
            } elseif ($account && Str::lower(Str::squish($account->nama)) !== Str::lower($nama)) {
                $rowErrors[] = 'Nama akun tidak cocok dengan master.';
            }

            if ($rowErrors) {
                $errors[] = ['baris' => $line, 'kode' => $kode, 'pesan' => implode(' ', $rowErrors)];

                continue;
            }

            $key = $tanggal.'|'.$nomor.'|'.$tipe;
            $order = ($transaksi[$key]['jumlah_detail'] ?? 0) + 1;
            $transaksi[$key] ??= ['tipe' => $tipe, 'debit' => '0.000000000000', 'kredit' => '0.000000000000', 'jumlah_detail' => 0];
            $transaksi[$key]['debit'] = bcadd($transaksi[$key]['debit'], $debit, 12);
            $transaksi[$key]['kredit'] = bcadd($transaksi[$key]['kredit'], $kredit, 12);
            $transaksi[$key]['jumlah_detail'] = $order;
            $totalDebit = bcadd($totalDebit, $debit, 12);
            $totalKredit = bcadd($totalKredit, $kredit, 12);
            $dates[] = $tanggal;

            $detail[] = [
                'id_akun_perkiraan' => $account->getKey(),
                'tanggal' => $tanggal,
                'nomor_transaksi' => $nomor,
                'tipe_transaksi' => $tipe,
                'urutan_detail' => $order,
                'deskripsi' => $deskripsi,
                'debit' => $debit,
                'kredit' => $kredit,
            ];
        }

        foreach ($transaksi as $key => $totals) {
            $difference = bcsub($totals['debit'], $totals['kredit'], 12);
            $absolute = ltrim($difference, '-');
            if (bccomp($absolute, '0.000001000000', 12) === 1) {
                [$tanggal, $nomor] = explode('|', $key, 3);
                $errors[] = [
                    'baris' => '-',
                    'kode' => $nomor,
                    'pesan' => "Transaksi {$tanggal} tidak seimbang; selisih {$difference}.",
                ];
            }
        }

        if (! $detail && ! $errors) {
            $errors[] = ['baris' => '-', 'kode' => '-', 'pesan' => 'File tidak memiliki detail jurnal.'];
        }

        return [
            'nama_file' => $file->getClientOriginalName(),
            'hash_file' => $hash,
            'periode_awal' => $dates ? min($dates) : null,
            'periode_akhir' => $dates ? max($dates) : null,
            'jumlah_transaksi' => count($transaksi),
            'jumlah_detail' => count($detail),
            'total_debit' => $totalDebit,
            'total_kredit' => $totalKredit,
            'ringkasan_tipe' => collect($transaksi)->countBy('tipe')->sortKeys()->all(),
            'detail' => $detail,
            'errors' => $errors,
        ];
    }

    public function simpan(array $preview, ?int $userId): ImporJurnalPerkiraan
    {
        if ($preview['errors'] ?? []) {
            throw ValidationException::withMessages(['file' => 'Import dibatalkan karena masih memiliki data gagal.']);
        }

        if (ImporJurnalPerkiraan::where('hash_file', $preview['hash_file'])->exists()) {
            throw ValidationException::withMessages(['file' => 'File yang sama sudah pernah diimport.']);
        }

        return DB::transaction(function () use ($preview, $userId) {
            $batch = ImporJurnalPerkiraan::create([
                'nama_file' => $preview['nama_file'],
                'sumber_data' => 'impor',
                'hash_file' => $preview['hash_file'],
                'periode_awal' => $preview['periode_awal'],
                'periode_akhir' => $preview['periode_akhir'],
                'jumlah_transaksi' => $preview['jumlah_transaksi'],
                'jumlah_detail' => $preview['jumlah_detail'],
                'total_debit' => $preview['total_debit'],
                'total_kredit' => $preview['total_kredit'],
                'status' => 'aktif',
                'diimpor_oleh' => $userId,
            ]);

            $now = now();
            foreach (array_chunk($preview['detail'], 500) as $chunk) {
                JurnalPerkiraan::insert(array_map(fn ($row) => array_merge($row, [
                    'id_impor_jurnal_perkiraan' => $batch->getKey(),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]), $chunk));
            }

            return $batch;
        });
    }

    private function normalizeHeader(mixed $value): string
    {
        return Str::of((string) $value)->lower()->replace('.', '')->squish()->toString();
    }

    private function parseDate(mixed $value): ?string
    {
        try {
            if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d');
            }
            if (is_numeric($value)) {
            return Date::excelToDateTimeObject($value)->format('Y-m-d');
            }

            return Carbon::parse((string) $value)->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }

    private function parseDecimal(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return '0.000000000000';
        }

        if (! is_numeric($value) || (float) $value < 0) {
            return null;
        }

        return number_format((float) $value, 12, '.', '');
    }
}
