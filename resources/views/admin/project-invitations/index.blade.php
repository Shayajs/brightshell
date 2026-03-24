@extends('layouts.admin')
@section('title', 'Invitations projets')
@section('topbar_label', 'Invitations projets')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="font-display text-2xl font-bold text-white">Invitations projets</h1>
        <p class="mt-1 text-sm text-zinc-500">Invitations envoyées, en attente d’acceptation (connexion ou inscription avec l’e-mail indiqué).</p>
    </div>

    @include('layouts.partials.flash')

    <div class="overflow-hidden rounded-2xl border border-zinc-800 bg-zinc-900/50 ring-1 ring-white/5">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[48rem] text-left text-sm">
                <thead>
                    <tr class="border-b border-zinc-800 bg-zinc-950/50 text-[10px] font-semibold uppercase tracking-wider text-zinc-500">
                        <th class="px-5 py-3">Projet</th>
                        <th class="px-5 py-3">E-mail</th>
                        <th class="px-5 py-3">Expire</th>
                        <th class="px-5 py-3">Invité par</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-800/60">
                    @forelse ($invitations as $inv)
                        <tr class="transition hover:bg-zinc-800/30">
                            <td class="px-5 py-3.5">
                                <a href="{{ route('admin.projects.edit', $inv->project) }}" class="font-medium text-indigo-300 hover:text-indigo-200">{{ $inv->project->name }}</a>
                            </td>
                            <td class="px-5 py-3.5 text-zinc-300">{{ $inv->email }}</td>
                            <td class="px-5 py-3.5 text-zinc-500">
                                @if ($inv->expires_at)
                                    {{ $inv->expires_at->format('d/m/Y H:i') }}
                                    @if ($inv->isExpired())
                                        <span class="ml-2 text-amber-400">expirée</span>
                                    @endif
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-zinc-500">{{ $inv->invitedBy?->name ?? '—' }}</td>
                            <td class="px-5 py-3.5">
                                <div class="flex flex-wrap justify-end gap-2">
                                    <form method="POST" action="{{ route('admin.project-invitations.resend', $inv) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="rounded-lg border border-zinc-600 px-3 py-1.5 text-xs font-semibold text-zinc-300 transition hover:border-indigo-500/40 hover:text-indigo-300" @disabled($inv->isExpired())>Renvoyer</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.project-invitations.destroy', $inv) }}" class="inline" onsubmit="return confirm('Supprimer cette invitation ?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded-lg border border-red-500/30 px-3 py-1.5 text-xs font-semibold text-red-300 transition hover:bg-red-500/10">Supprimer</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-12 text-center text-sm text-zinc-600">Aucune invitation en attente.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($invitations->hasPages())
            <div class="border-t border-zinc-800 px-5 py-4 text-sm text-zinc-500">
                {{ $invitations->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
