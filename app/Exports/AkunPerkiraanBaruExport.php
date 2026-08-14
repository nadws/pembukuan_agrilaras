<?php

namespace App\Exports;

use App\Models\AkunPerkiraan;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class AkunPerkiraanBaruExport implements FromCollection, WithHeadings, WithMapping, WithColumnFormatting, ShouldAutoSize
{
    public function __construct(private readonly bool $template = false)
    {
    }

    public function collection(): Collection
    {
        if ($this->template) {
            return collect();
        }

        return AkunPerkiraan::with('akunInduk')->orderBy('kode_perkiraan')->get();
    }

    public function headings(): array
    {
        return ['No. ', 'Tipe Akun', 'Kode Perkiraan', 'Nama', 'Akun Induk', 'Cabang Saldo', 'Catatan'];
    }

    public function map($akun): array
    {
        static $nomor = 0;

        return [
            ++$nomor,
            $akun->tipe_akun,
            $akun->kode_perkiraan,
            $akun->nama,
            $akun->akunInduk?->kode_perkiraan,
            $akun->cabang_saldo,
            $akun->catatan,
        ];
    }

    public function columnFormats(): array
    {
        return [
            'C' => NumberFormat::FORMAT_TEXT,
            'E' => NumberFormat::FORMAT_TEXT,
        ];
    }
}
