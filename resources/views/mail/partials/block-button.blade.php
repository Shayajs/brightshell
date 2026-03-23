<p style="margin:18px 0;">
    <a href="{{ $block['url'] ?? '#' }}" style="display:inline-block;background:{{ $layout['theme']['primaryColor'] ?? '#4f46e5' }};color:{{ $layout['theme']['buttonTextColor'] ?? '#ffffff' }};text-decoration:none;padding:10px 16px;border-radius:10px;font-weight:600;">
        {{ $block['label'] ?? 'Action' }}
    </a>
</p>
