<?php

namespace Tests\Unit;

use App\Http\Controllers\PiutangTransaksiController;
use Tests\TestCase;

class PiutangBulkPelunasanTest extends TestCase
{
    public function test_controller_has_bulk_methods(): void
    {
        $controller = new PiutangTransaksiController();
        $this->assertTrue(method_exists($controller, 'bulkPelunasan'));
        $this->assertTrue(method_exists($controller, 'storeBulkPelunasan'));
    }
}
