<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StudentCourse;
use App\Models\StudentCourseQuiz;
use App\Models\User;
use App\Services\StudentCourses\QuizJsonImporter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use JsonException;

class StudentCourseQuizzesController extends Controller
{
    public function __construct(
        private readonly QuizJsonImporter $quizJsonImporter
    ) {}

    public function index(User $user, StudentCourse $studentCourse): View
    {
        $this->authorizeCourse($user, $studentCourse);

        $quizzes = $studentCourse->quizzes()->withCount('questions')->orderBy('sort_order')->orderBy('id')->get();

        return view('admin.student-courses.quizzes.index', compact('user', 'studentCourse', 'quizzes'));
    }

    public function create(User $user, StudentCourse $studentCourse): View
    {
        $this->authorizeCourse($user, $studentCourse);

        return view('admin.student-courses.quizzes.form', [
            'user' => $user,
            'studentCourse' => $studentCourse,
            'quiz' => null,
        ]);
    }

    public function store(Request $request, User $user, StudentCourse $studentCourse): RedirectResponse
    {
        $this->authorizeCourse($user, $studentCourse);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'instructions' => ['nullable', 'string'],
            'is_published' => ['sometimes', 'boolean'],
        ]);
        $data['is_published'] = $request->boolean('is_published', true);
        $max = (int) ($studentCourse->quizzes()->max('sort_order') ?? 0);
        $data['student_course_id'] = $studentCourse->id;
        $data['sort_order'] = $max + 1;

        $quiz = StudentCourseQuiz::create($data);

        if ($request->filled('import_json')) {
            try {
                $payload = json_decode((string) $request->input('import_json'), true, 512, JSON_THROW_ON_ERROR);
                if (! is_array($payload)) {
                    throw new JsonException('Racine JSON invalide');
                }
                $this->quizJsonImporter->replaceQuizContent($quiz, $payload, $quiz->title);
            } catch (\Throwable $e) {
                $quiz->delete();

                return redirect()
                    ->back()
                    ->withInput()
                    ->withErrors(['import_json' => 'JSON invalide : '.$e->getMessage()]);
            }
        }

        return redirect()
            ->route('admin.student-course-quizzes.edit', [$user, $studentCourse, $quiz])
            ->with('success', 'Quiz créé.');
    }

    public function edit(User $user, StudentCourse $studentCourse, StudentCourseQuiz $quiz): View
    {
        $this->authorizeCourse($user, $studentCourse);
        abort_unless($quiz->student_course_id === $studentCourse->id, 404);

        $quiz->load(['questions.answers']);

        return view('admin.student-courses.quizzes.edit', [
            'user' => $user,
            'studentCourse' => $studentCourse,
            'quiz' => $quiz,
        ]);
    }

    public function update(Request $request, User $user, StudentCourse $studentCourse, StudentCourseQuiz $quiz): RedirectResponse
    {
        $this->authorizeCourse($user, $studentCourse);
        abort_unless($quiz->student_course_id === $studentCourse->id, 404);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'instructions' => ['nullable', 'string'],
            'is_published' => ['sometimes', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:32767'],
        ]);
        $data['is_published'] = $request->boolean('is_published', true);
        $quiz->update($data);

        return redirect()
            ->route('admin.student-course-quizzes.edit', [$user, $studentCourse, $quiz])
            ->with('success', 'Quiz mis à jour.');
    }

    public function importJson(Request $request, User $user, StudentCourse $studentCourse, StudentCourseQuiz $quiz): RedirectResponse
    {
        $this->authorizeCourse($user, $studentCourse);
        abort_unless($quiz->student_course_id === $studentCourse->id, 404);

        $request->validate([
            'import_json' => ['required', 'string'],
        ]);

        try {
            $payload = json_decode((string) $request->input('import_json'), true, 512, JSON_THROW_ON_ERROR);
            if (! is_array($payload)) {
                throw new JsonException('Racine JSON invalide');
            }
            $this->quizJsonImporter->replaceQuizContent($quiz, $payload, $quiz->title);
        } catch (\Throwable $e) {
            return redirect()
                ->back()
                ->withErrors(['import_json' => 'JSON invalide : '.$e->getMessage()]);
        }

        return redirect()
            ->route('admin.student-course-quizzes.edit', [$user, $studentCourse, $quiz])
            ->with('success', 'Questions importées (remplacement).');
    }

    public function destroy(User $user, StudentCourse $studentCourse, StudentCourseQuiz $quiz): RedirectResponse
    {
        $this->authorizeCourse($user, $studentCourse);
        abort_unless($quiz->student_course_id === $studentCourse->id, 404);

        $quiz->delete();

        return redirect()
            ->route('admin.student-course-quizzes.index', [$user, $studentCourse])
            ->with('success', 'Quiz supprimé.');
    }

    private function authorizeCourse(User $user, StudentCourse $studentCourse): void
    {
        abort_unless($user->hasRole('student'), 404, 'Cet utilisateur n’a pas le rôle élève.');
        abort_unless($studentCourse->user_id === $user->id, 404);
    }
}
