@extends('layouts.admin')

@section('title', 'API publique')
@section('topbar_label', 'API publique')

@section('content')
    <div class="space-y-8">
        <header class="space-y-2">
            <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-sky-400/90">Intégrations</p>
            <h1 class="font-display text-2xl font-bold tracking-tight text-white sm:text-3xl">Gestionnaire d’API publique</h1>
            <p class="max-w-3xl text-sm leading-relaxed text-zinc-400">
                Endpoints <strong class="text-zinc-300">lecture seule</strong> exposés sur le sous-domaine dédié
                (<code class="rounded bg-zinc-900 px-1 py-0.5 text-xs text-sky-300">api.{domaine}</code>).
                Les URLs complètes et exemples se mettent à jour selon ton <code class="text-zinc-500">.env</code>.
            </p>
        </header>

        @include('layouts.partials.flash')

        <section class="grid gap-4 lg:grid-cols-2">
            <div class="rounded-2xl border border-zinc-800 bg-zinc-900/50 p-5 ring-1 ring-white/5">
                <h2 class="font-display text-xs font-bold uppercase tracking-wide text-zinc-500">Origine API</h2>
                @if ($apiEnabled && $apiRoot)
                    <p class="mt-3 break-all font-mono text-sm text-sky-300">{{ $apiRoot }}</p>
                    <p class="mt-2 text-xs text-zinc-500">Hôte résolu : <span class="text-zinc-400">{{ $apiHost }}</span></p>
                @else
                    <p class="mt-3 text-sm text-amber-200/90">
                        API non routée : définis <code class="rounded bg-zinc-950 px-1 text-amber-300/90">BRIGHTSHELL_ROOT_DOMAIN</code>
                        ou <code class="rounded bg-zinc-950 px-1 text-amber-300/90">BRIGHTSHELL_API_HOST</code> pour activer
                        <code class="text-zinc-500">routes/api-public.php</code> (voir <code class="text-zinc-500">bootstrap/app.php</code>).
                    </p>
                @endif
            </div>
            <div class="rounded-2xl border border-zinc-800 bg-zinc-900/50 p-5 ring-1 ring-white/5">
                <h2 class="font-display text-xs font-bold uppercase tracking-wide text-zinc-500">CORS (réponses)</h2>
                <ul class="mt-3 space-y-2 text-xs text-zinc-400">
                    @foreach ($corsHeaders as $h => $v)
                        <li><span class="font-mono text-zinc-500">{{ $h }}</span> : <span class="text-zinc-300">{{ $v }}</span></li>
                    @endforeach
                </ul>
            </div>
        </section>

        <section class="overflow-hidden rounded-2xl border border-zinc-800 bg-zinc-900/50 ring-1 ring-white/5">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-zinc-800 px-5 py-4">
                <h2 class="font-display text-sm font-bold uppercase tracking-wide text-white">Endpoints enregistrés</h2>
                <span class="text-xs text-zinc-500">{{ $endpoints->count() }} route(s) nommée(s) <code class="text-zinc-600">api.public.*</code></span>
            </div>

            @if ($endpoints->isEmpty())
                <p class="px-5 py-10 text-center text-sm text-zinc-500">
                    Aucune route API publique chargée. Vérifie la configuration du domaine API ou ajoute des routes dans
                    <code class="rounded bg-zinc-950 px-1 text-zinc-400">routes/api-public.php</code>.
                </p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[40rem] text-left text-sm">
                        <thead>
                            <tr class="border-b border-zinc-800 bg-zinc-950/50 text-[10px] font-semibold uppercase tracking-wider text-zinc-500">
                                <th class="px-5 py-3">Méthode</th>
                                <th class="px-5 py-3">Chemin</th>
                                <th class="px-5 py-3">URL</th>
                                <th class="px-5 py-3 w-[28%]">Description</th>
                                <th class="px-5 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-800/80">
                            @foreach ($endpoints as $ep)
                                <tr class="align-top text-zinc-300">
                                    <td class="px-5 py-4">
                                        @foreach ($ep['methods'] as $m)
                                            <span class="mr-1 inline-block rounded border border-zinc-700 bg-zinc-950 px-1.5 py-0.5 font-mono text-[10px] font-semibold text-sky-400">{{ $m }}</span>
                                        @endforeach
                                    </td>
                                    <td class="px-5 py-4 font-mono text-xs text-zinc-400">{{ $ep['path'] }}</td>
                                    <td class="max-w-[14rem] px-5 py-4">
                                        @if ($ep['url'])
                                            <span class="break-all font-mono text-xs text-sky-300/90">{{ $ep['url'] }}</span>
                                        @else
                                            <span class="text-xs text-zinc-600">—</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 text-xs text-zinc-500">
                                        <span class="font-semibold text-zinc-300">{{ $ep['title'] }}</span>
                                        @if ($ep['summary'] !== '')
                                            <p class="mt-1 leading-relaxed">{{ $ep['summary'] }}</p>
                                        @endif
                                        @if ($ep['format'])
                                            <p class="mt-1 font-mono text-[10px] text-zinc-600">{{ $ep['format'] }}</p>
                                        @endif
                                        @if ($ep['cache'])
                                            <p class="mt-1 font-mono text-[10px] text-zinc-600">{{ $ep['cache'] }}</p>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 text-right">
                                        <div class="flex flex-col items-end gap-2">
                                            @if ($ep['url'])
                                                <button type="button" class="copy-btn rounded-lg border border-zinc-600 px-2.5 py-1 text-[11px] font-semibold text-zinc-300 hover:bg-zinc-800" data-copy="{{ $ep['url'] }}">Copier l’URL</button>
                                                <a href="{{ $ep['url'] }}" target="_blank" rel="noopener noreferrer" class="text-[11px] font-semibold text-indigo-400 hover:text-indigo-300">Ouvrir (JSON)</a>
                                                @if (in_array('GET', $ep['methods'], true))
                                                    <code class="max-w-full break-all rounded border border-zinc-800 bg-zinc-950 px-2 py-1 text-left font-mono text-[10px] text-zinc-500" data-curl>curl -sS "{{ $ep['url'] }}"</code>
                                                @endif
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>

        <section class="rounded-2xl border border-zinc-800 border-dashed bg-zinc-950/30 p-5 ring-1 ring-white/5">
            <h2 class="font-display text-xs font-bold uppercase tracking-wide text-zinc-500">Étendre l’API</h2>
            <ol class="mt-3 list-decimal space-y-2 pl-5 text-sm text-zinc-400">
                <li>Ajoute la route dans <code class="rounded bg-zinc-900 px-1 text-xs">routes/api-public.php</code> avec un nom <code class="text-zinc-500">api.public.*</code>.</li>
                <li>Décris-la dans <code class="rounded bg-zinc-900 px-1 text-xs">config/brightshell-api.php</code> (titre, résumé, format).</li>
                <li>Si besoin CORS au-delà de GET/OPTIONS, adapte <code class="text-zinc-500">PublicApiCors</code>.</li>
            </ol>
        </section>
    </div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('.copy-btn').forEach((btn) => {
    btn.addEventListener('click', async () => {
        const t = btn.getAttribute('data-copy');
        if (!t) return;
        try {
            await navigator.clipboard.writeText(t);
            const prev = btn.textContent;
            btn.textContent = 'Copié ✓';
            setTimeout(() => { btn.textContent = prev; }, 1600);
        } catch (e) {
            btn.textContent = 'Erreur';
            setTimeout(() => { btn.textContent = 'Copier l’URL'; }, 1600);
        }
    });
});
</script>
@endpush
