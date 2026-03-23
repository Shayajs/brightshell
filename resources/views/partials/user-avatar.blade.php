@php
    /** @var \App\Models\User $user */
    $size = $size ?? 'h-9 w-9';
    $textSize = $textSize ?? 'text-sm';
    $url = $user->avatarUrl();
@endphp
@if ($url)
    <img
        src="{{ $url }}"
        alt=""
        class="{{ $size }} shrink-0 rounded-full object-cover ring-1 ring-zinc-700"
    >
@else
    <div
        class="{{ $size }} flex shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-indigo-500 to-violet-600 {{ $textSize }} font-bold text-white font-display"
        aria-hidden="true"
    >{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($user->greetingFirstName() ?: $user->name ?: '?', 0, 1)) }}</div>
@endif
