<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class DetailLabaRugiPerkiraanExport implements FromCollection, ShouldAutoSize, WithHeadings
{
    public function __construct(private readonly Collection $detail) {}

    public function collection(): Collection
    {
        return $this->detail->map(fn ($item) => [
            $item->tanggal?->format('Y-m-d'), $item->nomor_transaksi, $item->tipe_transaksi,
            $item->akun->kode_perkiraan, $item->akun->nama, $item->deskripsi,
            $item->impor->nama_file, (float) $item->debit, (float) $item->kredit,
        ]);
    }

    public function headings(): array
    {
        return ['Tanggal', 'No. Transaksi', 'Tipe', 'Kode Akun', 'Nama Akun', 'Deskripsi', 'File Import', 'Debit', 'Kredit'];
    }
}
