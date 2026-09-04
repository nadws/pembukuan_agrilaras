<?php

namespace Tests\Unit;

use App\Http\Controllers\FakturPembelianController;
use App\Models\FakturModel;
use Illuminate\Support\Facades\DB;
use Mockery;
use Tests\TestCase;

class FakturPembelianPphHutangTest extends TestCase
{
    private function tagihan(array $payments = [], float $pph = 2000)
    {
        $query = Mockery::mock(\Illuminate\Database\Query\Builder::class);
        DB::shouldReceive('table')->once()->with('pelunasan_faktur_pembelian')->andReturn($query);
        foreach (['whereIn', 'select', 'selectRaw', 'groupBy'] as $method) {
            $query->shouldReceive($method)->once()->andReturnSelf();
        }
        $query->shouldReceive('get')->once()->andReturn(collect($payments)->map(fn ($p) => (object) $p));

        $faktur = new FakturModel([
            'id' => 999,
            'no_faktur' => 'TEST-FP',
            'jenis_faktur' => 'pakan',
            'tanggal_faktur' => '2026-09-04',
            'total_hutang' => 1110000,
            'biaya_lain' => [
                ['kode' => 'ongkir', 'nominal' => 100000, 'pph23_nominal' => $pph],
                ['kode' => 'admin', 'nominal' => 10000],
            ],
        ]);
        $faktur->id = 999;
        $faktur->setRelation('supplier', null);
        $method = new \ReflectionMethod(FakturPembelianController::class, 'tagihanKomponen');
        return $method->invoke(new FakturPembelianController(), collect([$faktur]))->keyBy('komponen_hutang');
    }

    public function test_pph_is_separate_and_not_counted_as_supplier_debt(): void
    {
        $rows = $this->tagihan();
        $this->assertCount(4, $rows);
        $this->assertEquals(1000000, $rows['barang']->nominal_hutang);
        $this->assertEquals(100000, $rows['ongkir']->nominal_hutang);
        $this->assertEquals(2000, $rows['pph23']->nominal_hutang);
        $this->assertEquals(1112000, $rows->sum('nominal_hutang'));
        $this->assertSame('TEST-FP-PPH23', $rows['pph23']->nomor_tagihan);
    }

    public function test_tax_payment_only_reduces_tax_balance(): void
    {
        $rows = $this->tagihan([
            ['faktur_pembelian_id' => 999, 'komponen_hutang' => 'pph23', 'total_bayar' => 1000],
        ]);
        $this->assertEquals(1000, $rows['pph23']->sisa_hutang);
        $this->assertEquals(1000000, $rows['barang']->sisa_hutang);
        $this->assertEquals(100000, $rows['ongkir']->sisa_hutang);
    }

    public function test_legacy_supplier_payments_never_pay_tax(): void
    {
        $rows = $this->tagihan([
            ['faktur_pembelian_id' => 999, 'komponen_hutang' => null, 'total_bayar' => 1112000],
        ]);
        $this->assertEquals(0, $rows['barang']->sisa_hutang);
        $this->assertEquals(0, $rows['ongkir']->sisa_hutang);
        $this->assertEquals(2000, $rows['pph23']->sisa_hutang);
    }

    public function test_zero_pph_does_not_create_a_tax_bill(): void
    {
        $rows = $this->tagihan([], 0);
        $this->assertCount(3, $rows);
        $this->assertFalse($rows->has('pph23'));
    }
}
