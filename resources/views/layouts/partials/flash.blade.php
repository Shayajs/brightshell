@if (session('generated_passwords'))
    <div class="mb-6 rounded-xl border border-amber-500/40 bg-amber-500/10 px-4 py-4 ring-1 ring-amber-500/20" role="alert">
        <p class="mb-2 flex items-center gap-2 text-sm font-semibold text-amber-300">
            <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
            Mots de passe générés automatiquement — note-les maintenant, ils ne seront plus affichés.
        </p>
        <ul class="space-y-1.5">
            @foreach (session('generated_passwords') as $email => $pwd)
                <li class="flex flex-wrap items-center gap-3 rounded-lg border border-amber-500/20 bg-amber-950/30 px-3 py-2">
                    <span class="text-xs text-amber-200/80">{{ $email }}</span>
                    <code class="ml-auto select-all rounded bg-zinc-900 px-2 py-0.5 font-mono text-xs font-bold tracking-widest text-amber-300">{{ $pwd }}</code>
                </li>
            @endforeach
        </ul>
    </div>
@endif

@if (session('success'))
    <div class="mb-6 flex items-center gap-3 rounded-xl border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-300 ring-1 ring-emerald-500/20" role="alert">
        <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M20 6L9 17l-5-5"/></svg>
        {{ session('success') }}
    </div>
@endif
@if (session('error') || $errors->any())
    <div class="mb-6 flex items-start gap-3 rounded-xl border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-300 ring-1 ring-red-500/20" role="alert">
        <svg class="mt-0.5 h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        <div>
            @if (session('error')) <p>{{ session('error') }}</p> @endif
            @if ($errors->any())
                <ul class="list-disc pl-4 space-y-0.5">
                    @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
                </ul>
            @endif
        </div>
    </div>
@endif
