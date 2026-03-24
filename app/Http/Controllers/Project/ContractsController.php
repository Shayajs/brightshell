<?php

namespace App\Http\Controllers\Project;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Project\Concerns\AuthorizesProjectModules;
use App\Models\Project;
use App\Models\ProjectContract;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ContractsController extends Controller
{
    use AuthorizesProjectModules;

    public function index(Project $project): View
    {
        $contracts = $project->contracts()->with('signedDocument')->get();
        $fileDocuments = $project->allDocuments()->whereNotNull('path')->orderBy('title')->get();

        return view('portals.project.contracts.index', compact('project', 'contracts', 'fileDocuments'));
    }

    public function store(Request $request, Project $project): RedirectResponse
    {
        $this->authorizeProjectModify($project);

        $data = $this->validated($request, $project);
        $project->contracts()->create($data);

        return back()->with('success', 'Contrat ajouté.');
    }

    public function update(Request $request, Project $project, ProjectContract $contract): RedirectResponse
    {
        $this->authorizeProjectModify($project);
        $this->assertContract($project, $contract);
        $contract->update($this->validated($request, $project, $contract));

        return back()->with('success', 'Contrat mis à jour.');
    }

    public function destroy(Project $project, ProjectContract $contract): RedirectResponse
    {
        $this->authorizeProjectModify($project);
        $this->assertContract($project, $contract);
        $contract->delete();

        return back()->with('success', 'Contrat supprimé.');
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
        if ($contract->project_id !== $project->id) {
            abort(404);
        }
    }
}
