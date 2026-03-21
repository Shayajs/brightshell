<div>
    <label for="{{ $name }}" class="block text-[11px] font-semibold uppercase tracking-[0.14em] text-zinc-500">
        {{ $label }}
    </label>
    <input
        id="{{ $name }}"
        type="{{ $type ?? 'text' }}"
        name="{{ $name }}"
        value="{{ $value ?? '' }}"
        @if(!empty($required)) required @endif
        placeholder="{{ $placeholder ?? '' }}"
        class="mt-2 w-full rounded-lg border border-zinc-700 bg-zinc-950/80 px-3.5 py-2.5 text-sm text-zinc-100 placeholder:text-zinc-600 focus:border-indigo-500/50 focus:outline-none focus:ring-2 focus:ring-indigo-500/25 @error($name) border-red-500/60 @enderror"
    >
    @error($name)
        <p class="mt-1.5 text-xs text-red-400">{{ $message }}</p>
    @enderror
</div>
