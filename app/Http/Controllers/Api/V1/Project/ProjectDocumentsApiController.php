<?php

namespace App\Http\Controllers\Api\V1\Project;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Project\Concerns\AuthorizesProjectModules;
use App\Models\Project;
use App\Models\ProjectDocument;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProjectDocumentsApiController extends Controller
{
    use AuthorizesProjectModules;

    public function index(Project $project): JsonResponse
    {
        $this->authorize('view', $project);

        $documents = $project->documents()->with(['uploader:id,first_name,last_name', 'children'])->get();

        return response()->json(['data' => $documents->map(fn (ProjectDocument $d) => $this->documentTree($d))]);
    }

    public function store(Request $request, Project $project): JsonResponse
    {
        $this->authorizeProjectModify($project);

        $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'file' => ['required', 'file', 'max:25600'],
        ]);

        $file = $request->file('file');
        $title = $request->input('title') ?: $file->getClientOriginalName();
        $path = $file->store('projects/'.$project->id, 'public');

        $doc = $project->documents()->create([
            'title' => $title,
            'disk' => 'public',
            'path' => $path,
            'mime' => $file->getClientMimeType(),
            'size_bytes' => $file->getSize(),
            'uploaded_by' => $request->user()->id,
            'sort_order' => (int) $project->documents()->max('sort_order') + 1,
        ]);

        return response()->json(['data' => ['id' => $doc->id, 'title' => $doc->title]], 201);
    }

    public function destroy(Project $project, ProjectDocument $document): JsonResponse
    {
        $this->authorizeProjectModify($project);
        abort_unless($document->project_id === $project->id, 404);

        if (! $document->isFolder() && $document->path) {
            Storage::disk($document->disk)->delete($document->path);
        }

        $document->delete();

        return response()->json(['message' => 'Élément supprimé.']);
    }

    public function download(Project $project, ProjectDocument $document): StreamedResponse|JsonResponse
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

    /** @return array<string, mixed> */
    private function documentTree(ProjectDocument $d): array
    {
        return [
            'id' => $d->id,
            'title' => $d->title,
            'is_folder' => $d->isFolder(),
            'mime' => $d->mime,
            'size_bytes' => $d->size_bytes,
            'uploader' => $d->uploader ? ['id' => $d->uploader->id, 'name' => $d->uploader->name] : null,
            'children' => $d->relationLoaded('children')
                ? $d->children->map(fn (ProjectDocument $c) => $this->documentTree($c))->values()->all()
                : [],
        ];
    }
}
