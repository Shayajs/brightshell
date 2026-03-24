@extends('layouts.admin')
@section('title', 'Santé technique')
@section('topbar_label', 'Santé')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="font-display text-2xl font-bold text-white">Santé technique</h1>
        <p class="mt-1 text-sm text-zinc-500">Aperçu rapide des files d’attente et de l’environnement (sans action destructive).</p>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <div class="rounded-2xl border border-zinc-800 bg-zinc-900/50 p-5 ring-1 ring-white/5">
            <p class="text-[10px] font-semibold uppercase tracking-wider text-zinc-500">Jobs en échec</p>
            <p class="mt-2 font-display text-3xl font-bold {{ $failedJobsCount > 0 ? 'text-amber-400' : 'text-emerald-400' }}">{{ $failedJobsCount }}</p>
            <p class="mt-1 text-xs text-zinc-500">Table <span class="font-mono text-zinc-400">failed_jobs</span></p>
        </div>
        <div class="rounded-2xl border border-zinc-800 bg-zinc-900/50 p-5 ring-1 ring-white/5">
            <p class="text-[10px] font-semibold uppercase tracking-wider text-zinc-500">Jobs en attente</p>
            <p class="mt-2 font-display text-3xl font-bold text-zinc-100">{{ $pendingJobsCount }}</p>
            <p class="mt-1 text-xs text-zinc-500">Table <span class="font-mono text-zinc-400">jobs</span></p>
        </div>
        <div class="rounded-2xl border border-zinc-800 bg-zinc-900/50 p-5 ring-1 ring-white/5">
            <p class="text-[10px] font-semibold uppercase tracking-wider text-zinc-500">Environnement</p>
            <p class="mt-2 text-sm text-zinc-300">{{ $appEnv }}</p>
            <p class="mt-1 text-xs text-zinc-500">File d’attente : <span class="font-mono text-zinc-400">{{ $queueDefault }}</span></p>
            <p class="text-xs text-zinc-500">Mailer : <span class="font-mono text-zinc-400">{{ $mailDefault }}</span></p>
        </div>
    </div>

    <div class="rounded-2xl border border-zinc-800 bg-zinc-900/50 p-6 ring-1 ring-white/5">
        <h2 class="font-display text-sm font-bold uppercase tracking-wide text-white">Versions</h2>
        <ul class="mt-3 space-y-1 text-sm text-zinc-400">
            <li>PHP <span class="font-mono text-zinc-200">{{ $phpVersion }}</span></li>
            <li>Laravel <span class="font-mono text-zinc-200">{{ $laravelVersion }}</span></li>
        </ul>
        <p class="mt-4 text-xs leading-relaxed text-zinc-600">En cas d’échecs de jobs : <code class="rounded bg-zinc-950 px-1 py-0.5 font-mono text-zinc-400">php artisan queue:failed</code> puis <code class="rounded bg-zinc-950 px-1 py-0.5 font-mono text-zinc-400">queue:retry</code> ou <code class="rounded bg-zinc-950 px-1 py-0.5 font-mono text-zinc-400">queue:flush</code> selon le cas.</p>
    </div>

    @if ($recentFailed->isNotEmpty())
        <div class="overflow-hidden rounded-2xl border border-zinc-800 bg-zinc-900/50 ring-1 ring-white/5">
            <div class="border-b border-zinc-800 bg-zinc-950/50 px-5 py-3">
                <h2 class="text-xs font-bold uppercase tracking-wide text-zinc-400">Derniers échecs</h2>
            </div>
            <ul class="divide-y divide-zinc-800/60">
                @foreach ($recentFailed as $row)
                    <li class="px-5 py-3 text-xs">
                        <span class="font-mono text-zinc-300">#{{ $row->id }}</span>
                        <span class="text-zinc-500">{{ $row->queue }} · {{ $row->connection }}</span>
                        <span class="block text-zinc-600">{{ $row->failed_at }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif
</div>
@endsection
