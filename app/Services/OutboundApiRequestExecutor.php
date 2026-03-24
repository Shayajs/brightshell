<?php

namespace App\Services;

use App\Models\AdminOutboundApiWidget;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

final class OutboundApiRequestExecutor
{
    /**
     * @return array{ok: bool, status_code: int|null, body: string|null, error: string|null}
     */
    public function execute(AdminOutboundApiWidget $widget): array
    {
        $timeout = max(3, min(120, (int) $widget->timeout_seconds));

        try {
            $req = Http::timeout($timeout)
                ->withHeaders($this->buildHeaders($widget))
                ->acceptJson();

            $url = $this->buildUrl($widget);
            $method = strtoupper($widget->http_method);
            $body = $widget->body;

            $response = match ($method) {
                'GET' => $req->get($url),
                'HEAD' => $req->head($url),
                'POST' => $this->sendWithBody($req, 'post', $url, $body, $widget),
                'PUT' => $this->sendWithBody($req, 'put', $url, $body, $widget),
                'PATCH' => $this->sendWithBody($req, 'patch', $url, $body, $widget),
                'DELETE' => $req->delete($url),
                default => $req->send($method, $url),
            };

            return $this->packResponse($response);
        } catch (ConnectionException $e) {
            return [
                'ok' => false,
                'status_code' => null,
                'body' => null,
                'error' => $e->getMessage(),
            ];
        } catch (\Throwable $e) {
            return [
                'ok' => false,
                'status_code' => null,
                'body' => null,
                'error' => $e->getMessage(),
            ];
        }
    }

    private function sendWithBody(\Illuminate\Http\Client\PendingRequest $req, string $fn, string $url, ?string $body, AdminOutboundApiWidget $widget): Response
    {
        $raw = (string) ($body ?? '');
        if ($raw === '') {
            return $req->{$fn}($url);
        }

        return $req->withBody($raw, $this->bodyContentType($widget))->{$fn}($url);
    }

    private function buildUrl(AdminOutboundApiWidget $widget): string
    {
        $url = $widget->url;
        $params = $this->effectiveQueryParams($widget);
        if ($params === []) {
            return $url;
        }

        $q = http_build_query($params);
        if ($q === '') {
            return $url;
        }

        return $url.(str_contains($url, '?') ? '&' : '?').$q;
    }

    /**
     * @return array<string, string|int|float|bool>
     */
    private function effectiveQueryParams(AdminOutboundApiWidget $widget): array
    {
        $params = $widget->query_params ?? [];
        if (! is_array($params)) {
            $params = [];
        }

        if (($widget->auth_type ?? '') === AdminOutboundApiWidget::AUTH_API_KEY_QUERY) {
            $secret = $widget->auth_secret;
            $param = $widget->auth_query_param ?: 'api_key';
            if ($secret !== null && $secret !== '') {
                $params[$param] = $secret;
            }
        }

        return $params;
    }

    /**
     * @return array<string, string>
     */
    private function buildHeaders(AdminOutboundApiWidget $widget): array
    {
        $h = $widget->headers ?? [];
        if (! is_array($h)) {
            $h = [];
        }

        $out = [];
        foreach ($h as $k => $v) {
            if (is_string($k) && (is_string($v) || is_numeric($v))) {
                $out[$k] = (string) $v;
            }
        }

        $secret = $widget->auth_secret;
        $type = $widget->auth_type ?? AdminOutboundApiWidget::AUTH_NONE;

        if ($type === AdminOutboundApiWidget::AUTH_BEARER && $secret !== null && $secret !== '') {
            $name = $widget->auth_header_name ?: 'Authorization';
            $out[$name] = 'Bearer '.$secret;
        }

        if ($type === AdminOutboundApiWidget::AUTH_API_KEY_HEADER && $secret !== null && $secret !== '') {
            $name = $widget->auth_header_name ?: 'X-Api-Key';
            $out[$name] = $secret;
        }

        if ($type === AdminOutboundApiWidget::AUTH_BASIC && $secret !== null && $widget->basic_username !== null) {
            $token = base64_encode($widget->basic_username.':'.$secret);
            $out['Authorization'] = 'Basic '.$token;
        }

        return $out;
    }

    private function bodyContentType(AdminOutboundApiWidget $widget): string
    {
        $h = $widget->headers ?? [];
        if (is_array($h) && isset($h['Content-Type'])) {
            return (string) $h['Content-Type'];
        }

        return 'application/json';
    }

    /**
     * @return array{ok: bool, status_code: int|null, body: string|null, error: string|null}
     */
    private function packResponse(Response $response): array
    {
        $body = $response->body();
        if ($body === '') {
            $json = $response->json();
            if ($json !== null) {
                try {
                    $body = json_encode($json, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
                } catch (\JsonException) {
                    $body = '';
                }
            }
        }

        $ok = $response->successful();

        return [
            'ok' => $ok,
            'status_code' => $response->status(),
            'body' => $body,
            'error' => $ok ? null : ('HTTP '.$response->status().' — '.Str::limit(strip_tags($body), 500)),
        ];
    }
}
