<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentCourseQuizAnswer extends Model
{
    protected $fillable = [
        'student_course_quiz_question_id',
        'body',
        'is_correct',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_correct' => 'boolean',
        ];
    }

    /** @return BelongsTo<StudentCourseQuizQuestion, $this> */
    public function question(): BelongsTo
    {
        return $this->belongsTo(StudentCourseQuizQuestion::class, 'student_course_quiz_question_id');
    }
}
