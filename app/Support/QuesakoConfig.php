<?php

namespace App\Support;

use App\Models\SiteAppearance;

class QuesakoConfig
{
    /**
     * @var array<int, string>
     */
    public const ALLOWED_MODULES = ['hero', 'about', 'services', 'timeline', 'text', 'quote', 'cta', 'stats', 'cards', 'faq', 'media', 'divider'];

    /**
     * @var array<int, string>
     */
    public const HERO_ANIMATIONS = ['fade-up', 'fade-in', 'slide-right'];

    /**
     * @param array<string, mixed>|null $config
     * @return array<string, mixed>
     */
    public static function normalize(?array $config): array
    {
        $base = SiteAppearance::defaultQuesakoConfig();
        $config = is_array($config) ? $config : [];

        $tabs = self::normalizeTabs($config['tabs'] ?? [], $base['tabs']);
        $modulesByTab = self::normalizeModulesByTab($config['modulesByTab'] ?? [], $tabs);

        $settingsIn = is_array($config['settings'] ?? null) ? $config['settings'] : [];
        $defaultTabSlug = (string) ($settingsIn['defaultTabSlug'] ?? '');
        $enabledSlugs = array_values(array_map(
            static fn (array $t): string => (string) $t['slug'],
            array_filter($tabs, static fn (array $t): bool => (bool) $t['enabled'])
        ));
        if ($defaultTabSlug === '' || !in_array($defaultTabSlug, $enabledSlugs, true)) {
            $defaultTabSlug = $enabledSlugs[0] ?? (string) $tabs[0]['slug'];
        }

        return [
            'tabs' => $tabs,
            'modulesByTab' => $modulesByTab,
            'settings' => [
                'defaultTabSlug' => $defaultTabSlug,
                'seoTitle' => self::cleanText($settingsIn['seoTitle'] ?? ($base['settings']['seoTitle'] ?? 'Quesako - BrightShell'), 120),
                'seoDescription' => self::cleanText($settingsIn['seoDescription'] ?? ($base['settings']['seoDescription'] ?? ''), 320),
            ],
        ];
    }

    /**
     * @param mixed $tabsIn
     * @param array<int, array<string,mixed>> $fallback
     * @return array<int, array<string,mixed>>
     */
    private static function normalizeTabs(mixed $tabsIn, array $fallback): array
    {
        $tabsIn = is_array($tabsIn) ? $tabsIn : [];
        $tabs = [];
        $usedSlugs = [];

        foreach ($tabsIn as $i => $tab) {
            if (!is_array($tab)) {
                continue;
            }
            $slug = self::slug((string) ($tab['slug'] ?? ''));
            if ($slug === '' || in_array($slug, $usedSlugs, true)) {
                continue;
            }
            $usedSlugs[] = $slug;
            $tabs[] = [
                'id' => self::safeId((string) ($tab['id'] ?? 'tab-'.$slug)),
                'slug' => $slug,
                'label' => self::cleanText($tab['label'] ?? ucfirst($slug), 40),
                'enabled' => (bool) ($tab['enabled'] ?? true),
                'order' => (int) ($tab['order'] ?? ($i + 1)),
            ];
        }

        if ($tabs === []) {
            return $fallback;
        }

        usort($tabs, static fn (array $a, array $b): int => (($a['order'] ?? 0) <=> ($b['order'] ?? 0)));

        $hasEnabled = false;
        foreach ($tabs as $tab) {
            if ($tab['enabled']) {
                $hasEnabled = true;
                break;
            }
        }
        if (!$hasEnabled) {
            $tabs[0]['enabled'] = true;
        }

        return array_values($tabs);
    }

    /**
     * @param mixed $modulesByTabIn
     * @param array<int, array<string,mixed>> $tabs
     * @return array<string, array<int, array<string,mixed>>>
     */
    private static function normalizeModulesByTab(mixed $modulesByTabIn, array $tabs): array
    {
        $modulesByTabIn = is_array($modulesByTabIn) ? $modulesByTabIn : [];
        $result = [];

        foreach ($tabs as $tab) {
            $slug = (string) $tab['slug'];
            $modsIn = $modulesByTabIn[$slug] ?? [];
            if (!is_array($modsIn)) {
                $modsIn = [];
            }

            $mods = [];
            foreach ($modsIn as $idx => $module) {
                if (!is_array($module)) {
                    continue;
                }
                $type = (string) ($module['type'] ?? '');
                if (!in_array($type, self::ALLOWED_MODULES, true)) {
                    continue;
                }
                $mods[] = [
                    'id' => self::safeId((string) ($module['id'] ?? ($type.'-'.$idx))),
                    'type' => $type,
                    'adminLabel' => self::cleanText($module['adminLabel'] ?? '', 80),
                    'enabled' => (bool) ($module['enabled'] ?? true),
                    'order' => (int) ($module['order'] ?? ($idx + 1)),
                    'props' => self::normalizeModuleProps($type, $module['props'] ?? []),
                ];
            }

            usort($mods, static fn (array $a, array $b): int => (($a['order'] ?? 0) <=> ($b['order'] ?? 0)));
            $result[$slug] = array_values($mods);
        }

        return $result;
    }

    /**
     * @param mixed $props
     * @return array<string, mixed>
     */
    private static function normalizeModuleProps(string $type, mixed $props): array
    {
        $props = is_array($props) ? $props : [];

        if ($type === 'hero') {
            $variant = (string) ($props['animationVariant'] ?? 'fade-up');
            if (!in_array($variant, self::HERO_ANIMATIONS, true)) {
                $variant = 'fade-up';
            }

            return [
                'headline' => self::cleanText($props['headline'] ?? '', 180),
                'subheadline' => self::cleanText($props['subheadline'] ?? '', 360),
                'animationVariant' => $variant,
            ];
        }

        if ($type === 'timeline') {
            $steps = [];
            foreach (($props['steps'] ?? []) as $step) {
                if (!is_array($step)) {
                    continue;
                }
                $label = self::cleanText($step['label'] ?? '', 60);
                $text = self::cleanText($step['text'] ?? '', 220);
                if ($label === '' && $text === '') {
                    continue;
                }
                $steps[] = ['label' => $label, 'text' => $text];
            }

            return [
                'title' => self::cleanText($props['title'] ?? '', 80),
                'steps' => $steps,
            ];
        }

        if ($type === 'services') {
            $items = [];
            foreach (($props['items'] ?? []) as $item) {
                $txt = self::cleanText($item, 120);
                if ($txt !== '') {
                    $items[] = $txt;
                }
            }

            return [
                'title' => self::cleanText($props['title'] ?? '', 80),
                'items' => $items,
            ];
        }

        if ($type === 'quote') {
            return [
                'quote' => self::cleanText($props['quote'] ?? '', 420),
                'author' => self::cleanText($props['author'] ?? '', 80),
            ];
        }

        if ($type === 'cta') {
            return [
                'title' => self::cleanText($props['title'] ?? '', 120),
                'body' => self::cleanText($props['body'] ?? '', 280),
                'buttonLabel' => self::cleanText($props['buttonLabel'] ?? '', 40),
                'buttonUrl' => self::cleanText($props['buttonUrl'] ?? '', 220),
            ];
        }

        if ($type === 'stats') {
            $items = [];
            foreach (($props['items'] ?? []) as $item) {
                if (!is_array($item)) {
                    continue;
                }
                $label = self::cleanText($item['label'] ?? '', 60);
                $value = self::cleanText($item['value'] ?? '', 30);
                if ($label === '' && $value === '') {
                    continue;
                }
                $items[] = ['label' => $label, 'value' => $value];
            }
            return [
                'title' => self::cleanText($props['title'] ?? '', 80),
                'items' => $items,
            ];
        }

        if ($type === 'cards') {
            $cards = [];
            foreach (($props['cards'] ?? []) as $card) {
                if (!is_array($card)) {
                    continue;
                }
                $title = self::cleanText($card['title'] ?? '', 80);
                $text = self::cleanText($card['text'] ?? '', 180);
                if ($title === '' && $text === '') {
                    continue;
                }
                $cards[] = ['title' => $title, 'text' => $text];
            }
            return [
                'title' => self::cleanText($props['title'] ?? '', 80),
                'cards' => $cards,
            ];
        }

        if ($type === 'faq') {
            $items = [];
            foreach (($props['items'] ?? []) as $item) {
                if (!is_array($item)) {
                    continue;
                }
                $q = self::cleanText($item['question'] ?? '', 120);
                $a = self::cleanText($item['answer'] ?? '', 260);
                if ($q === '' && $a === '') {
                    continue;
                }
                $items[] = ['question' => $q, 'answer' => $a];
            }
            return [
                'title' => self::cleanText($props['title'] ?? '', 80),
                'items' => $items,
            ];
        }

        if ($type === 'media') {
            return [
                'title' => self::cleanText($props['title'] ?? '', 80),
                'imageUrl' => self::cleanText($props['imageUrl'] ?? '', 220),
                'caption' => self::cleanText($props['caption'] ?? '', 220),
            ];
        }

        if ($type === 'divider') {
            return [
                'label' => self::cleanText($props['label'] ?? '', 60),
            ];
        }

        return [
            'title' => self::cleanText($props['title'] ?? '', 80),
            'body' => self::cleanText($props['body'] ?? '', 600),
        ];
    }

    private static function slug(string $value): string
    {
        $value = strtolower(trim($value));
        $value = preg_replace('/[^a-z0-9-]+/', '-', $value) ?? '';
        $value = trim($value, '-');

        return substr($value, 0, 40);
    }

    private static function safeId(string $value): string
    {
        $value = trim($value);
        $value = preg_replace('/[^a-zA-Z0-9_-]+/', '-', $value) ?? '';
        $value = trim($value, '-');
        if ($value === '') {
            $value = 'item-'.bin2hex(random_bytes(3));
        }

        return substr($value, 0, 64);
    }

    private static function cleanText(mixed $value, int $maxLen): string
    {
        $txt = trim((string) $value);
        $txt = preg_replace('/\s+/', ' ', $txt) ?? '';

        return mb_substr($txt, 0, $maxLen);
    }
}
