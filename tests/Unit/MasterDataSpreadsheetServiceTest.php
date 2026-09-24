<?php

namespace Tests\Unit;

use App\Services\MasterDataSpreadsheetService;
use PHPUnit\Framework\TestCase;

class MasterDataSpreadsheetServiceTest extends TestCase
{
    public function test_teks_sel_keeps_plain_text(): void
    {
        $this->assertSame('081234567890', MasterDataSpreadsheetService::teksSel('081234567890'));
        $this->assertSame('', MasterDataSpreadsheetService::teksSel(null));
        $this->assertSame('', MasterDataSpreadsheetService::teksSel('   '));
    }

    public function test_teks_sel_expands_numeric_cells(): void
    {
        $this->assertSame('81234567890', MasterDataSpreadsheetService::teksSel(81234567890));
        $this->assertSame('6371041205780000', MasterDataSpreadsheetService::teksSel(6371041205780000.0));
    }

    public function test_teks_sel_expands_scientific_notation_strings(): void
    {
        $this->assertSame('6370000000000000', MasterDataSpreadsheetService::teksSel('6.37E+15'));
        $this->assertSame('6371040000000000', MasterDataSpreadsheetService::teksSel('6.37104E+15'));
    }
}
