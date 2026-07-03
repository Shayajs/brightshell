const app = document.getElementById('visio-room-app');

if (app) {
    const stageEl = document.getElementById('visio-livekit-stage');
    const statusEl = document.getElementById('visio-status');
    const docEl = document.getElementById('visio-shared-doc');
    const pricesEl = document.getElementById('visio-prices');
    const shareScreenBtn = document.getElementById('visio-share-screen');
    const shareDocForm = document.getElementById('visio-share-doc-form');
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    const endpoint = {
        context: app.dataset.contextUrl || '',
        token: app.dataset.tokenUrl || '',
        heartbeat: app.dataset.heartbeatUrl || '',
        updateContext: app.dataset.updateContextUrl || '',
    };

    let livekitRoom = null;

    const requestJson = async (url, opts = {}) => {
        const response = await fetch(url, {
            credentials: 'include',
            headers: {
                'X-CSRF-TOKEN': csrf,
                'Accept': 'application/json',
                ...(opts.headers || {}),
            },
            ...opts,
        });
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }
        return await response.json();
    };

    const renderPrices = (prices = {}) => {
        const items = Array.isArray(prices.items) ? prices.items : [];
        const totals = prices.totals || { ht: 0, ttc: 0 };
        if (!items.length) {
            pricesEl.innerHTML = '<p class="text-zinc-500">Aucune ligne devis.</p>';
            return;
        }
        pricesEl.innerHTML = `
            <div class="space-y-1">
                ${items.map((item) => `
                    <div class="flex items-center justify-between rounded border border-zinc-800 bg-zinc-950/60 px-2 py-1">
                        <span>${item.label}</span>
                        <span class="font-mono">${Number(item.line_total_ttc || 0).toFixed(2)} €</span>
                    </div>
                `).join('')}
            </div>
            <div class="mt-2 border-t border-zinc-800 pt-2 text-zinc-300">
                <div class="flex items-center justify-between"><span>Total HT</span><span class="font-mono">${Number(totals.ht || 0).toFixed(2)} €</span></div>
                <div class="mt-1 flex items-center justify-between"><span>Total TTC</span><span class="font-mono text-cyan-300">${Number(totals.ttc || 0).toFixed(2)} €</span></div>
            </div>
        `;
    };

    const renderSharedDoc = (sharedDoc = {}) => {
        if (!sharedDoc.html) {
            docEl.innerHTML = '<p class="text-zinc-500">Aucun document partagé pour l’instant.</p>';
            return;
        }
        docEl.innerHTML = sharedDoc.html;
    };

    const pollContext = async () => {
        try {
            const payload = await requestJson(endpoint.context);
            renderPrices(payload?.data?.prices);
            renderSharedDoc(payload?.data?.shared_document);
        } catch (error) {
            pricesEl.innerHTML = '<p class="text-red-300">Impossible de charger le devis en direct.</p>';
        }
    };

    const connectLiveKit = async () => {
        if (!window.LivekitClient) {
            statusEl.textContent = 'LiveKit SDK non détecté (ajoute le SDK côté frontend).';
            return;
        }
        try {
            const tokenPayload = await requestJson(endpoint.token, { method: 'POST' });
            const wsUrl = tokenPayload?.data?.ws_url;
            const accessToken = tokenPayload?.data?.token;
            if (!wsUrl || !accessToken) {
                statusEl.textContent = 'Configuration LiveKit incomplète.';
                return;
            }

            livekitRoom = new window.LivekitClient.Room();
            await livekitRoom.connect(wsUrl, accessToken);
            statusEl.textContent = 'Connecté à la salle LiveKit.';
            stageEl.textContent = 'Connexion LiveKit active. Les flux vidéo apparaîtront ici avec votre intégration UI.';
        } catch (e) {
            statusEl.textContent = 'Échec de connexion LiveKit.';
        }
    };

    shareScreenBtn?.addEventListener('click', async () => {
        if (!livekitRoom?.localParticipant) {
            statusEl.textContent = 'Connexion LiveKit requise.';
            return;
        }
        try {
            await livekitRoom.localParticipant.setScreenShareEnabled(true);
            statusEl.textContent = 'Partage écran actif.';
        } catch (_) {
            statusEl.textContent = 'Partage écran refusé ou indisponible.';
        }
    });

    shareDocForm?.addEventListener('submit', async (event) => {
        event.preventDefault();
        const fd = new FormData(shareDocForm);
        const fileId = String(fd.get('student_subject_file_id') || '').trim();
        if (!fileId) return;
        await requestJson(endpoint.updateContext, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ student_subject_file_id: Number(fileId) }),
        });
        await pollContext();
    });

    setInterval(() => {
        requestJson(endpoint.heartbeat, { method: 'POST' }).catch(() => {});
    }, 15000);
    setInterval(pollContext, 1000);

    pollContext();
    connectLiveKit();
}
