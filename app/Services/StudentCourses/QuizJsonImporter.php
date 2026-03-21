<?php

namespace App\Services\StudentCourses;

use App\Models\StudentCourseQuiz;
use App\Models\StudentCourseQuizAnswer;
use App\Models\StudentCourseQuizQuestion;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Format JSON attendu (générable par une IA) :
 * {
 *   "title": "Quiz optionnel (sinon titre param)",
 *   "questions": [
 *     {
 *       "question": "Intitulé",
 *       "answers": [
 *         {"text": "Réponse A", "correct": false},
 *         {"text": "Réponse B", "correct": true}
 *       ]
 *     }
 *   ]
 * }
 */
final class QuizJsonImporter
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function replaceQuizContent(StudentCourseQuiz $quiz, array $payload, ?string $fallbackTitle = null): void
    {
        if (! isset($payload['questions']) || ! is_array($payload['questions'])) {
            throw new InvalidArgumentException('Le JSON doit contenir une clé "questions" (tableau).');
        }

        $questions = $payload['questions'];
        if ($questions === []) {
            throw new InvalidArgumentException('Au moins une question est requise.');
        }

        $title = isset($payload['title']) && is_string($payload['title']) && trim($payload['title']) !== ''
            ? trim($payload['title'])
            : $fallbackTitle;
        if ($title !== null && $title !== '') {
            $quiz->title = $title;
            $quiz->save();
        }

        DB::transaction(function () use ($quiz, $questions): void {
            $quiz->questions()->delete();

            $qOrder = 0;
            foreach ($questions as $block) {
                if (! is_array($block)) {
                    throw new InvalidArgumentException('Chaque question doit être un objet.');
                }
                $body = $block['question'] ?? $block['body'] ?? null;
                if (! is_string($body) || trim($body) === '') {
                    throw new InvalidArgumentException('Chaque question doit avoir "question" (texte).');
                }
                $answers = $block['answers'] ?? null;
                if (! is_array($answers) || $answers === []) {
                    throw new InvalidArgumentException('Chaque question doit avoir un tableau "answers".');
                }

                $correctCount = 0;
                $normalized = [];
                foreach ($answers as $a) {
                    if (! is_array($a)) {
                        throw new InvalidArgumentException('Réponse invalide.');
                    }
                    $text = $a['text'] ?? $a['body'] ?? null;
                    if (! is_string($text) || trim($text) === '') {
                        throw new InvalidArgumentException('Chaque réponse doit avoir "text".');
                    }
                    $correct = ! empty($a['correct']);
                    if ($correct) {
                        $correctCount++;
                    }
                    $normalized[] = ['body' => trim($text), 'correct' => $correct];
                }
                if ($correctCount !== 1) {
                    throw new InvalidArgumentException('Chaque question doit avoir exactement une réponse avec "correct": true.');
                }

                $question = StudentCourseQuizQuestion::query()->create([
                    'student_course_quiz_id' => $quiz->id,
                    'body' => trim($body),
                    'sort_order' => $qOrder++,
                ]);

                $aOrder = 0;
                foreach ($normalized as $row) {
                    StudentCourseQuizAnswer::query()->create([
                        'student_course_quiz_question_id' => $question->id,
                        'body' => $row['body'],
                        'is_correct' => $row['correct'],
                        'sort_order' => $aOrder++,
                    ]);
                }
            }
        });
    }
}
