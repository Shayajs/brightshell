<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $template['name'] ?? 'Brightshell' }}</title>
</head>
<body style="margin:0;padding:24px;background:{{ $layout['theme']['backgroundColor'] ?? '#f4f6fb' }};font-family:Arial,sans-serif;">
<table role="presentation" width="100%%" cellspacing="0" cellpadding="0" style="max-width:640px;margin:0 auto;background:{{ $layout['theme']['cardColor'] ?? '#ffffff' }};border-radius:14px;padding:24px;color:{{ $layout['theme']['textColor'] ?? '#111827' }};">
    <tr>
        <td>
            <p style="margin:0;font-size:12px;color:{{ $layout['theme']['mutedTextColor'] ?? '#6b7280' }};text-transform:uppercase;letter-spacing:0.08em;">
                {{ $layout['brand']['name'] ?? 'Brightshell' }}
            </p>
            @php($blocks = is_array($template['content'] ?? null) ? $template['content'] : [])
            @foreach ($blocks as $block)
                @php($type = $block['type'] ?? 'text')
                @includeIf('mail.partials.block-' . $type, ['block' => $block, 'layout' => $layout])
            @endforeach
            <hr style="border:none;border-top:1px solid #e5e7eb;margin:22px 0;">
            <p style="margin:0 0 6px;font-size:13px;color:{{ $layout['theme']['mutedTextColor'] ?? '#6b7280' }};">{{ $layout['footer']['signature'] ?? 'L'equipe Brightshell' }}</p>
            <p style="margin:0;font-size:12px;color:{{ $layout['theme']['mutedTextColor'] ?? '#6b7280' }};">{{ $layout['footer']['legal'] ?? 'Ce message est envoye automatiquement.' }}</p>
        </td>
    </tr>
</table>
</body>
</html>
