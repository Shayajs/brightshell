<?php

namespace Tests\Feature\Visio;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectVisioAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_project_visio_route_is_forbidden_for_unrelated_user(): void
    {
        $project = Project::query()->create(['name' => 'Projet Auth']);
        $outsider = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($outsider)
            ->withHeader('Host', $this->projectHost())
            ->get('/projets/'.$project->slug.'/visio')
            ->assertForbidden();
    }

    public function test_project_visio_route_is_available_for_admin(): void
    {
        $project = Project::query()->create(['name' => 'Projet Auth Admin']);
        $admin = User::factory()->create([
            'is_admin' => true,
            'email_verified_at' => now(),
        ]);

        $this->actingAs($admin)
            ->withHeader('Host', $this->projectHost())
            ->get('/projets/'.$project->slug.'/visio')
            ->assertOk();
    }

    private function projectHost(): string
    {
        $host = (string) config('brightshell.domains.project_host', '');
        if ($host !== '') {
            return $host;
        }

        $root = (string) parse_url((string) config('app.url'), PHP_URL_HOST);

        return 'project.'.$root;
    }
}
