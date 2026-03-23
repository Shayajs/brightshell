<?php

namespace App\Services\Mail\Template;

use Illuminate\Support\Facades\View;

class MailTemplateRenderer
{
    public function __construct(
        private readonly MailTemplateRegistry $registry,
        private readonly MailTemplateVariables $variables,
    ) {
    }

    /**
     * @param array<string, mixed> $vars
     */
    public function render(string $key, array $vars = []): RenderedMailTemplate
    {
        $template = $this->registry->resolve($key);
        $layout = is_array($template['layout_json'] ?? null) ? $template['layout_json'] : [];
        $content = is_array($template['content_json'] ?? null) ? $template['content_json'] : [];
        $normalizedVars = $this->variables->normalize($vars);

        $subjectTemplate = (string) ($template['subject_template'] ?? '');
        $subject = $this->replacePlaceholders($subjectTemplate, $normalizedVars);

        $resolvedContent = $this->replaceInMixed($content, $normalizedVars);
        $resolvedLayout = $this->replaceInMixed($layout, $normalizedVars);

        $html = View::make('mail.layouts.base', [
            'layout' => $resolvedLayout,
            'template' => $resolvedContent,
        ])->render();

        $text = $this->renderTextFallback($resolvedContent);

        return new RenderedMailTemplate($subject, $html, $text);
    }

    /**
     * @param array<string, mixed> $variables
     */
    private function replacePlaceholders(string $value, array $variables): string
    {
        return (string) preg_replace_callback('/\{\{\s*([a-zA-Z0-9_.-]+)\s*\}\}/', function (array $matches) use ($variables): string {
            $key = $matches[1] ?? '';

            return $variables[$key] ?? '';
        }, $value);
    }

    /**
     * @param mixed $value
     * @param array<string, string> $variables
     * @return mixed
     */
    private function replaceInMixed(mixed $value, array $variables): mixed
    {
        if (is_string($value)) {
            return $this->replacePlaceholders($value, $variables);
        }

        if (is_array($value)) {
            $result = [];
            foreach ($value as $k => $v) {
                $result[$k] = $this->replaceInMixed($v, $variables);
            }

            return $result;
        }

        return $value;
    }

    /**
     * @param array<string, mixed> $content
     */
    private function renderTextFallback(array $content): string
    {
        $lines = [];
        $lines[] = (string) ($content['name'] ?? 'Message Brightshell');
        $lines[] = '';

        $blocks = $content['content'] ?? [];
        if (is_array($blocks)) {
            foreach ($blocks as $block) {
                if (! is_array($block)) {
                    continue;
                }

                $type = (string) ($block['type'] ?? '');
                if ($type === 'button') {
                    $label = (string) ($block['label'] ?? 'Action');
                    $url = (string) ($block['url'] ?? '');
                    $lines[] = $label.($url !== '' ? ': '.$url : '');
                    continue;
                }

                if ($type === 'divider') {
                    $lines[] = '----------------';
                    continue;
                }

                $text = (string) ($block['text'] ?? '');
                if ($text !== '') {
                    $lines[] = $text;
                }
            }
        }

        return trim(implode("\n", $lines));
    }
}
