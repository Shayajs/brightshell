<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StudentSubject;
use App\Models\StudentSubjectFolder;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class StudentSubjectsController extends Controller
{
    public function index(): View
    {
        $students = User::query()
            ->whereHas('roles', fn ($q) => $q->where('slug', 'student'))
            ->withCount('studentSubjects')
            ->orderBy('name')
            ->paginate(30);

        return view('admin.student-subjects.index', compact('students'));
    }

    public function student(User $user): View
    {
        $this->ensureStudent($user);

        $subjects = $user->studentSubjects()->withCount('folders')->orderBy('sort_order')->orderBy('id')->get();

        return view('admin.student-subjects.student', compact('user', 'subjects'));
    }

    public function show(User $user, StudentSubject $studentSubject): View
    {
        $this->ensureStudent($user);
        abort_unless($studentSubject->user_id === $user->id, 404);

        $allFolders = $studentSubject->folders()->with('files')->orderBy('sort_order')->orderBy('id')->get();
        $folderTree = $this->buildFolderTree($allFolders);

        return view('admin.student-subjects.show', [
            'user' => $user,
            'subject' => $studentSubject,
            'folderTree' => $folderTree,
        ]);
    }

    public function create(User $user): View
    {
        $this->ensureStudent($user);

        return view('admin.student-subjects.form', ['user' => $user, 'subject' => null]);
    }

    public function store(Request $request, User $user): RedirectResponse
    {
        $this->ensureStudent($user);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);
        $data['user_id'] = $user->id;
        $data['sort_order'] = (int) ($user->studentSubjects()->max('sort_order') ?? 0) + 1;

        $subject = StudentSubject::create($data);

        return redirect()
            ->route('admin.student-subjects.show', [$user, $subject])
            ->with('success', 'Matière créée. Ajoutez des dossiers et des fichiers.');
    }

    public function edit(User $user, StudentSubject $studentSubject): View
    {
        $this->ensureStudent($user);
        abort_unless($studentSubject->user_id === $user->id, 404);

        return view('admin.student-subjects.form', ['user' => $user, 'subject' => $studentSubject]);
    }

    public function update(Request $request, User $user, StudentSubject $studentSubject): RedirectResponse
    {
        $this->ensureStudent($user);
        abort_unless($studentSubject->user_id === $user->id, 404);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:32767'],
        ]);
        $studentSubject->update($data);

        return redirect()
            ->route('admin.student-subjects.show', [$user, $studentSubject])
            ->with('success', 'Matière mise à jour.');
    }

    public function destroy(User $user, StudentSubject $studentSubject): RedirectResponse
    {
        $this->ensureStudent($user);
        abort_unless($studentSubject->user_id === $user->id, 404);

        $studentSubject->delete();

        return redirect()
            ->route('admin.student-subjects.student', $user)
            ->with('success', 'Matière et tout son contenu supprimés.');
    }

    /**
     * @param  Collection<int, StudentSubjectFolder>  $all
     * @return Collection<int, array{folder: StudentSubjectFolder, children: Collection}>
     */
    private function buildFolderTree(Collection $all, ?int $parentId = null): Collection
    {
        return $all
            ->filter(fn (StudentSubjectFolder $f) => $f->parent_id === $parentId)
            ->values()
            ->map(fn (StudentSubjectFolder $folder) => [
                'folder' => $folder,
                'children' => $this->buildFolderTree($all, $folder->id),
            ]);
    }

    private function ensureStudent(User $user): void
    {
        abort_unless($user->hasRole('student'), 404, 'Cet utilisateur n’a pas le rôle élève.');
    }
}
