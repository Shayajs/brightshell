<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectKanbanCard extends Model
{
    protected $fillable = ['column_id', 'title', 'body', 'sort_order'];

    /** @return BelongsTo<ProjectKanbanColumn, $this> */
    public function column(): BelongsTo
    {
        return $this->belongsTo(ProjectKanbanColumn::class, 'column_id');
    }
}
