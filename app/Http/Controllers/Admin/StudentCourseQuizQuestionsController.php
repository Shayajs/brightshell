<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StudentCourse;
use App\Models\StudentCourseQuiz;
use App\Models\StudentCourseQuizAnswer;
use App\Models\StudentCourseQuizQuestion;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class StudentCourseQuizQuestionsController extends Controller
{
    public function store(Request $request, User $user, StudentCourse $studentCourse, StudentCourseQuiz $quiz): RedirectResponse
    {
        $this->authorizeContext($user, $studentCourse, $quiz);

        $data = $request->validate([
            'body' => ['required', 'string', 'max:5000'],
            'answers' => ['required', 'array', 'min:2', 'max:8'],
            'answers.*' => ['nullable', 'string', 'max:500'],
            'correct_index' => ['required', 'integer', 'min:0', 'max:7'],
        ]);

        $answers = array_values(array_filter(
            array_map('trim', $data['answers']),
            fn (string $s) => $s !== ''
        ));

        if (count($answers) < 2) {
            throw ValidationException::withMessages([
                'answers' => 'Au moins deux réponses non vides sont requises.',
            ]);
        }

        $correctIndex = (int) $data['correct_index'];
        if (! isset($answers[$correctIndex])) {
            throw ValidationException::withMessages([
                'correct_index' => 'L’index de la bonne réponse ne correspond pas à une réponse remplie.',
            ]);
        }

        $maxQ = (int) ($quiz->questions()->max('sort_order') ?? 0);
        $question = StudentCourseQuizQuestion::query()->create([
            'student_course_quiz_id' => $quiz->id,
            'body' => $data['body'],
            'sort_order' => $maxQ + 1,
        ]);

        foreach ($answers as $i => $text) {
            StudentCourseQuizAnswer::query()->create([
                'student_course_quiz_question_id' => $question->id,
                'body' => $text,
                'is_correct' => $i === $correctIndex,
                'sort_order' => $i,
            ]);
        }

        return redirect()
            ->route('admin.student-course-quizzes.edit', [$user, $studentCourse, $quiz])
            ->with('success', 'Question ajoutée.');
    }

    public function destroy(User $user, StudentCourse $studentCourse, StudentCourseQuiz $quiz, StudentCourseQuizQuestion $question): RedirectResponse
    {
        $this->authorizeContext($user, $studentCourse, $quiz);
        abort_unless($question->student_course_quiz_id === $quiz->id, 404);

        $question->delete();

        return redirect()
            ->route('admin.student-course-quizzes.edit', [$user, $studentCourse, $quiz])
            ->with('success', 'Question supprimée.');
    }

    private function authorizeContext(User $user, StudentCourse $studentCourse, StudentCourseQuiz $quiz): void
    {
        abort_unless($user->hasRole('student'), 404);
        abort_unless($studentCourse->user_id === $user->id, 404);
        abort_unless($quiz->student_course_id === $studentCourse->id, 404);
    }
}
