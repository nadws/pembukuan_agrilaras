<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class StokKeluarApiRangeTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.stok_api.token' => 'TOKEN-UJI-JOMA']);
    }

    public function test_a_date_range_returns_only_the_lines_dated_inside_it(): void
    {
        $start = (int) DB::table('stok_produk_perencanaan')->max('id_stok_telur');
        $kandang = (int) DB::table('kandang')->insertGetId([
            'nm_kandang' => 'KANDANG RENTANG', 'chick_in' => '2026-01-01', 'stok_awal' => 1000, 'id_strain' => 1,
            'selesai' => 'T', 'rupiah' => 0, 'id_post' => 1,
        ]);
        $produk = (int) DB::table('tb_produk_perencanaan')->insertGetId([
            'nm_produk' => 'Rentang', 'kode_accurate' => 'P-RNG', 'kategori' => 'pakan', 'tgl' => '2026-01-01', 'admin' => 'uji',
        ]);

        foreach (['2026-03-01', '2026-03-02', '2026-03-03'] as $tgl) {
            DB::table('stok_produk_perencanaan')->insert([
                'id_kandang' => $kandang, 'id_pakan' => $produk, 'tgl' => $tgl, 'pcs' => 0, 'pcs_kredit' => 1,
                'admin' => 'uji', 'check' => 'T', 'cek_admin' => 'uji', 'opname' => 'T', 'total_rp' => 1000,
                'biaya_dll' => 0, 'no_nota' => 'NOTA-RNG', 'h_opname' => 'T', 'penyesuaian' => 'T',
            ]);
        }

        $response = $this->withHeader('Authorization', 'Bearer TOKEN-UJI-JOMA')
            ->getJson('/api/stok-keluar?after='.$start.'&tgl1=2026-03-02&tgl2=2026-03-02')
            ->assertOk();

        $this->assertSame(['2026-03-02'], collect($response->json('data'))->pluck('tgl')->unique()->values()->all());
        $this->assertCount(1, $response->json('data'));
    }

    public function test_a_range_that_ends_before_it_starts_is_refused(): void
    {
        $this->withHeader('Authorization', 'Bearer TOKEN-UJI-JOMA')
            ->getJson('/api/stok-keluar?tgl1=2026-03-05&tgl2=2026-03-01')
            ->assertUnprocessable();
    }
}
