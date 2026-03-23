<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $template['name'] ?? 'Brightshell' }}</title>
</head>
@php
    $t = $layout['theme'] ?? [];
    $divider = $t['dividerColor'] ?? '#2a3550';
@endphp
<body style="margin:0;padding:24px;background:{{ $t['backgroundColor'] ?? '#050810' }};font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:640px;margin:0 auto;background:{{ $t['cardColor'] ?? '#0a0e1a' }};border-radius:14px;padding:24px;color:{{ $t['textColor'] ?? '#e8f0f8' }};border:1px solid {{ $divider }};">
    <tr>
        <td>
            @if (! empty($layout['brand']['logoUrl'] ?? null))
                <p style="margin:0 0 14px;">
                    <img src="{{ $layout['brand']['logoUrl'] }}" alt="{{ $layout['brand']['name'] ?? 'BrightShell' }}" width="160" style="max-width:200px;height:auto;display:block;border:0;">
                </p>
            @endif
            <p style="margin:0;font-size:12px;color:{{ $t['mutedTextColor'] ?? '#a8b8d8' }};text-transform:uppercase;letter-spacing:0.08em;">
                {{ $layout['brand']['name'] ?? 'BrightShell' }}
            </p>
            @php($blocks = is_array($template['content'] ?? null) ? $template['content'] : [])
            @foreach ($blocks as $block)
                @php($type = $block['type'] ?? 'text')
                @includeIf('mail.partials.block-' . $type, ['block' => $block, 'layout' => $layout])
            @endforeach
            <hr style="border:none;border-top:1px solid {{ $divider }};margin:22px 0;">
            <p style="margin:0 0 6px;font-size:13px;color:{{ $t['mutedTextColor'] ?? '#a8b8d8' }};">{{ $layout['footer']['signature'] ?? "L'équipe BrightShell" }}</p>
            <p style="margin:0;font-size:12px;color:{{ $t['mutedTextColor'] ?? '#a8b8d8' }};">{{ $layout['footer']['legal'] ?? 'Ce message est envoyé automatiquement.' }}</p>
        </td>
    </tr>
</table>
</body>
</html>
