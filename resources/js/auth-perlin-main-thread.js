/**
 * Repli si OffscreenCanvas / Worker indisponibles — même rendu, même réglages lents.
 */
export default function bootstrapMainThreadCanvas(canvas) {
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

    const noiseScale = 0.005;
    const timeScale = 0.009;
    const cellSize = 12;
    const altitudeBands = 11;
    const vectorStride = 2;
    const vectorLenMin = 3.5;
    const vectorLenMax = 16;
    const secondaryFieldWeight = 0.32;
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

    function resize() {
        const ratio = Math.min(window.devicePixelRatio || 1, dprCap);
        width = window.innerWidth;
        height = window.innerHeight;
        canvas.width = Math.floor(width * ratio);
        canvas.height = Math.floor(height * ratio);
        ctx.setTransform(ratio, 0, 0, ratio, 0, 0);
        ctx.fillStyle = 'rgba(4, 11, 20, 1)';
        ctx.fillRect(0, 0, width, height);
    }

    function clearBackground() {
        ctx.fillStyle = 'rgba(4, 11, 20, 1)';
        ctx.fillRect(0, 0, width, height);
    }

    function drawAltitudeField() {
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

    function frame() {
        if (!running) return;
        time += timeStep;
        clearBackground();
        drawAltitudeField();
        animationFrameId = window.requestAnimationFrame(frame);
    }

    function scheduleResize() {
        window.clearTimeout(scheduleResize.timer);
        scheduleResize.timer = window.setTimeout(() => {
            resize();
        }, 120);
    }
    scheduleResize.timer = null;

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
    animationFrameId = window.requestAnimationFrame(frame);
}
