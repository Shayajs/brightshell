const canvas = document.getElementById('auth-perlin-canvas');

if (canvas) {
    const ctx = canvas.getContext('2d', { alpha: true });
    const dprCap = 1.5;
    let width = 0;
    let height = 0;
    let animationFrameId = null;
    let t = 0;
    let running = true;

    const lineCount = 20;
    const lineGap = 22;
    const sampleStep = 18;
    const amplitude = 26;
    const speed = 0.0054;

    const permutation = new Uint8Array(512);
    const p = new Uint8Array(256);
    for (let i = 0; i < 256; i += 1) p[i] = i;
    for (let i = 255; i > 0; i -= 1) {
        const j = (Math.random() * (i + 1)) | 0;
        const tmp = p[i];
        p[i] = p[j];
        p[j] = tmp;
    }
    for (let i = 0; i < 512; i += 1) permutation[i] = p[i & 255];

    function fade(n) {
        return n * n * n * (n * (n * 6 - 15) + 10);
    }

    function lerp(a, b, x) {
        return a + x * (b - a);
    }

    function grad(hash, x, y) {
        const h = hash & 3;
        if (h === 0) return x + y;
        if (h === 1) return -x + y;
        if (h === 2) return x - y;
        return -x - y;
    }

    function perlin(x, y) {
        const xi = Math.floor(x) & 255;
        const yi = Math.floor(y) & 255;
        const xf = x - Math.floor(x);
        const yf = y - Math.floor(y);
        const u = fade(xf);
        const v = fade(yf);

        const aa = permutation[permutation[xi] + yi];
        const ab = permutation[permutation[xi] + yi + 1];
        const ba = permutation[permutation[xi + 1] + yi];
        const bb = permutation[permutation[xi + 1] + yi + 1];

        const x1 = lerp(grad(aa, xf, yf), grad(ba, xf - 1, yf), u);
        const x2 = lerp(grad(ab, xf, yf - 1), grad(bb, xf - 1, yf - 1), u);
        return lerp(x1, x2, v);
    }

    function resize() {
        const ratio = Math.min(window.devicePixelRatio || 1, dprCap);
        width = window.innerWidth;
        height = window.innerHeight;
        canvas.width = Math.floor(width * ratio);
        canvas.height = Math.floor(height * ratio);
        ctx.setTransform(ratio, 0, 0, ratio, 0, 0);
    }

    function drawBackground() {
        ctx.fillStyle = 'rgba(4, 11, 20, 0.28)';
        ctx.fillRect(0, 0, width, height);
    }

    function drawLines() {
        ctx.lineWidth = 1;
        for (let i = 0; i < lineCount; i += 1) {
            const yBase = ((height + lineGap * 2) / lineCount) * i - lineGap;
            const alpha = 0.09 + (i / lineCount) * 0.18;
            ctx.strokeStyle = `rgba(0, 200, 255, ${alpha.toFixed(3)})`;
            ctx.beginPath();

            let firstPoint = true;
            for (let x = -40; x <= width + 40; x += sampleStep) {
                const nx = x * 0.008;
                const ny = i * 0.3 + t;
                const noise = perlin(nx, ny);
                const drift = perlin(nx * 0.45, ny * 0.65 + 91.7) * 18;
                const y = yBase + noise * amplitude + drift;

                if (firstPoint) {
                    ctx.moveTo(x, y);
                    firstPoint = false;
                } else {
                    ctx.lineTo(x, y);
                }
            }

            ctx.stroke();
        }
    }

    function frame() {
        if (!running) return;
        drawBackground();
        drawLines();
        t += speed;
        animationFrameId = window.requestAnimationFrame(frame);
    }

    function scheduleResize() {
        window.clearTimeout(scheduleResize.timer);
        scheduleResize.timer = window.setTimeout(() => {
            resize();
        }, 120);
    }
    scheduleResize.timer = null;

    function syncAuthTabs() {
        const root = document.querySelector('[data-auth-tabs]');
        if (!root) return;

        const buttons = Array.from(root.querySelectorAll('[data-auth-tab-trigger]'));
        const panels = Array.from(root.querySelectorAll('[data-auth-tab-panel]'));

        const setTab = (tab) => {
            root.dataset.authActiveTab = tab;
            for (const button of buttons) {
                const isActive = button.dataset.authTabTrigger === tab;
                button.setAttribute('aria-selected', isActive ? 'true' : 'false');
            }
            for (const panel of panels) {
                panel.hidden = panel.dataset.authTabPanel !== tab;
            }
        };

        for (const button of buttons) {
            button.addEventListener('click', () => setTab(button.dataset.authTabTrigger));
        }

        setTab(root.dataset.authActiveTab || 'login');
    }

    document.addEventListener('visibilitychange', () => {
        const shouldRun = document.visibilityState === 'visible';
        if (shouldRun && !running) {
            running = true;
            animationFrameId = window.requestAnimationFrame(frame);
        } else if (!shouldRun && running) {
            running = false;
            if (animationFrameId) window.cancelAnimationFrame(animationFrameId);
        }
    });

    window.addEventListener('resize', scheduleResize);
    resize();
    syncAuthTabs();
    animationFrameId = window.requestAnimationFrame(frame);
}
