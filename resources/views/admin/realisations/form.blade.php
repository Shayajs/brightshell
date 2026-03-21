@extends('layouts.admin')

@php
    $isEdit    = $item !== null;
    $pageTitle = $isEdit ? 'Modifier « '.$item['title'].' »' : 'Nouvelle réalisation';
@endphp

@section('content')
<div class="mx-auto max-w-2xl space-y-8">

    {{-- En-tête --}}
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.realisations.index') }}"
           class="flex size-9 items-center justify-center rounded-lg border border-zinc-700 bg-zinc-800 text-zinc-400 hover:text-zinc-100 transition">
            <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-zinc-100 font-display">{{ $pageTitle }}</h1>
            <p class="mt-0.5 text-sm text-zinc-500">
                Catégorie :
                <span class="font-medium text-zinc-300">{{ $category === 'websites' ? 'Sites Web' : 'Réalisations Perso' }}</span>
            </p>
        </div>
    </div>

    @include('layouts.partials.flash')

    {{-- Formulaire --}}
    <form
        method="POST"
        action="{{ $isEdit
            ? route('admin.realisations.update', ['category' => $category, 'id' => $item['id']])
            : route('admin.realisations.store') }}"
        enctype="multipart/form-data"
        class="space-y-6"
    >
        @csrf
        @if ($isEdit) @method('PUT') @endif
        <input type="hidden" name="category" value="{{ $category }}">

        {{-- Titre --}}
        <div>
            <label for="title" class="block text-sm font-medium text-zinc-300 mb-1.5">Titre <span class="text-red-400">*</span></label>
            <input
                type="text" id="title" name="title"
                value="{{ old('title', $item['title'] ?? '') }}"
                required
                class="w-full rounded-lg border border-zinc-700 bg-zinc-800 px-3.5 py-2.5 text-sm text-zinc-100 placeholder-zinc-500 outline-none ring-offset-zinc-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/30 transition"
                placeholder="Ex : allotata.fr"
            >
            @error('title')<p class="mt-1.5 text-xs text-red-400">{{ $message }}</p>@enderror
        </div>

        {{-- Description --}}
        <div>
            <label for="description" class="block text-sm font-medium text-zinc-300 mb-1.5">Description</label>
            <textarea
                id="description" name="description" rows="4"
                class="w-full rounded-lg border border-zinc-700 bg-zinc-800 px-3.5 py-2.5 text-sm text-zinc-100 placeholder-zinc-500 outline-none ring-offset-zinc-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/30 transition resize-y"
                placeholder="Décrivez le projet en quelques mots…"
            >{{ old('description', $item['description'] ?? '') }}</textarea>
        </div>

        {{-- URL (website) ou demo_url / preview_id (personal) --}}
        @if ($category === 'websites')
        <div>
            <label for="url" class="block text-sm font-medium text-zinc-300 mb-1.5">URL du site</label>
            <input
                type="url" id="url" name="url"
                value="{{ old('url', $item['url'] ?? '') }}"
                class="w-full rounded-lg border border-zinc-700 bg-zinc-800 px-3.5 py-2.5 text-sm text-zinc-100 placeholder-zinc-500 outline-none ring-offset-zinc-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/30 transition"
                placeholder="https://exemple.fr"
            >
            @error('url')<p class="mt-1.5 text-xs text-red-400">{{ $message }}</p>@enderror
        </div>
        @else
        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label for="demo_url" class="block text-sm font-medium text-zinc-300 mb-1.5">URL de démo</label>
                <input
                    type="text" id="demo_url" name="demo_url"
                    value="{{ old('demo_url', $item['demo_url'] ?? '') }}"
                    class="w-full rounded-lg border border-zinc-700 bg-zinc-800 px-3.5 py-2.5 text-sm text-zinc-100 placeholder-zinc-500 outline-none ring-offset-zinc-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/30 transition"
                    placeholder="/real/fichier.html"
                >
            </div>
            <div>
                <label for="preview_id" class="block text-sm font-medium text-zinc-300 mb-1.5">ID de prévisualisation JS</label>
                <input
                    type="text" id="preview_id" name="preview_id"
                    value="{{ old('preview_id', $item['preview_id'] ?? '') }}"
                    class="w-full rounded-lg border border-zinc-700 bg-zinc-800 px-3.5 py-2.5 text-sm text-zinc-100 placeholder-zinc-500 outline-none ring-offset-zinc-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/30 transition"
                    placeholder="clipped-preview"
                >
            </div>
        </div>
        @endif

        {{-- Tags --}}
        <div>
            <label for="tags_raw" class="block text-sm font-medium text-zinc-300 mb-1.5">Tags <span class="text-zinc-500 font-normal">(séparés par des virgules)</span></label>
            <input
                type="text" id="tags_raw" name="tags_raw"
                value="{{ old('tags_raw', implode(', ', $item['tags'] ?? [])) }}"
                class="w-full rounded-lg border border-zinc-700 bg-zinc-800 px-3.5 py-2.5 text-sm text-zinc-100 placeholder-zinc-500 outline-none ring-offset-zinc-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/30 transition"
                placeholder="PHP, Laravel, MySQL"
            >
        </div>

        {{-- Image --}}
        <div>
            <label class="block text-sm font-medium text-zinc-300 mb-2">Image / Capture d'écran</label>

            {{-- Aperçu existant --}}
            @if ($isEdit && !empty($item['image']))
            <div class="mb-3 flex items-start gap-4 rounded-xl border border-zinc-700 bg-zinc-800/50 p-3">
                <img src="{{ asset($item['image']) }}" alt="Aperçu" class="h-20 w-32 rounded-lg object-cover border border-zinc-700">
                <div class="min-w-0 flex-1">
                    <p class="truncate text-xs text-zinc-400">{{ $item['image'] }}</p>
                    <label class="mt-2 flex cursor-pointer items-center gap-2 text-xs text-red-400 hover:text-red-300 transition">
                        <input type="checkbox" name="remove_image" value="1" class="rounded border-zinc-600 bg-zinc-700 accent-red-500">
                        Supprimer cette image
                    </label>
                </div>
            </div>
            @endif

            {{-- Upload --}}
            <div class="relative flex w-full cursor-pointer flex-col items-center gap-2 rounded-xl border-2 border-dashed border-zinc-700 bg-zinc-800/30 px-4 py-8 text-center transition hover:border-indigo-500/50 hover:bg-zinc-800/50"
                 id="drop-zone">
                <svg class="size-8 text-zinc-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                <p class="text-sm text-zinc-400">Glissez une image ici, ou <span class="font-medium text-indigo-400">parcourir</span></p>
                <p class="text-xs text-zinc-600">PNG, JPG, WEBP — max 5 Mo</p>
                <input type="file" id="image" name="image" accept="image/*" class="absolute inset-0 cursor-pointer opacity-0">
            </div>
            <p id="file-name" class="mt-1.5 hidden text-xs text-zinc-400"></p>
            @error('image')<p class="mt-1.5 text-xs text-red-400">{{ $message }}</p>@enderror
        </div>

        {{-- Publié --}}
        <div class="flex items-center justify-between rounded-xl border border-zinc-800 bg-zinc-900/60 px-4 py-3">
            <div>
                <p class="text-sm font-medium text-zinc-200">Visibilité publique</p>
                <p class="text-xs text-zinc-500">Décocher pour masquer de la page réalisations.</p>
            </div>
            <label class="relative inline-flex cursor-pointer items-center">
                <input type="checkbox" name="published" value="1" class="peer sr-only"
                    {{ old('published', ($item['published'] ?? true) ? '1' : '') ? 'checked' : '' }}>
                <div class="peer h-6 w-11 rounded-full border border-zinc-600 bg-zinc-700 after:absolute after:start-[2px] after:top-[2px] after:size-5 after:rounded-full after:bg-white after:transition-all peer-checked:bg-indigo-600 peer-checked:border-indigo-600 peer-checked:after:translate-x-full"></div>
            </label>
        </div>

        {{-- Actions --}}
        <div class="flex items-center justify-between border-t border-zinc-800 pt-6">
            <a href="{{ route('admin.realisations.index') }}" class="text-sm text-zinc-500 hover:text-zinc-300 transition">Annuler</a>
            <button type="submit"
                    class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-500 transition">
                <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                {{ $isEdit ? 'Enregistrer les modifications' : 'Créer la réalisation' }}
            </button>
        </div>
    </form>

</div>
@endsection

@push('scripts')
<script>
// Affichage nom de fichier sélectionné
document.getElementById('image')?.addEventListener('change', function () {
    const label = document.getElementById('file-name');
    if (this.files[0]) {
        label.textContent = this.files[0].name;
        label.classList.remove('hidden');
    }
});

// Preview drag-over style
const dz = document.getElementById('drop-zone');
if (dz) {
    dz.addEventListener('dragover', e => { e.preventDefault(); dz.classList.add('border-indigo-500', 'bg-zinc-800/50'); });
    dz.addEventListener('dragleave', () => dz.classList.remove('border-indigo-500', 'bg-zinc-800/50'));
    dz.addEventListener('drop', e => {
        e.preventDefault();
        dz.classList.remove('border-indigo-500', 'bg-zinc-800/50');
        if (e.dataTransfer.files[0]) {
            const input = document.getElementById('image');
            input.files = e.dataTransfer.files;
            document.getElementById('file-name').textContent = e.dataTransfer.files[0].name;
            document.getElementById('file-name').classList.remove('hidden');
        }
    });
}
</script>
@endpush
