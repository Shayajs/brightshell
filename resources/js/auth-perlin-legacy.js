/**
 * LEGACY — Flow field particules + traînées limitées (super effet, conservé pour référence).
 * Pour réactiver : dans layouts/auth.blade.php, remplacer auth-perlin.js par ce fichier
 * et ajouter cette entrée dans vite.config.js si besoin.
 *
 * Dernière version avant le mode « altitude / cellules » (auth-perlin.js).
 */
const canvas = document.getElementById('auth-perlin-canvas');

if (canvas) {
    const ctx = canvas.getContext('2d', { alpha: true });
    if (!ctx) {
        throw new Error('2D canvas context unavailable.');
    }

    const dprCap = 1.5;
    let width = 0;
    let height = 0;
    let animationFrameId = null;
    let running = true;
    let time = 0;

    const TAU = Math.PI * 2;
    const noiseScale = 0.005;
    const timeScale = 0.11;
    const particleCount = 220;
    const particleSpeed = 1.15;
    /** Nombre max de points par traînée (longueur visuelle ≈ trailMaxPoints × particleSpeed px). */
    const trailMaxPoints = 32;
    const maxLife = 170;

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

    function grad3(hash, x, y, z) {
        const h = hash & 15;
        const u = h < 8 ? x : y;
        const v = h < 4 ? y : (h === 12 || h === 14 ? x : z);
        return ((h & 1) === 0 ? u : -u) + ((h & 2) === 0 ? v : -v);
    }

    function perlin3(x, y, z) {
        const xi = Math.floor(x) & 255;
        const yi = Math.floor(y) & 255;
        const zi = Math.floor(z) & 255;

        const xf = x - Math.floor(x);
        const yf = y - Math.floor(y);
        const zf = z - Math.floor(z);

        const u = fade(xf);
        const v = fade(yf);
        const w = fade(zf);

        const aaa = permutation[permutation[permutation[xi] + yi] + zi];
        const aba = permutation[permutation[permutation[xi] + yi + 1] + zi];
        const aab = permutation[permutation[permutation[xi] + yi] + zi + 1];
        const abb = permutation[permutation[permutation[xi] + yi + 1] + zi + 1];
        const baa = permutation[permutation[permutation[xi + 1] + yi] + zi];
        const bba = permutation[permutation[permutation[xi + 1] + yi + 1] + zi];
        const bab = permutation[permutation[permutation[xi + 1] + yi] + zi + 1];
        const bbb = permutation[permutation[permutation[xi + 1] + yi + 1] + zi + 1];

        const x1 = lerp(grad3(aaa, xf, yf, zf), grad3(baa, xf - 1, yf, zf), u);
        const x2 = lerp(grad3(aba, xf, yf - 1, zf), grad3(bba, xf - 1, yf - 1, zf), u);
        const y1 = lerp(x1, x2, v);

        const x3 = lerp(grad3(aab, xf, yf, zf - 1), grad3(bab, xf - 1, yf, zf - 1), u);
        const x4 = lerp(grad3(abb, xf, yf - 1, zf - 1), grad3(bbb, xf - 1, yf - 1, zf - 1), u);
        const y2 = lerp(x3, x4, v);

        return lerp(y1, y2, w);
    }

    function randomRange(min, max) {
        return min + Math.random() * (max - min);
    }

    function createParticle() {
        const x = randomRange(0, width);
        const y = randomRange(0, height);
        return {
            x,
            y,
            life: Math.floor(randomRange(0, maxLife)),
            /** @type {Array<{ x: number; y: number }>} */
            trail: [{ x, y }],
        };
    }

    /** @type {Array<{ x: number; y: number; life: number; trail: Array<{ x: number; y: number }> }>} */
    let particles = [];

    function resetParticle(particle) {
        particle.x = randomRange(0, width);
        particle.y = randomRange(0, height);
        particle.life = 0;
        particle.trail = [{ x: particle.x, y: particle.y }];
    }

    function seedParticles() {
        particles = [];
        for (let i = 0; i < particleCount; i += 1) {
            particles.push(createParticle());
        }
    }

    function resize() {
        const ratio = Math.min(window.devicePixelRatio || 1, dprCap);
        width = window.innerWidth;
        height = window.innerHeight;

        canvas.width = Math.floor(width * ratio);
        canvas.height = Math.floor(height * ratio);
        ctx.setTransform(ratio, 0, 0, ratio, 0, 0);
        ctx.fillStyle = 'rgba(4, 11, 20, 1)';
        ctx.fillRect(0, 0, width, height);

        if (particles.length === 0) {
            seedParticles();
            return;
        }

        for (const particle of particles) {
            resetParticle(particle);
        }
    }

    function clearBackground() {
        ctx.fillStyle = 'rgba(4, 11, 20, 1)';
        ctx.fillRect(0, 0, width, height);
    }

    function updateAndDrawParticles() {
        ctx.lineWidth = 0.8;
        ctx.lineCap = 'round';

        for (const particle of particles) {
            const n = perlin3(
                particle.x * noiseScale,
                particle.y * noiseScale,
                time * timeScale
            );
            const angle = ((n + 1) * 0.5) * TAU * 2;
            particle.x += Math.cos(angle) * particleSpeed;
            particle.y += Math.sin(angle) * particleSpeed;
            particle.life += 1;

            const outOfBounds =
                particle.x < -4 ||
                particle.x > width + 4 ||
                particle.y < -4 ||
                particle.y > height + 4;

            if (outOfBounds || particle.life > maxLife) {
                resetParticle(particle);
                continue;
            }

            particle.trail.push({ x: particle.x, y: particle.y });
            while (particle.trail.length > trailMaxPoints) {
                particle.trail.shift();
            }

            const trail = particle.trail;
            const segCount = trail.length - 1;
            if (segCount < 1) {
                continue;
            }

            const lifeAlpha = 0.12 + 0.18 * (1 - particle.life / maxLife);

            for (let i = 0; i < segCount; i += 1) {
                const t = (i + 1) / segCount;
                const alpha = lifeAlpha * (0.12 + 0.88 * t * t);
                ctx.strokeStyle = `rgba(0, 200, 255, ${alpha.toFixed(3)})`;
                ctx.beginPath();
                ctx.moveTo(trail[i].x, trail[i].y);
                ctx.lineTo(trail[i + 1].x, trail[i + 1].y);
                ctx.stroke();
            }
        }
    }

    function frame() {
        if (!running) return;
        clearBackground();
        updateAndDrawParticles();
        time += 1;
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
