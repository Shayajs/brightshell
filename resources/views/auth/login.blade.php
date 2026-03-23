@extends('layouts.auth')

@section('title', 'Connexion')

@section('content')
    @php
        $registerFieldErrors = $errors->hasAny(['first_name', 'last_name', 'password_confirmation', 'member_role']);
        $oldTab = old('_auth_tab');
        $isRegisterRoute = request()->routeIs('register');
        $activeTab = ($registerFieldErrors || $oldTab === 'register' || $isRegisterRoute) ? 'register' : 'login';
    @endphp

    <a
        href="{{ \App\Support\BrightshellDomain::publicSiteUrl() }}"
        class="auth-back-link"
    >
        ← Retour au site
    </a>

    @include('layouts.partials.flash')

    <h1 class="auth-title">Espace compte</h1>
    <p class="auth-subtitle">Connexion rapide sur mobile, onglets dédiés sur desktop.</p>

    <div class="auth-tabs" data-auth-tabs data-auth-active-tab="{{ $activeTab }}">
        <div class="auth-tabs__header" role="tablist" aria-label="Connexion et inscription">
            <button type="button" class="auth-tabs__trigger" data-auth-tab-trigger="login" role="tab" aria-selected="{{ $activeTab === 'login' ? 'true' : 'false' }}">
                Connexion
            </button>
            @if (Route::has('register'))
                <button type="button" class="auth-tabs__trigger" data-auth-tab-trigger="register" role="tab" aria-selected="{{ $activeTab === 'register' ? 'true' : 'false' }}">
                    Inscription
                </button>
            @endif
        </div>

        <section class="auth-tabs__panel" data-auth-tab-panel="login" @if($activeTab !== 'login') hidden @endif>
            <form method="post" action="{{ route('login') }}" class="auth-form">
                @csrf
                <input type="hidden" name="_auth_tab" value="login">

                <div>
                    <label for="login_email" class="auth-label">E-mail</label>
                    <input
                        id="login_email"
                        type="email"
                        name="email"
                        value="{{ $activeTab === 'login' ? old('email') : '' }}"
                        required
                        autofocus
                        autocomplete="username"
                        class="auth-input"
                    >
                    @if($activeTab === 'login')
                        @error('email')
                            <p class="auth-error">{{ $message }}</p>
                        @enderror
                    @endif
                </div>

                <div>
                    <label for="login_password" class="auth-label">Mot de passe</label>
                    <input
                        id="login_password"
                        type="password"
                        name="password"
                        required
                        autocomplete="current-password"
                        class="auth-input"
                    >
                    @if($activeTab === 'login')
                        @error('password')
                            <p class="auth-error">{{ $message }}</p>
                        @enderror
                    @endif
                </div>

                <label class="auth-check">
                    <input type="checkbox" name="remember" value="1" @checked(old('remember')) class="auth-check__box">
                    Se souvenir de moi
                </label>

                <button type="submit" class="auth-submit">Se connecter</button>
            </form>
        </section>

        @if (Route::has('register'))
            <section class="auth-tabs__panel" data-auth-tab-panel="register" @if($activeTab !== 'register') hidden @endif>
                <form method="post" action="{{ route('register') }}" class="auth-form">
                    @csrf
                    <input type="hidden" name="_auth_tab" value="register">

                    <div class="grid gap-3 sm:grid-cols-2">
                        <div>
                            <label for="register_first_name" class="auth-label">Prénom</label>
                            <input
                                id="register_first_name"
                                type="text"
                                name="first_name"
                                value="{{ old('first_name') }}"
                                required
                                autocomplete="given-name"
                                class="auth-input"
                            >
                            @error('first_name')
                                <p class="auth-error">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="register_last_name" class="auth-label">Nom</label>
                            <input
                                id="register_last_name"
                                type="text"
                                name="last_name"
                                value="{{ old('last_name') }}"
                                required
                                autocomplete="family-name"
                                class="auth-input"
                            >
                            @error('last_name')
                                <p class="auth-error">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label for="register_email" class="auth-label">E-mail</label>
                        <input
                            id="register_email"
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            required
                            autocomplete="username"
                            class="auth-input"
                        >
                        @if($activeTab === 'register')
                            @error('email')
                                <p class="auth-error">{{ $message }}</p>
                            @enderror
                        @endif
                    </div>

                    <div>
                        <label for="register_password" class="auth-label">Mot de passe</label>
                        <input
                            id="register_password"
                            type="password"
                            name="password"
                            required
                            autocomplete="new-password"
                            class="auth-input"
                        >
                        @error('password')
                            <p class="auth-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="register_password_confirmation" class="auth-label">Confirmation</label>
                        <input
                            id="register_password_confirmation"
                            type="password"
                            name="password_confirmation"
                            required
                            autocomplete="new-password"
                            class="auth-input"
                        >
                        @error('password_confirmation')
                            <p class="auth-error">{{ $message }}</p>
                        @enderror
                    </div>

                    @include('auth.partials.member-role-fields')

                    <button type="submit" class="auth-submit">S’inscrire</button>
                </form>
            </section>
        @endif
    </div>
@endsection
