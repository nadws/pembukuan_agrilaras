<?php

namespace App\Services;

use App\Models\AkunPerkiraan;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportAkunPerkiraanService
{
    private const HEADERS = [
        'no', 'tipe akun', 'kode perkiraan', 'nama', 'akun induk', 'cabang saldo', 'catatan',
    ];

    public function preview(UploadedFile $file): array
    {
        $sheet = IOFactory::load($file->getPathname())->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, false);
        $headers = array_map(fn ($value) => $this->normalizeHeader($value), array_shift($rows) ?? []);

        if (array_slice($headers, 0, 7) !== self::HEADERS) {
            throw ValidationException::withMessages([
                'file' => 'Header harus: No. | Tipe Akun | Kode Perkiraan | Nama | Akun Induk | Cabang Saldo | Catatan.',
            ]);
        }

        $data = collect($rows)
            ->map(fn (array $row, int $index) => $this->mapRow($row, $index + 2))
            ->filter(fn (array $row) => collect($row)->except(['baris', 'errors'])->filter(fn ($value) => $value !== null && $value !== '')->isNotEmpty())
            ->values();

        $this->validateRows($data);
        $existing = AkunPerkiraan::whereIn('kode_perkiraan', $data->pluck('kode_perkiraan')->filter())->pluck('id_akun_perkiraan', 'kode_perkiraan');

        return $data->map(function (array $row) use ($existing) {
            $row['status'] = $row['errors'] ? 'gagal' : ($existing->has($row['kode_perkiraan']) ? 'diperbarui' : 'baru');

            return $row;
        })->all();
    }

    public function simpan(array $rows): array
    {
        $data = collect($rows);
        $this->validateRows($data);

        if ($data->contains(fn (array $row) => ! empty($row['errors']))) {
            throw ValidationException::withMessages(['file' => 'Import dibatalkan karena masih memiliki data gagal.']);
        }

        return DB::transaction(function () use ($data) {
            $tersimpan = 0;
            $pending = $data->keyBy('kode_perkiraan');

            while ($pending->isNotEmpty()) {
                $progress = false;

                foreach ($pending as $kode => $row) {
                    $induk = null;
                    if ($row['kode_akun_induk']) {
                        $induk = AkunPerkiraan::where('kode_perkiraan', $row['kode_akun_induk'])->first();
                        if (! $induk) {
                            continue;
                        }
                    }

                    AkunPerkiraan::updateOrCreate(
                        ['kode_perkiraan' => $kode],
                        [
                            'tipe_akun' => $row['tipe_akun'],
                            'nama' => $row['nama'],
                            'id_akun_induk' => $induk?->getKey(),
                            'cabang_saldo' => $row['cabang_saldo'],
                            'catatan' => $row['catatan'],
                            'aktif' => true,
                        ]
                    );

                    $pending->forget($kode);
                    $tersimpan++;
                    $progress = true;
                }

                if (! $progress) {
                    throw ValidationException::withMessages(['file' => 'Akun induk tidak ditemukan atau hierarchy melingkar.']);
                }
            }

            return ['tersimpan' => $tersimpan];
        });
    }

    private function mapRow(array $row, int $line): array
    {
        return [
            'baris' => $line,
            'tipe_akun' => $this->text($row[1] ?? null),
            'kode_perkiraan' => $this->text($row[2] ?? null),
            'nama' => $this->text($row[3] ?? null),
            'kode_akun_induk' => $this->text($row[4] ?? null),
            'cabang_saldo' => $this->text($row[5] ?? null),
            'catatan' => $this->text($row[6] ?? null),
            'errors' => [],
        ];
    }

    private function validateRows(Collection $rows): void
    {
        $codes = $rows->pluck('kode_perkiraan')->filter();
        $duplicates = $codes->duplicates()->unique();
        $available = $codes->merge(AkunPerkiraan::pluck('kode_perkiraan'))->flip();
        $parents = collect(AkunPerkiraan::with('akunInduk')->get()->mapWithKeys(fn ($account) => [
            $account->kode_perkiraan => $account->akunInduk?->kode_perkiraan,
        ])->all())->merge($rows->pluck('kode_akun_induk', 'kode_perkiraan'));

        $rows->transform(function (array $row) use ($duplicates, $available, $parents) {
            $errors = [];
            if (! $row['tipe_akun']) {
            $errors[] = 'Tipe akun wajib diisi.';
            }
            if (! $row['kode_perkiraan']) {
            $errors[] = 'Kode perkiraan wajib diisi.';
            }
            if (! $row['nama']) {
            $errors[] = 'Nama wajib diisi.';
            }
            if ($row['kode_perkiraan'] && $duplicates->contains($row['kode_perkiraan'])) {
            $errors[] = 'Kode duplikat dalam file.';
            }
            if ($row['kode_akun_induk'] && ! $available->has($row['kode_akun_induk'])) {
            $errors[] = 'Akun induk tidak ditemukan.';
            }
            if ($row['kode_perkiraan'] && $row['kode_perkiraan'] === $row['kode_akun_induk']) {
            $errors[] = 'Akun tidak boleh menjadi induknya sendiri.';
            }
            if ($row['kode_perkiraan'] && $this->hasCycle($row['kode_perkiraan'], $parents)) {
            $errors[] = 'Hierarchy akun melingkar.';
            }
            $row['errors'] = array_values(array_unique($errors));

            return $row;
        });
    }

    private function hasCycle(string $start, Collection $parents): bool
    {
        $visited = [];
        $current = $start;

        while ($current && $parents->has($current)) {
            if (isset($visited[$current])) {
            return true;
            }
            $visited[$current] = true;
            $current = $parents->get($current);
        }

        return false;
    }

    private function normalizeHeader(mixed $value): string
    {
        return Str::of((string) $value)->lower()->replace('.', '')->squish()->toString();
    }

    private function text(mixed $value): ?string
    {
        if ($value === null || $value === '') {
        return null;
        }

        return trim((string) $value);
    }
}
