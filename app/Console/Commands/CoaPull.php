<?php

namespace App\Console\Commands;

use App\Services\CoaSync;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

/**
 * Pulls the COA of the main application into akun_perkiraan.
 * The first run takes the full list, then every later run takes only the changes since the last run.
 * Usage: php artisan coa:pull   (needs COA_API_URL and COA_API_TOKEN in .env)
 */
class CoaPull extends Command
{
    protected $signature = 'coa:pull';

    protected $description = 'Tarik COA dari aplikasi pusat dan terapkan ke akun perkiraan.';

    /**
     * One cursor per database, so a test database and the real one never share their sync position.
     */
    private function cursorFile(): string
    {
        return 'coa-sync-cursor-'.config('database.connections.'.config('database.default').'.database').'.txt';
    }

    public function handle(CoaSync $sync): int
    {
        $url = rtrim((string) config('services.coa.api_url'), '/');
        $token = (string) config('services.coa.api_token');

        if ($url === '' || $token === '') {
            $this->error('COA_API_URL dan COA_API_TOKEN harus diisi di .env.');

            return self::FAILURE;
        }

        if (! Storage::exists($this->cursorFile())) {
            $cursor = $this->snapshot($url, $token, $sync);

            if ($cursor === null) {
                return self::FAILURE;
            }
        } else {
            $cursor = (int) Storage::get($this->cursorFile());
        }

        $applied = 0;

        do {
            $response = Http::withToken($token)->acceptJson()->timeout(20)
                ->get($url.'/coa/changes', ['after' => $cursor, 'limit' => 200]);

            if (! $response->successful()) {
                $this->error('Gagal menarik perubahan dari '.$url.'/coa/changes: HTTP '.$response->status().' - '.$this->detail($response));

                return self::FAILURE;
            }

            $changes = $response->json('data', []);

            foreach ($changes as $change) {
                $sync->apply($change['account'], $change['action']);
                $applied++;
            }

            // Parents may come in a later batch, so the links are made after every batch.
            foreach ($changes as $change) {
                $sync->linkParent($change['account']);
            }

            $cursor = (int) $response->json('meta.next_after', $cursor);
            Storage::put($this->cursorFile(), (string) $cursor);

            $hasMore = (bool) $response->json('meta.has_more', false);
        } while ($hasMore);

        $this->info("Selesai. {$applied} perubahan COA diterapkan.");

        return self::SUCCESS;
    }

    /**
     * The first sync: every current account of the company. Returns the feed position to continue from.
     */
    private function snapshot(string $url, string $token, CoaSync $sync): ?int
    {
        $page = 1;
        $count = 0;
        $cursor = null;

        do {
            $response = Http::withToken($token)->acceptJson()->timeout(30)
                ->get($url.'/coa', ['page' => $page, 'per_page' => 200]);

            if (! $response->successful()) {
                $this->error('Gagal mengambil daftar COA dari '.$url.'/coa: HTTP '.$response->status().' - '.$this->detail($response));

                return null;
            }

            $accounts = $response->json('data', []);

            foreach ($accounts as $account) {
                $sync->apply($account, 'updated');
                $count++;
            }

            foreach ($accounts as $account) {
                $sync->linkParent($account);
            }

            // The feed position is taken with the first page; changes made while paging are pulled again later.
            $cursor ??= (int) $response->json('meta.changes_cursor', 0);
            $page++;
            $hasMore = (bool) $response->json('meta.has_more', false);
        } while ($hasMore);

        Storage::put($this->cursorFile(), (string) $cursor);
        $this->info("Daftar awal COA: {$count} akun diterapkan.");

        return $cursor;
    }

    private function detail(\Illuminate\Http\Client\Response $response): string
    {
        return (string) ($response->json('message') ?? mb_substr(strip_tags($response->body()), 0, 150));
    }
}
