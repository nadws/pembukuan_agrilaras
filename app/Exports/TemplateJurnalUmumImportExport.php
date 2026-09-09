<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class TemplateJurnalUmumImportExport implements WithMultipleSheets
{
    public function __construct(private readonly Collection $accounts)
    {
    }

    public function sheets(): array
    {
        return [
            new TemplateJurnalUmumSheet($this->accounts),
            new DaftarAkunJurnalUmumSheet($this->accounts),
        ];
    }
}

class TemplateJurnalUmumSheet implements FromArray, WithHeadings, WithColumnFormatting, ShouldAutoSize, WithTitle
{
    public function __construct(private readonly Collection $accounts)
    {
    }

    public function array(): array
    {
        $debit = $this->accounts->first();
        $credit = $this->accounts->skip(1)->first() ?? $debit;
        if (! $debit) return [];

        return [
            [now()->toDateString(), 'CONTOH-001', $debit->kode_perkiraan, $debit->nama, 'Contoh jurnal (hapus sebelum import)', 100000, 0, 'Jurnal Umum Manual'],
            [now()->toDateString(), 'CONTOH-001', $credit->kode_perkiraan, $credit->nama, 'Contoh jurnal (hapus sebelum import)', 0, 100000, 'Jurnal Umum Manual'],
        ];
    }

    public function headings(): array
    {
        return ['Tanggal', 'No Nota', 'Kode Akun', 'Nama Akun', 'Keterangan', 'Debit', 'Kredit', 'Tipe Jurnal'];
    }

    public function columnFormats(): array
    {
        return ['A' => 'yyyy-mm-dd', 'B' => NumberFormat::FORMAT_TEXT, 'C' => NumberFormat::FORMAT_TEXT, 'F' => '0.00', 'G' => '0.00'];
    }

    public function title(): string
    {
        return 'Import Jurnal';
    }
}

class DaftarAkunJurnalUmumSheet implements FromArray, WithHeadings, ShouldAutoSize, WithTitle
{
    public function __construct(private readonly Collection $accounts)
    {
    }

    public function array(): array
    {
        return $this->accounts->map(fn ($account) => [$account->kode_perkiraan, $account->nama])->all();
    }

    public function headings(): array
    {
        return ['Kode Akun', 'Nama Akun'];
    }

    public function title(): string
    {
        return 'Daftar Akun';
    }
}
