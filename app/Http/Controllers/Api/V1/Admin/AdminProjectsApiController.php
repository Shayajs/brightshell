<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminProjectsApiController extends Controller
{
    public function index(): JsonResponse
    {
        $this->authorizeAdmin();

        $projects = Project::query()
            ->with('company:id,name')
            ->withCount('members')
            ->orderByDesc('id')
            ->paginate(25);

        return response()->json($projects);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorizeAdmin();

        $project = Project::create($this->validatedProjectPayload($request, null));

        return response()->json(['data' => ['slug' => $project->slug, 'id' => $project->getKey()]], 201);
    }

    public function show(Project $project): JsonResponse
    {
        $this->authorizeAdmin();

        $project->load([
            'company:id,name',
            'members' => fn ($q) => $q->orderBy('last_name')->orderBy('first_name'),
        ]);

        return response()->json([
            'data' => [
                'slug' => $project->slug,
                'name' => $project->name,
                'description' => $project->description,
                'archived_at' => $project->archived_at?->toIso8601String(),
                'company' => $project->company,
                'members' => $project->members->map(fn (User $u) => [
                    'id' => $u->id,
                    'name' => $u->name,
                    'email' => $u->email,
                    'pivot' => [
                        'can_view' => (bool) $u->pivot->can_view,
                        'can_modify' => (bool) $u->pivot->can_modify,
                        'can_annotate' => (bool) $u->pivot->can_annotate,
                        'can_download' => (bool) $u->pivot->can_download,
                    ],
                ]),
            ],
        ]);
    }

    public function update(Request $request, Project $project): JsonResponse
    {
        $this->authorizeAdmin();

        $project->update($this->validatedProjectPayload($request, $project));

        return response()->json(['message' => 'Projet mis à jour.']);
    }

    public function destroy(Project $project): JsonResponse
    {
        $this->authorizeAdmin();

        $project->delete();

        return response()->json(['message' => 'Projet mis en corbeille.']);
    }

    public function archive(Project $project): JsonResponse
    {
        $this->authorizeAdmin();

        $project->update(['archived_at' => now()]);

        return response()->json(['message' => 'Projet archivé.']);
    }

    public function unarchive(Project $project): JsonResponse
    {
        $this->authorizeAdmin();

        $project->update(['archived_at' => null]);

        return response()->json(['message' => 'Projet réactivé.']);
    }

    public function attachMember(Request $request, Project $project): JsonResponse
    {
        $this->authorizeAdmin();

        $data = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        if ($project->members()->where('users.id', $data['user_id'])->exists()) {
            return response()->json(['message' => 'Ce membre est déjà associé au projet.'], 422);
        }

        $project->members()->attach($data['user_id'], [
            'can_view' => $request->boolean('can_view', true),
            'can_modify' => $request->boolean('can_modify'),
            'can_annotate' => $request->boolean('can_annotate'),
            'can_download' => $request->boolean('can_download'),
        ]);

        return response()->json(['message' => 'Membre ajouté.']);
    }

    public function updateMember(Request $request, Project $project, User $user): JsonResponse
    {
        $this->authorizeAdmin();

        if (! $project->members()->where('users.id', $user->id)->exists()) {
            abort(404);
        }

        $project->members()->updateExistingPivot($user->id, [
            'can_view' => $request->boolean('can_view'),
            'can_modify' => $request->boolean('can_modify'),
            'can_annotate' => $request->boolean('can_annotate'),
            'can_download' => $request->boolean('can_download'),
        ]);

        return response()->json(['message' => 'Droits mis à jour.']);
    }

    public function detachMember(Project $project, User $user): JsonResponse
    {
        $this->authorizeAdmin();

        $project->members()->detach($user->id);

        return response()->json(['message' => 'Membre retiré.']);
    }

    public function formMeta(): JsonResponse
    {
        $this->authorizeAdmin();

        return response()->json([
            'data' => [
                'companies' => Company::query()->orderBy('name')->get(['id', 'name']),
                'users' => User::query()->orderBy('last_name')->orderBy('first_name')->get(['id', 'first_name', 'last_name', 'email']),
            ],
        ]);
    }

    private function authorizeAdmin(): void
    {
        $u = auth()->user();
        abort_unless($u && ($u->isAdmin() || $u->hasRole('admin')), 403);
    }

    /** @return array<string, mixed> */
    private function validatedProjectPayload(Request $request, ?Project $project): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('projects', 'slug')->ignore($project?->id)->whereNull('deleted_at'),
            ],
            'description' => ['nullable', 'string'],
            'company_id' => ['nullable', 'integer', 'exists:companies,id'],
        ]);

        if (array_key_exists('slug', $data) && ($data['slug'] === null || $data['slug'] === '')) {
            unset($data['slug']);
        }

        return $data;
    }
}
