<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminOutboundApiWidget;
use App\Services\OutboundApiRequestExecutor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AdminOutboundApiWidgetsController extends Controller
{
    public function index(): View
    {
        $widgets = AdminOutboundApiWidget::query()->orderBy('sort_order')->orderBy('id')->paginate(30);

        return view('admin.outbound-api-widgets.index', compact('widgets'));
    }

    public function create(): View
    {
        $widget = new AdminOutboundApiWidget([
            'http_method' => 'GET',
            'auth_type' => AdminOutboundApiWidget::AUTH_NONE,
            'fetch_mode' => AdminOutboundApiWidget::FETCH_LIVE,
            'display_mode' => AdminOutboundApiWidget::DISPLAY_RAW_JSON,
            'timeout_seconds' => 20,
            'is_enabled' => true,
            'sort_order' => 0,
        ]);

        return view('admin.outbound-api-widgets.form', compact('widget'));
    }

    public function store(Request $request, OutboundApiRequestExecutor $executor): RedirectResponse
    {
        $data = $this->validated($request);
        $widget = AdminOutboundApiWidget::create($data);

        if ($widget->fetch_mode === AdminOutboundApiWidget::FETCH_SCHEDULED) {
            $this->refreshScheduled($widget, $executor);
        }

        return redirect()
            ->route('admin.outbound-api-widgets.index')
            ->with('success', 'Module API créé.');
    }

    public function edit(AdminOutboundApiWidget $outbound_api_widget): View
    {
        $widget = $outbound_api_widget;

        return view('admin.outbound-api-widgets.form', compact('widget'));
    }

    public function update(Request $request, AdminOutboundApiWidget $outbound_api_widget, OutboundApiRequestExecutor $executor): RedirectResponse
    {
        $data = $this->validated($request);
        if (! $request->filled('auth_secret')) {
            unset($data['auth_secret']);
        }
        $outbound_api_widget->update($data);

        if ($outbound_api_widget->fresh()->fetch_mode === AdminOutboundApiWidget::FETCH_SCHEDULED) {
            $this->refreshScheduled($outbound_api_widget->fresh(), $executor);
        }

        return redirect()
            ->route('admin.outbound-api-widgets.index')
            ->with('success', 'Module API mis à jour.');
    }

    public function destroy(AdminOutboundApiWidget $outbound_api_widget): RedirectResponse
    {
        $outbound_api_widget->delete();

        return redirect()
            ->route('admin.outbound-api-widgets.index')
            ->with('success', 'Module supprimé.');
    }

    public function test(AdminOutboundApiWidget $outbound_api_widget, OutboundApiRequestExecutor $executor): RedirectResponse
    {
        $r = $executor->execute($outbound_api_widget);

        if ($outbound_api_widget->fetch_mode === AdminOutboundApiWidget::FETCH_SCHEDULED) {
            $outbound_api_widget->update([
                'cached_status_code' => $r['status_code'],
                'cached_body' => $r['body'],
                'cached_fetched_at' => now(),
                'last_error' => $r['error'],
            ]);
        }

        $preview = Str::limit((string) ($r['body'] ?? ''), 200);
        $msg = $r['error'] ?? ('HTTP '.$r['status_code'].' — '.$preview);

        return back()->with(
            $r['ok'] ? 'success' : 'error',
            $r['ok'] ? 'Test OK : '.$msg : 'Échec : '.$msg
        );
    }

    private function refreshScheduled(AdminOutboundApiWidget $widget, OutboundApiRequestExecutor $executor): void
    {
        $r = $executor->execute($widget);
        $widget->update([
            'cached_status_code' => $r['status_code'],
            'cached_body' => $r['body'],
            'cached_fetched_at' => now(),
            'last_error' => $r['error'],
        ]);
    }

    /** @return array<string, mixed> */
    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:128'],
            'title' => ['required', 'string', 'max:255'],
            'is_enabled' => ['boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999999'],
            'http_method' => ['required', Rule::in(['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD'])],
            'url' => ['required', 'string', 'max:2048'],
            'query_params_json' => ['nullable', 'string', 'max:10000'],
            'body' => ['nullable', 'string', 'max:65535'],
            'headers_json' => ['nullable', 'string', 'max:10000'],
            'auth_type' => ['required', Rule::in([
                AdminOutboundApiWidget::AUTH_NONE,
                AdminOutboundApiWidget::AUTH_BEARER,
                AdminOutboundApiWidget::AUTH_API_KEY_HEADER,
                AdminOutboundApiWidget::AUTH_API_KEY_QUERY,
                AdminOutboundApiWidget::AUTH_BASIC,
            ])],
            'auth_secret' => ['nullable', 'string', 'max:5000'],
            'auth_header_name' => ['nullable', 'string', 'max:128'],
            'auth_query_param' => ['nullable', 'string', 'max:128'],
            'basic_username' => ['nullable', 'string', 'max:255'],
            'timeout_seconds' => ['required', 'integer', 'min:3', 'max:120'],
            'fetch_mode' => ['required', Rule::in(['live', 'scheduled'])],
            'cron_interval_minutes' => [
                Rule::requiredIf(fn () => $request->input('fetch_mode') === 'scheduled'),
                'nullable',
                'integer',
                'min:1',
                'max:10080',
            ],
            'display_mode' => ['required', Rule::in(['raw_json', 'key_paths'])],
            'display_paths_json' => ['nullable', 'string', 'max:5000'],
        ]);

        $data['query_params'] = $this->decodeJsonObject($request->string('query_params_json')->toString(), 'query_params_json');
        $data['headers'] = $this->decodeJsonObject($request->string('headers_json')->toString(), 'headers_json');
        $data['display_paths'] = $this->decodeJsonArray($request->string('display_paths_json')->toString(), 'display_paths_json');

        unset($data['query_params_json'], $data['headers_json'], $data['display_paths_json']);

        $data['is_enabled'] = $request->boolean('is_enabled');

        if ($data['fetch_mode'] === 'scheduled') {
            $data['cron_interval_minutes'] = (int) ($data['cron_interval_minutes'] ?? 15);
        } else {
            $data['cron_interval_minutes'] = null;
        }

        if ($data['auth_type'] === AdminOutboundApiWidget::AUTH_NONE) {
            $data['auth_secret'] = null;
            $data['auth_header_name'] = null;
            $data['auth_query_param'] = null;
            $data['basic_username'] = null;
        }

        if ($data['auth_type'] !== AdminOutboundApiWidget::AUTH_BASIC) {
            $data['basic_username'] = null;
        }

        return $data;
    }

    /**
     * @return array<string, mixed>
     */
    private function decodeJsonObject(string $raw, string $field): array
    {
        $raw = trim($raw);
        if ($raw === '') {
            return [];
        }
        $d = json_decode($raw, true);
        if (! is_array($d)) {
            throw ValidationException::withMessages([
                $field => ['JSON invalide (objet attendu).'],
            ]);
        }

        return $d;
    }

    /**
     * @return list<string>
     */
    private function decodeJsonArray(string $raw, string $field): array
    {
        $raw = trim($raw);
        if ($raw === '') {
            return [];
        }
        $d = json_decode($raw, true);
        if (! is_array($d)) {
            throw ValidationException::withMessages([
                $field => ['JSON invalide (tableau de chaînes attendu).'],
            ]);
        }

        $out = [];
        foreach ($d as $v) {
            if (is_string($v) && $v !== '') {
                $out[] = $v;
            }
        }

        return $out;
    }
}
