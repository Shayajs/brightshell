<?php

namespace Tests\Feature\Visio;

use App\Models\Project;
use App\Models\VisioRoom;
use App\Services\Visio\VisioContextResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LiveQuoteSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_context_reflects_latest_project_quote_totals(): void
    {
        $project = Project::query()->create(['name' => 'Projet Devis Live']);
        $project->priceItems()->create([
            'label' => 'Prestation A',
            'quantity' => 1,
            'unit_price_ht' => 100,
            'vat_rate' => 20,
            'sort_order' => 1,
        ]);
        $project->priceItems()->create([
            'label' => 'Prestation B',
            'quantity' => 2,
            'unit_price_ht' => 50,
            'vat_rate' => 20,
            'sort_order' => 2,
        ]);

        $room = VisioRoom::query()->create([
            'project_id' => $project->id,
            'title' => 'Room Devis',
            'slug' => 'room-devis',
            'status' => 'live',
            'meta' => [],
        ]);

        $resolver = app(VisioContextResolver::class);
        $contextBefore = $resolver->roomContext($room);
        $this->assertSame(240.0, $contextBefore['prices']['totals']['ttc']);

        $item = $project->priceItems()->firstOrFail();
        $item->update(['unit_price_ht' => 150]);

        $contextAfter = $resolver->roomContext($room->fresh());
        $this->assertSame(300.0, $contextAfter['prices']['totals']['ttc']);
    }
}
