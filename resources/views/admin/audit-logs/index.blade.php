@extends('layouts.admin')
@section('title', 'Journal d’activité')
@section('topbar_label', 'Journal admin')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="font-display text-2xl font-bold text-white">Journal d’activité</h1>
        <p class="mt-1 text-sm text-zinc-500">Actions sensibles réalisées depuis l’administration (membres, projets, invitations).</p>
    </div>

    <div class="overflow-hidden rounded-2xl border border-zinc-800 bg-zinc-900/50 ring-1 ring-white/5">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[52rem] text-left text-sm">
                <thead>
                    <tr class="border-b border-zinc-800 bg-zinc-950/50 text-[10px] font-semibold uppercase tracking-wider text-zinc-500">
                        <th class="px-5 py-3">Date</th>
                        <th class="px-5 py-3">Acteur</th>
                        <th class="px-5 py-3">Action</th>
                        <th class="px-5 py-3">Cible</th>
                        <th class="px-5 py-3">Détails</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-800/60">
                    @forelse ($logs as $log)
                        <tr class="align-top transition hover:bg-zinc-800/30">
                            <td class="whitespace-nowrap px-5 py-3.5 text-zinc-500">{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                            <td class="px-5 py-3.5 text-zinc-300">{{ $log->actor?->name ?? '—' }}</td>
                            <td class="px-5 py-3.5 font-mono text-xs text-indigo-300">{{ $log->action }}</td>
                            <td class="px-5 py-3.5 text-xs text-zinc-400">
                                @if ($log->subject_type)
                                    {{ class_basename($log->subject_type) }} #{{ $log->subject_id }}
                                @else
                                    —
                                @endif
                            </td>
                            <td class="max-w-md px-5 py-3.5 text-xs text-zinc-500">
                                @if ($log->properties)
                                    <pre class="whitespace-pre-wrap break-words font-mono text-[11px] leading-relaxed">{{ json_encode($log->properties, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                @else
                                    —
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-12 text-center text-sm text-zinc-600">Aucune entrée pour l’instant.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($logs->hasPages())
            <div class="border-t border-zinc-800 px-5 py-4 text-sm text-zinc-500">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
