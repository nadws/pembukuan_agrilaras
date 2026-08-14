<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class TemplateJurnalPerkiraanExport implements FromArray, WithHeadings, WithColumnFormatting, ShouldAutoSize
{
    public function array(): array
    {
        return [];
    }

    public function headings(): array
    {
        return ['Tanggal', 'No. Transaksi', 'Tipe Transaksi', 'Kode Perkiraan', 'Nama Perkiraan', 'Deskripsi', 'Debit', 'Kredit'];
    }

    public function columnFormats(): array
    {
        return ['A' => 'yyyy-mm-dd', 'D' => NumberFormat::FORMAT_TEXT, 'G' => '0.000000000000', 'H' => '0.000000000000'];
    }
}
