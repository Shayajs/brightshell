<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProjectKanbanBoard extends Model
{
    protected $fillable = ['project_id', 'name'];

    /** @return BelongsTo<Project, $this> */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /** @return HasMany<ProjectKanbanColumn, $this> */
    public function columns(): HasMany
    {
        return $this->hasMany(ProjectKanbanColumn::class, 'board_id')->orderBy('sort_order')->orderBy('id');
    }
}
