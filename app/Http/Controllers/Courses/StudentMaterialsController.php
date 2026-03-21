<?php

namespace App\Http\Controllers\Courses;

use App\Http\Controllers\Controller;
use App\Models\StudentSubject;
use App\Models\StudentSubjectFile;
use App\Models\StudentSubjectFolder;
use App\Support\StudentMaterials\StudentMaterialsMarkdownRenderer;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StudentMaterialsController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        $subjects = $user->studentSubjects()->withCount('folders')->orderBy('sort_order')->orderBy('id')->get();

        return view('portals.courses.matieres.index', compact('subjects'));
    }

    public function show(StudentSubject $studentSubject): View
    {
        $this->authorizeSubject($studentSubject);

        $allFolders = $studentSubject->folders()->with('files')->orderBy('sort_order')->orderBy('id')->get();
        $folderTree = $this->buildFolderTree($allFolders);

        return view('portals.courses.matieres.show', [
            'subject' => $studentSubject,
            'folderTree' => $folderTree,
        ]);
    }

    public function readMarkdown(int $file, StudentMaterialsMarkdownRenderer $renderer): View
    {
        $studentSubjectFile = StudentSubjectFile::query()
            ->with('folder.subject')
            ->findOrFail($file);

        $subject = $studentSubjectFile->folder->subject;
        $this->authorizeSubject($subject);
        abort_unless($studentSubjectFile->folder->student_subject_id === $subject->id, 404);
        abort_unless($studentSubjectFile->isMarkdown(), 404);
        abort_unless(Storage::disk('local')->exists($studentSubjectFile->stored_path), 404);

        $raw = Storage::disk('local')->get($studentSubjectFile->stored_path);
        $html = $renderer->toHtml($raw);

        return view('portals.courses.matieres.markdown', [
            'subject' => $subject,
            'file' => $studentSubjectFile,
            'html' => $html,
            'isAdminPreview' => false,
        ]);
    }

    public function download(int $file): StreamedResponse
    {
        $studentSubjectFile = StudentSubjectFile::query()
            ->with('folder.subject')
            ->findOrFail($file);

        $subject = $studentSubjectFile->folder->subject;
        $this->authorizeSubject($subject);
        abort_unless($studentSubjectFile->folder->student_subject_id === $subject->id, 404);
        abort_unless(Storage::disk('local')->exists($studentSubjectFile->stored_path), 404);

        return Storage::disk('local')->download($studentSubjectFile->stored_path, $studentSubjectFile->original_name);
    }

    private function authorizeSubject(StudentSubject $studentSubject): void
    {
        abort_unless(auth()->id() === $studentSubject->user_id, 403);
    }

    /**
     * @param  Collection<int, StudentSubjectFolder>  $all
     * @return Collection<int, array{folder: StudentSubjectFolder, children: Collection}>
     */
    private function buildFolderTree(Collection $all, ?int $parentId = null): Collection
    {
        return $all
            ->filter(fn ($f) => $f->parent_id === $parentId)
            ->values()
            ->map(fn ($folder) => [
                'folder' => $folder,
                'children' => $this->buildFolderTree($all, $folder->id),
            ]);
    }
}
