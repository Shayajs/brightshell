@extends('layouts.auth')

@section('title', 'Inscription')

@section('content')
    @php
        $registerFieldErrors = $errors->hasAny(['first_name', 'last_name', 'password_confirmation', 'member_role']);
        $oldTab = old('_auth_tab');
        $activeTab = ($registerFieldErrors || $oldTab === 'register') ? 'register' : 'register';
    @endphp

    <a href="{{ \App\Support\BrightshellDomain::publicSiteUrl() }}" class="auth-back-link">← Retour au site</a>

    @include('layouts.partials.flash')

    <h1 class="auth-title">Espace compte</h1>
    <p class="auth-subtitle">Connexion rapide sur mobile, onglets dédiés sur desktop.</p>

    <div class="auth-tabs" data-auth-tabs data-auth-active-tab="{{ $activeTab }}">
        <div class="auth-tabs__header" role="tablist" aria-label="Connexion et inscription">
            <button type="button" class="auth-tabs__trigger" data-auth-tab-trigger="login" role="tab" aria-selected="false">
                Connexion
            </button>
            <button type="button" class="auth-tabs__trigger" data-auth-tab-trigger="register" role="tab" aria-selected="true">
                Inscription
            </button>
        </div>

        <section class="auth-tabs__panel" data-auth-tab-panel="login" hidden>
            <form method="post" action="{{ route('login') }}" class="auth-form">
                @csrf
                <input type="hidden" name="_auth_tab" value="login">
                <div>
                    <label for="login_email" class="auth-label">E-mail</label>
                    <input id="login_email" type="email" name="email" required autocomplete="username" class="auth-input">
                </div>
                <div>
                    <label for="login_password" class="auth-label">Mot de passe</label>
                    <input id="login_password" type="password" name="password" required autocomplete="current-password" class="auth-input">
                </div>
                <label class="auth-check">
                    <input type="checkbox" name="remember" value="1" class="auth-check__box">
                    Se souvenir de moi
                </label>
                <button type="submit" class="auth-submit">Se connecter</button>
            </form>
        </section>

        <section class="auth-tabs__panel" data-auth-tab-panel="register">
            <form method="post" action="{{ route('register') }}" class="auth-form">
                @csrf
                <input type="hidden" name="_auth_tab" value="register">
                @if (! empty($projectInvitation))
                    <input type="hidden" name="project_invitation_token" value="{{ $projectInvitation->token }}">
                    <div class="mb-4 rounded-xl border border-cyan-500/30 bg-cyan-500/10 px-4 py-3 text-sm text-cyan-100">
                        <p class="font-semibold">Invitation au projet « {{ $projectInvitation->project->name }} »</p>
                        <p class="mt-1 text-cyan-200/90">Inscrivez-vous avec l’e-mail <strong>{{ $projectInvitation->email }}</strong>. Le rôle <strong>client</strong> sera attribué automatiquement.</p>
                    </div>
                @endif

                <div class="grid gap-3 sm:grid-cols-2">
                    <div>
                        <label for="register_first_name" class="auth-label">Prénom</label>
                        <input id="register_first_name" type="text" name="first_name" value="{{ old('first_name') }}" required autocomplete="given-name" class="auth-input">
                        @error('first_name')
                            <p class="auth-error">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="register_last_name" class="auth-label">Nom</label>
                        <input id="register_last_name" type="text" name="last_name" value="{{ old('last_name') }}" required autocomplete="family-name" class="auth-input">
                        @error('last_name')
                            <p class="auth-error">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                <div>
                    <label for="register_email" class="auth-label">E-mail</label>
                    <input id="register_email" type="email" name="email" value="{{ old('email', isset($projectInvitation) ? $projectInvitation->email : '') }}" required autocomplete="username" class="auth-input" @if(! empty($projectInvitation)) readonly @endif>
                    @error('email')
                        <p class="auth-error">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="register_password" class="auth-label">Mot de passe</label>
                    <input id="register_password" type="password" name="password" required autocomplete="new-password" class="auth-input">
                    @error('password')
                        <p class="auth-error">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="register_password_confirmation" class="auth-label">Confirmation</label>
                    <input id="register_password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" class="auth-input">
                    @error('password_confirmation')
                        <p class="auth-error">{{ $message }}</p>
                    @enderror
                </div>

                @if (empty($projectInvitation))
                    @include('auth.partials.member-role-fields')
                @endif

                <button type="submit" class="auth-submit">S’inscrire</button>
            </form>
        </section>
    </div>
@endsection
