@extends('layouts.admin')

@section('title', 'Quesako Builder')
@section('topbar_label', 'Quesako Builder')
@section('portal_main_max', 'max-w-7xl')

@section('content')
    <div class="space-y-6" id="quesako-builder"
         data-initial-config='@json($config)'
         data-allowed-modules='@json($allowedModules)'
         data-preview-url="{{ route('admin.quesako-builder.preview') }}"
         data-save-url="{{ route('admin.quesako-builder.update') }}"
         data-public-base="{{ url('/quesako') }}">
        <header class="space-y-2">
            <a href="{{ route('admin.dashboard') }}" class="text-xs font-semibold text-indigo-300 hover:text-indigo-200">← Tableau de bord</a>
            <h1 class="font-display text-2xl font-bold tracking-tight text-white sm:text-3xl">Builder Quesako</h1>
            <p class="text-sm text-zinc-400">Gere les onglets et les modules de <code>/quesako</code> avec apercu en direct.</p>
        </header>

        @include('layouts.partials.flash')

        <div class="grid gap-6 xl:grid-cols-12">
            <section class="space-y-6 xl:col-span-7">
                <form method="POST" action="{{ route('admin.quesako-builder.update') }}" id="quesako-builder-form" class="space-y-6">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="quesako_config" id="quesako-config-input">

                    <div class="rounded-2xl border border-zinc-800 bg-zinc-900/40 p-5 ring-1 ring-white/5">
                        <div class="mb-3 flex items-center justify-between">
                            <h2 class="text-sm font-bold uppercase tracking-wide text-white">Onglets</h2>
                            <button type="button" id="add-tab-btn" class="rounded-md border border-zinc-700 px-3 py-1.5 text-xs text-zinc-200 hover:bg-zinc-800">Ajouter</button>
                        </div>
                        <div id="tabs-list" class="space-y-3"></div>
                    </div>

                    <div class="rounded-2xl border border-zinc-800 bg-zinc-900/40 p-5 ring-1 ring-white/5">
                        <div class="mb-3 flex items-center justify-between">
                            <h2 class="text-sm font-bold uppercase tracking-wide text-white">Modules de l'onglet actif</h2>
                            <button type="button" id="add-module-btn" class="rounded-md border border-zinc-700 px-3 py-1.5 text-xs text-zinc-200 hover:bg-zinc-800">Ajouter</button>
                        </div>
                        <div id="modules-list" class="space-y-3"></div>
                    </div>

                    <div class="rounded-2xl border border-zinc-800 bg-zinc-900/40 p-5 ring-1 ring-white/5">
                        <h2 class="mb-3 text-sm font-bold uppercase tracking-wide text-white">Reglages SEO</h2>
                        <div class="space-y-3">
                            <label class="block text-xs text-zinc-400">Titre SEO
                                <input type="text" id="seo-title" class="mt-1 w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2 text-sm text-zinc-100">
                            </label>
                            <label class="block text-xs text-zinc-400">Description SEO
                                <textarea id="seo-description" rows="3" class="mt-1 w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2 text-sm text-zinc-100"></textarea>
                            </label>
                        </div>
                    </div>

                    <button type="submit" class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-500">Enregistrer Quesako</button>
                </form>
            </section>

            <section class="space-y-3 xl:col-span-5">
                <h2 class="text-sm font-bold uppercase tracking-wide text-white">Apercu live</h2>
                <iframe id="quesako-preview-frame" class="h-[70vh] w-full rounded-2xl border border-zinc-800 bg-zinc-950"></iframe>
            </section>
        </div>
    </div>
@endsection

@push('vite')
    @vite(['resources/js/quesako-builder.js'])
@endpush

@include('admin.quesako-builder.partials.module-form-hero')
@include('admin.quesako-builder.partials.module-form-about')
@include('admin.quesako-builder.partials.module-form-services')
@include('admin.quesako-builder.partials.module-form-timeline')
