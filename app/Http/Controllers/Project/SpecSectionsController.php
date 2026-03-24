<?php

namespace App\Http\Controllers\Project;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Project\Concerns\AuthorizesProjectModules;
use App\Models\Project;
use App\Models\ProjectSpecSection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class SpecSectionsController extends Controller
{
    use AuthorizesProjectModules;

    public function index(Project $project): View
    {
        $query = $project->specSections()->orderBy('sort_order')->orderBy('id');

        if (! Gate::allows('update', $project)) {
            $query->where('status', ProjectSpecSection::STATUS_PUBLISHED);
        }

        $sections = $query->get();

        return view('portals.project.specs.index', compact('project', 'sections'));
    }

    public function store(Request $request, Project $project): RedirectResponse
    {
        $this->authorizeProjectModify($project);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'body' => ['nullable', 'string'],
            'status' => ['required', 'string', 'in:'.ProjectSpecSection::STATUS_DRAFT.','.ProjectSpecSection::STATUS_PUBLISHED],
        ]);
        $data['sort_order'] = (int) $project->specSections()->max('sort_order') + 1;
        $project->specSections()->create($data);

        return back()->with('success', 'Section ajoutée.');
    }

    public function update(Request $request, Project $project, ProjectSpecSection $section): RedirectResponse
    {
        $this->authorizeProjectModify($project);
        $this->assertSection($project, $section);

        $section->update($request->validate([
            'title' => ['required', 'string', 'max:255'],
            'body' => ['nullable', 'string'],
            'status' => ['required', 'string', 'in:'.ProjectSpecSection::STATUS_DRAFT.','.ProjectSpecSection::STATUS_PUBLISHED],
        ]));

        return back()->with('success', 'Section mise à jour.');
    }

    public function destroy(Project $project, ProjectSpecSection $section): RedirectResponse
    {
        $this->authorizeProjectModify($project);
        $this->assertSection($project, $section);
        $section->delete();

        return back()->with('success', 'Section supprimée.');
    }

    private function assertSection(Project $project, ProjectSpecSection $section): void
    {
        if ($section->project_id !== $project->id) {
            abort(404);
        }
    }
}
