
/*
 * Screen-capture thumbnails. In the running system these are real screenshots
 * sent by the desktop monitoring component; here the same drawn mock from the
 * prototype stands in for each capture record, keyed by its id so it is stable.
 */
function rnd(seed) { const x = Math.sin(seed * 997.31) * 10000; return x - Math.floor(x); }

function drawShot(cv, seed) {
    const W = 320, H = 180, ctx = cv.getContext('2d');
    cv.width = W; cv.height = H;
    ctx.fillStyle = '#10161A'; ctx.fillRect(0, 0, W, H);
    ctx.fillStyle = '#19222A'; ctx.fillRect(0, 0, W, 11);
    ctx.fillStyle = '#2C3A44';
    for (let i = 0; i < 6; i++) ctx.fillRect(7 + i * 25, 4, 14 + rnd(seed + i) * 6, 4);
    ctx.fillStyle = '#141C22'; ctx.fillRect(0, 11, 74, 98);
    ctx.fillStyle = '#232E36';
    for (let b = 0; b < 7; b++) ctx.fillRect(6, 18 + b * 12, 38 + rnd(seed + b * 3) * 24, 5);
    ctx.fillStyle = '#0A0E11'; ctx.fillRect(78, 13, 160, 92);
    const hue = 25 + rnd(seed) * 180;
    ctx.fillStyle = `hsl(${hue},26%,${22 + rnd(seed + 5) * 14}%)`; ctx.fillRect(82, 17, 152, 84);
    ctx.fillStyle = `hsl(${hue},32%,${40 + rnd(seed + 9) * 18}%)`;
    ctx.beginPath(); ctx.ellipse(112 + rnd(seed + 2) * 86, 70, 24 + rnd(seed + 4) * 14, 28, 0, 0, Math.PI * 2); ctx.fill();
    ctx.fillStyle = 'rgba(255,255,255,.08)'; ctx.fillRect(82, 17, 152, 24);
    ctx.fillStyle = '#141C22'; ctx.fillRect(242, 13, 74, 92);
    const ty = [116, 131, 146, 161], pal = ['#3E6B87', '#87673E', '#3F7C5C', '#68507C'];
    for (let t = 0; t < 4; t++) {
        ctx.fillStyle = '#1A242B'; ctx.fillRect(0, ty[t], W, 12);
        let x = 5 + rnd(seed + t * 11) * 12;
        while (x < W - 8) {
            const w = 13 + rnd(seed + x * 0.13 + t) * 50;
            ctx.fillStyle = pal[t]; ctx.globalAlpha = .85; ctx.fillRect(x, ty[t] + 1, w, 10); ctx.globalAlpha = 1;
            ctx.fillStyle = 'rgba(255,255,255,.13)'; ctx.fillRect(x, ty[t] + 1, w, 2);
            x += w + 2 + rnd(seed + x) * 6;
        }
    }
    const ph = 38 + rnd(seed + 3) * 230;
    ctx.fillStyle = '#E7B44C'; ctx.fillRect(ph, 111, 1.5, H - 111); ctx.fillRect(ph - 4, 109, 9, 4);
}

function drawAllShots() {
    document.querySelectorAll('canvas.trov-shot').forEach((cv) => {
        if (cv.dataset.drawn) return;
        drawShot(cv, parseInt(cv.dataset.seed || '1', 10));
        cv.dataset.drawn = '1';
    });
}

document.addEventListener('DOMContentLoaded', drawAllShots);
document.addEventListener('livewire:navigated', drawAllShots);
document.addEventListener('livewire:update', () => setTimeout(drawAllShots, 30));
window.addEventListener('load', drawAllShots);


/*
 * The work-session timer. Ticks work seconds while Working and break seconds
 * while on Break, so hitting Break pauses the visible timer at once. State is
 * mirrored to the Livewire component so the server heartbeat persists it.
 */
document.addEventListener('alpine:init', () => {
    window.Alpine.data('sessionCard', (cfg) => ({
        working: cfg.working,
        work: cfg.workBase,
        brk: cfg.breakBase,
        _timer: null,
        // Livewire re-renders (the 30s heartbeat, Working/Break) can re-create
        // this Alpine component and snap the counters back to the server values,
        // which lag up to 30s behind. We keep the live values in a JS global that
        // survives those re-renders, and never let the display go backwards, so
        // the timer ticks smoothly instead of resetting.
        boot() {
            const c = window.__trovTimer;
            if (c && c.sid === cfg.sid) {
                this.work    = Math.max(cfg.workBase, c.work);
                this.brk     = Math.max(cfg.breakBase, c.brk);
                this.working = c.working;
            }
            this._save();
            this.start();
        },
        start() {
            if (this._timer) clearInterval(this._timer); // never stack intervals
            this._timer = setInterval(() => {
                if (this.working) { this.work++; } else { this.brk++; }
                this._save();
            }, 1000);
        },
        _save() {
            window.__trovTimer = { sid: cfg.sid, work: this.work, brk: this.brk, working: this.working };
        },
        destroy() { if (this._timer) clearInterval(this._timer); },
        setWorking(w) {
            this.working = w;
            this._save();
            if (this.$wire) { this.$wire.setWorking(w); }
        },
        get total() { return this.work + this.brk; },
        fmt(s) {
            s = Math.max(0, Math.floor(s));
            const p = (n) => String(Math.floor(n)).padStart(2, '0');
            return p(s / 3600) + ':' + p((s / 60) % 60) + ':' + p(s % 60);
        },
    }));
});
