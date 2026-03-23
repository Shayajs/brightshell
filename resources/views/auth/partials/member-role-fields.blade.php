@php
    $memberRoleOld = old('member_role', 'client');
@endphp
<fieldset class="auth-role-fieldset">
    <legend class="auth-role-legend" id="auth-member-role-legend">Type de compte</legend>
    <p class="auth-role-hint">Choisissez comment vous utiliserez BrightShell — vous pourrez demander d’autres accès plus tard si besoin.</p>
    <div class="auth-role-options" role="radiogroup" aria-labelledby="auth-member-role-legend">
        <label class="auth-role-option">
            <input
                type="radio"
                name="member_role"
                value="client"
                class="auth-role-option__input"
                @checked($memberRoleOld === 'client')
                required
            >
            <span class="auth-role-option__card">
                <span class="auth-role-option__title">Client</span>
                <span class="auth-role-option__desc">Espace client, suivi de prestations.</span>
            </span>
        </label>
        <label class="auth-role-option">
            <input
                type="radio"
                name="member_role"
                value="student"
                class="auth-role-option__input"
                @checked($memberRoleOld === 'student')
            >
            <span class="auth-role-option__card">
                <span class="auth-role-option__title">Élève</span>
                <span class="auth-role-option__desc">Cours, matières et contenus pédagogiques.</span>
            </span>
        </label>
        <label class="auth-role-option">
            <input
                type="radio"
                name="member_role"
                value="collaborator"
                class="auth-role-option__input"
                @checked($memberRoleOld === 'collaborator')
            >
            <span class="auth-role-option__card">
                <span class="auth-role-option__title">Collaborateur</span>
                <span class="auth-role-option__desc">Espace collaborateur BrightShell.</span>
            </span>
        </label>
    </div>
    @error('member_role')
        <p class="auth-error">{{ $message }}</p>
    @enderror
</fieldset>
