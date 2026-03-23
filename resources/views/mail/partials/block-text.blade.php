@php($th = $layout['theme'] ?? [])
<p style="margin:10px 0;font-size:14px;line-height:1.6;white-space:pre-line;color:{{ $th['textColor'] ?? '#e8f0f8' }};">{{ $block['text'] ?? '' }}</p>
