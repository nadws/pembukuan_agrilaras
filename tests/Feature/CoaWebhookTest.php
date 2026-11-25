<?php

namespace Tests\Feature;

use App\Models\AkunPerkiraan;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class CoaWebhookTest extends TestCase
{
    use DatabaseTransactions;

    private const SECRET = 'rahasia-uji-coa';

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.coa.webhook_secret' => self::SECRET]);
        Cache::flush();
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{0: string, 1: array<string, string>}
     */
    private function signed(array $data, string $action, string $eventId, ?int $timestamp = null): array
    {
        $timestamp ??= time();
        $body = json_encode([
            'event_id' => $eventId,
            'event' => 'coa.account.'.$action,
            'data' => $data,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $headers = [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_EVENT_ID' => $eventId,
            'HTTP_X_TIMESTAMP' => (string) $timestamp,
            'HTTP_X_SIGNATURE' => 'sha256='.hash_hmac('sha256', $timestamp.'.'.$body, self::SECRET),
        ];

        return [$body, $headers];
    }

    private function account(string $code, string $name, string $category = 'expense', bool $active = true): array
    {
        return [
            'code' => $code,
            'name' => $name,
            'category' => $category,
            'parent_code' => null,
            'is_header' => false,
            'is_active' => $active,
            'notes' => null,
            'updated_at' => now()->toIso8601String(),
            'deleted_at' => null,
        ];
    }

    public function test_a_signed_notice_creates_the_account(): void
    {
        [$body, $headers] = $this->signed($this->account('61-6100001', 'Beban Bensin'), 'created', 'coa-1');

        $this->call('POST', '/api/coa-webhook', [], [], [], $headers, $body)->assertOk();

        $account = AkunPerkiraan::query()->where('kode_perkiraan', '61-6100001')->sole();
        $this->assertSame('Beban Bensin', $account->nama);
        $this->assertSame('EXPS', $account->tipe_akun);
        $this->assertTrue($account->aktif);
    }

    public function test_a_notice_with_a_wrong_signature_is_refused_and_changes_nothing(): void
    {
        [$body, $headers] = $this->signed($this->account('61-6100001', 'Beban Bensin'), 'created', 'coa-2');
        $headers['HTTP_X_SIGNATURE'] = 'sha256=salah';

        $this->call('POST', '/api/coa-webhook', [], [], [], $headers, $body)->assertUnauthorized();

        $this->assertSame(0, AkunPerkiraan::query()->count());
    }

    public function test_an_old_notice_is_refused(): void
    {
        [$body, $headers] = $this->signed($this->account('61-6100001', 'Beban Bensin'), 'created', 'coa-3', time() - 3600);

        $this->call('POST', '/api/coa-webhook', [], [], [], $headers, $body)->assertUnauthorized();

        $this->assertSame(0, AkunPerkiraan::query()->count());
    }

    public function test_a_deleted_account_is_switched_off_not_erased(): void
    {
        [$body, $headers] = $this->signed($this->account('61-6100001', 'Beban Bensin'), 'created', 'coa-4');
        $this->call('POST', '/api/coa-webhook', [], [], [], $headers, $body)->assertOk();

        [$body, $headers] = $this->signed($this->account('61-6100001', 'Beban Bensin', 'expense', false), 'deleted', 'coa-5');
        $this->call('POST', '/api/coa-webhook', [], [], [], $headers, $body)->assertOk();

        $account = AkunPerkiraan::query()->where('kode_perkiraan', '61-6100001')->sole();
        $this->assertFalse($account->aktif);
    }

    public function test_the_same_event_is_applied_only_once(): void
    {
        [$body, $headers] = $this->signed($this->account('61-6100001', 'Beban Bensin'), 'created', 'coa-6');
        $this->call('POST', '/api/coa-webhook', [], [], [], $headers, $body)->assertOk();

        // The same event again, with a different name inside: it must not be applied a second time.
        [$again, $againHeaders] = $this->signed($this->account('61-6100001', 'Nama Lain'), 'updated', 'coa-6');
        $this->call('POST', '/api/coa-webhook', [], [], [], $againHeaders, $again)
            ->assertOk()
            ->assertJsonPath('duplicate', true);

        $this->assertSame('Beban Bensin', AkunPerkiraan::query()->where('kode_perkiraan', '61-6100001')->value('nama'));
    }
}
