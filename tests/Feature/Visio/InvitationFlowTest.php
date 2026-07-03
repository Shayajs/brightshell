<?php

namespace Tests\Feature\Visio;

use App\Models\Project;
use App\Models\User;
use App\Models\VisioInvitation;
use App\Models\VisioRoom;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class InvitationFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_with_matching_email_accepts_invitation_and_is_tracked(): void
    {
        $host = $this->visioHost();
        $user = User::factory()->create([
            'email' => 'client@example.test',
            'email_verified_at' => now(),
        ]);
        $project = Project::query()->create(['name' => 'Projet Flow']);
        $room = VisioRoom::query()->create([
            'project_id' => $project->id,
            'title' => 'Flow visio',
            'slug' => 'flow-visio',
            'status' => 'scheduled',
            'meta' => [],
        ]);
        $invitation = VisioInvitation::query()->create([
            'visio_room_id' => $room->id,
            'email' => 'client@example.test',
            'token' => Str::random(48),
            'expires_at' => now()->addDays(3),
            'can_join' => true,
        ]);

        $this->actingAs($user)
            ->withHeader('Host', $host)
            ->post('/join/'.$invitation->token)
            ->assertRedirectContains('/r/'.$room->slug);

        $this->assertDatabaseHas('visio_participants', [
            'visio_room_id' => $room->id,
            'user_id' => $user->id,
        ]);
        $this->assertNotNull($invitation->fresh()->accepted_at);
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
