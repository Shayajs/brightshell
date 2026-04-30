<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouveau message de contact</title>
</head>
<body style="margin:0;font-family:system-ui,sans-serif;background:#0c0c0f;color:#e4e4e7;line-height:1.55;padding:24px;">
    <div style="max-width:36rem;margin:0 auto;background:#18181b;border-radius:12px;padding:28px;border:1px solid #27272a;">
        <p style="margin:0 0 6px;color:#a1a1aa;font-size:0.78rem;text-transform:uppercase;letter-spacing:0.12em;">{{ $typeLabel }}</p>
        <h1 style="margin:0 0 16px;font-size:1.2rem;color:#fafafa;">Nouveau message de contact</h1>

        <table cellpadding="0" cellspacing="0" border="0" style="width:100%;font-size:0.9rem;color:#e4e4e7;">
            <tr>
                <td style="padding:6px 0;color:#a1a1aa;width:140px;">De</td>
                <td style="padding:6px 0;"><strong>{{ $message->fullName() }}</strong> &lt;<a href="mailto:{{ $message->email }}" style="color:#818cf8;text-decoration:none;">{{ $message->email }}</a>&gt;</td>
            </tr>
            @if ($message->phone)
                <tr>
                    <td style="padding:6px 0;color:#a1a1aa;">Téléphone</td>
                    <td style="padding:6px 0;">{{ $message->phone }}</td>
                </tr>
            @endif
            @if ($message->company)
                <tr>
                    <td style="padding:6px 0;color:#a1a1aa;">Société</td>
                    <td style="padding:6px 0;">{{ $message->company }}</td>
                </tr>
            @endif
            @if ($message->subject)
                <tr>
                    <td style="padding:6px 0;color:#a1a1aa;">Sujet</td>
                    <td style="padding:6px 0;">{{ $message->subject }}</td>
                </tr>
            @endif
            @if ($message->reference)
                <tr>
                    <td style="padding:6px 0;color:#a1a1aa;">Référence</td>
                    <td style="padding:6px 0;">{{ $message->reference }}</td>
                </tr>
            @endif
            @if ($message->project_title)
                <tr>
                    <td style="padding:6px 0;color:#a1a1aa;">Titre du projet</td>
                    <td style="padding:6px 0;"><strong>{{ $message->project_title }}</strong></td>
                </tr>
            @endif
            @if ($message->project_kind)
                <tr>
                    <td style="padding:6px 0;color:#a1a1aa;">Type de projet</td>
                    <td style="padding:6px 0;">{{ \App\Models\ContactMessage::projectKindChoices()[$message->project_kind] ?? $message->project_kind }}</td>
                </tr>
            @endif
            @if ($message->budget_range)
                <tr>
                    <td style="padding:6px 0;color:#a1a1aa;">Budget</td>
                    <td style="padding:6px 0;">{{ \App\Models\ContactMessage::budgetChoices()[$message->budget_range] ?? $message->budget_range }}</td>
                </tr>
            @endif
            @if ($message->deadline)
                <tr>
                    <td style="padding:6px 0;color:#a1a1aa;">Délai souhaité</td>
                    <td style="padding:6px 0;">{{ \App\Models\ContactMessage::deadlineChoices()[$message->deadline] ?? $message->deadline }}</td>
                </tr>
            @endif
            @if ($message->user)
                <tr>
                    <td style="padding:6px 0;color:#a1a1aa;">Compte</td>
                    <td style="padding:6px 0;color:#a3e635;">Utilisateur connecté · #{{ $message->user_id }}</td>
                </tr>
            @endif
        </table>

        <hr style="border:0;border-top:1px solid #27272a;margin:18px 0;">

        @if ($message->type === \App\Models\ContactMessage::TYPE_PROJECT && filled($message->body_html))
            <div style="background:#0c0c0f;border-radius:8px;padding:14px 16px;border:1px solid #27272a;color:#e4e4e7;font-size:0.92rem;">
                {!! $message->body_html !!}
            </div>
        @else
            <pre style="white-space:pre-wrap;word-wrap:break-word;background:#0c0c0f;border-radius:8px;padding:14px 16px;border:1px solid #27272a;color:#e4e4e7;font-family:inherit;font-size:0.92rem;margin:0;">{{ $message->body }}</pre>
        @endif

        @if ($message->attachments->isNotEmpty())
            <h2 style="margin:20px 0 8px;font-size:0.95rem;color:#fafafa;">Pièces jointes ({{ $message->attachments->count() }})</h2>
            <ul style="margin:0;padding-left:18px;color:#a1a1aa;font-size:0.85rem;">
                @foreach ($message->attachments as $attachment)
                    <li style="margin:3px 0;">{{ $attachment->original_name }} <span style="color:#71717a;">— {{ $attachment->humanSize() }}</span></li>
                @endforeach
            </ul>
            <p style="margin:8px 0 0;font-size:0.78rem;color:#71717a;">Téléchargez-les depuis l’admin BrightShell &gt; Messages de contact.</p>
        @endif

        <hr style="border:0;border-top:1px solid #27272a;margin:18px 0;">

        <p style="margin:0;font-size:0.78rem;color:#71717a;">
            Reçu le {{ $message->created_at->format('d/m/Y à H:i') }}
            @if ($message->ip)
                · IP {{ $message->ip }}
            @endif
        </p>
    </div>
</body>
</html>
