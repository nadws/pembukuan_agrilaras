<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class CoaSyncPageTest extends TestCase
{
    use DatabaseTransactions;

    public function test_the_sync_page_shows_the_status_but_never_the_token(): void
    {
        config(['services.coa.api_token' => 'RAHASIA-TOKEN-UJI']);

        $user = User::factory()->create();

        $this->actingAs($user)->get(route('master.coa-sync.index'))
            ->assertOk()
            ->assertSee('Sinkron COA')
            ->assertSee('Sudah diisi')
            ->assertDontSee('RAHASIA-TOKEN-UJI');
    }
}
