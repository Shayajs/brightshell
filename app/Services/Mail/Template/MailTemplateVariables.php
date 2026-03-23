<?php

namespace App\Services\Mail\Template;

class MailTemplateVariables
{
    /**
     * @param array<string, mixed> $vars
     * @return array<string, string>
     */
    public function normalize(array $vars): array
    {
        $normalized = [];

        foreach ($vars as $key => $value) {
            if (is_array($value) || is_object($value)) {
                $normalized[$key] = json_encode($value, JSON_UNESCAPED_UNICODE) ?: '';
                continue;
            }

            if (is_bool($value)) {
                $normalized[$key] = $value ? 'true' : 'false';
                continue;
            }

            $normalized[$key] = (string) ($value ?? '');
        }

        return $normalized;
    }
}
