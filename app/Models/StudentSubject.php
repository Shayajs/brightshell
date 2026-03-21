<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StudentSubject extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'description',
        'sort_order',
    ];

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return HasMany<StudentSubjectFolder, $this> */
    public function folders(): HasMany
    {
        return $this->hasMany(StudentSubjectFolder::class)->orderBy('sort_order')->orderBy('id');
    }

    /** Dossiers racine (pas de parent). */
    /** @return HasMany<StudentSubjectFolder, $this> */
    public function rootFolders(): HasMany
    {
        return $this->folders()->whereNull('parent_id');
    }
}
