@props(['allCapabilities', 'team' => null])

@php
    $selected = $team !== null
        ? $team->capabilities->pluck('id')->map(fn ($id) => (int) $id)->all()
        : collect(old('capabilities', []))->map(fn ($id) => (int) $id)->all();
@endphp

<fieldset class="space-y-3">
    <legend class="text-sm font-semibold text-zinc-200">Accès (capabilities)</legend>
    <p class="text-xs text-zinc-500">Un collaborateur cumule les droits de <strong class="text-zinc-400">tous</strong> les groupes auxquels il est rattaché, en plus de ses rôles globaux (admin plateforme, rôle <span class="text-zinc-400">developer</span>, etc.).</p>
    @foreach ($allCapabilities as $cap)
        <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-zinc-800 bg-zinc-950/50 px-4 py-3 transition hover:border-zinc-700 has-[:checked]:border-indigo-500/40">
            <input type="checkbox" name="capabilities[]" value="{{ $cap->id }}"
                   @checked(in_array($cap->id, $selected, true))
                   class="h-4 w-4 rounded border-zinc-600 bg-zinc-950 text-indigo-500 focus:ring-indigo-500/40">
            <div>
                <p class="text-sm font-medium text-zinc-100">{{ $cap->label }}</p>
                @if ($cap->description)
                    <p class="text-[11px] text-zinc-500">{{ $cap->description }}</p>
                @endif
            </div>
        </label>
    @endforeach
</fieldset>
