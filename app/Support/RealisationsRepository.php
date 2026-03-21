<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class RealisationsRepository
{
    private string $path;

    public function __construct()
    {
        $this->path = resource_path('data/realisations.json');
    }

    public function all(): array
    {
        return Cache::remember('realisations_all', 3600, fn () => $this->load());
    }

    public function websites(): array
    {
        return collect($this->all()['websites'] ?? [])
            ->filter(fn ($r) => $r['published'] ?? true)
            ->sortBy('order')
            ->values()
            ->all();
    }

    public function personal(): array
    {
        return collect($this->all()['personal'] ?? [])
            ->filter(fn ($r) => $r['published'] ?? true)
            ->sortBy('order')
            ->values()
            ->all();
    }

    /** Toutes, published ou non, pour l'admin. */
    public function allForAdmin(): array
    {
        $data = $this->load();

        return [
            'websites' => collect($data['websites'] ?? [])->sortBy('order')->values()->all(),
            'personal' => collect($data['personal'] ?? [])->sortBy('order')->values()->all(),
        ];
    }

    public function findInCategory(string $category, string $id): ?array
    {
        $data = $this->load();

        foreach ($data[$category] ?? [] as $item) {
            if (($item['id'] ?? '') === $id) {
                return $item;
            }
        }

        return null;
    }

    public function save(array $data): void
    {
        File::put($this->path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        Cache::forget('realisations_all');
    }

    public function upsert(string $category, array $item): void
    {
        $data = $this->load();
        $items = $data[$category] ?? [];
        $found = false;

        foreach ($items as &$existing) {
            if ($existing['id'] === $item['id']) {
                $existing = $item;
                $found = true;
                break;
            }
        }

        if (! $found) {
            $items[] = $item;
        }

        // Réordre
        usort($items, fn ($a, $b) => ($a['order'] ?? 99) <=> ($b['order'] ?? 99));

        $data[$category] = $items;
        $this->save($data);
    }

    public function delete(string $category, string $id): void
    {
        $data = $this->load();
        $data[$category] = array_values(
            array_filter($data[$category] ?? [], fn ($r) => ($r['id'] ?? '') !== $id)
        );
        $this->save($data);
    }

    public function generateId(string $title): string
    {
        return Str::slug($title);
    }

    public function load(): array
    {
        if (! File::exists($this->path)) {
            return ['websites' => [], 'personal' => []];
        }

        $raw = File::get($this->path);
        $data = json_decode($raw, true);

        return is_array($data) ? $data : ['websites' => [], 'personal' => []];
    }
}
