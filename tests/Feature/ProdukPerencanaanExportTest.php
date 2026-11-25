<?php

namespace Tests\Feature;

use App\Exports\ProdukPerencanaanExport;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ProdukPerencanaanExportTest extends TestCase
{
    use DatabaseTransactions;

    public function test_the_export_has_every_product_and_the_category_filter_narrows_it(): void
    {
        $all = (new ProdukPerencanaanExport)->array();

        $this->assertCount((int) DB::table('tb_produk_perencanaan')->count(), $all);
        $this->assertSame(['ID Produk', 'Kode Accurate', 'Nama Produk', 'Kategori', 'Satuan Dosis', 'Satuan Campuran', 'Kegunaan'], (new ProdukPerencanaanExport)->headings());

        $kategori = (string) DB::table('tb_produk_perencanaan')->value('kategori');
        $filtered = (new ProdukPerencanaanExport('', $kategori))->array();

        $this->assertSame(
            (int) DB::table('tb_produk_perencanaan')->where('kategori', $kategori)->count(),
            count($filtered),
        );
    }

    public function test_the_export_file_is_sent_as_a_download(): void
    {
        $response = $this->actingAs(\App\Models\User::factory()->create())
            ->get(route('produk-perencanaan.export'));

        $response->assertOk();
        $this->assertStringContainsString('produk-perencanaan.xlsx', (string) $response->headers->get('content-disposition'));
    }
}
