const DPR_CAP = 2;

function getDimensions() {
    const dpr = Math.min(window.devicePixelRatio || 1, DPR_CAP);
    const width = window.innerWidth;
    const height = window.innerHeight;

    return {
        width,
        height,
        dpr,
        pixelWidth: Math.floor(width * dpr),
        pixelHeight: Math.floor(height * dpr),
    };
}

function canUseOffscreen(canvas) {
    return (
        canvas &&
        typeof Worker !== 'undefined' &&
        typeof OffscreenCanvas !== 'undefined' &&
        typeof canvas.transferControlToOffscreen === 'function'
    );
}

export default function initBackground() {
    if (window.__homeAuroraInitialized) return;

    const body = document.body;
    if (!body || !body.classList.contains('home-vitrine')) return;

    const canvas = document.getElementById('hero-canvas');
    if (!canvas) return;

    window.__homeAuroraInitialized = true;

    const setFallback = () => {
        body.classList.add('home-aurora--fallback');
    };

    if (!canUseOffscreen(canvas)) {
        setFallback();
        return;
    }

    let worker;

    try {
        const offscreen = canvas.transferControlToOffscreen();
        worker = new Worker(new URL('./background.worker.js', import.meta.url), { type: 'module' });
        const dim = getDimensions();

        worker.postMessage(
            {
                type: 'init',
                canvas: offscreen,
                ...dim,
            },
            [offscreen]
        );
    } catch {
        setFallback();
        return;
    }

    let resizeTimer = null;
    const onResize = () => {
        window.clearTimeout(resizeTimer);
        resizeTimer = window.setTimeout(() => {
            if (!worker) return;
            worker.postMessage({
                type: 'resize',
                ...getDimensions(),
            });
        }, 120);
    };

    const onVisibility = () => {
        if (!worker) return;
        worker.postMessage({ type: document.hidden ? 'pause' : 'resume' });
    };

    const onBeforeUnload = () => {
        if (!worker) return;
        worker.postMessage({ type: 'stop' });
        worker.terminate();
        worker = null;
    };

    window.addEventListener('resize', onResize, { passive: true });
    document.addEventListener('visibilitychange', onVisibility);
    window.addEventListener('beforeunload', onBeforeUnload);
}
