function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
}

function maxMessageId(container) {
    let max = 0;
    container.querySelectorAll('.collab-msg[data-id]').forEach((el) => {
        const id = parseInt(el.getAttribute('data-id') || '0', 10);
        if (!Number.isNaN(id) && id > max) {
            max = id;
        }
    });
    return max;
}

function appendMessages(container, items) {
    items.forEach((m) => {
        if (container.querySelector(`.collab-msg[data-id="${m.id}"]`)) {
            return;
        }
        const article = document.createElement('article');
        article.className =
            'collab-msg rounded-xl border border-zinc-800/80 bg-zinc-950/50 px-4 py-3 sm:px-5 sm:py-4 xl:max-w-5xl xl:rounded-2xl';
        article.dataset.id = String(m.id);
        const when = m.created_at ? new Date(m.created_at).toLocaleString('fr-FR') : '';
        const who = m.user?.name ?? 'Compte supprimé';
        const safeBody = document.createElement('p');
        safeBody.className =
            'mt-2 whitespace-pre-wrap text-sm leading-relaxed text-zinc-200 lg:text-base';
        safeBody.textContent = m.body;
        article.innerHTML = `
            <div class="flex flex-wrap items-baseline justify-between gap-2 text-[11px] text-zinc-500 lg:text-xs">
                <span class="font-semibold text-zinc-300"></span>
                <time></time>
            </div>
        `;
        article.querySelector('.font-semibold').textContent = who;
        const timeEl = article.querySelector('time');
        if (timeEl) {
            timeEl.dateTime = m.created_at || '';
            timeEl.textContent = when;
        }
        article.appendChild(safeBody);
        container.appendChild(article);
    });
    container.scrollTop = container.scrollHeight;
}

function initCollabTeamChat() {
    const root = document.getElementById('collab-team-chat');
    if (!root) {
        return;
    }

    const list = document.getElementById('collab-team-messages-list');
    const form = document.getElementById('collab-team-message-form');
    const errEl = document.getElementById('collab-team-msg-error');
    const pollUrl = root.dataset.pollUrl;
    const storeUrl = root.dataset.storeUrl;

    if (!list || !form || !pollUrl || !storeUrl) {
        return;
    }

    let afterId = parseInt(root.dataset.afterId || '0', 10) || maxMessageId(list);

    const showErr = (msg) => {
        if (!errEl) {
            return;
        }
        errEl.textContent = msg;
        errEl.classList.remove('hidden');
    };
    const hideErr = () => {
        if (!errEl) {
            return;
        }
        errEl.classList.add('hidden');
        errEl.textContent = '';
    };

    const poll = async () => {
        if (document.visibilityState !== 'visible') {
            return;
        }
        try {
            const url = new URL(pollUrl, window.location.origin);
            url.searchParams.set('after_id', String(afterId));
            const res = await fetch(url.toString(), {
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
            });
            if (!res.ok) {
                return;
            }
            const data = await res.json();
            const msgs = data.messages || [];
            if (msgs.length) {
                appendMessages(list, msgs);
                afterId = maxMessageId(list);
                root.dataset.afterId = String(afterId);
            }
        } catch {
            /* ignore transient network errors */
        }
    };

    window.setInterval(poll, 1000);

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        hideErr();
        const ta = form.querySelector('#collab-msg-body');
        const body = (ta?.value || '').trim();
        if (!body) {
            return;
        }
        try {
            const res = await fetch(storeUrl, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken(),
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
                body: JSON.stringify({ body }),
            });
            const data = await res.json().catch(() => ({}));
            if (!res.ok) {
                const firstErr =
                    data.errors && typeof data.errors === 'object'
                        ? Object.values(data.errors).flat()[0]
                        : null;
                showErr(firstErr || data.message || "Envoi impossible.");
                return;
            }
            if (data.message) {
                appendMessages(list, [data.message]);
                afterId = maxMessageId(list);
                root.dataset.afterId = String(afterId);
            }
            if (ta) {
                ta.value = '';
            }
        } catch {
            showErr('Erreur réseau.');
        }
    });

    list.scrollTop = list.scrollHeight;
}

document.addEventListener('DOMContentLoaded', initCollabTeamChat);
