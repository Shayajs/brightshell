<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StudentCourseQuiz extends Model
{
    protected $fillable = [
        'student_course_id',
        'title',
        'instructions',
        'sort_order',
        'is_published',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
        ];
    }

    /** @return BelongsTo<StudentCourse, $this> */
    public function course(): BelongsTo
    {
        return $this->belongsTo(StudentCourse::class, 'student_course_id');
    }

    /** @return HasMany<StudentCourseQuizQuestion, $this> */
    public function questions(): HasMany
    {
        return $this->hasMany(StudentCourseQuizQuestion::class)->orderBy('sort_order')->orderBy('id');
    }

    /** @return HasMany<StudentQuizAttempt, $this> */
    public function attempts(): HasMany
    {
        return $this->hasMany(StudentQuizAttempt::class);
    }
}
