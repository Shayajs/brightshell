<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StudentCourse;
use App\Models\User;
use App\Services\StudentCourses\ScheduleOverlapChecker;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class StudentCoursesController extends Controller
{
    public function __construct(
        private readonly ScheduleOverlapChecker $scheduleOverlapChecker
    ) {}

    /** Élèves = utilisateurs avec le rôle student. */
    public function index(): View
    {
        $students = User::query()
            ->whereHas('roles', fn ($q) => $q->where('slug', 'student'))
            ->withCount(['studentCourses', 'studentSubjects'])
            ->orderBy('name')
            ->paginate(30);

        return view('admin.student-courses.index', compact('students'));
    }

    public function student(User $user): View
    {
        $this->ensureStudent($user);

        $courses = $user->studentCourses()
            ->withCount('quizzes')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $scheduleConflicts = $this->scheduleOverlapChecker->conflictMap($user->id);

        return view('admin.student-courses.student', compact('user', 'courses', 'scheduleConflicts'));
    }

    public function create(User $user): View
    {
        $this->ensureStudent($user);

        return view('admin.student-courses.form', [
            'user' => $user,
            'course' => null,
            'statuses' => StudentCourse::statuses(),
        ]);
    }

    public function store(Request $request, User $user): RedirectResponse
    {
        $this->ensureStudent($user);

        $data = $this->validated($request);
        $this->assertScheduleNoOverlap($user->id, $data, null);
        $data['user_id'] = $user->id;
        $max = (int) ($user->studentCourses()->max('sort_order') ?? 0);
        $data['sort_order'] = array_key_exists('sort_order', $data) && $data['sort_order'] !== null
            ? (int) $data['sort_order']
            : $max + 1;

        StudentCourse::create($data);

        return redirect()
            ->route('admin.student-courses.student', $user)
            ->with('success', 'Cours ajouté pour '.$user->name.'.');
    }

    public function edit(User $user, StudentCourse $studentCourse): View
    {
        $this->ensureStudent($user);
        abort_unless($studentCourse->user_id === $user->id, 404);

        return view('admin.student-courses.form', [
            'user' => $user,
            'course' => $studentCourse,
            'statuses' => StudentCourse::statuses(),
        ]);
    }

    public function update(Request $request, User $user, StudentCourse $studentCourse): RedirectResponse
    {
        $this->ensureStudent($user);
        abort_unless($studentCourse->user_id === $user->id, 404);

        $data = $this->validated($request);
        $this->assertScheduleNoOverlap($user->id, $data, $studentCourse->id);
        $studentCourse->update($data);

        return redirect()
            ->route('admin.student-courses.student', $user)
            ->with('success', 'Cours mis à jour.');
    }

    public function destroy(User $user, StudentCourse $studentCourse): RedirectResponse
    {
        $this->ensureStudent($user);
        abort_unless($studentCourse->user_id === $user->id, 404);

        $studentCourse->delete();

        return redirect()
            ->route('admin.student-courses.student', $user)
            ->with('success', 'Cours supprimé.');
    }

    private function ensureStudent(User $user): void
    {
        abort_unless($user->hasRole('student'), 404, 'Cet utilisateur n’a pas le rôle élève.');
    }

    /** @return array<string, mixed> */
    private function validated(Request $request): array
    {
        $request->merge([
            'schedule_weekday' => $request->input('schedule_weekday') === '' || $request->input('schedule_weekday') === null
                ? null
                : $request->input('schedule_weekday'),
            'schedule_time_start' => $request->filled('schedule_time_start') ? $request->input('schedule_time_start') : null,
            'schedule_time_end' => $request->filled('schedule_time_end') ? $request->input('schedule_time_end') : null,
        ]);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'string', 'in:'.implode(',', array_keys(StudentCourse::statuses()))],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:32767'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'notes' => ['nullable', 'string'],
            'schedule_weekday' => ['nullable', 'integer', 'min:1', 'max:7'],
            'schedule_time_start' => ['nullable', 'string', 'regex:/^\d{2}:\d{2}(:\d{2})?$/'],
            'schedule_time_end' => ['nullable', 'string', 'regex:/^\d{2}:\d{2}(:\d{2})?$/'],
        ]);

        $hasAny = ($data['schedule_weekday'] ?? null) !== null
            || ($data['schedule_time_start'] ?? null) !== null
            || ($data['schedule_time_end'] ?? null) !== null;

        if ($hasAny) {
            if (($data['schedule_weekday'] ?? null) === null
                || ($data['schedule_time_start'] ?? null) === null
                || ($data['schedule_time_end'] ?? null) === null) {
                throw ValidationException::withMessages([
                    'schedule_weekday' => 'Pour un créneau récurrent, renseigne le jour, l’heure de début et de fin.',
                ]);
            }
            foreach (['schedule_time_start', 'schedule_time_end'] as $tf) {
                if (isset($data[$tf]) && is_string($data[$tf]) && strlen($data[$tf]) > 5) {
                    $data[$tf] = substr($data[$tf], 0, 5);
                }
            }
            if ($data['schedule_time_start'] >= $data['schedule_time_end']) {
                throw ValidationException::withMessages([
                    'schedule_time_end' => 'L’heure de fin doit être après l’heure de début.',
                ]);
            }
        } else {
            $data['schedule_weekday'] = null;
            $data['schedule_time_start'] = null;
            $data['schedule_time_end'] = null;
        }

        return $data;
    }

    /** @param  array<string, mixed>  $data */
    private function assertScheduleNoOverlap(int $userId, array $data, ?int $excludeCourseId): void
    {
        $conflicts = $this->scheduleOverlapChecker->conflictingCourses($userId, [
            'schedule_weekday' => $data['schedule_weekday'] ?? null,
            'schedule_time_start' => $data['schedule_time_start'] ?? null,
            'schedule_time_end' => $data['schedule_time_end'] ?? null,
        ], $excludeCourseId);

        if ($conflicts->isEmpty()) {
            return;
        }

        $names = $conflicts->pluck('title')->implode(', ');
        throw ValidationException::withMessages([
            'schedule_time_start' => 'Ce créneau chevauche un autre cours de cet élève : '.$names.'. Modifie les horaires ou le jour.',
        ]);
    }
}
