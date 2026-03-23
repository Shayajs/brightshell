<?php

namespace App\Support;

use App\Models\DocNode;
use App\Models\User;
use Illuminate\Support\Collection;

final class DocAccessResolver
{
    /**
     * Rôles effectifs pour la lecture (héritage si aucun rôle explicite sur le nœud).
     *
     * @return Collection<int, int> role ids
     */
    public function effectiveRoleIds(DocNode $node): Collection
    {
        $node->loadMissing('parent');

        $explicit = $node->explicitReaderRoles()->pluck('id');
        if ($explicit->isNotEmpty()) {
            return $explicit;
        }

        if ($node->parent_id === null) {
            return collect();
        }

        $parent = $node->parent;
        if ($parent === null) {
            return collect();
        }

        return $this->effectiveRoleIds($parent);
    }

    public function can(User $user, DocNode $node): bool
    {
        if ($user->isAdmin() || $user->hasRole('admin')) {
            return true;
        }

        $effective = $this->effectiveRoleIds($node);
        if ($effective->isEmpty()) {
            return false;
        }

        $userRoleIds = $user->roles()->pluck('roles.id');

        return $userRoleIds->intersect($effective)->isNotEmpty();
    }

    /**
     * Filtre une collection de nœuds racine / enfants selon l’accès.
     *
     * @param  Collection<int, DocNode>  $nodes
     * @return Collection<int, DocNode>
     */
    public function filterAccessible(User $user, Collection $nodes): Collection
    {
        return $nodes->filter(fn (DocNode $n) => $this->can($user, $n))->values();
    }

    /**
     * Arborescence des pages accessibles (pour la navigation latérale).
     *
     * @return list<array{node: DocNode, children: list<array{node: DocNode, children: mixed}>}>
     */
    public function accessibleNavTree(User $user): array
    {
        $roots = DocNode::query()
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $roots = $this->filterAccessible($user, $roots);

        return $roots
            ->map(fn (DocNode $root) => $this->buildNavBranch($user, $root))
            ->values()
            ->all();
    }

    /**
     * @return array{node: DocNode, children: list<array{node: DocNode, children: mixed}>}
     */
    private function buildNavBranch(User $user, DocNode $node): array
    {
        $branch = [
            'node' => $node,
            'children' => [],
        ];

        $children = $node->children()->orderBy('sort_order')->orderBy('id')->get();
        $children = $this->filterAccessible($user, $children);

        $branch['children'] = $children
            ->map(fn (DocNode $child) => $this->buildNavBranch($user, $child))
            ->values()
            ->all();

        return $branch;
    }
}
