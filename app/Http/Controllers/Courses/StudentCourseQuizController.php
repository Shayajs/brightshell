<?php

namespace App\Http\Controllers\Courses;

use App\Http\Controllers\Controller;
use App\Models\StudentCourse;
use App\Models\StudentCourseQuiz;
use App\Models\StudentQuizAttempt;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StudentCourseQuizController extends Controller
{
    public function show(StudentCourse $studentCourse, StudentCourseQuiz $quiz): View
    {
        $this->authorizeStudentCourse($studentCourse);
        $this->authorizeQuiz($studentCourse, $quiz);

        abort_unless($quiz->is_published, 404);

        $quiz->load(['questions.answers']);

        $lastAttempt = StudentQuizAttempt::query()
            ->where('user_id', auth()->id())
            ->where('student_course_quiz_id', $quiz->id)
            ->latest('completed_at')
            ->first();

        return view('portals.courses.quiz.show', compact('studentCourse', 'quiz', 'lastAttempt'));
    }

    public function submit(Request $request, StudentCourse $studentCourse, StudentCourseQuiz $quiz): RedirectResponse
    {
        $this->authorizeStudentCourse($studentCourse);
        $this->authorizeQuiz($studentCourse, $quiz);

        abort_unless($quiz->is_published, 404);

        $quiz->load(['questions.answers']);

        $rules = [];
        foreach ($quiz->questions as $q) {
            $rules['answers.'.$q->id] = [
                'required',
                'integer',
                Rule::exists('student_course_quiz_answers', 'id')->where('student_course_quiz_question_id', $q->id),
            ];
        }

        if ($rules === []) {
            return redirect()->back()->withErrors(['answers' => 'Ce quiz n’a pas encore de questions.']);
        }

        $validated = $request->validate($rules);

        $correct = 0;
        $total = $quiz->questions->count();
        $responses = [];

        foreach ($quiz->questions as $q) {
            $pickedId = (int) $validated['answers'][$q->id];
            $picked = $q->answers->firstWhere('id', $pickedId);
            $isOk = $picked && $picked->is_correct;
            if ($isOk) {
                $correct++;
            }
            $responses[$q->id] = [
                'answer_id' => $pickedId,
                'correct' => (bool) $isOk,
            ];
        }

        $percent = $total > 0 ? (int) round(100 * $correct / $total) : 0;

        StudentQuizAttempt::query()->create([
            'user_id' => auth()->id(),
            'student_course_quiz_id' => $quiz->id,
            'score_percent' => $percent,
            'responses' => $responses,
            'completed_at' => now(),
        ]);

        return redirect()
            ->route('portals.courses.quiz.show', [$studentCourse, $quiz])
            ->with('quiz_result', $percent);
    }

    private function authorizeStudentCourse(StudentCourse $studentCourse): void
    {
        abort_unless(auth()->id() === $studentCourse->user_id, 403);
    }

    private function authorizeQuiz(StudentCourse $studentCourse, StudentCourseQuiz $quiz): void
    {
        abort_unless($quiz->student_course_id === $studentCourse->id, 404);
    }
}
