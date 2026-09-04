<?php

namespace Tests\Unit;

use App\Http\Controllers\LaporanAkhirBulanController;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class LaporanAkhirBulanPeriodTest extends TestCase
{
    private function period(array $data): array
    {
        return (new \ReflectionMethod(LaporanAkhirBulanController::class, 'reportPeriod'))
            ->invoke(new LaporanAkhirBulanController(), $data);
    }

    public function test_exact_cross_month_range_is_preserved(): void
    {
        [$start, $end] = $this->period(['tgl1' => '2026-07-15', 'tgl2' => '2026-08-20']);
        $this->assertSame('2026-07-15 00:00:00', $start->toDateTimeString());
        $this->assertSame('2026-08-20 23:59:59', $end->toDateTimeString());
        $this->assertSame('2026-07-14', $start->copy()->subDay()->toDateString());
    }

    public function test_same_day_range_is_allowed(): void
    {
        [$start, $end] = $this->period(['tgl1' => '2026-08-20', 'tgl2' => '2026-08-20']);
        $this->assertTrue($end->gt($start));
        $this->assertSame($start->toDateString(), $end->toDateString());
    }

    public function test_reversed_range_is_rejected(): void
    {
        $this->expectException(ValidationException::class);
        $this->period(['tgl1' => '2026-08-20', 'tgl2' => '2026-07-15']);
    }

    public function test_defaults_and_legacy_bookmarks(): void
    {
        Carbon::setTestNow('2026-09-04 12:00:00');
        try {
            [$start, $end] = $this->period([]);
            $this->assertSame('2026-09-01', $start->toDateString());
            $this->assertSame('2026-09-04', $end->toDateString());
            [$start, $end] = $this->period(['bulan' => 7, 'tahun' => 2026]);
            $this->assertSame('2026-07-01', $start->toDateString());
            $this->assertSame('2026-07-31', $end->toDateString());
        } finally {
            Carbon::setTestNow();
        }
    }
}
