<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectContract extends Model
{
    protected $fillable = [
        'project_id', 'reference', 'status', 'effective_on', 'ends_on', 'signed_document_id',
    ];

    protected function casts(): array
    {
        return [
            'effective_on' => 'date',
            'ends_on' => 'date',
        ];
    }

    /** @return BelongsTo<Project, $this> */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /** @return BelongsTo<ProjectDocument, $this> */
    public function signedDocument(): BelongsTo
    {
        return $this->belongsTo(ProjectDocument::class, 'signed_document_id');
    }
}
