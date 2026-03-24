<?php

namespace App\Http\Controllers\Project;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectKanbanCard;
use Illuminate\View\View;

class ShowController extends Controller
{
    public function __invoke(Project $project): View
    {
        $project->load('company');
        $project->loadCount([
            'appointments',
            'notes',
            'documents',
            'specSections',
            'contracts',
            'priceItems',
            'requests',
        ]);

        $kanbanCardsCount = ProjectKanbanCard::query()
            ->whereHas('column.board', fn ($q) => $q->where('project_id', $project->id))
            ->count();

        return view('portals.project.show', [
            'project' => $project,
            'kanbanCardsCount' => $kanbanCardsCount,
        ]);
    }
}
