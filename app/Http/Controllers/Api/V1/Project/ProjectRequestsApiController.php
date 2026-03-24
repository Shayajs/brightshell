<?php

namespace App\Http\Controllers\Api\V1\Project;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Project\Concerns\AuthorizesProjectModules;
use App\Models\Project;
use App\Models\ProjectRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class ProjectRequestsApiController extends Controller
{
    use AuthorizesProjectModules;

    public function index(Request $request, Project $project): JsonResponse
    {
        $this->authorize('view', $project);

        $requests = $project->requests()->with(['user:id,first_name,last_name', 'supportTicket:id,subject'])->paginate(25);

        return response()->json($requests);
    }

    public function store(Request $request, Project $project): JsonResponse
    {
        $this->authorize('view', $project);

        $rules = [
            'title' => ['required', 'string', 'max:255'],
            'body' => ['nullable', 'string', 'max:20000'],
        ];

        if (Gate::allows('update', $project)) {
            $rules['support_ticket_id'] = [
                'nullable',
                'integer',
                Rule::exists('support_tickets', 'id'),
            ];
        }

        $data = $request->validate($rules);

        if (! Gate::allows('update', $project)) {
            unset($data['support_ticket_id']);
        }

        $req = $project->requests()->create([
            'user_id' => $request->user()->id,
            'title' => $data['title'],
            'body' => $data['body'] ?? null,
            'status' => ProjectRequest::STATUS_OPEN,
            'support_ticket_id' => $data['support_ticket_id'] ?? null,
        ]);
        $req->load(['user:id,first_name,last_name', 'supportTicket:id,subject']);

        return response()->json(['data' => $this->requestPayload($req)], 201);
    }

    public function update(Request $request, Project $project, ProjectRequest $project_request): JsonResponse
    {
        $this->authorizeProjectModify($project);
        abort_unless($project_request->project_id === $project->id, 404);

        $project_request->update($request->validate([
            'status' => ['required', 'string', 'in:'.implode(',', [
                ProjectRequest::STATUS_OPEN,
                ProjectRequest::STATUS_IN_PROGRESS,
                ProjectRequest::STATUS_DONE,
                ProjectRequest::STATUS_CANCELLED,
            ])],
            'support_ticket_id' => [
                'nullable',
                'integer',
                Rule::exists('support_tickets', 'id'),
            ],
        ]));
        $project_request->load(['user:id,first_name,last_name', 'supportTicket:id,subject']);

        return response()->json(['data' => $this->requestPayload($project_request)]);
    }

    public function destroy(Project $project, ProjectRequest $project_request): JsonResponse
    {
        $this->authorizeProjectModify($project);
        abort_unless($project_request->project_id === $project->id, 404);
        $project_request->delete();

        return response()->json(['message' => 'Demande supprimée.']);
    }

    /** @return array<string, mixed> */
    private function requestPayload(ProjectRequest $r): array
    {
        return [
            'id' => $r->id,
            'title' => $r->title,
            'body' => $r->body,
            'status' => $r->status,
            'support_ticket_id' => $r->support_ticket_id,
            'support_ticket' => $r->supportTicket ? ['id' => $r->supportTicket->id, 'subject' => $r->supportTicket->subject] : null,
            'user' => $r->user ? ['id' => $r->user->id, 'name' => $r->user->name] : null,
            'created_at' => $r->created_at?->toIso8601String(),
            'updated_at' => $r->updated_at?->toIso8601String(),
        ];
    }
}
