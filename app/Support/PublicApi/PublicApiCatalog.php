<?php

namespace App\Support\PublicApi;

use Illuminate\Routing\Route;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route as RouteFacade;

/**
 * Liste les routes enregistrées sous le domaine API (nom api.public.*).
 */
final class PublicApiCatalog
{
    /**
     * @return Collection<int, array{
     *     name: string,
     *     methods: list<string>,
     *     path: string,
     *     url: string|null,
     *     title: string,
     *     summary: string,
     *     format: string|null,
     *     cache: string|null,
     * }>
     */
    public function all(): Collection
    {
        $meta = config('brightshell-api.endpoints', []);

        return collect(RouteFacade::getRoutes())
            ->filter(function (Route $route): bool {
                $name = $route->getName();

                return is_string($name) && str_starts_with($name, 'api.public.');
            })
            ->filter(function (Route $route): bool {
                $methods = array_diff($route->methods(), ['HEAD']);

                return ! in_array('OPTIONS', $methods, true);
            })
            ->values()
            ->map(function (Route $route) use ($meta): array {
                $name = (string) $route->getName();
                $methods = array_values(array_diff($route->methods(), ['HEAD']));
                $m = is_array($meta[$name] ?? null) ? $meta[$name] : [];

                return [
                    'name' => $name,
                    'methods' => $methods,
                    'path' => '/'.ltrim($route->uri(), '/'),
                    'url' => PublicApiSupport::namedRouteUrl($name),
                    'title' => (string) ($m['title'] ?? $name),
                    'summary' => (string) ($m['summary'] ?? ''),
                    'format' => isset($m['format']) ? (string) $m['format'] : null,
                    'cache' => isset($m['cache']) ? (string) $m['cache'] : null,
                ];
            })
            ->sortBy('path')
            ->values();
    }
}
