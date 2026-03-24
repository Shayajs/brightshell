<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProjectDocument extends Model
{
    protected $fillable = [
        'project_id', 'parent_id', 'title', 'disk', 'path', 'mime', 'size_bytes', 'uploaded_by', 'sort_order',
    ];

    /** @return BelongsTo<Project, $this> */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /** @return BelongsTo<ProjectDocument, $this> */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(ProjectDocument::class, 'parent_id');
    }

    /** @return HasMany<ProjectDocument, $this> */
    public function children(): HasMany
    {
        return $this->hasMany(ProjectDocument::class, 'parent_id')->orderBy('sort_order')->orderBy('id');
    }

    /** @return BelongsTo<User, $this> */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function isFolder(): bool
    {
        return $this->path === null || $this->path === '';
    }

    public function downloadUrl(): ?string
    {
        if ($this->isFolder() || $this->path === null) {
            return null;
        }

        return route('portals.project.documents.download', ['project' => $this->project, 'document' => $this]);
    }
}
