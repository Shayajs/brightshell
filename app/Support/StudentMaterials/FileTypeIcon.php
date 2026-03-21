<?php

namespace App\Support\StudentMaterials;

use App\Models\StudentSubjectFile;

/** Icônes SVG inline par type de fichier (liste matières admin + portail élève). */
final class FileTypeIcon
{
    public static function svg(StudentSubjectFile $file): string
    {
        $ext = strtolower(pathinfo($file->original_name, PATHINFO_EXTENSION));
        $mime = strtolower((string) ($file->mime_type ?? ''));

        return match (true) {
            in_array($ext, ['md', 'markdown'], true) || str_contains($mime, 'markdown') => self::wrap('text-violet-400', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>'),
            $ext === 'pdf' || str_contains($mime, 'pdf') => self::wrap('text-red-400', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 3v6h6"/>'),
            in_array($ext, ['png', 'jpg', 'jpeg', 'gif', 'webp', 'svg', 'bmp', 'ico'], true) || str_starts_with($mime, 'image/') => self::wrap('text-sky-400', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 5a2 2 0 012-2h12a2 2 0 012 2v14a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm4 2v10l4-3 4 3V7H8z"/>'),
            in_array($ext, ['zip', 'rar', '7z', 'gz', 'tar'], true) || str_contains($mime, 'zip') => self::wrap('text-amber-400', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>'),
            in_array($ext, ['doc', 'docx', 'odt'], true) || str_contains($mime, 'word') => self::wrap('text-blue-400', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6M7 4h8l4 4v12a2 2 0 01-2 2H7a2 2 0 01-2-2V6a2 2 0 012-2z"/>'),
            in_array($ext, ['xls', 'xlsx', 'ods', 'csv'], true) || str_contains($mime, 'spreadsheet') || str_contains($mime, 'excel') => self::wrap('text-emerald-400', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h18v18H3V3zm2 4h4v4H5V7zm0 6h4v4H5v-4zm6-6h8v4h-8V7zm0 6h8v8h-8v-8z"/>'),
            in_array($ext, ['ppt', 'pptx', 'odp'], true) => self::wrap('text-orange-400', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 4h8l4 4v12a2 2 0 01-2 2H6a2 2 0 01-2-2V6a2 2 0 012-2z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 11v6m-3-3h6"/>'),
            in_array($ext, ['mp3', 'wav', 'ogg', 'flac', 'm4a'], true) || str_starts_with($mime, 'audio/') => self::wrap('text-fuchsia-400', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/>'),
            in_array($ext, ['mp4', 'webm', 'mov', 'mkv'], true) || str_starts_with($mime, 'video/') => self::wrap('text-rose-400', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>'),
            in_array($ext, ['js', 'ts', 'tsx', 'jsx', 'php', 'py', 'rb', 'go', 'rs', 'java', 'c', 'cpp', 'h', 'css', 'scss', 'html', 'vue', 'svelte'], true) => self::wrap('text-cyan-400', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>'),
            in_array($ext, ['txt', 'log', 'env', 'ini', 'yaml', 'yml', 'json', 'xml'], true) || $mime === 'text/plain' => self::wrap('text-zinc-400', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 12h10M4 18h14"/>'),
            default => self::wrap('text-zinc-500', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>'),
        };
    }

    private static function wrap(string $class, string $path): string
    {
        return '<svg class="size-5 shrink-0 '.$class.'" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">'.$path.'</svg>';
    }
}
