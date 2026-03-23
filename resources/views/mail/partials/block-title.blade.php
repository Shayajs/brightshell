@php($th = $layout['theme'] ?? [])
<h1 style="margin:18px 0 10px;font-size:24px;line-height:1.3;color:{{ $th['textColor'] ?? '#e8f0f8' }};">{{ $block['text'] ?? '' }}</h1>
