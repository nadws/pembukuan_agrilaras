<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

/**
 * Master produk perencanaan, with the same search and category filter as the page. All matching rows, not one page.
 * Satuan dosis and campuran are shown by their names from tb_satuan.
 */
class ProdukPerencanaanExport implements FromArray, WithHeadings, WithTitle
{
    public function __construct(private readonly string $cari = '', private readonly string $kategori = '')
    {
    }

    /**
     * @return list<string>
     */
    public function headings(): array
    {
        return ['ID Produk', 'Kode Accurate', 'Nama Produk', 'Kategori', 'Satuan Dosis', 'Satuan Campuran', 'Kegunaan'];
    }

    public function title(): string
    {
        return 'Produk Perencanaan';
    }

    /**
     * @return list<list<mixed>>
     */
    public function array(): array
    {
        $cari = trim($this->cari);

        return DB::table('tb_produk_perencanaan as p')
            ->leftJoin('tb_satuan as dosis', 'dosis.id_satuan', '=', 'p.dosis_satuan')
            ->leftJoin('tb_satuan as campuran', 'campuran.id_satuan', '=', 'p.campuran_satuan')
            ->when($this->kategori !== '', fn ($query) => $query->where('p.kategori', $this->kategori))
            ->when($cari !== '', fn ($query) => $query->where(function ($search) use ($cari) {
                $search->where('p.nm_produk', 'like', "%{$cari}%")
                    ->orWhere('p.kode_accurate', 'like', "%{$cari}%")
                    ->orWhere('p.kegunaan', 'like', "%{$cari}%");
            }))
            ->orderBy('p.kategori')
            ->orderBy('p.nm_produk')
            ->get([
                'p.id_produk',
                'p.kode_accurate',
                'p.nm_produk',
                'p.kategori',
                'dosis.nm_satuan as nm_satuan_dosis',
                'campuran.nm_satuan as nm_satuan_campuran',
                'p.kegunaan',
            ])
            ->map(fn ($row): array => [
                (int) $row->id_produk,
                (string) ($row->kode_accurate ?? ''),
                (string) $row->nm_produk,
                (string) ($row->kategori ?? ''),
                (string) ($row->nm_satuan_dosis ?? ''),
                (string) ($row->nm_satuan_campuran ?? ''),
                (string) ($row->kegunaan ?? ''),
            ])
            ->values()
            ->all();
    }
}
