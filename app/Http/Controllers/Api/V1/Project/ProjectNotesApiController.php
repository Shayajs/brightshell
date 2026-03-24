<?php

namespace App\Http\Controllers\Api\V1\Project;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Project\Concerns\AuthorizesProjectModules;
use App\Models\Project;
use App\Models\ProjectNote;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectNotesApiController extends Controller
{
    use AuthorizesProjectModules;

    public function index(Request $request, Project $project): JsonResponse
    {
        $this->authorize('view', $project);

        $notes = $project->notes()->with('user:id,first_name,last_name,email')->paginate(30);

        return response()->json($notes);
    }

    public function store(Request $request, Project $project): JsonResponse
    {
        $this->authorizeProjectAnnotate($project);

        $data = $request->validate(['body' => ['required', 'string', 'max:20000']]);
        $note = $project->notes()->create([
            'user_id' => $request->user()->id,
            'body' => $data['body'],
        ]);
        $note->load('user:id,first_name,last_name,email');

        return response()->json(['data' => $this->notePayload($note)], 201);
    }

    public function destroy(Request $request, Project $project, ProjectNote $note): JsonResponse
    {
        abort_unless($note->project_id === $project->id, 404);

        $user = $request->user();
        if ($note->user_id === $user->id) {
            $this->authorizeProjectAnnotate($project);
        } else {
            $this->authorizeProjectModify($project);
        }

        $note->delete();

        return response()->json(['message' => 'Note supprimée.']);
    }

    /** @return array<string, mixed> */
    private function notePayload(ProjectNote $note): array
    {
        return [
            'id' => $note->id,
            'body' => $note->body,
            'user' => $note->user ? [
                'id' => $note->user->id,
                'name' => $note->user->name,
            ] : null,
            'created_at' => $note->created_at?->toIso8601String(),
        ];
    }
}
