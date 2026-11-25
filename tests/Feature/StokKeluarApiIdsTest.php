<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class StokKeluarApiIdsTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.stok_api.token' => 'TOKEN-UJI-JOMA']);
    }

    public function test_ids_returns_only_the_asked_lines(): void
    {
        $kandang = (int) DB::table('kandang')->insertGetId([
            'nm_kandang' => 'KANDANG IDS', 'chick_in' => '2026-01-01', 'stok_awal' => 1000, 'id_strain' => 1,
            'selesai' => 'T', 'rupiah' => 0, 'id_post' => 1,
        ]);
        $produk = (int) DB::table('tb_produk_perencanaan')->insertGetId([
            'nm_produk' => 'Uji', 'kode_accurate' => 'P-999', 'kategori' => 'pakan', 'tgl' => '2026-01-01', 'admin' => 'uji',
        ]);

        $ids = [];

        foreach (['2026-01-05', '2026-01-06', '2026-01-07'] as $tgl) {
            $ids[] = (int) DB::table('stok_produk_perencanaan')->insertGetId([
                'id_kandang' => $kandang, 'id_pakan' => $produk, 'tgl' => $tgl, 'pcs' => 0, 'pcs_kredit' => 1,
                'admin' => 'uji', 'check' => 'T', 'cek_admin' => 'uji', 'opname' => 'T', 'total_rp' => 1000,
                'biaya_dll' => 0, 'no_nota' => 'NOTA-IDS', 'h_opname' => 'T', 'penyesuaian' => 'T',
            ]);
        }

        $response = $this->withHeader('Authorization', 'Bearer TOKEN-UJI-JOMA')
            ->getJson('/api/stok-keluar?ids[]='.$ids[0].'&ids[]='.$ids[2])
            ->assertOk();

        $this->assertSame([$ids[0], $ids[2]], collect($response->json('data'))->pluck('id')->all());
    }
}
