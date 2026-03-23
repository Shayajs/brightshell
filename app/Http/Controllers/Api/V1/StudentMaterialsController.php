<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\StudentSubject;
use App\Models\StudentSubjectFile;
use App\Models\StudentSubjectFolder;
use App\Support\StudentMaterials\StudentMaterialsMarkdownRenderer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StudentMaterialsController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user->hasRole('student')) {
            return response()->json(['message' => 'Rôle élève requis pour accéder aux matières.'], 403);
        }

        $subjects = $user->studentSubjects()->withCount('folders')->orderBy('sort_order')->orderBy('id')->get();

        return response()->json([
            'data' => $subjects->map(fn (StudentSubject $s) => [
                'id' => $s->id,
                'title' => $s->title,
                'description' => $s->description,
                'sort_order' => $s->sort_order,
                'folders_count' => $s->folders_count,
            ]),
        ]);
    }

    public function show(Request $request, StudentSubject $studentSubject): JsonResponse
    {
        $user = $request->user();
        if (! $user->hasRole('student')) {
            return response()->json(['message' => 'Rôle élève requis pour accéder aux matières.'], 403);
        }

        $this->authorizeSubject($user->id, $studentSubject);

        $allFolders = $studentSubject->folders()
            ->with([
                'files' => fn ($q) => $q->where('is_hidden_from_student', false)
                    ->orderBy('sort_order')
                    ->orderBy('id'),
            ])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $folderTree = $this->buildFolderTree($allFolders);

        return response()->json([
            'data' => [
                'id' => $studentSubject->id,
                'title' => $studentSubject->title,
                'description' => $studentSubject->description,
                'sort_order' => $studentSubject->sort_order,
                'folder_tree' => $folderTree,
            ],
        ]);
    }

    public function markdown(Request $request, int $file, StudentMaterialsMarkdownRenderer $renderer): JsonResponse
    {
        $user = $request->user();
        if (! $user->hasRole('student')) {
            return response()->json(['message' => 'Rôle élève requis.'], 403);
        }

        $studentSubjectFile = StudentSubjectFile::query()
            ->with('folder.subject')
            ->findOrFail($file);

        $subject = $studentSubjectFile->folder->subject;
        $this->authorizeSubject($user->id, $subject);
        abort_unless($studentSubjectFile->folder->student_subject_id === $subject->id, 404);
        abort_unless($studentSubjectFile->isMarkdown(), 404);
        abort_if($studentSubjectFile->is_hidden_from_student, 404);
        abort_unless(! $studentSubjectFile->is_locked, 403, 'Ce contenu est encore verrouillé.');
        abort_unless(Storage::disk('local')->exists($studentSubjectFile->stored_path), 404);

        $raw = Storage::disk('local')->get($studentSubjectFile->stored_path);

        return response()->json([
            'data' => [
                'id' => $studentSubjectFile->id,
                'original_name' => $studentSubjectFile->original_name,
                'markdown' => $raw,
                'html' => $renderer->toHtml($raw),
            ],
        ]);
    }

    public function download(Request $request, int $file): StreamedResponse|JsonResponse
    {
        $user = $request->user();
        if (! $user->hasRole('student')) {
            return response()->json(['message' => 'Rôle élève requis.'], 403);
        }

        $studentSubjectFile = StudentSubjectFile::query()
            ->with('folder.subject')
            ->findOrFail($file);

        $subject = $studentSubjectFile->folder->subject;
        $this->authorizeSubject($user->id, $subject);
        abort_unless($studentSubjectFile->folder->student_subject_id === $subject->id, 404);
        abort_if($studentSubjectFile->is_hidden_from_student, 404);
        abort_unless(! $studentSubjectFile->is_locked, 403, 'Ce fichier est encore verrouillé.');
        abort_unless(Storage::disk('local')->exists($studentSubjectFile->stored_path), 404);

        return Storage::disk('local')->download($studentSubjectFile->stored_path, $studentSubjectFile->original_name);
    }

    private function authorizeSubject(int $userId, StudentSubject $studentSubject): void
    {
        abort_unless($userId === $studentSubject->user_id, 403);
    }

    /**
     * @param  Collection<int, StudentSubjectFolder>  $all
     * @return Collection<int, array{folder: array<string, mixed>, children: Collection}>
     */
    private function buildFolderTree(Collection $all, ?int $parentId = null): Collection
    {
        return $all
            ->filter(fn ($f) => $f->parent_id === $parentId)
            ->values()
            ->map(fn (StudentSubjectFolder $folder) => [
                'folder' => [
                    'id' => $folder->id,
                    'name' => $folder->name,
                    'sort_order' => $folder->sort_order,
                    'files' => $folder->files->map(fn (StudentSubjectFile $file) => [
                        'id' => $file->id,
                        'original_name' => $file->original_name,
                        'mime_type' => $file->mime_type,
                        'size' => $file->size,
                        'human_size' => $file->humanSize(),
                        'is_markdown' => $file->isMarkdown(),
                        'is_locked' => $file->is_locked,
                        'sort_order' => $file->sort_order,
                    ])->values()->all(),
                ],
                'children' => $this->buildFolderTree($all, $folder->id),
            ]);
    }
}
