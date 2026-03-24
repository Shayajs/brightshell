/**
 * Reboot complet : aurore boréale Perlin, teintes 100% bleues.
 * Rendu simple : 1 grand champ d'aurore + rubans internes.
 */

const BASE_COLOR = '#020b1c';
const FPS_FALLBACK = 1000 / 30;
const X_STEP = 26;

// Teintes autorisées (bleu/cyan uniquement)
const HUE_MIN = 192;
const HUE_MAX = 228;

const AURORA = {
    baseY: 0.21,
    ampLarge: 0.23,
    ampMedium: 0.10,
    ampFine: 0.02,
    thicknessBase: 0.36,
    thicknessRange: 0.22,
    speed: 0.22,
    speedThickness: 0.16,
    blurBody: 36,
    blurGlow: 68,
};

/* ─── État ────────────────────────────────────────────────── */
let ctx = null;
let width = 0;
let height = 0;
let running = false;
let paused = false;
let rafId = null;
let startTime = 0;
const permutation = new Uint8Array(512);

/* ─── Perlin 3D ───────────────────────────────────────────── */
function initPermutation() {
    const p = new Uint8Array(256);
    for (let i = 0; i < 256; i += 1) p[i] = i;
    for (let i = 255; i > 0; i -= 1) {
        const j = (Math.random() * (i + 1)) | 0;
        const tmp = p[i]; p[i] = p[j]; p[j] = tmp;
    }
    for (let i = 0; i < 512; i += 1) permutation[i] = p[i & 255];
}
function fade(n) { return n * n * n * (n * (n * 6 - 15) + 10); }
function lerp(a, b, x) { return a + x * (b - a); }
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
    const A = permutation[xi] + yi;
    const B = permutation[xi + 1] + yi;
    const AA = permutation[A] + zi;
    const AB = permutation[A + 1] + zi;
    const BA = permutation[B] + zi;
    const BB = permutation[B + 1] + zi;
    return lerp(
        lerp(lerp(grad3(permutation[AA],     xf,     yf,     zf), grad3(permutation[BA],     xf-1, yf,     zf),   u),
             lerp(grad3(permutation[AB],     xf,     yf - 1, zf), grad3(permutation[BB],     xf-1, yf - 1, zf),   u), v),
        lerp(lerp(grad3(permutation[AA + 1], xf,     yf,     zf-1), grad3(permutation[BA + 1], xf-1, yf,     zf-1), u),
             lerp(grad3(permutation[AB + 1], xf,     yf - 1, zf-1), grad3(permutation[BB + 1], xf-1, yf - 1, zf-1), u), v), w);
}

/* ─── Scheduler ───────────────────────────────────────────── */
const scheduleFrame = typeof self.requestAnimationFrame === 'function'
    ? (cb) => self.requestAnimationFrame(cb)
    : (cb) => self.setTimeout(() => cb(performance.now()), FPS_FALLBACK);
function cancelFrame(id) {
    typeof self.cancelAnimationFrame === 'function' ? self.cancelAnimationFrame(id) : self.clearTimeout(id);
}

/* ─── Fond ────────────────────────────────────────────────── */
function paintBase() {
    ctx.globalCompositeOperation = 'source-over';
    ctx.filter = 'none';
    ctx.fillStyle = BASE_COLOR;
    ctx.fillRect(0, 0, width, height);
}

function clamp(v, min, max) {
    return Math.max(min, Math.min(max, v));
}

function hueAt(xNorm, t) {
    const n = (perlin3(xNorm * 2.7, 9.1, t * 0.00002) + 1) * 0.5;
    return HUE_MIN + n * (HUE_MAX - HUE_MIN);
}

function auroraCenterY(x, t) {
    const x1 = x * 0.00023;
    const z = t * 0.000045 * AURORA.speed;
    const n1 = perlin3(x1, 0.0, z);
    const n2 = perlin3(x1 * 1.9 + n1 * 0.7, 13.4, z * 1.7);
    const n3 = perlin3(x1 * 3.3 + n2 * 0.5, 27.1, z * 2.4);
    return height * AURORA.baseY
        + n1 * height * AURORA.ampLarge
        + n2 * height * AURORA.ampMedium
        + n3 * height * AURORA.ampFine;
}

function auroraThickness(x, t) {
    const z = t * 0.00004 * AURORA.speedThickness;
    const n1 = (perlin3(x * 0.00033 + 17.0, 41.2, z) + 1) * 0.5;
    const n2 = perlin3(x * 0.0012 + 73.0, 58.7, z * 2.1);
    const raw = AURORA.thicknessBase + n1 * AURORA.thicknessRange + n2 * AURORA.thicknessRange * 0.35;
    return height * clamp(raw, 0.18, 0.7);
}

function buildAuroraSteps(t) {
    const steps = [];
    for (let x = -X_STEP; x <= width + X_STEP; x += X_STEP) {
        const yCenter = auroraCenterY(x, t);
        const th = auroraThickness(x, t);
        const top = clamp(yCenter - th * 0.25, -30, height * 0.75);
        const bottom = clamp(yCenter + th, 0, height * 0.95);
        const hue = hueAt((x + X_STEP) / (width + X_STEP * 2), t);
        steps.push({ x, yTop: top, yBot: bottom, hue });
    }
    return steps;
}

function drawSmoothClosed(steps) {
    ctx.moveTo(steps[0].x, steps[0].yTop);
    for (let i = 1; i < steps.length - 1; i += 1) {
        const c = steps[i];
        const n = steps[i + 1];
        ctx.quadraticCurveTo(c.x, c.yTop, (c.x + n.x) * 0.5, (c.yTop + n.yTop) * 0.5);
    }
    const last = steps[steps.length - 1];
    ctx.lineTo(last.x, last.yTop);

    for (let i = steps.length - 1; i > 0; i -= 1) {
        const c = steps[i];
        const p = steps[i - 1];
        ctx.quadraticCurveTo(c.x, c.yBot, (c.x + p.x) * 0.5, (c.yBot + p.yBot) * 0.5);
    }
    ctx.lineTo(steps[0].x, steps[0].yBot);
    ctx.closePath();
}

function drawAuroraBody(steps, t) {
    let yMin = Infinity;
    let yMax = -Infinity;
    for (const s of steps) {
        if (s.yTop < yMin) yMin = s.yTop;
        if (s.yBot > yMax) yMax = s.yBot;
    }

    const hueMid = hueAt(0.5, t);
    const gradient = ctx.createLinearGradient(0, yMin, 0, yMax);
    gradient.addColorStop(0.0, `hsla(${hueMid} 88% 86% / 0.0)`);
    gradient.addColorStop(0.06, `hsla(${hueMid} 88% 86% / 0.78)`);
    gradient.addColorStop(0.28, `hsla(${hueMid + 6} 84% 65% / 0.62)`);
    gradient.addColorStop(0.62, `hsla(${hueMid + 12} 78% 44% / 0.36)`);
    gradient.addColorStop(1.0, `hsla(${hueMid + 18} 70% 30% / 0.0)`);

    ctx.save();
    ctx.globalCompositeOperation = 'screen';
    ctx.filter = `blur(${AURORA.blurBody}px)`;
    ctx.fillStyle = gradient;
    ctx.beginPath();
    drawSmoothClosed(steps);
    ctx.fill();
    ctx.restore();
}

function drawInternalStreaks(t) {
    // Sous-rubans internes animés par Perlin, toujours bleus
    for (let k = 0; k < 4; k += 1) {
        const phase = 19.7 * k;
        const steps = [];
        for (let x = -X_STEP; x <= width + X_STEP; x += X_STEP) {
            const yMain = auroraCenterY(x, t + k * 400);
            const th = auroraThickness(x, t);
            const n = perlin3(x * (0.0017 + k * 0.0003), phase, t * 0.00008 + k * 1.3);
            const y = yMain + n * th * (0.35 + k * 0.08);
            const hw = height * (0.010 + k * 0.002);
            const hue = hueAt((x + 20 * k) / Math.max(width, 1), t + k * 250);
            steps.push({ x, yTop: y - hw, yBot: y + hw, hue });
        }

        let yMin = Infinity;
        let yMax = -Infinity;
        for (const s of steps) {
            if (s.yTop < yMin) yMin = s.yTop;
            if (s.yBot > yMax) yMax = s.yBot;
        }

        const hue = hueAt(0.2 * k + 0.3, t);
        const gradient = ctx.createLinearGradient(0, yMin, 0, yMax);
        gradient.addColorStop(0, `hsla(${hue} 90% 88% / 0)`);
        gradient.addColorStop(0.3, `hsla(${hue} 88% 78% / ${0.55 - k * 0.08})`);
        gradient.addColorStop(0.5, `hsla(${hue + 4} 86% 70% / ${0.70 - k * 0.08})`);
        gradient.addColorStop(0.7, `hsla(${hue + 10} 82% 56% / ${0.44 - k * 0.06})`);
        gradient.addColorStop(1, `hsla(${hue + 14} 76% 42% / 0)`);

        ctx.save();
        ctx.globalCompositeOperation = 'screen';
        ctx.filter = `blur(${12 + k * 3}px)`;
        ctx.fillStyle = gradient;
        ctx.beginPath();
        drawSmoothClosed(steps);
        ctx.fill();
        ctx.restore();
    }
}

function drawFullRibbon(t) {
    const steps = buildAuroraSteps(t);
    let maxY = -Infinity;
    for (const s of steps) if (s.yBot > maxY) maxY = s.yBot;

    // Base bleue continue (pas de trou)
    const bg = ctx.createLinearGradient(0, 0, 0, maxY + 70);
    bg.addColorStop(0.0, 'rgba(7, 18, 48, 0.92)');
    bg.addColorStop(0.45, 'rgba(6, 16, 44, 0.84)');
    bg.addColorStop(0.8, 'rgba(4, 12, 36, 0.34)');
    bg.addColorStop(1.0, 'rgba(3, 10, 32, 0.00)');
    ctx.globalCompositeOperation = 'source-over';
    ctx.filter = 'none';
    ctx.fillStyle = bg;
    ctx.fillRect(0, 0, width, maxY + 70);

    // Lueur globale
    const cX = width * (0.35 + ((perlin3(2.3, 5.6, t * 0.000015) + 1) * 0.22));
    const cY = height * 0.24;
    const glow = ctx.createRadialGradient(cX, cY, 0, cX, cY, Math.max(width, height) * 0.65);
    glow.addColorStop(0, 'hsla(210 82% 44% / 0.18)');
    glow.addColorStop(0.55, 'hsla(216 76% 28% / 0.1)');
    glow.addColorStop(1, 'hsla(220 70% 14% / 0)');
    ctx.save();
    ctx.globalCompositeOperation = 'screen';
    ctx.filter = `blur(${AURORA.blurGlow}px)`;
    ctx.fillStyle = glow;
    ctx.fillRect(0, 0, width, height);
    ctx.restore();

    drawAuroraBody(steps, t);
    drawInternalStreaks(t);
}

/* ─── Boucle principale ───────────────────────────────────── */
function render(timestamp) {
    if (!running) return;
    if (!paused && ctx) {
        if (!startTime) startTime = timestamp;
        const t = timestamp - startTime;
        paintBase();
        drawFullRibbon(t);
    }
    rafId = scheduleFrame(render);
}

function startLoop() {
    running = true; paused = false; startTime = 0;
    if (rafId != null) cancelFrame(rafId);
    rafId = scheduleFrame(render);
}
function stopLoop() {
    running = false;
    if (rafId != null) { cancelFrame(rafId); rafId = null; }
}
function applyResize(data) {
    if (!ctx) return;
    const cv = ctx.canvas;
    width = data.width; height = data.height;
    cv.width = data.pixelWidth; cv.height = data.pixelHeight;
    ctx.setTransform(data.dpr, 0, 0, data.dpr, 0, 0);
}

/* ─── Messages ────────────────────────────────────────────── */
self.onmessage = (event) => {
    const { data } = event;
    if (!data || !data.type) return;
    if (data.type === 'init') {
        ctx = data.canvas.getContext('2d', { alpha: false });
        if (!ctx) return;
        applyResize(data);
        initPermutation();
        startLoop();
        return;
    }
    if (data.type === 'resize')  { applyResize(data); return; }
    if (data.type === 'pause')   { paused = true;  return; }
    if (data.type === 'resume')  { paused = false; return; }
    if (data.type === 'stop')    { stopLoop(); }
};
