<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>BrightShell Notification Bridge</title>
</head>
<body>
<script>
    (function () {
        const ROOT_DOMAIN = @json(\App\Support\BrightshellDomain::effectiveRoot());
        const canUseDomainCheck = typeof ROOT_DOMAIN === 'string' && ROOT_DOMAIN.length > 0;

        function isAllowedOrigin(origin) {
            try {
                const url = new URL(origin);
                if (!canUseDomainCheck) return false;
                return url.hostname === ROOT_DOMAIN || url.hostname.endsWith('.' + ROOT_DOMAIN);
            } catch (_) {
                return false;
            }
        }

        function reply(target, origin, data) {
            target?.postMessage(data, origin);
        }

        async function handleMessage(event) {
            const data = event?.data;
            if (!data || data.__bsNotifBridge !== true || !isAllowedOrigin(event.origin)) {
                return;
            }

            if (data.action === 'status') {
                reply(event.source, event.origin, {
                    __bsNotifBridge: true,
                    requestId: data.requestId,
                    ok: true,
                    permission: ('Notification' in window) ? Notification.permission : 'unsupported',
                });
                return;
            }

            if (!('Notification' in window)) {
                reply(event.source, event.origin, {
                    __bsNotifBridge: true,
                    requestId: data.requestId,
                    ok: false,
                    error: 'unsupported',
                });
                return;
            }

            if (data.action === 'requestPermission') {
                try {
                    const permission = await Notification.requestPermission();
                    reply(event.source, event.origin, {
                        __bsNotifBridge: true,
                        requestId: data.requestId,
                        ok: true,
                        permission,
                    });
                } catch (_) {
                    reply(event.source, event.origin, {
                        __bsNotifBridge: true,
                        requestId: data.requestId,
                        ok: false,
                        error: 'permission_failed',
                        permission: Notification.permission,
                    });
                }
                return;
            }

            if (data.action === 'notify') {
                if (Notification.permission !== 'granted') {
                    reply(event.source, event.origin, {
                        __bsNotifBridge: true,
                        requestId: data.requestId,
                        ok: false,
                        error: 'permission_not_granted',
                        permission: Notification.permission,
                    });
                    return;
                }

                try {
                    const title = String(data.title || 'BrightShell');
                    const options = typeof data.options === 'object' && data.options !== null ? data.options : {};
                    new Notification(title, options);
                    reply(event.source, event.origin, {
                        __bsNotifBridge: true,
                        requestId: data.requestId,
                        ok: true,
                        permission: Notification.permission,
                    });
                } catch (_) {
                    reply(event.source, event.origin, {
                        __bsNotifBridge: true,
                        requestId: data.requestId,
                        ok: false,
                        error: 'notify_failed',
                        permission: Notification.permission,
                    });
                }
            }
        }

        window.addEventListener('message', handleMessage);
    })();
</script>
</body>
</html>
