@extends('layouts.auth')

@section('title', 'Confirmez votre e-mail')

@section('auth_panel_class', 'auth-shell__panel--wide')

@section('content')
    <a href="{{ \App\Support\BrightshellDomain::publicSiteUrl() }}" class="auth-back-link">
        ← Retour au site
    </a>

    @include('layouts.partials.flash')

    @if (session('status') === 'verification-link-sent')
        <div class="mb-6 rounded-xl border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200 ring-1 ring-emerald-500/20" role="status">
            Un nouveau lien de confirmation vient d’être envoyé à <strong class="font-mono text-emerald-100">{{ $user->email }}</strong>.
        </div>
    @endif

    @if (session('ticket_created'))
        <div class="mb-6 rounded-xl border border-indigo-500/30 bg-indigo-500/10 px-4 py-3 text-sm text-indigo-200 ring-1 ring-indigo-500/20" role="status">
            Votre demande a été enregistrée. Le service technique vous recontactera si besoin.
        </div>
    @endif

    <h1 class="auth-title">Confirmez votre adresse e-mail</h1>
    <p class="auth-subtitle">
        BrightShell vous a envoyé un message de confirmation à <span class="font-mono text-zinc-200">{{ $user->email }}</span>.
        Ouvrez-le et cliquez sur le bouton pour finaliser votre inscription.
        <strong class="font-semibold text-zinc-200">Pensez à vérifier le dossier SPAM ou les courriers indésirables</strong> si vous ne voyez rien dans la boîte de réception.
    </p>

    <div class="mt-8 space-y-8">
        <div class="rounded-xl border border-zinc-800 bg-zinc-950/40 p-5 ring-1 ring-white/5">
            <h2 class="font-display text-sm font-bold uppercase tracking-wider text-zinc-400">Renvoyer le mail</h2>
            <p class="mt-2 text-sm text-zinc-500">Toujours rien reçu ? Vous pouvez demander un nouveau lien.</p>
            <form method="post" action="{{ route('verification.send') }}" class="auth-form mt-4">
                @csrf
                <button type="submit" class="auth-submit w-full sm:w-auto">Renvoyer l’e-mail de confirmation</button>
            </form>
        </div>

        <div class="rounded-xl border border-zinc-800 bg-zinc-950/40 p-5 ring-1 ring-white/5">
            <h2 class="font-display text-sm font-bold uppercase tracking-wider text-zinc-400">Contacter le service technique</h2>
            <p class="mt-2 text-sm text-zinc-500">Si votre fournisseur bloque nos envois, ouvrez un ticket ou écrivez-nous directement.</p>
            <div class="mt-4 flex flex-wrap gap-3">
                @if (! empty($supportEmail))
                    <a
                        href="mailto:{{ $supportEmail }}?subject={{ rawurlencode('Confirmation e-mail BrightShell') }}"
                        class="inline-flex items-center justify-center rounded-lg border border-zinc-600 bg-zinc-900 px-4 py-2.5 text-sm font-semibold text-zinc-200 transition hover:border-indigo-500/40 hover:text-white"
                    >
                        Écrire à {{ $supportEmail }}
                    </a>
                @endif
                <form method="post" action="{{ route('verification.support-ticket') }}" class="flex min-w-0 flex-1 flex-col gap-3 sm:min-w-[16rem]">
                    @csrf
                    <label for="ticket_message" class="auth-label">Ou envoyer une demande depuis ici</label>
                    <textarea
                        id="ticket_message"
                        name="message"
                        rows="3"
                        class="auth-input resize-y"
                        placeholder="Décris brièvement le problème (fournisseur mail, message d’erreur…)"
                    >{{ old('message') }}</textarea>
                    @error('message')
                        <p class="auth-error">{{ $message }}</p>
                    @enderror
                    <button type="submit" class="auth-submit">Envoyer la demande</button>
                </form>
            </div>
        </div>

        @if ($mailboxFromAddress)
            <div class="rounded-xl border border-amber-500/20 bg-amber-500/5 p-5 ring-1 ring-amber-500/10">
                <h2 class="font-display text-sm font-bold uppercase tracking-amber-200/80 text-amber-200/90">Confirmation inverse (test)</h2>
                <p class="mt-2 text-sm text-zinc-400">
                    @if (config('mailbox.verify_reverse_require_token_in_subject', true))
                        Envoie un e-mail <strong class="text-zinc-200">depuis {{ $user->email }}</strong> vers l’adresse ci-dessous.
                        Le <strong class="text-zinc-200">sujet</strong> doit contenir exactement ce code (copie-colle) :
                    @else
                        En mode test souple : envoie un message simple <strong class="text-zinc-200">depuis {{ $user->email }}</strong> vers l’adresse ci-dessous. Nous traitons la boîte régulièrement.
                    @endif
                </p>
                <p class="mt-3 break-all rounded-lg border border-zinc-700 bg-zinc-900/80 px-3 py-2 font-mono text-xs text-indigo-200">{{ $reverseToken }}</p>
                <p class="mt-2 text-xs text-zinc-500">Destinataire : <span class="font-mono text-zinc-400">{{ $mailboxFromAddress }}</span></p>
                <p class="mt-2 text-[11px] text-zinc-600">
                    Si l’expéditeur correspond à votre compte non confirmé, le compte sera validé automatiquement après traitement (commande planifiée ou cron).
                </p>
            </div>
        @endif
    </div>

    <div class="mt-10 flex flex-wrap items-center justify-between gap-4 border-t border-zinc-800/80 pt-6 text-sm">
        <form method="post" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="text-zinc-500 underline decoration-zinc-600 underline-offset-2 transition hover:text-zinc-300">
                Se déconnecter (changer de compte)
            </button>
        </form>
        <span class="text-xs text-zinc-600">Compte : {{ $user->email }}</span>
    </div>
@endsection
