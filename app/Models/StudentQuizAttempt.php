<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentQuizAttempt extends Model
{
    protected $fillable = [
        'user_id',
        'student_course_quiz_id',
        'score_percent',
        'responses',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'responses' => 'array',
            'completed_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<StudentCourseQuiz, $this> */
    public function quiz(): BelongsTo
    {
        return $this->belongsTo(StudentCourseQuiz::class, 'student_course_quiz_id');
    }
}
