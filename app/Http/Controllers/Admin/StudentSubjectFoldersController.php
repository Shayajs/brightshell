<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StudentSubject;
use App\Models\StudentSubjectFolder;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentSubjectFoldersController extends Controller
{
    public function store(Request $request, User $user, StudentSubject $studentSubject): RedirectResponse
    {
        $this->authorizeSubject($user, $studentSubject);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'parent_id' => ['nullable', 'integer', 'exists:student_subject_folders,id'],
        ]);

        if (! empty($data['parent_id'])) {
            $parent = StudentSubjectFolder::query()->findOrFail($data['parent_id']);
            abort_unless($parent->student_subject_id === $studentSubject->id, 422);
        }

        $max = (int) $studentSubject->folders()
            ->where('parent_id', $data['parent_id'] ?? null)
            ->max('sort_order');

        StudentSubjectFolder::create([
            'student_subject_id' => $studentSubject->id,
            'parent_id' => $data['parent_id'] ?? null,
            'name' => $data['name'],
            'sort_order' => $max + 1,
        ]);

        return back()->with('success', 'Dossier créé.');
    }

    public function edit(User $user, StudentSubject $studentSubject, int $folder): View
    {
        $this->authorizeSubject($user, $studentSubject);
        $folder = StudentSubjectFolder::query()->whereKey($folder)->where('student_subject_id', $studentSubject->id)->firstOrFail();

        $options = $studentSubject->folders()
            ->orderBy('sort_order')
            ->get()
            ->filter(fn (StudentSubjectFolder $f) => $f->id !== $folder->id && ! $this->isDescendantOf($folder, $f));

        $subject = $studentSubject;

        return view('admin.student-subjects.folder-form', compact('user', 'subject', 'folder', 'options'));
    }

    public function update(Request $request, User $user, StudentSubject $studentSubject, int $folder): RedirectResponse
    {
        $this->authorizeSubject($user, $studentSubject);
        $folder = StudentSubjectFolder::query()->whereKey($folder)->where('student_subject_id', $studentSubject->id)->firstOrFail();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'parent_id' => ['nullable', 'integer', 'exists:student_subject_folders,id'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:32767'],
        ]);

        $newParentId = $data['parent_id'] ?? null;
        if ($newParentId !== null) {
            abort_if((int) $newParentId === $folder->id, 422);
            $parent = StudentSubjectFolder::query()->findOrFail($newParentId);
            abort_unless($parent->student_subject_id === $studentSubject->id, 422);
            abort_if($this->isDescendantOf($folder, $parent), 422);
        }

        $folder->update([
            'name' => $data['name'],
            'parent_id' => $newParentId,
            'sort_order' => $data['sort_order'],
        ]);

        return redirect()
            ->route('admin.student-subjects.show', [$user, $studentSubject])
            ->with('success', 'Dossier mis à jour.');
    }

    public function destroy(User $user, StudentSubject $studentSubject, int $folder): RedirectResponse
    {
        $this->authorizeSubject($user, $studentSubject);
        $folder = StudentSubjectFolder::query()->whereKey($folder)->where('student_subject_id', $studentSubject->id)->firstOrFail();

        $folder->delete();

        return back()->with('success', 'Dossier supprimé (sous-dossiers et fichiers inclus).');
    }

    private function authorizeSubject(User $user, StudentSubject $subject): void
    {
        abort_unless($user->hasRole('student'), 404);
        abort_unless($subject->user_id === $user->id, 404);
    }

    /** Le dossier $node est-il un descendant de $ancestor (ou égal) ? */
    private function isDescendantOf(StudentSubjectFolder $ancestor, StudentSubjectFolder $node): bool
    {
        if ($node->id === $ancestor->id) {
            return true;
        }
        $p = $node->parent_id;
        $guard = 0;
        while ($p !== null && $guard < 200) {
            if ((int) $p === $ancestor->id) {
                return true;
            }
            $p = StudentSubjectFolder::query()->whereKey($p)->value('parent_id');
            $guard++;
        }

        return false;
    }
}
