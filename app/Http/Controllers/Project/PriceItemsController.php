<?php

namespace App\Http\Controllers\Project;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Project\Concerns\AuthorizesProjectModules;
use App\Models\Project;
use App\Models\ProjectPriceItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PriceItemsController extends Controller
{
    use AuthorizesProjectModules;

    public function index(Project $project): View
    {
        $items = $project->priceItems;
        $totalHt = round($items->sum(fn (ProjectPriceItem $i) => $i->lineTotalHt()), 2);
        $totalTtc = round($items->sum(fn (ProjectPriceItem $i) => $i->lineTotalTtc()), 2);

        return view('portals.project.prices.index', compact('project', 'items', 'totalHt', 'totalTtc'));
    }

    public function store(Request $request, Project $project): RedirectResponse
    {
        $this->authorizeProjectModify($project);

        $data = $request->validate([
            'label' => ['required', 'string', 'max:255'],
            'quantity' => ['required', 'numeric', 'min:0.001', 'max:999999'],
            'unit_price_ht' => ['required', 'numeric', 'min:0'],
            'vat_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);
        $data['sort_order'] = (int) $project->priceItems()->max('sort_order') + 1;
        $project->priceItems()->create($data);

        return back()->with('success', 'Ligne ajoutée.');
    }

    public function update(Request $request, Project $project, ProjectPriceItem $item): RedirectResponse
    {
        $this->authorizeProjectModify($project);
        $this->assertItem($project, $item);

        $item->update($request->validate([
            'label' => ['required', 'string', 'max:255'],
            'quantity' => ['required', 'numeric', 'min:0.001', 'max:999999'],
            'unit_price_ht' => ['required', 'numeric', 'min:0'],
            'vat_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]));

        return back()->with('success', 'Ligne mise à jour.');
    }

    public function destroy(Project $project, ProjectPriceItem $item): RedirectResponse
    {
        $this->authorizeProjectModify($project);
        $this->assertItem($project, $item);
        $item->delete();

        return back()->with('success', 'Ligne supprimée.');
    }

    private function assertItem(Project $project, ProjectPriceItem $item): void
    {
        if ($item->project_id !== $project->id) {
            abort(404);
        }
    }
}
