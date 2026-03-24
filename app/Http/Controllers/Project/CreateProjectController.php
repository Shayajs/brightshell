<?php

namespace App\Http\Controllers\Project;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CreateProjectController extends Controller
{
    public function create(): View
    {
        return view('portals.project.create', [
            'companies' => Company::query()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $project = Project::create($this->validatedProjectPayload($request, null));

        return redirect()
            ->route('admin.projects.edit', $project)
            ->with('success', 'Projet créé. Ajoutez les membres et leurs droits ci-dessous.');
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
