<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invitation projet</title>
</head>
<body style="margin:0;font-family:system-ui,sans-serif;background:#0c0c0f;color:#e4e4e7;line-height:1.5;padding:24px;">
    <div style="max-width:32rem;margin:0 auto;background:#18181b;border-radius:12px;padding:28px;border:1px solid #27272a;">
        <h1 style="margin:0 0 12px;font-size:1.25rem;color:#fafafa;">Invitation au projet</h1>
        <p style="margin:0 0 16px;color:#a1a1aa;font-size:0.95rem;">Vous avez été invité à rejoindre le projet <strong style="color:#e4e4e7;">{{ $projectName }}</strong> sur BrightShell ({{ $invitedEmail }}).</p>
        <p style="margin:0 0 20px;">
            <a href="{{ $acceptUrl }}" style="display:inline-block;background:#4f46e5;color:#fff;text-decoration:none;padding:10px 18px;border-radius:8px;font-weight:600;font-size:0.9rem;">Accepter l’invitation</a>
        </p>
        <p style="margin:0 0 12px;color:#a1a1aa;font-size:0.875rem;">Pas encore de compte ? Inscrivez-vous avec la même adresse e-mail :</p>
        <p style="margin:0 0 20px;">
            <a href="{{ $registerUrl }}" style="color:#818cf8;font-size:0.875rem;">{{ $registerUrl }}</a>
        </p>
        <p style="margin:0;font-size:0.75rem;color:#71717a;">Si vous n’êtes pas à l’origine de cette demande, ignorez ce message.</p>
    </div>
</body>
</html>
