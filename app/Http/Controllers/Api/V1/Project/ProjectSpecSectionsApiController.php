<?php

namespace App\Http\Controllers\Api\V1\Project;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Project\Concerns\AuthorizesProjectModules;
use App\Models\Project;
use App\Models\ProjectSpecSection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ProjectSpecSectionsApiController extends Controller
{
    use AuthorizesProjectModules;

    public function index(Project $project): JsonResponse
    {
        $this->authorize('view', $project);

        $query = $project->specSections()->orderBy('sort_order')->orderBy('id');

        if (! Gate::allows('update', $project)) {
            $query->where('status', ProjectSpecSection::STATUS_PUBLISHED);
        }

        $sections = $query->get();

        return response()->json([
            'data' => $sections->map(fn (ProjectSpecSection $s) => [
                'id' => $s->id,
                'title' => $s->title,
                'body' => $s->body,
                'status' => $s->status,
                'sort_order' => $s->sort_order,
            ]),
        ]);
    }

    public function store(Request $request, Project $project): JsonResponse
    {
        $this->authorizeProjectModify($project);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'body' => ['nullable', 'string'],
            'status' => ['required', 'string', 'in:'.ProjectSpecSection::STATUS_DRAFT.','.ProjectSpecSection::STATUS_PUBLISHED],
        ]);
        $data['sort_order'] = (int) $project->specSections()->max('sort_order') + 1;
        $section = $project->specSections()->create($data);

        return response()->json(['data' => ['id' => $section->id]], 201);
    }

    public function update(Request $request, Project $project, ProjectSpecSection $section): JsonResponse
    {
        $this->authorizeProjectModify($project);
        $this->assertSection($project, $section);

        $section->update($request->validate([
            'title' => ['required', 'string', 'max:255'],
            'body' => ['nullable', 'string'],
            'status' => ['required', 'string', 'in:'.ProjectSpecSection::STATUS_DRAFT.','.ProjectSpecSection::STATUS_PUBLISHED],
        ]));

        return response()->json(['message' => 'Section mise à jour.']);
    }

    public function destroy(Project $project, ProjectSpecSection $section): JsonResponse
    {
        $this->authorizeProjectModify($project);
        $this->assertSection($project, $section);
        $section->delete();

        return response()->json(['message' => 'Section supprimée.']);
    }

    private function assertSection(Project $project, ProjectSpecSection $section): void
    {
        abort_unless($section->project_id === $project->id, 404);
    }
}
