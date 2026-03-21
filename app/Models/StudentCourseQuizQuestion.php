<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StudentCourseQuizQuestion extends Model
{
    protected $fillable = [
        'student_course_quiz_id',
        'body',
        'sort_order',
    ];

    /** @return BelongsTo<StudentCourseQuiz, $this> */
    public function quiz(): BelongsTo
    {
        return $this->belongsTo(StudentCourseQuiz::class, 'student_course_quiz_id');
    }

    /** @return HasMany<StudentCourseQuizAnswer, $this> */
    public function answers(): HasMany
    {
        return $this->hasMany(StudentCourseQuizAnswer::class)->orderBy('sort_order')->orderBy('id');
    }
}
