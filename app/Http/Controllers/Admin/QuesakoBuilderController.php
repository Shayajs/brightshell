<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteAppearance;
use App\Support\QuesakoConfig;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class QuesakoBuilderController extends Controller
{
    public function edit(): View
    {
        $appearance = SiteAppearance::settings();
        $config = QuesakoConfig::normalize($appearance->quesako_config);

        return view('admin.quesako-builder.edit', [
            'config' => $config,
            'allowedModules' => QuesakoConfig::ALLOWED_MODULES,
            'heroAnimations' => QuesakoConfig::HERO_ANIMATIONS,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'quesako_config' => ['required', 'string'],
        ]);

        $decoded = json_decode($validated['quesako_config'], true);
        $normalized = QuesakoConfig::normalize(is_array($decoded) ? $decoded : null);

        $appearance = SiteAppearance::settings();
        $appearance->quesako_config = $normalized;
        $appearance->save();

        return redirect()->route('admin.quesako-builder.edit')->with('success', 'Page Quesako enregistree.');
    }

    public function preview(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'quesako_config' => ['required', 'string'],
            'tab_slug' => ['nullable', 'string', 'max:40'],
        ]);

        $decoded = json_decode($validated['quesako_config'], true);
        $config = QuesakoConfig::normalize(is_array($decoded) ? $decoded : null);

        $enabledTabs = array_values(array_filter(
            $config['tabs'],
            static fn (array $tab): bool => (bool) ($tab['enabled'] ?? false)
        ));

        $activeSlug = (string) ($validated['tab_slug'] ?: ($config['settings']['defaultTabSlug'] ?? ''));
        if ($activeSlug === '' && isset($enabledTabs[0]['slug'])) {
            $activeSlug = (string) $enabledTabs[0]['slug'];
        }

        $activeTab = null;
        foreach ($enabledTabs as $tab) {
            if ((string) $tab['slug'] === $activeSlug) {
                $activeTab = $tab;
                break;
            }
        }
        if ($activeTab === null) {
            $activeTab = $enabledTabs[0] ?? ['slug' => 'about', 'label' => 'Quesako'];
        }

        $modules = array_values(array_filter(
            $config['modulesByTab'][$activeTab['slug']] ?? [],
            static fn (array $module): bool => (bool) ($module['enabled'] ?? false)
        ));

        $html = view('pages.quesako', [
            'config' => $config,
            'tabs' => $enabledTabs,
            'activeTab' => $activeTab,
            'modules' => $modules,
            'isPreview' => true,
        ])->render();

        return response()->json([
            'ok' => true,
            'html' => $html,
            'activeTab' => $activeTab['slug'],
        ]);
    }
}
