<?php

namespace App\Http\Controllers\Project;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Project\Concerns\AuthorizesProjectModules;
use App\Models\Project;
use App\Models\ProjectDocument;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentsController extends Controller
{
    use AuthorizesProjectModules;

    public function index(Project $project): View
    {
        $documents = $project->documents()->with(['uploader', 'children'])->get();

        return view('portals.project.documents.index', compact('project', 'documents'));
    }

    public function store(Request $request, Project $project): RedirectResponse
    {
        $this->authorizeProjectModify($project);

        $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'file' => ['required', 'file', 'max:25600'],
        ]);

        $file = $request->file('file');
        $title = $request->input('title') ?: $file->getClientOriginalName();
        $path = $file->store('projects/'.$project->id, 'public');

        $project->documents()->create([
            'title' => $title,
            'disk' => 'public',
            'path' => $path,
            'mime' => $file->getClientMimeType(),
            'size_bytes' => $file->getSize(),
            'uploaded_by' => $request->user()->id,
            'sort_order' => (int) $project->documents()->max('sort_order') + 1,
        ]);

        return back()->with('success', 'Fichier ajouté.');
    }

    public function destroy(Project $project, ProjectDocument $document): RedirectResponse
    {
        $this->authorizeProjectModify($project);
        if ($document->project_id !== $project->id) {
            abort(404);
        }

        if (! $document->isFolder() && $document->path) {
            Storage::disk($document->disk)->delete($document->path);
        }

        $document->delete();

        return back()->with('success', 'Élément supprimé.');
    }

    public function download(Project $project, ProjectDocument $document): StreamedResponse
    {
        Gate::authorize('download', $project);

        if ($document->project_id !== $project->id || $document->isFolder() || ! $document->path) {
            abort(404);
        }

        if (! Storage::disk($document->disk)->exists($document->path)) {
            abort(404);
        }

        $name = Str::ascii($document->title);
        if (! str_contains($name, '.')) {
            $ext = pathinfo((string) $document->path, PATHINFO_EXTENSION);
            if ($ext) {
                $name .= '.'.$ext;
            }
        }

        return Storage::disk($document->disk)->download($document->path, $name);
    }
}
