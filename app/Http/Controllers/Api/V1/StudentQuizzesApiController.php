<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\StudentCourse;
use App\Models\StudentCourseQuiz;
use App\Models\StudentQuizAttempt;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StudentQuizzesApiController extends Controller
{
    public function show(Request $request, StudentCourse $studentCourse, StudentCourseQuiz $quiz): JsonResponse
    {
        $this->authorizeStudentCourse($request, $studentCourse);
        $this->authorizeQuiz($studentCourse, $quiz);

        abort_unless($quiz->is_published, 404);

        $quiz->load(['questions.answers']);

        $lastAttempt = StudentQuizAttempt::query()
            ->where('user_id', $request->user()->id)
            ->where('student_course_quiz_id', $quiz->id)
            ->latest('completed_at')
            ->first();

        return response()->json([
            'data' => [
                'id' => $quiz->id,
                'title' => $quiz->title,
                'instructions' => $quiz->instructions,
                'questions' => $quiz->questions->map(fn ($q) => [
                    'id' => $q->id,
                    'body' => $q->body,
                    'answers' => $q->answers->shuffle()->values()->map(fn ($a) => [
                        'id' => $a->id,
                        'body' => $a->body,
                    ]),
                ]),
                'last_attempt' => $lastAttempt ? [
                    'score_percent' => $lastAttempt->score_percent,
                    'completed_at' => $lastAttempt->completed_at?->toIso8601String(),
                ] : null,
            ],
        ]);
    }

    public function submit(Request $request, StudentCourse $studentCourse, StudentCourseQuiz $quiz): JsonResponse
    {
        $this->authorizeStudentCourse($request, $studentCourse);
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
            return response()->json(['message' => 'Ce quiz n’a pas encore de questions.'], 422);
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
            'user_id' => $request->user()->id,
            'student_course_quiz_id' => $quiz->id,
            'score_percent' => $percent,
            'responses' => $responses,
            'completed_at' => now(),
        ]);

        return response()->json([
            'data' => [
                'score_percent' => $percent,
                'correct' => $correct,
                'total' => $total,
            ],
        ]);
    }

    private function authorizeStudentCourse(Request $request, StudentCourse $studentCourse): void
    {
        abort_unless($request->user()->hasRole('student'), 403);
        abort_unless($request->user()->id === $studentCourse->user_id, 403);
    }

    private function authorizeQuiz(StudentCourse $studentCourse, StudentCourseQuiz $quiz): void
    {
        abort_unless($quiz->student_course_id === $studentCourse->id, 404);
    }
}
