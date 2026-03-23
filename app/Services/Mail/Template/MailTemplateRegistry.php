<?php

namespace App\Services\Mail\Template;

use App\Models\MailTemplate;
use Illuminate\Support\Facades\File;
use RuntimeException;

class MailTemplateRegistry
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function all(): array
    {
        $defaults = $this->defaultTemplates();
        $dbTemplates = MailTemplate::query()->get()->keyBy('key');
        $templates = [];

        foreach ($defaults as $key => $data) {
            /** @var MailTemplate|null $db */
            $db = $dbTemplates->get($key);

            $templates[] = $this->mergeTemplateData($data, $db);
        }

        foreach ($dbTemplates as $db) {
            if (! isset($defaults[$db->key])) {
                $templates[] = $this->mergeTemplateData([], $db);
            }
        }

        return $templates;
    }

    /**
     * @return array<string, mixed>
     */
    public function resolve(string $key): array
    {
        $defaults = $this->defaultTemplates();

        if (! isset($defaults[$key])) {
            throw new RuntimeException("Template mail introuvable: {$key}");
        }

        $db = MailTemplate::query()->where('key', $key)->first();

        return $this->mergeTemplateData($defaults[$key], $db);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function defaultTemplates(): array
    {
        $dir = resource_path('mail-templates');
        $files = File::exists($dir) ? File::files($dir) : [];
        $data = [];

        foreach ($files as $file) {
            if ($file->getFilename() === 'base.layout.json') {
                continue;
            }

            $decoded = json_decode(File::get($file->getRealPath()), true);
            if (! is_array($decoded) || ! isset($decoded['key'])) {
                continue;
            }

            $data[(string) $decoded['key']] = $decoded;
        }

        return $data;
    }

    /**
     * @return array<string, mixed>
     */
    public function baseLayout(): array
    {
        $path = resource_path('mail-templates/base.layout.json');
        if (! File::exists($path)) {
            return [];
        }

        $decoded = json_decode(File::get($path), true);

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * @param array<string, mixed> $default
     * @return array<string, mixed>
     */
    private function mergeTemplateData(array $default, ?MailTemplate $db): array
    {
        if (! $db) {
            return [
                'id' => null,
                'key' => (string) ($default['key'] ?? ''),
                'name' => (string) ($default['name'] ?? ''),
                'category' => (string) ($default['category'] ?? 'custom'),
                'subject_template' => (string) ($default['subject_template'] ?? ''),
                'layout_json' => $this->baseLayout(),
                'content_json' => $default,
                'variables_json' => ['variables' => $default['variables'] ?? []],
                'is_active' => true,
                'version' => 1,
                'updated_by' => null,
                'published_at' => null,
                'source' => 'default',
            ];
        }

        return [
            'id' => $db->id,
            'key' => $db->key,
            'name' => $db->name ?: (string) ($default['name'] ?? $db->key),
            'category' => $db->category ?: (string) ($default['category'] ?? 'custom'),
            'subject_template' => $db->subject_template ?: (string) ($default['subject_template'] ?? ''),
            'layout_json' => $db->layout_json ?: $this->baseLayout(),
            'content_json' => $db->content_json ?: $default,
            'variables_json' => $db->variables_json ?: ['variables' => $default['variables'] ?? []],
            'is_active' => $db->is_active,
            'version' => $db->version,
            'updated_by' => $db->updated_by,
            'published_at' => $db->published_at,
            'source' => 'database',
        ];
    }
}
