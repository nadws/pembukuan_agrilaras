<?php

namespace Tests\Feature;

use App\Exports\FakturPembelianExport;
use App\Http\Controllers\FakturPembelianController;
use App\Models\FakturModel;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Tests\TestCase;

class FakturPembelianExportTest extends TestCase
{
    use DatabaseTransactions;

    public function test_the_export_has_one_row_per_barang_in_the_columns_of_the_joma_import(): void
    {
        $faktur = FakturModel::query()->whereHas('detail')->first();

        if ($faktur === null) {
            $this->markTestSkipped('Belum ada faktur pembelian dengan barang di database test.');
        }

        $export = new FakturPembelianExport($faktur);

        $this->assertSame(['Kode Barang', 'Nama Barang', 'Satuan', 'Jumlah', 'Harga', 'Subtotal'], $export->headings());
        $this->assertCount($faktur->detail()->count(), $export->array());
        $this->assertCount(6, $export->array()[0]);
    }

    public function test_the_excel_file_is_sent_as_a_download(): void
    {
        $faktur = FakturModel::query()->whereHas('detail')->first();

        if ($faktur === null) {
            $this->markTestSkipped('Belum ada faktur pembelian dengan barang di database test.');
        }

        $response = app(FakturPembelianController::class)->exportExcel($faktur);

        $this->assertInstanceOf(BinaryFileResponse::class, $response);
        $this->assertStringContainsString('.xlsx', $response->headers->get('content-disposition'));
    }
}
