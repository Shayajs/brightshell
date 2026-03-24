<?php

namespace App\Http\Controllers\Api\V1\Project;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Project\Concerns\AuthorizesProjectModules;
use App\Models\Project;
use App\Models\ProjectKanbanBoard;
use App\Models\ProjectKanbanCard;
use App\Models\ProjectKanbanColumn;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectKanbanApiController extends Controller
{
    use AuthorizesProjectModules;

    public function show(Request $request, Project $project): JsonResponse
    {
        $this->authorize('view', $project);

        $board = $this->primaryBoard($project);
        $board->load(['columns.cards']);

        return response()->json(['data' => $this->boardPayload($board)]);
    }

    public function storeColumn(Request $request, Project $project): JsonResponse
    {
        $this->authorizeProjectModify($project);
        $board = $this->primaryBoard($project);

        $data = $request->validate(['name' => ['required', 'string', 'max:120']]);
        $max = (int) $board->columns()->max('sort_order');
        $column = $board->columns()->create(['name' => $data['name'], 'sort_order' => $max + 1]);

        return response()->json(['data' => ['id' => $column->id, 'name' => $column->name, 'sort_order' => $column->sort_order]], 201);
    }

    public function destroyColumn(Project $project, ProjectKanbanColumn $column): JsonResponse
    {
        $this->authorizeProjectModify($project);
        $this->assertColumnOnProject($project, $column);
        $column->delete();

        return response()->json(['message' => 'Colonne supprimée.']);
    }

    public function storeCard(Request $request, Project $project, ProjectKanbanColumn $column): JsonResponse
    {
        $this->authorizeProjectModify($project);
        $this->assertColumnOnProject($project, $column);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'body' => ['nullable', 'string', 'max:10000'],
        ]);
        $max = (int) $column->cards()->max('sort_order');
        $card = $column->cards()->create([
            'title' => $data['title'],
            'body' => $data['body'] ?? null,
            'sort_order' => $max + 1,
        ]);

        return response()->json(['data' => ['id' => $card->id, 'title' => $card->title, 'body' => $card->body, 'column_id' => $column->id]], 201);
    }

    public function moveCard(Request $request, Project $project, ProjectKanbanCard $card): JsonResponse
    {
        $this->authorizeProjectModify($project);

        $data = $request->validate([
            'column_id' => ['required', 'integer', 'exists:project_kanban_columns,id'],
        ]);

        $column = ProjectKanbanColumn::query()->findOrFail($data['column_id']);
        $this->assertColumnOnProject($project, $column);

        abort_unless($card->column->board->project_id === $project->id, 404);

        $max = (int) ProjectKanbanCard::query()->where('column_id', $column->id)->max('sort_order');
        $card->update(['column_id' => $column->id, 'sort_order' => $max + 1]);

        return response()->json(['data' => ['id' => $card->id, 'column_id' => $card->column_id, 'sort_order' => $card->sort_order]]);
    }

    public function destroyCard(Project $project, ProjectKanbanCard $card): JsonResponse
    {
        $this->authorizeProjectModify($project);
        abort_unless($card->column->board->project_id === $project->id, 404);
        $card->delete();

        return response()->json(['message' => 'Carte supprimée.']);
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
        $column->loadMissing('board');
        abort_unless($column->board->project_id === $project->id, 404);
    }

    /** @return array<string, mixed> */
    private function boardPayload(ProjectKanbanBoard $board): array
    {
        return [
            'id' => $board->id,
            'name' => $board->name,
            'columns' => $board->columns->map(fn (ProjectKanbanColumn $c) => [
                'id' => $c->id,
                'name' => $c->name,
                'sort_order' => $c->sort_order,
                'cards' => $c->cards->map(fn (ProjectKanbanCard $card) => [
                    'id' => $card->id,
                    'title' => $card->title,
                    'body' => $card->body,
                    'sort_order' => $card->sort_order,
                ]),
            ]),
        ];
    }
}
