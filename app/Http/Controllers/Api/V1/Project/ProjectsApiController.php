<?php

namespace App\Http\Controllers\Api\V1\Project;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectsApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Project::class);

        $user = $request->user();
        $projects = Project::query()
            ->forUser($user)
            ->with('company:id,name')
            ->orderedForDisplay()
            ->get();

        return response()->json([
            'data' => $projects->map(fn (Project $p) => $this->projectSummary($request, $p)),
        ]);
    }

    public function show(Request $request, Project $project): JsonResponse
    {
        $this->authorize('view', $project);

        $project->load('company:id,name');

        return response()->json(['data' => $this->projectDetail($request, $project)]);
    }

    /** @return array<string, mixed> */
    private function projectSummary(Request $request, Project $project): array
    {
        $pivot = $project->membershipFor($request->user());

        return [
            'slug' => $project->slug,
            'name' => $project->name,
            'description' => $project->description,
            'archived_at' => $project->archived_at?->toIso8601String(),
            'company' => $project->company ? ['id' => $project->company->id, 'name' => $project->company->name] : null,
            'membership' => $pivot ? [
                'can_view' => (bool) $pivot->can_view,
                'can_modify' => (bool) $pivot->can_modify,
                'can_annotate' => (bool) $pivot->can_annotate,
                'can_download' => (bool) $pivot->can_download,
            ] : null,
        ];
    }

    /** @return array<string, mixed> */
    private function projectDetail(Request $request, Project $project): array
    {
        return $this->projectSummary($request, $project);
    }
}
