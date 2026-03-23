<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MailTemplate;
use App\Services\Mail\Template\MailTemplateRegistry;
use App\Services\Mail\Template\MailTemplateRenderer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MailTemplatesController extends Controller
{
    public function __construct(
        private readonly MailTemplateRegistry $registry,
        private readonly MailTemplateRenderer $renderer,
    ) {
    }

    public function index(): View
    {
        return view('admin.mail-templates.index', [
            'templates' => collect($this->registry->all())->sortBy('category')->values(),
        ]);
    }

    public function edit(string $key): View
    {
        $template = $this->registry->resolve($key);
        $preview = $this->renderer->render($key, []);

        return view('admin.mail-templates.edit', [
            'template' => $template,
            'preview' => $preview,
        ]);
    }

    public function update(Request $request, string $key): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:100'],
            'subject_template' => ['required', 'string', 'max:255'],
            'layout_json' => ['required', 'array'],
            'content_json' => ['required', 'array'],
            'variables_json' => ['nullable', 'array'],
            'is_active' => ['nullable', 'boolean'],
            'publish' => ['nullable', 'boolean'],
        ]);

        $record = MailTemplate::query()->firstOrNew(['key' => $key]);
        $record->fill([
            'name' => $validated['name'],
            'category' => $validated['category'],
            'subject_template' => $validated['subject_template'],
            'layout_json' => $validated['layout_json'],
            'content_json' => $validated['content_json'],
            'variables_json' => $validated['variables_json'] ?? ['variables' => []],
            'is_active' => (bool) ($validated['is_active'] ?? true),
            'updated_by' => $request->user()?->id,
        ]);
        $record->version = $record->exists ? $record->version + 1 : 1;
        if (($validated['publish'] ?? false) === true) {
            $record->published_at = now();
        }
        $record->save();

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'version' => $record->version,
                'updated_at' => optional($record->updated_at)->toIso8601String(),
            ]);
        }

        return redirect()->route('admin.mail-templates.edit', $key)->with('success', 'Template sauvegarde.');
    }

    public function preview(Request $request, string $key): JsonResponse
    {
        $vars = $request->input('vars', []);
        if (! is_array($vars)) {
            $vars = [];
        }

        $rendered = $this->renderer->render($key, $vars);

        return response()->json([
            'subject' => $rendered->subject,
            'html' => $rendered->html,
            'text' => $rendered->text,
        ]);
    }
}
