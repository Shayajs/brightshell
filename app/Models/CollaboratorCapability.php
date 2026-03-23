<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class CollaboratorCapability extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'slug',
        'label',
        'description',
    ];

    /**
     * @return BelongsToMany<CollaboratorTeam, $this>
     */
    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(
            CollaboratorTeam::class,
            'collaborator_team_capability',
            'collaborator_capability_id',
            'collaborator_team_id'
        );
    }
}
