/**
 * Rendu du fond auth sur un thread séparé (OffscreenCanvas 2D).
 * Le thread principal ne fait que resize / pause saisie / onglets.
 */

const dprCap = 1.5;
const noiseScale = 0.005;
/** Plus lent qu’avant (animation moins nerveuse). */
const timeScale = 0.009;
const cellSize = 12;
const altitudeBands = 11;
const vectorStride = 2;
const vectorLenMin = 3.5;
const vectorLenMax = 16;
const secondaryFieldWeight = 0.32;
/** Vitesse du « temps » (plus bas = évolution plus lente). */
const timeStep = 0.28;

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

function altitudeAt(x, y, t) {
    const z = t * timeScale;
    const n1 = perlin3(x * noiseScale, y * noiseScale, z);
    let h = (n1 + 1) * 0.5;
    if (secondaryFieldWeight > 0) {
        const n2 = perlin3(x * noiseScale * 1.85 + 41.2, y * noiseScale * 1.85 - 17.9, z * 0.82 + 3.1);
        const h2 = (n2 + 1) * 0.5;
        h = (1 - secondaryFieldWeight) * h + secondaryFieldWeight * h2;
    }
    return Math.min(1, Math.max(0, h));
}

function bandIndex(h) {
    return Math.floor(h * altitudeBands);
}

/** @type {CanvasRenderingContext2D | null} */
let ctx = null;
let width = 0;
let height = 0;
let time = 0;
let running = true;
let pausedDraw = false;
let hasDrawnOnce = false;
/** @type {number | null} */
let rafId = null;

function clearBackground() {
    if (!ctx) return;
    ctx.fillStyle = 'rgba(4, 11, 20, 1)';
    ctx.fillRect(0, 0, width, height);
}

function drawAltitudeField() {
    if (!ctx) return;
    const cols = Math.ceil(width / cellSize);
    const rows = Math.ceil(height / cellSize);
    const w = cols + 1;
    const h = rows + 1;
    const len = w * h;
    const heights = new Float32Array(len);

    for (let j = 0; j < h; j += 1) {
        const y = j * cellSize;
        const row = j * w;
        for (let i = 0; i < w; i += 1) {
            const x = i * cellSize;
            heights[row + i] = altitudeAt(x, y, time);
        }
    }

    ctx.lineCap = 'round';
    ctx.lineJoin = 'round';

    ctx.lineWidth = 1.05;
    ctx.strokeStyle = 'rgba(0, 200, 255, 0.16)';

    for (let j = 0; j < h; j += 1) {
        const row = j * w;
        for (let i = 0; i < w - 1; i += 1) {
            const a = heights[row + i];
            const b = heights[row + i + 1];
            if (bandIndex(a) !== bandIndex(b)) {
                const x0 = i * cellSize;
                const y0 = j * cellSize;
                ctx.beginPath();
                ctx.moveTo(x0, y0);
                ctx.lineTo(x0 + cellSize, y0);
                ctx.stroke();
            }
        }
    }

    for (let j = 0; j < h - 1; j += 1) {
        const row = j * w;
        for (let i = 0; i < w; i += 1) {
            const a = heights[row + i];
            const b = heights[row + w + i];
            if (bandIndex(a) !== bandIndex(b)) {
                const x0 = i * cellSize;
                const y0 = j * cellSize;
                ctx.beginPath();
                ctx.moveTo(x0, y0);
                ctx.lineTo(x0, y0 + cellSize);
                ctx.stroke();
            }
        }
    }

    ctx.lineWidth = 0.8;
    const half = cellSize * 0.5;
    const inv2cs = 1 / (2 * cellSize);

    for (let j = 0; j < rows; j += vectorStride) {
        for (let i = 0; i < cols; i += vectorStride) {
            const idx = j * w + i;
            const h00 = heights[idx];
            const h10 = heights[idx + 1];
            const h01 = heights[idx + w];
            const h11 = heights[idx + w + 1];
            const hx = (h10 + h11 - h00 - h01) * inv2cs;
            const hy = (h01 + h11 - h00 - h10) * inv2cs;
            const mag = Math.hypot(hx, hy) + 1e-5;
            const ux = hx / mag;
            const uy = hy / mag;
            const cx = i * cellSize + half;
            const cy = j * cellSize + half;
            const len = vectorLenMin + (vectorLenMax - vectorLenMin) * Math.min(mag * cellSize * 2.2, 1);
            const pulse = 0.75 + 0.25 * Math.sin(time * 0.08 + cx * 0.01 + cy * 0.01);
            const alpha = 0.12 * pulse * (0.45 + 0.55 * Math.min(mag * cellSize * 1.8, 1));

            ctx.strokeStyle = `rgba(0, 200, 255, ${alpha.toFixed(3)})`;
            ctx.beginPath();
            ctx.moveTo(cx - ux * len * 0.5, cy - uy * len * 0.5);
            ctx.lineTo(cx + ux * len * 0.5, cy + uy * len * 0.5);
            ctx.stroke();
        }
    }
}

const scheduleFrame =
    typeof self.requestAnimationFrame === 'function'
        ? (cb) => self.requestAnimationFrame(cb)
        : (cb) => self.setTimeout(cb, 1000 / 30);

function cancelScheduled(id) {
    if (typeof self.cancelAnimationFrame === 'function') {
        self.cancelAnimationFrame(id);
    } else {
        self.clearTimeout(id);
    }
}

function frame() {
    if (!running) return;
    time += timeStep;
    const skipDraw = pausedDraw && hasDrawnOnce;
    if (!skipDraw) {
        clearBackground();
        drawAltitudeField();
        hasDrawnOnce = true;
    }
    rafId = scheduleFrame(frame);
}

self.onmessage = (e) => {
    const { data } = e;
    if (data.type === 'init') {
        const offscreen = data.canvas;
        ctx = offscreen.getContext('2d', { alpha: true });
        if (!ctx) return;
        width = data.width;
        height = data.height;
        const dpr = data.dpr;
        offscreen.width = data.pixelWidth;
        offscreen.height = data.pixelHeight;
        ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
        ctx.fillStyle = 'rgba(4, 11, 20, 1)';
        ctx.fillRect(0, 0, width, height);
        time = 0;
        hasDrawnOnce = false;
        pausedDraw = false;
        running = true;
        if (rafId != null) cancelScheduled(rafId);
        rafId = scheduleFrame(frame);
    }
    if (data.type === 'resize') {
        if (!ctx) return;
        const offscreen = ctx.canvas;
        width = data.width;
        height = data.height;
        const dpr = data.dpr;
        offscreen.width = data.pixelWidth;
        offscreen.height = data.pixelHeight;
        ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
        ctx.fillStyle = 'rgba(4, 11, 20, 1)';
        ctx.fillRect(0, 0, width, height);
        hasDrawnOnce = false;
    }
    if (data.type === 'paused') {
        pausedDraw = Boolean(data.paused);
    }
    if (data.type === 'stop') {
        running = false;
        if (rafId != null) cancelScheduled(rafId);
        rafId = null;
    }
};
