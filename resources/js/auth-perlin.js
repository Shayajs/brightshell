/**
 * Fond auth : Web Worker + OffscreenCanvas quand c’est possible (calcul + dessin hors thread UI).
 * Repli : auth-perlin-main-thread.js
 */
import PerlinWorker from './auth-perlin.worker.js?worker';

const canvas = document.getElementById('auth-perlin-canvas');

function getDimensions() {
    const dprCap = 1.5;
    const ratio = Math.min(window.devicePixelRatio || 1, dprCap);
    const width = window.innerWidth;
    const height = window.innerHeight;
    return {
        width,
        height,
        dpr: ratio,
        pixelWidth: Math.floor(width * ratio),
        pixelHeight: Math.floor(height * ratio),
    };
}

function syncAuthTabs() {
    const root = document.querySelector('[data-auth-tabs]');
    if (!root) return;

    const buttons = Array.from(root.querySelectorAll('[data-auth-tab-trigger]'));
    const panels = Array.from(root.querySelectorAll('[data-auth-tab-panel]'));
    const registerPanel = root.querySelector('[data-auth-tab-panel="register"]');

    const prefersReducedMotion = () =>
        typeof window.matchMedia === 'function' &&
        window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    const clearRegisterEnter = () => {
        if (!registerPanel) return;
        registerPanel.classList.remove('auth-tabs__panel--enter', 'auth-tabs__panel--enter-active');
    };

    const setTab = (tab, options = {}) => {
        const { fromClick = false } = options;
        const prev = root.dataset.authActiveTab || 'login';

        root.dataset.authActiveTab = tab;
        for (const button of buttons) {
            const isActive = button.dataset.authTabTrigger === tab;
            button.setAttribute('aria-selected', isActive ? 'true' : 'false');
        }
        for (const panel of panels) {
            panel.hidden = panel.dataset.authTabPanel !== tab;
        }

        const shouldAnimateRegister =
            fromClick &&
            prev === 'login' &&
            tab === 'register' &&
            registerPanel &&
            !prefersReducedMotion();

        if (shouldAnimateRegister) {
            clearRegisterEnter();
            void registerPanel.offsetWidth;
            registerPanel.classList.add('auth-tabs__panel--enter');
            window.requestAnimationFrame(() => {
                registerPanel.classList.add('auth-tabs__panel--enter-active');
            });
            window.setTimeout(() => {
                clearRegisterEnter();
            }, 520);
        }
    };

    for (const button of buttons) {
        button.addEventListener('click', () => {
            setTab(button.dataset.authTabTrigger, { fromClick: true });
        });
    }

    setTab(root.dataset.authActiveTab || 'login', { fromClick: false });
}

/**
 * Pause uniquement quand l’onglet est en arrière-plan (économie CPU).
 * Plus de pause à la saisie : le worker porte le dessin, l’anim reste fluide et « classe ».
 *
 * @param {Worker} worker
 */
function postPausedToWorker(worker) {
    worker.postMessage({ type: 'paused', paused: document.hidden });
}

if (canvas) {
    syncAuthTabs();

    const useWorker =
        typeof canvas.transferControlToOffscreen === 'function' && typeof Worker !== 'undefined';

    if (useWorker) {
        try {
            const offscreen = canvas.transferControlToOffscreen();
            const worker = new PerlinWorker();
            const dim = getDimensions();
            worker.postMessage(
                {
                    type: 'init',
                    canvas: offscreen,
                    width: dim.width,
                    height: dim.height,
                    dpr: dim.dpr,
                    pixelWidth: dim.pixelWidth,
                    pixelHeight: dim.pixelHeight,
                },
                [offscreen]
            );

            postPausedToWorker(worker);
            document.addEventListener('visibilitychange', () => postPausedToWorker(worker));

            let resizeTimer = null;
            window.addEventListener('resize', () => {
                window.clearTimeout(resizeTimer);
                resizeTimer = window.setTimeout(() => {
                    const d = getDimensions();
                    worker.postMessage({
                        type: 'resize',
                        width: d.width,
                        height: d.height,
                        dpr: d.dpr,
                        pixelWidth: d.pixelWidth,
                        pixelHeight: d.pixelHeight,
                    });
                }, 120);
            });
        } catch {
            import('./auth-perlin-main-thread.js').then((m) => m.default(canvas));
        }
    } else {
        import('./auth-perlin-main-thread.js').then((m) => m.default(canvas));
    }
}
