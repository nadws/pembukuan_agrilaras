<?php

namespace App\Http\Controllers;

use App\Services\CoaSync;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Throwable;

/**
 * Receives the COA change notices of the main application (Pembukuan side of the webhook).
 * A notice is accepted only with a valid HMAC signature and a recent timestamp; each event id is applied once.
 */
class CoaWebhookController extends Controller
{
    /**
     * Notices older than this are refused, so a captured notice cannot be replayed later.
     */
    private const MAX_AGE_SECONDS = 300;

    public function __construct(private CoaSync $sync)
    {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $secret = (string) config('services.coa.webhook_secret');

        if ($secret === '') {
            return response()->json(['message' => 'Webhook COA belum dikonfigurasi.'], 500);
        }

        $timestamp = (string) $request->header('X-Timestamp');
        $signature = (string) $request->header('X-Signature');
        $eventId = (string) $request->header('X-Event-Id');
        $body = $request->getContent();

        if (! ctype_digit($timestamp) || abs(time() - (int) $timestamp) > self::MAX_AGE_SECONDS) {
            return response()->json(['message' => 'Waktu pesan tidak valid.'], 401);
        }

        $expected = 'sha256='.hash_hmac('sha256', $timestamp.'.'.$body, $secret);

        if (! hash_equals($expected, $signature)) {
            return response()->json(['message' => 'Tanda tangan tidak cocok.'], 401);
        }

        // Claim the event id first; a repeat of an event already applied is answered without applying it again.
        $cacheKey = 'coa-webhook-event:'.$eventId;

        if ($eventId === '' || ! Cache::add($cacheKey, true, now()->addDays(7))) {
            return response()->json(['message' => 'Sudah diproses.', 'duplicate' => $eventId !== ''], 200);
        }

        $payload = json_decode($body, true);
        $action = str_replace('coa.account.', '', (string) ($payload['event'] ?? ''));

        if (! is_array($payload) || ! isset($payload['data']['code']) || ! in_array($action, ['created', 'updated', 'deleted'], true)) {
            Cache::forget($cacheKey);

            return response()->json(['message' => 'Isi pesan tidak dikenali.'], 422);
        }

        try {
            $this->sync->apply($payload['data'], $action);
            $this->sync->linkParent($payload['data']);
        } catch (Throwable $exception) {
            // Let the sender retry this event: release the claim before answering with an error.
            Cache::forget($cacheKey);
            report($exception);

            return response()->json(['message' => 'Gagal menyimpan perubahan.'], 500);
        }

        return response()->json(['ok' => true]);
    }
}
