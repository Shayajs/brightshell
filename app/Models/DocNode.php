<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DocNode extends Model
{
    protected $fillable = [
        'parent_id',
        'slug',
        'title',
        'is_folder',
        'body',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_folder' => 'boolean',
        ];
    }

    /** @return BelongsTo<DocNode, $this> */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(DocNode::class, 'parent_id');
    }

    /** @return HasMany<DocNode, $this> */
    public function children(): HasMany
    {
        return $this->hasMany(DocNode::class, 'parent_id')->orderBy('sort_order')->orderBy('id');
    }

    /**
     * Rôles explicitement assignés à ce nœud (vide = hériter du parent).
     *
     * @return BelongsToMany<Role, $this>
     */
    public function explicitReaderRoles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'doc_node_role')->withTimestamps();
    }

    public static function findByPathSegments(array $segments): ?self
    {
        $segments = array_values(array_filter($segments, fn ($s) => $s !== '' && $s !== null));
        if ($segments === []) {
            return null;
        }

        $parentId = null;
        $node = null;

        foreach ($segments as $slug) {
            $q = static::query()->where('slug', $slug);
            if ($parentId === null) {
                $q->whereNull('parent_id');
            } else {
                $q->where('parent_id', $parentId);
            }
            $node = $q->first();
            if ($node === null) {
                return null;
            }
            $parentId = $node->id;
        }

        return $node;
    }

    /** Segments slug du chemin racine → ce nœud (pour URLs). */
    public function pathSegments(): array
    {
        $segments = [];
        $current = $this;
        while ($current !== null) {
            array_unshift($segments, $current->slug);
            $current = $current->parent;
        }

        return $segments;
    }

    public function pathString(): string
    {
        return implode('/', $this->pathSegments());
    }
}
