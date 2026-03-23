<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MailTemplate extends Model
{
    protected $fillable = [
        'key',
        'name',
        'category',
        'subject_template',
        'layout_json',
        'content_json',
        'variables_json',
        'is_active',
        'version',
        'updated_by',
        'published_at',
    ];

    protected $casts = [
        'layout_json' => 'array',
        'content_json' => 'array',
        'variables_json' => 'array',
        'is_active' => 'boolean',
        'published_at' => 'datetime',
    ];

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
