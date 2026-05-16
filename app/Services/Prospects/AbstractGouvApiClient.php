<?php

declare(strict_types=1);

namespace App\Services\Prospects;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Base commune des clients HTTP vers les APIs gouvernementales.
 *
 * Mutualise :
 * - configuration baseUrl + timeout + retry (sur 429 / 5xx)
 * - throttle interne (usleep entre 2 appels)
 * - logging cohérent en cas d'erreur
 *
 * Chaque sous-classe expose les méthodes métier (search, geocode, …).
 */
abstract class AbstractGouvApiClient
{
    /**
     * Timestamp (en microsecondes) du dernier appel — partagé par instance.
     */
    private float $lastCallAt = 0.0;

    public function __construct(
        protected readonly string $baseUrl,
        protected readonly int $timeout = 15,
        protected readonly int $throttleMicroseconds = 150_000,
        /** @var array<string, string> */
        protected readonly array $defaultHeaders = [],
    ) {}

    /**
     * Pre-configured HTTP client (Bearer, headers, baseUrl, retry, timeout).
     *
     * Override dans les sous-classes pour ajouter de l'auth (Bearer token).
     */
    protected function http(): PendingRequest
    {
        $request = Http::baseUrl($this->baseUrl)
            ->timeout($this->timeout)
            ->acceptJson()
            ->withUserAgent('BrightShell-Prospects/1.0 (+contact@brightshell.fr)')
            ->retry(2, 200, function (\Throwable $e): bool {
                return $e instanceof RequestException
                    && $e->response !== null
                    && in_array($e->response->status(), [429, 500, 502, 503, 504], true);
            });

        if ($this->defaultHeaders !== []) {
            $request = $request->withHeaders($this->defaultHeaders);
        }

        return $request;
    }

    /**
     * Respecte le rate limit : si moins de `throttleMicroseconds` µs se sont
     * écoulées depuis le dernier appel, on dort la différence.
     */
    protected function throttle(): void
    {
        if ($this->throttleMicroseconds <= 0) {
            return;
        }
        $now = microtime(true) * 1_000_000;
        $elapsed = $now - $this->lastCallAt;

        if ($this->lastCallAt > 0 && $elapsed < $this->throttleMicroseconds) {
            usleep((int) ($this->throttleMicroseconds - $elapsed));
        }

        $this->lastCallAt = microtime(true) * 1_000_000;
    }

    /**
     * Log standardisé des erreurs API (utile pour traquer une panne d'endpoint).
     */
    protected function logFailure(string $endpoint, \Throwable $e, array $context = []): void
    {
        Log::warning("[Prospects][{$this->shortName()}] {$endpoint} failed", array_merge([
            'message' => $e->getMessage(),
            'code' => $e->getCode(),
        ], $context));
    }

    private function shortName(): string
    {
        return class_basename(static::class);
    }
}
