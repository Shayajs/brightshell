@extends('layouts.admin')

@section('title', 'Templates mail')
@section('topbar_label', 'Templates mail')

@section('content')
    <div class="space-y-8">
        <header class="space-y-2">
            <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-indigo-400/90">Mail</p>
            <h1 class="font-display text-2xl font-bold tracking-tight text-white sm:text-3xl">Templates mail</h1>
            <p class="max-w-3xl text-sm text-zinc-400">Gestion JSON + PHP des messages envoyes par Brightshell.</p>
        </header>

        @include('layouts.partials.flash')

        <section class="overflow-hidden rounded-2xl border border-zinc-800 bg-zinc-900/50 ring-1 ring-white/5">
            <table class="min-w-full divide-y divide-zinc-800">
                <thead class="bg-zinc-950/70">
                    <tr class="text-left text-xs uppercase tracking-wide text-zinc-500">
                        <th class="px-5 py-3">Template</th>
                        <th class="px-5 py-3">Categorie</th>
                        <th class="px-5 py-3">Version</th>
                        <th class="px-5 py-3">Etat</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-800/80 text-sm">
                    @foreach ($templates as $template)
                        <tr>
                            <td class="px-5 py-4">
                                <p class="font-semibold text-zinc-200">{{ $template['name'] }}</p>
                                <p class="text-xs text-zinc-500">{{ $template['key'] }}</p>
                            </td>
                            <td class="px-5 py-4 text-zinc-400">{{ $template['category'] }}</td>
                            <td class="px-5 py-4 text-zinc-400">v{{ $template['version'] }}</td>
                            <td class="px-5 py-4">
                                <span class="rounded-full px-2 py-1 text-xs {{ $template['is_active'] ? 'bg-emerald-500/15 text-emerald-300' : 'bg-zinc-700 text-zinc-300' }}">
                                    {{ $template['is_active'] ? 'Actif' : 'Inactif' }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-right">
                                <a class="rounded-lg border border-zinc-700 px-3 py-1.5 text-xs font-semibold text-zinc-200 hover:bg-zinc-800"
                                   href="{{ route('admin.mail-templates.edit', $template['key']) }}">
                                    Modifier
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </section>
    </div>
@endsection
