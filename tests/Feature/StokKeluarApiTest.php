<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class StokKeluarApiTest extends TestCase
{
    use DatabaseTransactions;

    private int $kandang;

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.stok_api.token' => 'TOKEN-UJI-JOMA']);

        $this->kandang = $this->makeKandang('KANDANG UJI A');
    }

    /**
     * The database already holds real rows, so each test only looks at rows added after this id.
     */
    private function lastId(): int
    {
        return (int) DB::table('stok_produk_perencanaan')->max('id_stok_telur');
    }

    private function makeKandang(string $name): int
    {
        return (int) DB::table('kandang')->insertGetId([
            'nm_kandang' => $name, 'chick_in' => '2026-01-01', 'stok_awal' => 1000, 'id_strain' => 1,
            'selesai' => 'T', 'rupiah' => 0, 'id_post' => 1,
        ]);
    }

    private function makeProduk(string $name, string $kode): int
    {
        return (int) DB::table('tb_produk_perencanaan')->insertGetId([
            'nm_produk' => $name, 'kode_accurate' => $kode, 'kategori' => 'pakan', 'tgl' => '2026-01-01', 'admin' => 'uji',
        ]);
    }

    private function makeStok(int $kandang, int $produk, string $tgl, float $keluar, string $opname = 'T'): int
    {
        return (int) DB::table('stok_produk_perencanaan')->insertGetId([
            'id_kandang' => $kandang, 'id_pakan' => $produk, 'tgl' => $tgl, 'pcs' => 0, 'pcs_kredit' => $keluar,
            'admin' => 'uji', 'check' => 'T', 'cek_admin' => 'uji', 'opname' => 'T', 'total_rp' => $keluar * 1000,
            'biaya_dll' => 0, 'no_nota' => 'NOTA-UJI', 'h_opname' => $opname, 'penyesuaian' => 'T',
        ]);
    }

    public function test_a_request_without_the_token_is_refused(): void
    {
        $this->getJson('/api/stok-keluar')->assertUnauthorized();
        $this->withHeader('Authorization', 'Bearer SALAH')->getJson('/api/stok-keluar')->assertUnauthorized();
    }

    public function test_it_returns_stock_out_lines_with_the_item_code_and_skips_opname(): void
    {
        $start = $this->lastId();
        $produk = $this->makeProduk('Jagung', 'PKN-001');
        $idKeluar = $this->makeStok($this->kandang, $produk, '2026-01-05', 12.5);
        $this->makeStok($this->kandang, $produk, '2026-01-06', 3, 'Y');
        $this->makeStok($this->kandang, $produk, '2026-01-07', 0);

        $response = $this->withHeader('Authorization', 'Bearer TOKEN-UJI-JOMA')
            ->getJson('/api/stok-keluar?after='.$start)
            ->assertOk();

        $this->assertCount(1, $response->json('data'));
        $this->assertSame($idKeluar, $response->json('data.0.id'));
        $this->assertSame('PKN-001', $response->json('data.0.kode_barang'));
        $this->assertSame(12.5, (float) $response->json('data.0.jumlah'));
        $this->assertSame($idKeluar, $response->json('meta.next_after'));
        $this->assertFalse($response->json('meta.has_more'));
    }

    public function test_the_position_moves_forward_in_pages(): void
    {
        $start = $this->lastId();
        $produk = $this->makeProduk('Dedak', 'PKN-002');
        $this->makeStok($this->kandang, $produk, '2026-01-05', 1);
        $this->makeStok($this->kandang, $produk, '2026-01-06', 2);
        $this->makeStok($this->kandang, $produk, '2026-01-07', 3);

        $first = $this->withHeader('Authorization', 'Bearer TOKEN-UJI-JOMA')
            ->getJson('/api/stok-keluar?limit=2&after='.$start)
            ->assertOk();

        $this->assertCount(2, $first->json('data'));
        $this->assertTrue($first->json('meta.has_more'));

        $second = $this->withHeader('Authorization', 'Bearer TOKEN-UJI-JOMA')
            ->getJson('/api/stok-keluar?limit=2&after='.$first->json('meta.next_after'))
            ->assertOk();

        $this->assertCount(1, $second->json('data'));
        $this->assertFalse($second->json('meta.has_more'));
    }
}
