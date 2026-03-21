<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StudentSubjectFolder extends Model
{
    protected $fillable = [
        'student_subject_id',
        'parent_id',
        'name',
        'sort_order',
    ];

    /** @return BelongsTo<StudentSubject, $this> */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(StudentSubject::class, 'student_subject_id');
    }

    /** @return BelongsTo<StudentSubjectFolder, $this> */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(StudentSubjectFolder::class, 'parent_id');
    }

    /** @return HasMany<StudentSubjectFolder, $this> */
    public function children(): HasMany
    {
        return $this->hasMany(StudentSubjectFolder::class, 'parent_id')->orderBy('sort_order')->orderBy('id');
    }

    /** @return HasMany<StudentSubjectFile, $this> */
    public function files(): HasMany
    {
        return $this->hasMany(StudentSubjectFile::class, 'student_subject_folder_id')->orderBy('sort_order')->orderBy('id');
    }
}
