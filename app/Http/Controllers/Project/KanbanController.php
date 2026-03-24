<?php

namespace App\Http\Controllers\Project;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Project\Concerns\AuthorizesProjectModules;
use App\Models\Project;
use App\Models\ProjectKanbanBoard;
use App\Models\ProjectKanbanCard;
use App\Models\ProjectKanbanColumn;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KanbanController extends Controller
{
    use AuthorizesProjectModules;

    public function index(Project $project): View
    {
        $board = $this->primaryBoard($project);
        $board->load(['columns.cards']);

        return view('portals.project.kanban.index', compact('project', 'board'));
    }

    public function storeColumn(Request $request, Project $project): RedirectResponse
    {
        $this->authorizeProjectModify($project);
        $board = $this->primaryBoard($project);

        $data = $request->validate(['name' => ['required', 'string', 'max:120']]);
        $max = (int) $board->columns()->max('sort_order');
        $board->columns()->create(['name' => $data['name'], 'sort_order' => $max + 1]);

        return back()->with('success', 'Colonne ajoutée.');
    }

    public function destroyColumn(Project $project, ProjectKanbanColumn $column): RedirectResponse
    {
        $this->authorizeProjectModify($project);
        $this->assertColumnOnProject($project, $column);
        $column->delete();

        return back()->with('success', 'Colonne supprimée.');
    }

    public function storeCard(Request $request, Project $project, ProjectKanbanColumn $column): RedirectResponse
    {
        $this->authorizeProjectModify($project);
        $this->assertColumnOnProject($project, $column);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'body' => ['nullable', 'string', 'max:10000'],
        ]);
        $max = (int) $column->cards()->max('sort_order');
        $column->cards()->create([
            'title' => $data['title'],
            'body' => $data['body'] ?? null,
            'sort_order' => $max + 1,
        ]);

        return back()->with('success', 'Carte créée.');
    }

    public function moveCard(Request $request, Project $project, ProjectKanbanCard $card): RedirectResponse
    {
        $this->authorizeProjectModify($project);

        $data = $request->validate([
            'column_id' => ['required', 'integer', 'exists:project_kanban_columns,id'],
        ]);

        $column = ProjectKanbanColumn::query()->findOrFail($data['column_id']);
        $this->assertColumnOnProject($project, $column);

        if ($card->column->board->project_id !== $project->id) {
            abort(404);
        }

        $max = (int) ProjectKanbanCard::query()->where('column_id', $column->id)->max('sort_order');
        $card->update(['column_id' => $column->id, 'sort_order' => $max + 1]);

        return back()->with('success', 'Carte déplacée.');
    }

    public function destroyCard(Project $project, ProjectKanbanCard $card): RedirectResponse
    {
        $this->authorizeProjectModify($project);
        if ($card->column->board->project_id !== $project->id) {
            abort(404);
        }
        $card->delete();

        return back()->with('success', 'Carte supprimée.');
    }

    private function primaryBoard(Project $project): ProjectKanbanBoard
    {
        $board = $project->kanbanBoards()->first();
        if ($board === null) {
            $board = $project->kanbanBoards()->create(['name' => 'Tableau principal']);
            $board->columns()->createMany([
                ['name' => 'À faire', 'sort_order' => 0],
                ['name' => 'En cours', 'sort_order' => 1],
                ['name' => 'Terminé', 'sort_order' => 2],
            ]);
            $board->load('columns');
        }

        return $board;
    }

    private function assertColumnOnProject(Project $project, ProjectKanbanColumn $column): void
    {
        if ($column->board->project_id !== $project->id) {
            abort(404);
        }
    }
}
