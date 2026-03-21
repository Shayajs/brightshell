@extends('layouts.admin')
@section('title', 'CV — Expériences')
@section('topbar_label', 'CV — Expériences')

@section('content')
<div class="space-y-6">
    <div class="flex flex-wrap items-center gap-3">
        <a href="{{ route('admin.cv.index') }}" class="text-sm text-zinc-500 hover:text-indigo-400">← Mon CV</a>
        <span class="text-zinc-700">/</span>
        <span class="text-sm text-zinc-300">Expériences</span>
    </div>

    @include('layouts.partials.flash')

    <form method="POST" action="{{ route('admin.cv.experience.update') }}" id="exp-form">
        @csrf
        <div class="space-y-4" id="exp-list">
            @foreach ($experience as $i => $exp)
                @include('admin.cv.partials.experience-item', ['i' => $i, 'exp' => $exp])
            @endforeach
        </div>

        <div class="mt-4 flex items-center justify-between">
            <button type="button" id="add-exp"
                class="flex items-center gap-2 rounded-lg border border-dashed border-zinc-700 px-4 py-2.5 text-sm font-medium text-zinc-400 transition hover:border-indigo-500/60 hover:text-indigo-300">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Ajouter une expérience
            </button>
            <button type="submit"
                class="rounded-lg border border-indigo-500/40 bg-indigo-600/90 px-6 py-2.5 text-sm font-semibold text-white shadow-lg shadow-indigo-950/40 transition hover:bg-indigo-500">
                Enregistrer tout
            </button>
        </div>
    </form>
</div>

<template id="exp-template">
    @include('admin.cv.partials.experience-item', ['i' => '__IDX__', 'exp' => []])
</template>

<script>
document.addEventListener('DOMContentLoaded', () => {
    let idx = {{ count($experience) }};
    const list   = document.getElementById('exp-list');
    const addBtn = document.getElementById('add-exp');
    const tpl    = document.getElementById('exp-template').innerHTML;

    function bindRemove(container) {
        container.querySelector('button[data-remove-item]')?.addEventListener('click', () => container.remove());
    }

    list.querySelectorAll('[data-remove-item]').forEach(btn => {
        btn.addEventListener('click', () => btn.closest('[data-remove-item]').remove());
    });

    addBtn.addEventListener('click', () => {
        const html = tpl.replace(/__IDX__/g, idx++);
        list.insertAdjacentHTML('beforeend', html);
        const item = list.lastElementChild;
        item.querySelector('button[data-remove-item]')?.addEventListener('click', () => item.remove());
    });
});
</script>
@endsection
