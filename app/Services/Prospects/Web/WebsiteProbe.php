<?php

declare(strict_types=1);

namespace App\Services\Prospects\Web;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Probe HTTP minimal d'un site web prospect.
 *
 * Stratégie :
 *   1. Cache 30 jours par domaine (les sites bougent rarement).
 *   2. GET avec follow_redirects + timeout court (5s) + User-Agent commun.
 *   3. Si non-200 → snapshot `dead()`.
 *   4. Si 200 → parse léger du HTML (regex, pas de DOM lourd) pour extraire :
 *      - meta `viewport` (responsive)
 *      - meta `generator` (plateforme + version)
 *      - copyright année (regex)
 *      - URL finale (pour détecter HTTPS)
 *
 * Aucune dépendance externe : pas de cURL custom, pas de Symfony DomCrawler.
 * Tout passe par le HTTP client Laravel pour bénéficier des `Http::fake()` dans les tests.
 */
final class WebsiteProbe
{
    /** Plateformes que l'on sait reconnaître via la meta `generator` ou des marqueurs HTML. */
    private const PLATFORM_SIGNATURES = [
        // generator meta
        'WordPress' => '/WordPress(?:\s+(\d+\.\d+(?:\.\d+)?))?/i',
        'Wix-classic' => '/Wix\.com Website Builder/i',
        'Wix' => '/wix-site|Wix Editor/i',
        'Squarespace' => '/Squarespace/i',
        'Shopify' => '/Shopify/i',
        'Drupal' => '/Drupal(?:\s+(\d+))?/i',
        'Joomla' => '/Joomla!?\s*(\d+\.\d+)?/i',
        'PrestaShop' => '/PrestaShop\s*(\d+\.\d+(?:\.\d+)?)?/i',
        'Webflow' => '/Webflow/i',
        'Jimdo-classic' => '/jimdo\.com.*?creator/i',
        'Jimdo' => '/JimdoDolphin|jimdo\.com/i',
        'Site123' => '/site123\.com/i',
        'e-monsite' => '/e\-monsite\.com/i',
        'Sitew' => '/sitew\.com/i',
        'Hubspot' => '/HubSpot/i',
        'Notion' => '/notion\.so|notion-static/i',
    ];

    public function __construct(
        private readonly int $timeoutSeconds = 5,
        private readonly int $cacheTtlDays = 30,
        private readonly int $maxBytes = 65_536, // 64 KiB suffisent pour le <head>
    ) {}

    /**
     * Probe un site web. Retourne `WebsiteSnapshot::notProbed()` si l'URL est vide/invalide.
     *
     * Le résultat est caché 30 jours par défaut (clé = domaine normalisé).
     */
    public function probe(?string $url): WebsiteSnapshot
    {
        if ($url === null || trim($url) === '') {
            return WebsiteSnapshot::notProbed();
        }

        $normalized = $this->normalizeUrl($url);
        if ($normalized === null) {
            return WebsiteSnapshot::notProbed();
        }

        $cacheKey = 'prospects:website:'.md5($normalized);

        return Cache::remember($cacheKey, $this->cacheTtlDays * 86_400, function () use ($normalized) {
            return $this->doProbe($normalized);
        });
    }

    private function doProbe(string $url): WebsiteSnapshot
    {
        try {
            $response = Http::timeout($this->timeoutSeconds)
                ->withUserAgent('BrightShell-Prospects/1.0 (Website discovery)')
                ->withHeaders(['Accept-Language' => 'fr,en;q=0.7'])
                ->get($url);
        } catch (\Throwable $e) {
            Log::info('[Prospects][WebsiteProbe] failed', ['url' => $url, 'error' => $e->getMessage()]);

            return new WebsiteSnapshot(
                probed: true,
                alive: false,
                statusCode: null,
                confidence: 30,
            );
        }

        $finalUrl = method_exists($response, 'effectiveUri') && $response->effectiveUri() !== null
            ? (string) $response->effectiveUri()
            : $url;

        return $this->parseResponse($response->status(), $finalUrl, (string) $response->body());
    }

    /**
     * Parse une réponse HTTP en {@see WebsiteSnapshot}. Publique pour permettre les tests
     * sans dépendance au client HTTP.
     */
    public function parseResponse(int $status, string $finalUrl, string $body): WebsiteSnapshot
    {
        if ($status >= 400 || $status < 200) {
            return WebsiteSnapshot::dead($status);
        }

        $head = mb_substr($body, 0, $this->maxBytes);

        return new WebsiteSnapshot(
            probed: true,
            alive: true,
            statusCode: $status,
            https: str_starts_with($finalUrl, 'https://'),
            responsive: $this->detectResponsive($head),
            platform: $this->detectPlatformKey($head),
            platformVersion: $this->detectPlatformVersion($head),
            copyrightYear: $this->detectCopyrightYear($body),
            finalUrl: $finalUrl,
            confidence: 80,
        );
    }

    private function normalizeUrl(string $url): ?string
    {
        $url = trim($url);
        if (! preg_match('#^https?://#i', $url)) {
            $url = 'https://'.ltrim($url, '/');
        }
        $parts = parse_url($url);
        if (! is_array($parts) || empty($parts['host'])) {
            return null;
        }
        $scheme = $parts['scheme'] ?? 'https';
        $host = strtolower($parts['host']);
        // Pas de www. en clé de cache (les deux hosts servent souvent le même contenu).
        $host = preg_replace('/^www\./', '', $host) ?? $host;

        return $scheme.'://'.$host.'/';
    }

    private function detectResponsive(string $head): ?bool
    {
        if (preg_match('/<meta[^>]+name=["\']?viewport["\']?[^>]+content=["\'][^"\']*width=device-width/i', $head) === 1) {
            return true;
        }
        if (preg_match('/<head[\s\S]{0,5000}<\/head>/i', $head) === 1) {
            // <head> trouvé mais pas de viewport responsive
            return false;
        }

        return null;
    }

    private function detectPlatformKey(string $head): ?string
    {
        // Meta generator d'abord (le plus fiable).
        if (preg_match('/<meta[^>]+name=["\']?generator["\']?[^>]+content=["\']([^"\']+)["\']/i', $head, $m) === 1) {
            $generator = $m[1];
            foreach (self::PLATFORM_SIGNATURES as $key => $regex) {
                if (preg_match($regex, $generator) === 1) {
                    return $key;
                }
            }
        }
        // Fallback : signature dans le body (CDN, classes, etc.)
        foreach (self::PLATFORM_SIGNATURES as $key => $regex) {
            if (preg_match($regex, $head) === 1) {
                return $key;
            }
        }

        return null;
    }

    private function detectPlatformVersion(string $head): ?string
    {
        if (preg_match('/<meta[^>]+name=["\']?generator["\']?[^>]+content=["\']([^"\']+)["\']/i', $head, $m) === 1) {
            $generator = $m[1];
            foreach (self::PLATFORM_SIGNATURES as $key => $regex) {
                if (preg_match($regex, $generator, $vm) === 1 && isset($vm[1]) && $vm[1] !== '') {
                    return $vm[1];
                }
            }
        }

        return null;
    }

    private function detectCopyrightYear(string $body): ?int
    {
        $currentYear = (int) date('Y');
        // Patterns : "© 2018", "Copyright 2018", "&copy; 2018", "©2018-2020"
        if (preg_match_all('/(?:©|&copy;|copyright)[\s\-:]*((?:19|20)\d{2})(?:\s*[\-–]\s*((?:19|20)\d{2}))?/iu', $body, $matches, PREG_SET_ORDER) > 0) {
            $years = [];
            foreach ($matches as $m) {
                if (isset($m[2]) && $m[2] !== '') {
                    $years[] = (int) $m[2];
                } else {
                    $years[] = (int) $m[1];
                }
            }
            $years = array_filter($years, static fn (int $y): bool => $y >= 1995 && $y <= $currentYear + 1);
            if ($years !== []) {
                // On retient la plus récente (souvent celle du footer "à jour").
                return max($years);
            }
        }

        return null;
    }
}
