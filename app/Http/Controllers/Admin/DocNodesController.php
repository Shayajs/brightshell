<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DocNode;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DocNodesController extends Controller
{
    public function index(): View
    {
        $nodes = DocNode::query()
            ->with('parent')
            ->orderBy('parent_id')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return view('admin.doc-nodes.index', [
            'nodes' => $nodes,
        ]);
    }

    public function create(Request $request): View
    {
        $parentId = $request->query('parent');
        $parent = null;
        if ($parentId !== null && $parentId !== '') {
            $parent = DocNode::query()->findOrFail((int) $parentId);
        }

        $roles = Role::orderByDesc('priority')->orderBy('id')->get();

        return view('admin.doc-nodes.form', [
            'node' => new DocNode([
                'parent_id' => $parent?->id,
                'is_folder' => true,
                'sort_order' => 0,
            ]),
            'parent' => $parent,
            'roles' => $roles,
            'mode' => 'create',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->prepareParentId($request);
        $data = $this->validateNode($request);
        $roleIds = $data['reader_role_ids'] ?? [];
        unset($data['reader_role_ids']);

        $node = DocNode::create($data);

        $node->explicitReaderRoles()->sync($roleIds);

        return redirect()
            ->route('admin.doc-nodes.edit', $node)
            ->with('success', 'Page créée.');
    }

    public function edit(DocNode $docNode): View
    {
        $docNode->load('explicitReaderRoles');
        $roles = Role::orderByDesc('priority')->orderBy('id')->get();
        $parent = $docNode->parent;

        return view('admin.doc-nodes.form', [
            'node' => $docNode,
            'parent' => $parent,
            'roles' => $roles,
            'mode' => 'edit',
        ]);
    }

    public function update(Request $request, DocNode $docNode): RedirectResponse
    {
        $this->prepareParentId($request);
        $data = $this->validateNode($request, $docNode);
        $roleIds = $data['reader_role_ids'] ?? [];
        unset($data['reader_role_ids']);

        $docNode->update($data);

        $docNode->explicitReaderRoles()->sync($roleIds);

        return redirect()
            ->route('admin.doc-nodes.edit', $docNode)
            ->with('success', 'Enregistré.');
    }

    public function destroy(DocNode $docNode): RedirectResponse
    {
        $docNode->delete();

        return redirect()
            ->route('admin.doc-nodes.index')
            ->with('success', 'Élément supprimé.');
    }

    private function prepareParentId(Request $request): void
    {
        $request->merge([
            'parent_id' => $request->filled('parent_id') ? (int) $request->input('parent_id') : null,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function validateNode(Request $request, ?DocNode $existing = null): array
    {
        $parentId = $request->input('parent_id');

        return $request->validate([
            'parent_id' => ['nullable', 'integer', 'exists:doc_nodes,id'],
            'slug' => [
                'required',
                'string',
                'max:128',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('doc_nodes', 'slug')
                    ->where(function ($query) use ($parentId) {
                        if ($parentId === null || $parentId === '') {
                            return $query->whereNull('parent_id');
                        }

                        return $query->where('parent_id', (int) $parentId);
                    })
                    ->ignore($existing?->id),
            ],
            'title' => ['required', 'string', 'max:255'],
            'is_folder' => ['required', 'boolean'],
            'body' => ['nullable', 'string', 'max:2000000'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:999999'],
            'reader_role_ids' => ['nullable', 'array'],
            'reader_role_ids.*' => ['integer', 'exists:roles,id'],
        ]);
    }
}
