<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProjectKanbanColumn extends Model
{
    protected $fillable = ['board_id', 'name', 'sort_order'];

    /** @return BelongsTo<ProjectKanbanBoard, $this> */
    public function board(): BelongsTo
    {
        return $this->belongsTo(ProjectKanbanBoard::class, 'board_id');
    }

    /** @return HasMany<ProjectKanbanCard, $this> */
    public function cards(): HasMany
    {
        return $this->hasMany(ProjectKanbanCard::class, 'column_id')->orderBy('sort_order')->orderBy('id');
    }
}
