<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\RealisationsRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RealisationsAdminController extends Controller
{
    public function __construct(protected RealisationsRepository $repo) {}

    public function index(): View
    {
        return view('admin.realisations.index', [
            'data' => $this->repo->allForAdmin(),
        ]);
    }

    public function create(Request $request): View
    {
        $category = $request->query('category', 'websites');

        return view('admin.realisations.form', [
            'item' => null,
            'category' => $category,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $category = $request->input('category', 'websites');
        $data = $this->validated($request, $category);
        $data = $this->handleImageUpload($request, $data, null);

        // Générer l'id si absent
        if (empty($data['id'])) {
            $data['id'] = $this->repo->generateId($data['title']);
        }

        // Ordre : dernier de la catégorie + 1
        $existing = $this->repo->allForAdmin()[$category] ?? [];
        $data['order'] = count($existing) + 1;

        $this->repo->upsert($category, $data);

        return redirect()
            ->route('admin.realisations.edit', ['category' => $category, 'id' => $data['id']])
            ->with('success', 'Réalisation créée.');
    }

    public function edit(string $category, string $id): View
    {
        $item = $this->repo->findInCategory($category, $id);

        abort_if($item === null, 404);

        return view('admin.realisations.form', compact('item', 'category'));
    }

    public function update(Request $request, string $category, string $id): RedirectResponse
    {
        $existing = $this->repo->findInCategory($category, $id);
        abort_if($existing === null, 404);

        $data = $this->validated($request, $category);
        $data['id'] = $id;
        $data['order'] = $existing['order'] ?? 99;
        $data = $this->handleImageUpload($request, $data, $existing);

        $this->repo->upsert($category, $data);

        return back()->with('success', 'Réalisation mise à jour.');
    }

    public function destroy(string $category, string $id): RedirectResponse
    {
        $item = $this->repo->findInCategory($category, $id);

        if ($item && ! empty($item['image'])) {
            $this->deleteImageFile($item['image']);
        }

        $this->repo->delete($category, $id);

        return redirect()->route('admin.realisations.index')->with('success', 'Réalisation supprimée.');
    }

    public function reorder(Request $request): RedirectResponse
    {
        $request->validate([
            'category' => ['required', 'in:websites,personal'],
            'ids' => ['required', 'array'],
        ]);

        $category = $request->input('category');
        $ids = $request->input('ids');
        $data = $this->repo->load();

        $indexed = collect($data[$category] ?? [])->keyBy('id');

        $reordered = [];
        foreach ($ids as $order => $id) {
            if ($indexed->has($id)) {
                $item = $indexed[$id];
                $item['order'] = $order + 1;
                $reordered[] = $item;
            }
        }

        $data[$category] = $reordered;
        $this->repo->save($data);

        return back()->with('success', 'Ordre sauvegardé.');
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    /** @return array<string, mixed> */
    private function validated(Request $request, string $category): array
    {
        $rules = [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'tags_raw' => ['nullable', 'string'],
            'published' => ['nullable'],
        ];

        if ($category === 'websites') {
            $rules['url'] = ['nullable', 'url', 'max:255'];
        } else {
            $rules['demo_url'] = ['nullable', 'string', 'max:255'];
            $rules['preview_id'] = ['nullable', 'string', 'max:100'];
        }

        $v = $request->validate($rules);

        $tags = array_values(array_filter(
            array_map('trim', explode(',', $v['tags_raw'] ?? ''))
        ));

        $result = [
            'title' => $v['title'],
            'description' => $v['description'] ?? '',
            'tags' => $tags,
            'published' => ! empty($v['published']),
        ];

        if ($category === 'websites') {
            $result['url'] = $v['url'] ?? '';
        } else {
            $result['demo_url'] = $v['demo_url'] ?? '';
            $result['preview_id'] = $v['preview_id'] ?? '';
        }

        return $result;
    }

    /** @param array<string, mixed> $data
     * @param  array<string, mixed>|null  $existing
     * @return array<string, mixed>
     */
    private function handleImageUpload(Request $request, array $data, ?array $existing): array
    {
        // Supprimer l'image si demandé
        if ($request->boolean('remove_image')) {
            if (! empty($existing['image'])) {
                $this->deleteImageFile($existing['image']);
            }
            $data['image'] = null;

            return $data;
        }

        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            // Supprimer l'ancienne
            if (! empty($existing['image'])) {
                $this->deleteImageFile($existing['image']);
            }

            $file = $request->file('image');
            $filename = uniqid('real_', true).'.'.$file->getClientOriginalExtension();
            $file->move(public_path('img/realisations'), $filename);
            $data['image'] = 'img/realisations/'.$filename;
        } else {
            // Conserver l'existante
            $data['image'] = $existing['image'] ?? null;
        }

        return $data;
    }

    private function deleteImageFile(string $relative): void
    {
        $full = public_path($relative);
        if (is_file($full)) {
            @unlink($full);
        }
    }
}
