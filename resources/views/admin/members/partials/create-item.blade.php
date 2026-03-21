<div class="member-block rounded-2xl border border-zinc-800 bg-zinc-900/50 p-5 ring-1 ring-white/5 space-y-4">
    <div class="flex items-center justify-between gap-3">
        <p class="font-display text-xs font-bold uppercase tracking-wide text-zinc-400">Membre <span class="member-num">{{ is_numeric($i) ? $i + 1 : '' }}</span></p>
        <button
            type="button"
            data-remove-member
            class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg border border-zinc-700 text-zinc-500 transition hover:border-red-500/40 hover:bg-red-500/10 hover:text-red-400"
            aria-label="Retirer ce membre"
        >
            <svg class="h-3.5 w-3.5 pointer-events-none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
    </div>

    <div class="grid gap-3 sm:grid-cols-2">
        <div>
            <label class="block text-[11px] font-semibold uppercase tracking-[0.14em] text-zinc-500">Nom complet *</label>
            <input
                type="text"
                name="members[{{ $i }}][name]"
                value="{{ old("members.{$i}.name") }}"
                required
                placeholder="Lucas Espinar"
                class="mt-1.5 w-full rounded-lg border border-zinc-700 bg-zinc-950/80 px-3.5 py-2.5 text-sm text-zinc-100 placeholder:text-zinc-600 focus:border-indigo-500/50 focus:outline-none focus:ring-2 focus:ring-indigo-500/25"
            >
        </div>
        <div>
            <label class="block text-[11px] font-semibold uppercase tracking-[0.14em] text-zinc-500">E-mail *</label>
            <input
                type="email"
                name="members[{{ $i }}][email]"
                value="{{ old("members.{$i}.email") }}"
                required
                placeholder="contact@example.com"
                class="mt-1.5 w-full rounded-lg border border-zinc-700 bg-zinc-950/80 px-3.5 py-2.5 text-sm text-zinc-100 placeholder:text-zinc-600 focus:border-indigo-500/50 focus:outline-none focus:ring-2 focus:ring-indigo-500/25"
            >
        </div>
        <div>
            <label class="block text-[11px] font-semibold uppercase tracking-[0.14em] text-zinc-500">Mot de passe <span class="normal-case font-normal text-zinc-600">(vide = aléatoire)</span></label>
            <input
                type="password"
                name="members[{{ $i }}][password]"
                placeholder="••••••••"
                autocomplete="new-password"
                class="mt-1.5 w-full rounded-lg border border-zinc-700 bg-zinc-950/80 px-3.5 py-2.5 text-sm text-zinc-100 placeholder:text-zinc-600 focus:border-indigo-500/50 focus:outline-none focus:ring-2 focus:ring-indigo-500/25"
            >
        </div>
        <div>
            <label class="block text-[11px] font-semibold uppercase tracking-[0.14em] text-zinc-500">Confirmation MDP</label>
            <input
                type="password"
                name="members[{{ $i }}][password_confirmation]"
                placeholder="••••••••"
                autocomplete="new-password"
                class="mt-1.5 w-full rounded-lg border border-zinc-700 bg-zinc-950/80 px-3.5 py-2.5 text-sm text-zinc-100 placeholder:text-zinc-600 focus:border-indigo-500/50 focus:outline-none focus:ring-2 focus:ring-indigo-500/25"
            >
        </div>
    </div>

    {{-- Rôles --}}
    <div>
        <p class="mb-2 text-[11px] font-semibold uppercase tracking-[0.14em] text-zinc-500">Rôles</p>
        <div class="flex flex-wrap gap-2">
            @foreach ($allRoles as $role)
                <label class="flex cursor-pointer items-center gap-2 rounded-lg border border-zinc-700 bg-zinc-950/50 px-3 py-2 text-xs font-medium text-zinc-400 transition hover:border-indigo-500/30 has-[:checked]:border-indigo-500/40 has-[:checked]:bg-indigo-500/10 has-[:checked]:text-indigo-300">
                    <input
                        type="checkbox"
                        name="members[{{ $i }}][roles][]"
                        value="{{ $role->id }}"
                        class="h-3.5 w-3.5 rounded border-zinc-600 bg-zinc-950 text-indigo-500 focus:ring-indigo-500/40"
                    >
                    {{ ucfirst($role->slug) }}
                </label>
            @endforeach
            <label class="flex cursor-pointer items-center gap-2 rounded-lg border border-zinc-700 bg-zinc-950/50 px-3 py-2 text-xs font-medium text-zinc-400 transition hover:border-amber-500/30 has-[:checked]:border-amber-500/40 has-[:checked]:bg-amber-500/10 has-[:checked]:text-amber-300">
                <input
                    type="checkbox"
                    name="members[{{ $i }}][is_admin]"
                    value="1"
                    class="h-3.5 w-3.5 rounded border-zinc-600 bg-zinc-950 text-amber-500 focus:ring-amber-500/40"
                >
                Admin système
            </label>
        </div>
    </div>
</div>
