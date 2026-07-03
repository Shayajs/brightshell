<?php

namespace Tests\Feature\Visio;

use App\Models\Project;
use App\Models\VisioInvitation;
use App\Models\VisioRoom;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class GuestJoinTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_join_visio_with_token_without_account(): void
    {
        $host = $this->visioHost();
        $project = Project::query()->create(['name' => 'Projet Test Visio']);
        $room = VisioRoom::query()->create([
            'project_id' => $project->id,
            'title' => 'Session test',
            'slug' => 'session-test',
            'status' => 'live',
            'meta' => [],
        ]);
        $invitation = VisioInvitation::query()->create([
            'visio_room_id' => $room->id,
            'email' => null,
            'token' => Str::random(48),
            'expires_at' => now()->addDays(2),
            'can_join' => true,
        ]);

        $this->withHeader('Host', $host)
            ->get('/join/'.$invitation->token)
            ->assertOk();

        $this->withHeader('Host', $host)
            ->post('/join/'.$invitation->token, ['guest_name' => 'Invité QA'])
            ->assertRedirectContains('/r/'.$room->slug);

        $this->assertDatabaseHas('visio_participants', [
            'visio_room_id' => $room->id,
            'guest_name' => 'Invité QA',
        ]);
    }

    private function visioHost(): string
    {
        $host = (string) config('brightshell.domains.visio_host', '');
        if ($host !== '') {
            return $host;
        }

        $root = (string) parse_url((string) config('app.url'), PHP_URL_HOST);

        return 'visio.'.$root;
    }
}
