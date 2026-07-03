<h1 style="margin:0 0 14px;font-size:20px;line-height:1.3;color:#ffffff;">
    Invitation visioconférence BrightShell
</h1>

<p style="margin:0 0 12px;color:#c7d2fe;font-size:14px;line-height:1.6;">
    Vous êtes invité(e) à rejoindre la session <strong>{{ $roomTitle }}</strong>.
</p>

@if (!empty($projectName))
    <p style="margin:0 0 16px;color:#a5b4fc;font-size:13px;line-height:1.6;">
        Projet associé : {{ $projectName }}
    </p>
@endif

<p style="margin:0 0 22px;">
    <a href="{{ $joinUrl }}" style="display:inline-block;padding:10px 16px;border-radius:10px;background:#c026d3;color:#fff;text-decoration:none;font-weight:700;font-size:14px;">
        Rejoindre la visio
    </a>
</p>

<p style="margin:0;color:#94a3b8;font-size:12px;line-height:1.5;">
    Pas besoin de compte pour participer. Si vous avez déjà un compte BrightShell, l’invitation apparaît aussi dans vos réglages.
</p>
