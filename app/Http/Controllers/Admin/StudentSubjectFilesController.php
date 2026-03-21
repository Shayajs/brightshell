<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StudentSubject;
use App\Models\StudentSubjectFile;
use App\Models\StudentSubjectFolder;
use App\Models\User;
use App\Support\StudentMaterials\StudentMaterialsMarkdownRenderer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StudentSubjectFilesController extends Controller
{
    /** @var list<string> */
    private const UPLOAD_EXTENSIONS = [
        'pdf', 'png', 'jpg', 'jpeg', 'gif', 'webp', 'svg', 'bmp', 'ico',
        'md', 'markdown', 'txt', 'csv', 'json', 'xml', 'yaml', 'yml',
        'doc', 'docx', 'odt', 'xls', 'xlsx', 'ods', 'ppt', 'pptx', 'odp',
        'zip', 'rar', '7z', 'gz', 'tar',
        'mp3', 'wav', 'ogg', 'flac', 'm4a',
        'mp4', 'webm', 'mov', 'mkv',
        'js', 'ts', 'tsx', 'jsx', 'php', 'py', 'css', 'html', 'vue',
    ];

    public function store(Request $request, User $user, StudentSubject $studentSubject): RedirectResponse
    {
        $this->authorizeSubject($user, $studentSubject);

        $folder = StudentSubjectFolder::query()
            ->whereKey((int) $request->input('student_subject_folder_id'))
            ->where('student_subject_id', $studentSubject->id)
            ->firstOrFail();

        $request->validate([
            'files' => ['required', 'array', 'min:1'],
            'files.*' => ['file', 'max:51200'],
        ]);

        $max = (int) $folder->files()->max('sort_order');
        $n = 0;

        foreach ($request->file('files', []) as $upload) {
            if (! $upload->isValid()) {
                continue;
            }
            $ext = strtolower($upload->getClientOriginalExtension());
            if (! in_array($ext, self::UPLOAD_EXTENSIONS, true)) {
                continue;
            }
            $dir = 'student-materials/'.$folder->id;
            $safeName = Str::uuid()->toString().'_'.preg_replace('/[^a-zA-Z0-9._-]/u', '_', $upload->getClientOriginalName());
            $path = $upload->storeAs($dir, $safeName, 'local');
            if ($path === false) {
                continue;
            }

            $folder->files()->create([
                'original_name' => $upload->getClientOriginalName(),
                'stored_path' => $path,
                'mime_type' => $upload->getClientMimeType(),
                'size' => $upload->getSize(),
                'sort_order' => ++$max,
            ]);
            $n++;
        }

        $folderId = (int) $request->input('student_subject_folder_id');

        return redirect()
            ->route('admin.student-subjects.show', [$user, $studentSubject, 'dossier' => $folderId])
            ->with($n > 0 ? 'success' : 'error', $n > 0 ? $n.' fichier(s) importé(s).' : 'Aucun fichier valide.');
    }

    public function storeMarkdown(Request $request, User $user, StudentSubject $studentSubject): RedirectResponse
    {
        $this->authorizeSubject($user, $studentSubject);

        $folder = StudentSubjectFolder::query()
            ->whereKey((int) $request->input('student_subject_folder_id'))
            ->where('student_subject_id', $studentSubject->id)
            ->firstOrFail();

        $data = $request->validate([
            'markdown_title' => ['required', 'string', 'max:255'],
            'markdown_body' => ['required', 'string', 'max:2000000'],
        ]);

        $base = trim($data['markdown_title']);
        if ($base === '') {
            return back()->withInput()->with('error', 'Titre de la note invalide.');
        }
        if (! Str::endsWith(strtolower($base), '.md')) {
            $base .= '.md';
        }

        $dir = 'student-materials/'.$folder->id;
        $safeName = Str::uuid()->toString().'_'.preg_replace('/[^a-zA-Z0-9._-]/u', '_', $base);
        $path = $dir.'/'.$safeName;
        Storage::disk('local')->put($path, $data['markdown_body']);

        $max = (int) $folder->files()->max('sort_order');
            $folder->files()->create([
                'original_name' => $base,
                'stored_path' => $path,
                'mime_type' => 'text/markdown; charset=UTF-8',
                'size' => strlen($data['markdown_body']),
                'sort_order' => $max + 1,
                'is_locked' => false,
                'is_hidden_from_student' => false,
            ]);

        return redirect()
            ->route('admin.student-subjects.show', [$user, $studentSubject, 'dossier' => $folder->id])
            ->with('success', 'Note Markdown enregistrée.');
    }

    public function createMarkdown(User $user, StudentSubject $studentSubject, StudentSubjectFolder $folder): View
    {
        $this->authorizeSubject($user, $studentSubject);
        abort_unless($folder->student_subject_id === $studentSubject->id, 404);

        return view('admin.student-subjects.markdown-editor', [
            'user' => $user,
            'subject' => $studentSubject,
            'folder' => $folder,
            'file' => null,
            'initialTitle' => old('markdown_title', ''),
            'initialBody' => old('markdown_body', "# Nouvelle note\n\n"),
            'isCreate' => true,
        ]);
    }

    public function editMarkdown(User $user, StudentSubject $studentSubject, StudentSubjectFile $file): View
    {
        $this->authorizeSubject($user, $studentSubject);
        $this->assertFileInSubject($studentSubject, $file);
        abort_unless($file->isMarkdown(), 404);
        abort_unless(Storage::disk('local')->exists($file->stored_path), 404);

        return view('admin.student-subjects.markdown-editor', [
            'user' => $user,
            'subject' => $studentSubject,
            'folder' => $file->folder,
            'file' => $file,
            'initialTitle' => old('markdown_title', $file->original_name),
            'initialBody' => old('markdown_body', Storage::disk('local')->get($file->stored_path)),
            'isCreate' => false,
        ]);
    }

    public function updateMarkdown(Request $request, User $user, StudentSubject $studentSubject, StudentSubjectFile $file): RedirectResponse
    {
        $this->authorizeSubject($user, $studentSubject);
        $this->assertFileInSubject($studentSubject, $file);
        abort_unless($file->isMarkdown(), 404);

        $data = $request->validate([
            'markdown_title' => ['required', 'string', 'max:255'],
            'markdown_body' => ['required', 'string', 'max:2000000'],
        ]);

        $title = trim($data['markdown_title']);
        if ($title === '') {
            return back()->withInput()->with('error', 'Nom de fichier invalide.');
        }
        if (! Str::endsWith(strtolower($title), '.md')) {
            $title .= '.md';
        }

        Storage::disk('local')->put($file->stored_path, $data['markdown_body']);
        $file->update([
            'original_name' => $title,
            'size' => strlen($data['markdown_body']),
            'mime_type' => 'text/markdown; charset=UTF-8',
        ]);

        return redirect()
            ->route('admin.student-subjects.show', [$user, $studentSubject, 'dossier' => $file->student_subject_folder_id])
            ->with('success', 'Markdown enregistré.');
    }

    public function previewMarkdownJson(Request $request, StudentMaterialsMarkdownRenderer $renderer): JsonResponse
    {
        $data = $request->validate([
            'markdown' => ['required', 'string', 'max:2000000'],
        ]);

        $html = $renderer->toHtml($data['markdown']);

        return response()->json(['html' => $html]);
    }

    public function updateFileAccess(Request $request, User $user, StudentSubject $studentSubject, StudentSubjectFile $file): RedirectResponse
    {
        $this->authorizeSubject($user, $studentSubject);
        $this->assertFileInSubject($studentSubject, $file);

        $file->update([
            'is_locked' => $request->boolean('is_locked'),
            'is_hidden_from_student' => $request->boolean('is_hidden_from_student'),
        ]);

        return redirect()
            ->route('admin.student-subjects.show', [$user, $studentSubject, 'dossier' => $file->student_subject_folder_id])
            ->with('success', 'Accès élève mis à jour pour « '.$file->original_name.' ».');
    }

    public function previewMarkdown(int $file): View
    {
        $studentSubjectFile = StudentSubjectFile::query()
            ->with('folder.subject')
            ->findOrFail($file);

        abort_unless($studentSubjectFile->isMarkdown(), 404);
        abort_unless(Storage::disk('local')->exists($studentSubjectFile->stored_path), 404);

        $raw = Storage::disk('local')->get($studentSubjectFile->stored_path);
        $html = app(StudentMaterialsMarkdownRenderer::class)->toHtml($raw);

        return view('portals.courses.matieres.markdown', [
            'subject' => $studentSubjectFile->folder->subject,
            'file' => $studentSubjectFile,
            'html' => $html,
            'isAdminPreview' => true,
        ]);
    }

    public function download(int $file): StreamedResponse
    {
        $studentSubjectFile = StudentSubjectFile::query()->findOrFail($file);
        abort_unless(Storage::disk('local')->exists($studentSubjectFile->stored_path), 404);

        return Storage::disk('local')->download($studentSubjectFile->stored_path, $studentSubjectFile->original_name);
    }

    public function destroy(User $user, StudentSubject $studentSubject, int $file): RedirectResponse
    {
        $this->authorizeSubject($user, $studentSubject);
        $studentSubjectFile = StudentSubjectFile::query()
            ->whereKey($file)
            ->whereHas('folder', fn ($q) => $q->where('student_subject_id', $studentSubject->id))
            ->firstOrFail();

        $folderId = $studentSubjectFile->student_subject_folder_id;
        $studentSubjectFile->delete();

        return redirect()
            ->route('admin.student-subjects.show', [$user, $studentSubject, 'dossier' => $folderId])
            ->with('success', 'Fichier supprimé.');
    }

    private function assertFileInSubject(StudentSubject $studentSubject, StudentSubjectFile $file): void
    {
        abort_unless($file->folder->student_subject_id === $studentSubject->id, 404);
    }

    private function authorizeSubject(User $user, StudentSubject $subject): void
    {
        abort_unless($user->hasRole('student'), 404);
        abort_unless($subject->user_id === $user->id, 404);
    }
}
