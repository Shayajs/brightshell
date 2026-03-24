<?php

namespace App\Http\Controllers\Api\V1\Project;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Project\Concerns\AuthorizesProjectModules;
use App\Models\Project;
use App\Models\ProjectPriceItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectPriceItemsApiController extends Controller
{
    use AuthorizesProjectModules;

    public function index(Project $project): JsonResponse
    {
        $this->authorize('view', $project);

        $items = $project->priceItems;
        $totalHt = round($items->sum(fn (ProjectPriceItem $i) => $i->lineTotalHt()), 2);
        $totalTtc = round($items->sum(fn (ProjectPriceItem $i) => $i->lineTotalTtc()), 2);

        return response()->json([
            'data' => [
                'items' => $items->map(fn (ProjectPriceItem $i) => [
                    'id' => $i->id,
                    'label' => $i->label,
                    'quantity' => $i->quantity,
                    'unit_price_ht' => $i->unit_price_ht,
                    'vat_rate' => $i->vat_rate,
                    'line_total_ht' => $i->lineTotalHt(),
                    'line_total_ttc' => $i->lineTotalTtc(),
                    'sort_order' => $i->sort_order,
                ]),
                'totals' => ['ht' => $totalHt, 'ttc' => $totalTtc],
            ],
        ]);
    }

    public function store(Request $request, Project $project): JsonResponse
    {
        $this->authorizeProjectModify($project);

        $data = $request->validate([
            'label' => ['required', 'string', 'max:255'],
            'quantity' => ['required', 'numeric', 'min:0.001', 'max:999999'],
            'unit_price_ht' => ['required', 'numeric', 'min:0'],
            'vat_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);
        $data['sort_order'] = (int) $project->priceItems()->max('sort_order') + 1;
        $item = $project->priceItems()->create($data);

        return response()->json(['data' => ['id' => $item->id]], 201);
    }

    public function update(Request $request, Project $project, ProjectPriceItem $item): JsonResponse
    {
        $this->authorizeProjectModify($project);
        $this->assertItem($project, $item);

        $item->update($request->validate([
            'label' => ['required', 'string', 'max:255'],
            'quantity' => ['required', 'numeric', 'min:0.001', 'max:999999'],
            'unit_price_ht' => ['required', 'numeric', 'min:0'],
            'vat_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]));

        return response()->json(['message' => 'Ligne mise à jour.']);
    }

    public function destroy(Project $project, ProjectPriceItem $item): JsonResponse
    {
        $this->authorizeProjectModify($project);
        $this->assertItem($project, $item);
        $item->delete();

        return response()->json(['message' => 'Ligne supprimée.']);
    }

    private function assertItem(Project $project, ProjectPriceItem $item): void
    {
        abort_unless($item->project_id === $project->id, 404);
    }
}
