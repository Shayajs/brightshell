@extends('layouts.admin')
@section('title', $member->name)
@section('topbar_label', 'Membre')

@section('content')
<div class="space-y-6">
    <div class="flex flex-wrap items-center gap-3">
        <a href="{{ route('admin.members.index') }}" class="text-sm text-zinc-500 hover:text-indigo-400">← Membres</a>
        <span class="text-zinc-700">/</span>
        <span class="text-sm text-zinc-300">{{ $member->name }}</span>
    </div>

    @include('layouts.partials.flash')

    <div class="grid gap-6 lg:grid-cols-3">
        {{-- Profil --}}
        <div class="rounded-2xl border border-zinc-800 bg-zinc-900/50 p-6 ring-1 ring-white/5">
            <div class="flex items-center gap-4">
                <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-indigo-500 to-violet-600 text-xl font-bold text-white font-display">
                    {{ strtoupper(substr(trim($member->name), 0, 1)) }}
                </div>
                <div>
                    <p class="font-display text-lg font-bold text-white">{{ $member->name }}</p>
                    <p class="text-sm text-zinc-400">{{ $member->email }}</p>
                </div>
            </div>
            <dl class="mt-5 space-y-3 border-t border-zinc-800 pt-5 text-sm">
                <div class="flex justify-between">
                    <dt class="text-zinc-500">Inscrit le</dt>
                    <dd class="text-zinc-200">{{ $member->created_at->format('d/m/Y') }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-zinc-500">Admin système</dt>
                    <dd>
                        @if ($member->is_admin)
                            <span class="rounded-md border border-amber-500/30 bg-amber-500/10 px-2 py-0.5 text-xs font-semibold text-amber-300">Oui</span>
                        @else
                            <span class="text-zinc-500">Non</span>
                        @endif
                    </dd>
                </div>
                @if ($member->companies->isNotEmpty())
                <div class="flex justify-between">
                    <dt class="text-zinc-500">Société(s)</dt>
                    <dd class="text-right text-zinc-300">
                        @foreach ($member->companies as $c)
                            <a href="{{ route('admin.companies.show', $c) }}" class="block hover:text-indigo-400">{{ $c->name }}</a>
                        @endforeach
                    </dd>
                </div>
                @endif
            </dl>
            @if ($member->hasRole('student'))
                <div class="mt-5 space-y-2 border-t border-zinc-800 pt-5">
                    <a href="{{ route('admin.student-courses.student', $member) }}"
                       class="inline-flex w-full items-center justify-center gap-2 rounded-lg border border-indigo-500/40 bg-indigo-500/10 px-3 py-2.5 text-sm font-semibold text-indigo-300 transition hover:bg-indigo-500/20">
                        <svg class="size-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                        Cours de cet élève
                    </a>
                    <a href="{{ route('admin.student-subjects.student', $member) }}"
                       class="inline-flex w-full items-center justify-center gap-2 rounded-lg border border-violet-500/40 bg-violet-500/10 px-3 py-2.5 text-sm font-semibold text-violet-300 transition hover:bg-violet-500/20">
                        <svg class="size-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                        Matières &amp; dossiers
                    </a>
                </div>
            @endif
        </div>

        {{-- Rôles --}}
        <div class="rounded-2xl border border-zinc-800 bg-zinc-900/50 p-6 ring-1 ring-white/5 lg:col-span-2">
            <h2 class="font-display text-sm font-bold uppercase tracking-wide text-white">Rôles &amp; accès</h2>
            <p class="mt-1 text-xs text-zinc-500">Les rôles définissent les portails accessibles par ce membre.</p>

            <form method="POST" action="{{ route('admin.members.roles', $member) }}" class="mt-5 space-y-4">
                @csrf
                <div class="grid gap-3 sm:grid-cols-2">
                    @foreach ($allRoles as $role)
                        <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-zinc-800 bg-zinc-950/50 px-4 py-3 transition hover:border-zinc-700 has-[:checked]:border-indigo-500/40 has-[:checked]:bg-indigo-500/8">
                            <input
                                type="checkbox"
                                name="roles[]"
                                value="{{ $role->id }}"
                                @checked($member->roles->contains($role))
                                class="h-4 w-4 rounded border-zinc-600 bg-zinc-950 text-indigo-500 focus:ring-indigo-500/40"
                            >
                            <div>
                                <p class="text-sm font-medium text-zinc-100">{{ ucfirst($role->slug) }}</p>
                                <p class="text-[11px] text-zinc-500">Priorité {{ $role->priority }}</p>
                            </div>
                        </label>
                    @endforeach
                </div>

                <div class="flex items-center gap-3 rounded-xl border border-zinc-800 bg-zinc-950/50 px-4 py-3">
                    <input type="hidden" name="is_admin" value="0">
                    <label class="flex cursor-pointer items-center gap-3">
                        <input
                            type="checkbox"
                            name="is_admin"
                            value="1"
                            @checked($member->is_admin)
                            class="h-4 w-4 rounded border-zinc-600 bg-zinc-950 text-amber-500 focus:ring-amber-500/40"
                        >
                        <div>
                            <p class="text-sm font-medium text-amber-200">Admin système</p>
                            <p class="text-[11px] text-zinc-500">Accès à tous les portails + flag is_admin</p>
                        </div>
                    </label>
                </div>

                <div class="flex justify-end">
                    <button type="submit"
                        class="rounded-lg border border-indigo-500/40 bg-indigo-600/90 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-indigo-950/40 transition hover:bg-indigo-500">
                        Enregistrer les rôles
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
