<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteAppearance;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SiteAppearanceController extends Controller
{
    private const PATH_RULE = ['nullable', 'string', 'max:512', 'regex:/^(?!.*\.\.)[a-zA-Z0-9_.\/-]+$/'];

    public function edit(): View
    {
        $appearance = SiteAppearance::settings();
        $configMail = config('brightshell.mail_layout', []);
        $partial = is_array($appearance->mail_layout_partial) ? $appearance->mail_layout_partial : [];

        $theme = array_merge($configMail['theme'] ?? [], $partial['theme'] ?? []);
        $brand = array_merge($configMail['brand'] ?? [], $partial['brand'] ?? []);
        $footer = array_merge($configMail['footer'] ?? [], $partial['footer'] ?? []);

        return view('admin.site-appearance.edit', [
            'appearance' => $appearance,
            'theme' => $theme,
            'brandMail' => $brand,
            'footerMail' => $footer,
            'defaultFavicon' => config('brightshell.brand.favicon'),
            'defaultSiteLogo' => config('brightshell.brand.site_logo'),
            'mailUseCustomTheme' => isset($partial['theme']) && is_array($partial['theme']) && $partial['theme'] !== [],
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'favicon_path' => self::PATH_RULE,
            'site_logo_path' => self::PATH_RULE,
            'mail_brand_name' => ['nullable', 'string', 'max:120'],
            'mail_brand_tagline' => ['nullable', 'string', 'max:200'],
            'mail_footer_signature' => ['nullable', 'string', 'max:255'],
            'mail_footer_legal' => ['nullable', 'string', 'max:500'],
            'mail_primary_color' => ['nullable', 'string', 'max:32'],
            'mail_background_color' => ['nullable', 'string', 'max:32'],
            'mail_card_color' => ['nullable', 'string', 'max:32'],
            'mail_text_color' => ['nullable', 'string', 'max:32'],
            'mail_muted_text_color' => ['nullable', 'string', 'max:32'],
            'mail_button_text_color' => ['nullable', 'string', 'max:32'],
            'mail_divider_color' => ['nullable', 'string', 'max:32'],
            'mail_use_custom_theme' => ['nullable', 'boolean'],
        ]);

        $appearance = SiteAppearance::settings();

        $appearance->favicon_path = $this->nullableString($validated['favicon_path'] ?? null);
        $appearance->site_logo_path = $this->nullableString($validated['site_logo_path'] ?? null);

        $partial = is_array($appearance->mail_layout_partial) ? $appearance->mail_layout_partial : [];
        $configMail = config('brightshell.mail_layout', []);

        if ($request->boolean('mail_use_custom_theme')) {
            $configTheme = is_array($configMail['theme'] ?? null) ? $configMail['theme'] : [];
            $themeInputs = [
                'mail_primary_color' => 'primaryColor',
                'mail_background_color' => 'backgroundColor',
                'mail_card_color' => 'cardColor',
                'mail_text_color' => 'textColor',
                'mail_muted_text_color' => 'mutedTextColor',
                'mail_button_text_color' => 'buttonTextColor',
                'mail_divider_color' => 'dividerColor',
            ];
            $newTheme = [];
            foreach ($themeInputs as $input => $jsonKey) {
                $in = $this->nullableString($validated[$input] ?? null);
                $cfg = (string) ($configTheme[$jsonKey] ?? '');
                if ($in !== null && strtolower($in) !== strtolower($cfg)) {
                    $newTheme[$jsonKey] = $in;
                }
            }
            if ($newTheme === []) {
                unset($partial['theme']);
            } else {
                $partial['theme'] = $newTheme;
            }
        } else {
            unset($partial['theme']);
        }

        $configBrand = is_array($configMail['brand'] ?? null) ? $configMail['brand'] : [];
        $newBrand = [];
        $nameIn = $this->nullableString($validated['mail_brand_name'] ?? null);
        if ($nameIn !== null && $nameIn !== (string) ($configBrand['name'] ?? '')) {
            $newBrand['name'] = $nameIn;
        }
        $tagIn = $this->nullableString($validated['mail_brand_tagline'] ?? null);
        if ($tagIn !== null && $tagIn !== (string) ($configBrand['tagline'] ?? '')) {
            $newBrand['tagline'] = $tagIn;
        }
        if ($newBrand === []) {
            unset($partial['brand']);
        } else {
            $partial['brand'] = $newBrand;
        }

        $configFooter = is_array($configMail['footer'] ?? null) ? $configMail['footer'] : [];
        $newFooter = [];
        $sigIn = $this->nullableString($validated['mail_footer_signature'] ?? null);
        if ($sigIn !== null && $sigIn !== (string) ($configFooter['signature'] ?? '')) {
            $newFooter['signature'] = $sigIn;
        }
        $legalIn = $this->nullableString($validated['mail_footer_legal'] ?? null);
        if ($legalIn !== null && $legalIn !== (string) ($configFooter['legal'] ?? '')) {
            $newFooter['legal'] = $legalIn;
        }
        if ($newFooter === []) {
            unset($partial['footer']);
        } else {
            $partial['footer'] = $newFooter;
        }

        $appearance->mail_layout_partial = $partial === [] ? null : $partial;
        $appearance->save();

        return redirect()
            ->route('admin.site-appearance.edit')
            ->with('success', 'Identité et thème e-mail enregistrés.');
    }

    public function resetMailTheme(): RedirectResponse
    {
        $appearance = SiteAppearance::settings();
        $partial = is_array($appearance->mail_layout_partial) ? $appearance->mail_layout_partial : [];
        unset($partial['theme']);
        $appearance->mail_layout_partial = $partial === [] ? null : $partial;
        $appearance->save();

        return redirect()
            ->route('admin.site-appearance.edit')
            ->with('success', 'Couleurs des e-mails réinitialisées (fichier de config).');
    }

    private function nullableString(?string $value): ?string
    {
        $value = $value !== null ? trim($value) : '';

        return $value === '' ? null : $value;
    }
}
