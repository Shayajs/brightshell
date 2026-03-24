<?php

namespace App\Http\Controllers\Project;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Project\Concerns\AuthorizesProjectModules;
use App\Models\Project;
use App\Models\ProjectRequest;
use App\Models\SupportTicket;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RequestsController extends Controller
{
    use AuthorizesProjectModules;

    public function index(Project $project): View
    {
        $requests = $project->requests()->with(['user', 'supportTicket'])->paginate(25);
        $supportTickets = Gate::allows('update', $project)
            ? SupportTicket::query()->orderByDesc('id')->limit(150)->get()
            : collect();

        return view('portals.project.requests.index', compact('project', 'requests', 'supportTickets'));
    }

    public function store(Request $request, Project $project): RedirectResponse
    {
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

        $project->requests()->create([
            'user_id' => $request->user()->id,
            'title' => $data['title'],
            'body' => $data['body'] ?? null,
            'status' => ProjectRequest::STATUS_OPEN,
            'support_ticket_id' => $data['support_ticket_id'] ?? null,
        ]);

        return back()->with('success', 'Demande créée.');
    }

    public function update(Request $request, Project $project, ProjectRequest $project_request): RedirectResponse
    {
        $this->authorizeProjectModify($project);
        if ($project_request->project_id !== $project->id) {
            abort(404);
        }

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

        return back()->with('success', 'Demande mise à jour.');
    }

    public function destroy(Project $project, ProjectRequest $project_request): RedirectResponse
    {
        $this->authorizeProjectModify($project);
        if ($project_request->project_id !== $project->id) {
            abort(404);
        }
        $project_request->delete();

        return back()->with('success', 'Demande supprimée.');
    }
}
