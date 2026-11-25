<?php

namespace App\Exports;

use App\Models\FakturModel;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

/**
 * Barang of one faktur pembelian, in the columns the A/P Nota import of JOMA reads (Kode Barang, Satuan, Jumlah, Harga),
 * so the file can go straight into the import. Kode Barang is the barang code: kode_accurate for barang perencanaan,
 * kd_produk for barang umum.
 */
class FakturPembelianExport implements FromArray, WithHeadings, WithTitle
{
    public function __construct(private readonly FakturModel $faktur)
    {
    }

    /**
     * @return list<string>
     */
    public function headings(): array
    {
        return ['Kode Barang', 'Nama Barang', 'Satuan', 'Jumlah', 'Harga', 'Subtotal'];
    }

    public function title(): string
    {
        return 'Barang';
    }

    /**
     * @return list<list<mixed>>
     */
    public function array(): array
    {
        $this->faktur->loadMissing(['detail.produk', 'detail.produkUmum']);

        return $this->faktur->detail->map(function ($detail): array {
            $umum = $detail->sumber_produk === 'barang_umum';
            $produk = $umum ? $detail->produkUmum : $detail->produk;

            return [
                $umum ? (string) ($produk->kd_produk ?? '') : (string) ($produk->kode_accurate ?? ''),
                (string) ($produk->nm_produk ?? ''),
                (string) ($detail->satuan ?? ''),
                (float) $detail->qty,
                (float) $detail->harga_satuan,
                (float) $detail->subtotal,
            ];
        })->values()->all();
    }
}
