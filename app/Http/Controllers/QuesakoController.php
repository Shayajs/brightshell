<?php

namespace App\Http\Controllers;

use App\Models\SiteAppearance;
use App\Support\QuesakoConfig;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class QuesakoController extends Controller
{
    public function index(): View
    {
        $appearance = SiteAppearance::settings();
        $config = QuesakoConfig::normalize($appearance->quesako_config);

        return $this->renderSinglePage($config);
    }

    public function show(string $tabSlug): View|RedirectResponse
    {
        $appearance = SiteAppearance::settings();
        $config = QuesakoConfig::normalize($appearance->quesako_config);

        $enabledTabs = array_values(array_filter(
            $config['tabs'],
            static fn (array $tab): bool => (bool) ($tab['enabled'] ?? false)
        ));
        $tabBySlug = [];
        foreach ($enabledTabs as $tab) {
            $tabBySlug[(string) $tab['slug']] = $tab;
        }

        if (!isset($tabBySlug[$tabSlug])) {
            $fallback = (string) ($config['settings']['defaultTabSlug'] ?? ($enabledTabs[0]['slug'] ?? 'about'));

            return redirect()->route('quesako.tab', ['tabSlug' => $fallback]);
        }

        $activeTab = $tabBySlug[$tabSlug];
        $modules = array_values(array_filter(
            $config['modulesByTab'][$tabSlug] ?? [],
            static fn (array $module): bool => (bool) ($module['enabled'] ?? false)
        ));

        return $this->renderSinglePage($config, $enabledTabs, $activeTab, $modules);
    }

    /**
     * @param array<string, mixed> $config
     * @param array<int, array<string, mixed>>|null $tabs
     * @param array<string, mixed>|null $activeTab
     * @param array<int, array<string, mixed>>|null $activeModules
     */
    private function renderSinglePage(array $config, ?array $tabs = null, ?array $activeTab = null, ?array $activeModules = null): View
    {
        $tabs = $tabs ?? array_values(array_filter(
            $config['tabs'] ?? [],
            static fn (array $tab): bool => (bool) ($tab['enabled'] ?? false)
        ));

        $activeTab = $activeTab ?? collect($tabs)->firstWhere('slug', $config['settings']['defaultTabSlug'] ?? '') ?? ($tabs[0] ?? null);
        $activeSlug = (string) ($activeTab['slug'] ?? '');

        $activeModules = $activeModules ?? array_values(array_filter(
            $config['modulesByTab'][$activeSlug] ?? [],
            static fn (array $module): bool => (bool) ($module['enabled'] ?? false)
        ));

        return view('pages.quesako', [
            'config' => $config,
            'tabs' => $tabs,
            'activeTab' => $activeTab,
            'modules' => $activeModules,
            'isPreview' => false,
        ]);
    }
}
