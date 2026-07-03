<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouvelle demande de rendez-vous</title>
</head>
<body style="margin:0;font-family:system-ui,sans-serif;background:#0c0c0f;color:#e4e4e7;line-height:1.55;padding:24px;">
    <div style="max-width:36rem;margin:0 auto;background:#18181b;border-radius:12px;padding:28px;border:1px solid #27272a;">
        <p style="margin:0 0 6px;color:#a1a1aa;font-size:0.78rem;text-transform:uppercase;letter-spacing:0.12em;">Rendez-vous</p>
        <h1 style="margin:0 0 16px;font-size:1.2rem;color:#fafafa;">Nouvelle demande de rendez-vous</h1>

        @if ($slot)
            <p style="margin:0 0 18px;padding:12px 14px;background:#0c0c0f;border-radius:8px;border:1px solid #27272a;color:#c4b5fd;font-weight:600;">
                {{ $slot->formattedRange() }}
            </p>
        @endif

        <table cellpadding="0" cellspacing="0" border="0" style="width:100%;font-size:0.9rem;color:#e4e4e7;">
            <tr>
                <td style="padding:6px 0;color:#a1a1aa;width:140px;">De</td>
                <td style="padding:6px 0;"><strong>{{ $booking->fullName() }}</strong> &lt;<a href="mailto:{{ $booking->email }}" style="color:#818cf8;text-decoration:none;">{{ $booking->email }}</a>&gt;</td>
            </tr>
            @if ($booking->phone)
                <tr>
                    <td style="padding:6px 0;color:#a1a1aa;">Téléphone</td>
                    <td style="padding:6px 0;">{{ $booking->phone }}</td>
                </tr>
            @endif
        </table>

        @if ($booking->message)
            <hr style="border:0;border-top:1px solid #27272a;margin:18px 0;">
            <pre style="white-space:pre-wrap;word-wrap:break-word;background:#0c0c0f;border-radius:8px;padding:14px 16px;border:1px solid #27272a;color:#e4e4e7;font-family:inherit;font-size:0.92rem;margin:0;">{{ $booking->message }}</pre>
        @endif

        <hr style="border:0;border-top:1px solid #27272a;margin:18px 0;">

        <p style="margin:0;font-size:0.78rem;color:#71717a;">
            Reçu le {{ $booking->created_at->format('d/m/Y à H:i') }}
            @if ($booking->ip)
                · IP {{ $booking->ip }}
            @endif
        </p>
    </div>
</body>
</html>
