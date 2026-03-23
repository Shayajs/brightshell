@php($th = $layout['theme'] ?? [])
<p style="margin:18px 0;">
    <a href="{{ $block['url'] ?? '#' }}" style="display:inline-block;background:{{ $th['primaryColor'] ?? '#4a6fa5' }};color:{{ $th['buttonTextColor'] ?? '#ffffff' }};text-decoration:none;padding:10px 16px;border-radius:10px;font-weight:600;">
        {{ $block['label'] ?? 'Action' }}
    </a>
</p>
