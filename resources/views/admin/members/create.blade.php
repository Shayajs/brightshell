@extends('layouts.admin')
@section('title', 'Créer des membres')
@section('topbar_label', 'Nouveau(x) membre(s)')

@section('content')
<div class="space-y-6">

    <div class="flex flex-wrap items-center gap-3">
        <a href="{{ route('admin.members.index') }}" class="text-sm text-zinc-500 hover:text-indigo-400">← Membres</a>
        <span class="text-zinc-700">/</span>
        <span class="text-sm text-zinc-300">Créer</span>
    </div>

    @include('layouts.partials.flash')

    <form method="POST" action="{{ route('admin.members.store') }}" id="create-form" novalidate>
        @csrf

        {{-- Erreurs globales --}}
        @if ($errors->any())
            <div class="mb-4 rounded-xl border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-300 ring-1 ring-red-500/20" role="alert">
                <ul class="list-disc pl-4 space-y-0.5">
                    @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
                </ul>
            </div>
        @endif

        <div class="mx-auto max-w-2xl space-y-4" id="members-list">
            @include('admin.members.partials.create-item', ['i' => 0, 'allRoles' => $allRoles])
        </div>

        <div class="mx-auto max-w-2xl mt-4">
            <button type="button" id="add-member"
                class="flex w-full items-center justify-center gap-2 rounded-2xl border border-dashed border-zinc-700 py-3.5 text-sm font-medium text-zinc-400 transition hover:border-indigo-500/50 hover:bg-indigo-500/5 hover:text-indigo-300">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Ajouter un autre membre
            </button>
        </div>

        <div class="mx-auto max-w-2xl">
            <div class="rounded-2xl border border-zinc-800 bg-zinc-900/40 px-5 py-4">
                <p class="mb-3 text-xs text-zinc-500">
                    Si vous ne renseignez pas de mot de passe, un mot de passe aléatoire sera généré et affiché après création.
                </p>
                <div class="flex justify-end gap-3">
                    <a href="{{ route('admin.members.index') }}"
                        class="rounded-lg border border-zinc-700 px-5 py-2.5 text-sm font-medium text-zinc-400 transition hover:text-zinc-200">
                        Annuler
                    </a>
                    <button type="submit" id="submit-btn"
                        class="min-w-[12rem] rounded-lg border border-indigo-500/40 bg-indigo-600/90 px-6 py-2.5 text-center text-sm font-semibold text-white shadow-lg shadow-indigo-950/40 transition hover:bg-indigo-500">
                        Créer le membre
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<template id="member-template">
    @include('admin.members.partials.create-item', ['i' => '__IDX__', 'allRoles' => $allRoles])
</template>

<script>
document.addEventListener('DOMContentLoaded', () => {
    let idx    = 1;
    const list = document.getElementById('members-list');
    const btn  = document.getElementById('submit-btn');
    const tpl  = document.getElementById('member-template').innerHTML;

    function updateButton() {
        const n = list.querySelectorAll('.member-block').length;
        btn.textContent = n > 1 ? `Créer les ${n} membres` : 'Créer le membre';
    }

    function bindRemove(block) {
        block.querySelector('[data-remove-member]')?.addEventListener('click', () => {
            if (list.querySelectorAll('.member-block').length > 1) {
                block.remove();
                renumber();
                updateButton();
            }
        });
    }

    function renumber() {
        list.querySelectorAll('.member-block').forEach((block, n) => {
            const span = block.querySelector('.member-num');
            if (span) span.textContent = n + 1;
        });
    }

    document.getElementById('add-member').addEventListener('click', () => {
        const html = tpl.replace(/__IDX__/g, idx++);
        list.insertAdjacentHTML('beforeend', html);
        const block = list.lastElementChild;
        bindRemove(block);
        block.querySelector('input[name$="[name]"]')?.focus();
        updateButton();
    });

    list.querySelectorAll('.member-block').forEach(bindRemove);
    updateButton();
});
</script>
@endsection
