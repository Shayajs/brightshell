<?php

namespace App\Http\Controllers\Api\V1\Project;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Project\Concerns\AuthorizesProjectModules;
use App\Models\Project;
use App\Models\ProjectContract;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProjectContractsApiController extends Controller
{
    use AuthorizesProjectModules;

    public function index(Project $project): JsonResponse
    {
        $this->authorize('view', $project);

        $contracts = $project->contracts()->with('signedDocument:id,title')->get();

        return response()->json([
            'data' => $contracts->map(fn (ProjectContract $c) => [
                'id' => $c->id,
                'reference' => $c->reference,
                'status' => $c->status,
                'effective_on' => $c->effective_on?->toDateString(),
                'ends_on' => $c->ends_on?->toDateString(),
                'signed_document_id' => $c->signed_document_id,
                'signed_document' => $c->signedDocument ? ['id' => $c->signedDocument->id, 'title' => $c->signedDocument->title] : null,
            ]),
        ]);
    }

    public function store(Request $request, Project $project): JsonResponse
    {
        $this->authorizeProjectModify($project);

        $contract = $project->contracts()->create($this->validated($request, $project));

        return response()->json(['data' => ['id' => $contract->id]], 201);
    }

    public function update(Request $request, Project $project, ProjectContract $contract): JsonResponse
    {
        $this->authorizeProjectModify($project);
        $this->assertContract($project, $contract);

        $contract->update($this->validated($request, $project, $contract));

        return response()->json(['message' => 'Contrat mis à jour.']);
    }

    public function destroy(Project $project, ProjectContract $contract): JsonResponse
    {
        $this->authorizeProjectModify($project);
        $this->assertContract($project, $contract);
        $contract->delete();

        return response()->json(['message' => 'Contrat supprimé.']);
    }

    /** @return array<string, mixed> */
    private function validated(Request $request, Project $project, ?ProjectContract $ignore = null): array
    {
        return $request->validate([
            'reference' => ['required', 'string', 'max:255'],
            'status' => ['required', 'string', 'max:64'],
            'effective_on' => ['nullable', 'date'],
            'ends_on' => ['nullable', 'date', 'after_or_equal:effective_on'],
            'signed_document_id' => [
                'nullable',
                'integer',
                Rule::exists('project_documents', 'id')->where('project_id', $project->id),
            ],
        ]);
    }

    private function assertContract(Project $project, ProjectContract $contract): void
    {
        abort_unless($contract->project_id === $project->id, 404);
    }
}
