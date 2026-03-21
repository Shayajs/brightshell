<?php

namespace App\Support\StudentMaterials;

use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;
use League\CommonMark\MarkdownConverter;

/**
 * Markdown + callouts Obsidian (> [!type] titre) + GFM (tableaux, cases à cocher…).
 * LaTeX : laisser $...$ et $$...$$ dans le HTML pour KaTeX côté client.
 */
final class StudentMaterialsMarkdownRenderer
{
    private MarkdownConverter $converter;

    public function __construct()
    {
        $config = [
            'html_input' => 'allow',
            'allow_unsafe_links' => false,
            'max_nesting_level' => 64,
        ];
        $environment = new Environment($config);
        $environment->addExtension(new CommonMarkCoreExtension);
        $environment->addExtension(new GithubFlavoredMarkdownExtension);

        $this->converter = new MarkdownConverter($environment);
    }

    public function toHtml(string $markdown): string
    {
        $withCallouts = $this->preprocessObsidianCallouts($markdown);

        return $this->converter->convert($withCallouts)->getContent();
    }

    /**
     * Transforme les blocs callout Obsidian en HTML (puis passage CommonMark global).
     */
    private function preprocessObsidianCallouts(string $markdown): string
    {
        $lines = preg_split('/\r\n|\r|\n/', $markdown) ?: [];
        $out = [];
        $i = 0;
        $n = count($lines);

        while ($i < $n) {
            if (preg_match('/^>\s*\[!([a-zA-Z0-9_-]+)\]\s*[+-]?\s*(.*?)\s*$/', $lines[$i], $m)) {
                $type = strtolower($m[1]);
                $title = trim($m[2]);
                $i++;
                $bodyLines = [];
                while ($i < $n && preg_match('/^>\s?(.*)$/', $lines[$i], $bm)) {
                    $bodyLines[] = $bm[1];
                    $i++;
                }
                $body = implode("\n", $bodyLines);
                $out[] = $this->calloutBlockToPlaceholder($type, $title, $body);

                continue;
            }
            $out[] = $lines[$i];
            $i++;
        }

        return implode("\n", $out);
    }

    private function calloutBlockToPlaceholder(string $type, string $title, string $body): string
    {
        $safeType = preg_replace('/[^a-z0-9_-]/i', '', $type);
        if ($safeType === '') {
            $safeType = 'note';
        }

        $innerMd = $this->preprocessObsidianCallouts($body);
        $innerHtml = trim($this->converter->convert($innerMd)->getContent());

        $safeTitle = $title !== '' ? '<p class="callout-title">'.htmlspecialchars($title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8').'</p>' : '';

        return "\n\n".'<div class="callout callout-'.$safeType.'" data-callout="'.htmlspecialchars($safeType, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8').'">'.$safeTitle.'<div class="callout-body">'.$innerHtml.'</div></div>'."\n\n";
    }
}
