/* =====================================================================
   CCLB parish site motion and behaviour.
   One clock: GSAP's ticker drives Lenis, ScrollTrigger and the waterline.
   Vendored globals (loaded with defer before this module): gsap,
   ScrollTrigger, SplitText, Lenis.
   ===================================================================== */
const { gsap, ScrollTrigger, SplitText } = window;
gsap.registerPlugin(ScrollTrigger, SplitText);

const html = document.documentElement;
window.__booted = true;
const reduced = matchMedia('(prefers-reduced-motion: reduce)').matches;
const touch = matchMedia('(hover: none), (pointer: coarse)').matches;
const ease = { out: 'expo.out', inOut: 'expo.inOut', soft: 'power3.out' };
const $ = (s, r = document) => r.querySelector(s);
const $$ = (s, r = document) => [...r.querySelectorAll(s)];

html.classList.add('js');
html.classList.toggle('motion', !reduced);

/* ---------------------------------------------------------------------
   Scroll: Lenis on the GSAP ticker (native under reduced motion)
   --------------------------------------------------------------------- */
const scroll = { y: scrollY, velocity: 0, dir: 1 };
const scrollFns = new Set();
const onScroll = (fn) => { scrollFns.add(fn); return () => scrollFns.delete(fn); };
let lenis = null;
const publish = () => scrollFns.forEach((fn) => fn(scroll));

if (!reduced && !html.hasAttribute('data-native-scroll') && window.Lenis) {
  lenis = new window.Lenis({ lerp: 0.11, wheelMultiplier: 1, anchors: { offset: -72 } });
  lenis.on('scroll', (l) => {
    scroll.velocity = l.velocity;
    scroll.dir = l.direction || scroll.dir;
    scroll.y = l.scroll;
    ScrollTrigger.update();
    publish();
  });
  gsap.ticker.add((t) => lenis.raf(t * 1000));
  gsap.ticker.lagSmoothing(0);
} else {
  addEventListener('scroll', () => {
    scroll.velocity = scrollY - scroll.y;
    scroll.dir = scroll.velocity >= 0 ? 1 : -1;
    scroll.y = scrollY;
    publish();
  }, { passive: true });
}
const stopScroll = () => lenis?.stop();
const startScroll = () => lenis?.start();

/* ---------------------------------------------------------------------
   Reveals: rise from below the line, like something surfacing
   --------------------------------------------------------------------- */
function initReveals() {
  if (reduced) return;
  $$('[data-split]').forEach((el) => {
    const state = { done: false };
    SplitText.create(el, {
      type: 'lines', mask: 'lines', autoSplit: true, linesClass: 'split-line',
      onSplit(self) {
        gsap.set(el, { visibility: 'visible' });
        if (state.done) return;
        return gsap.from(self.lines, {
          yPercent: 112, duration: 1.25, stagger: 0.08, ease: ease.out,
          delay: parseFloat(el.dataset.delay || '0'),
          onComplete: () => { state.done = true; },
          scrollTrigger: { trigger: el, start: 'top 88%', once: true },
        });
      },
    });
  });
  $$('[data-reveal]').forEach((el) => {
    gsap.set(el, { visibility: 'visible' });
    gsap.from(el, {
      y: 40, autoAlpha: 0, duration: 1.3, ease: ease.out,
      delay: parseFloat(el.dataset.delay || '0'),
      scrollTrigger: { trigger: el, start: 'top 90%', once: true },
    });
  });
}

/* ---------------------------------------------------------------------
   The waterline driver. One variable per surface: the water's height.
   Inputs: scroll progress (raises it), the intro (drains it to rest),
   time (the wave travels, the reflection ripples), scroll velocity
   (disturbs the reflection). Everything that carries the concept reads it.
   --------------------------------------------------------------------- */
const surfaces = [];
let arriving = html.classList.contains('arriving');

function initSurfaces() {
  $$('[data-surface]').forEach((el, index) => {
    const stage = $('.surface__stage', el);
    const water = $('[data-water]', el);
    const inner = $('[data-water-inner]', el);
    const probe = $('.surface__probe', el);
    if (!stage || !water || !inner || !probe) return;
    const s = {
      el, stage, water, inner, index,
      wave: $('.wave', water),
      slices: $$('.reflection__slice', el),
      reflection: $('.reflection', el),
      mode: el.dataset.surface, // 'home' | 'inner' | 'lost'
      rest: 0, cover: 0, p: 0, intro: reduced ? 1 : 0, y: 0, ripple: 0, visible: true,
    };
    const measure = () => {
      s.wh = s.wave ? s.wave.getBoundingClientRect().height : 0;
      s.rest = probe.offsetTop;
      s.cover = -stage.clientHeight * 0.03;
      if (reduced) s.y = s.mode === 'lost' ? 0 : s.rest; // CSS holds the water here
    };
    measure();
    s.measure = measure;
    surfaces.push(s);

    if (reduced) return;

    // Scroll raises the water. Home: through the sticky stage. Inner: as the hero leaves.
    ScrollTrigger.create({
      trigger: el,
      start: 'top top',
      end: s.mode === 'home' ? 'bottom bottom' : 'bottom top',
      onUpdate: (st) => { s.p = st.progress; },
      onRefresh: measure,
    });
    ScrollTrigger.create({
      trigger: stage, start: 'top bottom', end: 'bottom top',
      onToggle: (st) => { s.visible = st.isActive; },
    });
  });
}

function waterTarget(s) {
  if (s.mode === 'lost') return s.cover;
  const k = s.mode === 'home' ? gsap.parseEase('power2.inOut')(s.p) : gsap.parseEase('power1.in')(s.p) * 0.62;
  return s.rest + (s.cover - s.rest) * k;
}

function apply(s, t) {
  const target = waterTarget(s);
  const bob = reduced ? 0 : Math.sin(t * 0.9 + s.index) * 2.5;
  // the intro drains the water from full cover down to where scroll wants it
  s.y = s.cover + (target - s.cover) * s.intro + bob * s.intro;
  const top = s.y - s.wh; // the water box starts with the wave band above the line
  s.water.style.transform = `translate3d(0, ${top.toFixed(2)}px, 0)`;
  s.inner.style.transform = `translate3d(0, ${(-top).toFixed(2)}px, 0)`;
}

function rippleTick(t) {
  const v = Math.min(Math.abs(scroll.velocity), 60);
  for (const s of surfaces) {
    if (!s.visible) continue;
    apply(s, t);
    if (s.wave) s.wave.style.transform = `translate3d(${(-((t * 2.2) % 50)).toFixed(3)}%, 0, 0)`;
    s.ripple += ((0.35 + v * 0.06) - s.ripple) * 0.06;
    const n = s.slices.length;
    s.slices.forEach((sl, i) => {
      if (i === 0) return;
      const depth = i / n;
      const x = Math.sin(t * 1.6 - i * 0.85) * (2 + depth * 14) * s.ripple;
      sl.style.transform = `translate3d(${x.toFixed(2)}px, 0, 0)`;
    });
    if (s.reflection && s.mode === 'home') s.reflection.style.opacity = String(0.22 * (1 - s.p));
  }
}

function playIntro() {
  if (!surfaces.length) return Promise.resolve();
  if (reduced) return Promise.resolve();
  return Promise.all(surfaces.map((s) => {
    s.intro = 0;
    apply(s, 0);
    return gsap.to(s, { intro: 1, duration: s.mode === 'home' ? 1.6 : 1.3, ease: ease.out, delay: 0.05 });
  }));
}

/* ---------------------------------------------------------------------
   Header: ink follows the tone under it; steps aside on the way down
   --------------------------------------------------------------------- */
function initHeader() {
  const header = $('.site-header');
  if (!header) return;
  const tones = $$('[data-tone]');
  let last = scroll.y;
  const update = () => {
    const probeY = (header.offsetHeight || 72) / 2;
    let tone = 'air';
    for (const el of tones) {
      const r = el.getBoundingClientRect();
      if (r.top <= probeY && r.bottom > probeY) {
        tone = el.dataset.tone;
        const s = surfaces.find((x) => x.el === el);
        if (s) {
          const sr = s.stage.getBoundingClientRect();
          tone = sr.top + s.y <= probeY ? 'water' : 'air';
        }
        break;
      }
    }
    tone = tone === 'deep' ? 'water' : tone;
    if (html.dataset.under !== tone) html.dataset.under = tone;
    html.classList.toggle('is-scrolled', scroll.y > 12);
    const dy = scroll.y - last;
    if (Math.abs(dy) > 6) {
      html.classList.toggle('header-away', dy > 0 && scroll.y > 420 && !html.classList.contains('menu-open'));
      last = scroll.y;
    }
  };
  onScroll(update);
  gsap.ticker.add(() => { if (surfaces.some((s) => s.visible && s.intro < 1)) update(); });
  update();
  header.addEventListener('focusin', () => html.classList.remove('header-away'));
}

/* ---------------------------------------------------------------------
   Mobile menu: the water rises; focus is held inside; Escape closes
   --------------------------------------------------------------------- */
function initMenu() {
  const btn = $('[data-menu-open]');
  const menu = $('#menu');
  if (!btn || !menu) return;
  const close = $('[data-menu-close]', menu);
  const links = $$('a, button', menu);
  let open = false;
  const show = () => {
    open = true;
    menu.hidden = false;
    btn.setAttribute('aria-expanded', 'true');
    html.classList.add('menu-open');
    stopScroll();
    document.body.style.overflow = 'hidden';
    if (!reduced) {
      gsap.fromTo(menu, { clipPath: 'inset(100% 0% 0% 0%)' }, { clipPath: 'inset(0% 0% 0% 0%)', duration: 0.8, ease: ease.inOut });
      gsap.from($$('.menu__list li', menu), { yPercent: 60, autoAlpha: 0, duration: 0.9, ease: ease.out, stagger: 0.05, delay: 0.25 });
    }
    ($('.menu__list a', menu) || close).focus();
  };
  const hide = (restore = true) => {
    if (!open) return;
    open = false;
    btn.setAttribute('aria-expanded', 'false');
    html.classList.remove('menu-open');
    document.body.style.overflow = '';
    startScroll();
    const done = () => { menu.hidden = true; if (restore) btn.focus(); };
    if (reduced) done();
    else gsap.to(menu, { clipPath: 'inset(0% 0% 100% 0%)', duration: 0.6, ease: ease.inOut, onComplete: done });
  };
  btn.addEventListener('click', show);
  close.addEventListener('click', () => hide());
  menu.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') { e.preventDefault(); hide(); }
    if (e.key !== 'Tab') return;
    const first = links[0];
    const lastEl = links[links.length - 1];
    if (e.shiftKey && document.activeElement === first) { e.preventDefault(); lastEl.focus(); }
    else if (!e.shiftKey && document.activeElement === lastEl) { e.preventDefault(); first.focus(); }
  });
  $$('a[href^="#"], a[href^="/#"]', menu).forEach((a) => a.addEventListener('click', () => hide(false)));
  window.__closeMenu = () => hide(false);
}

/* ---------------------------------------------------------------------
   Page transition: going under, then coming up on the next page
   --------------------------------------------------------------------- */
function initTransitions() {
  const curtain = $('#curtain');
  if (!curtain) return;
  const panel = $('.curtain__panel', curtain);
  const label = $('.curtain__label', curtain);
  const wave = $('.wave', curtain);
  let busy = false;

  const prefetched = new Set();
  const prefetch = (a) => {
    const u = new URL(a.href, location.href);
    if (prefetched.has(u.pathname)) return;
    prefetched.add(u.pathname);
    const l = document.createElement('link');
    l.rel = 'prefetch'; l.href = u.pathname;
    document.head.appendChild(l);
  };

  const internal = (a, e) => {
    if (!a || e.defaultPrevented || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return null;
    if (a.target && a.target !== '_self') return null;
    if (a.hasAttribute('download')) return null;
    const u = new URL(a.href, location.href);
    if (u.origin !== location.origin) return null;
    if (u.pathname.startsWith('/admin')) return null;
    if (u.pathname === location.pathname && u.search === location.search) return null; // same page or hash
    if (/\.(jpe?g|png|webp|pdf|json)$/i.test(u.pathname)) return null;
    return u;
  };

  document.addEventListener('pointerover', (e) => {
    const a = e.target.closest?.('a[href]');
    if (a && internal(a, { button: 0 })) prefetch(a);
  }, { passive: true });

  document.addEventListener('click', (e) => {
    const a = e.target.closest('a[href]');
    const u = internal(a, e);
    if (!u) return;
    e.preventDefault();
    if (busy) return;
    busy = true;
    window.__closeMenu?.();
    if (reduced) { location.href = u.href; return; }
    label.textContent = a.dataset.dest || a.textContent.trim().split('\n')[0];
    stopScroll();
    let wtick = (t) => { wave.style.transform = `translate3d(${(-((t * 3) % 50)).toFixed(3)}%,0,0)`; };
    gsap.ticker.add(wtick);
    gsap.timeline({
      onComplete: () => {
        try { sessionStorage.setItem('cclb:arrive', '1'); } catch (_) { /* private mode */ }
        location.href = u.href;
      },
    })
      .set(curtain, { visibility: 'visible' })
      .fromTo(panel, { yPercent: 104 }, { yPercent: 0, duration: 0.75, ease: ease.inOut })
      .from(label, { yPercent: 60, autoAlpha: 0, duration: 0.6, ease: ease.out }, 0.3);
  });

  // Back/forward cache: never come back to a covered page.
  addEventListener('pageshow', (e) => {
    if (!e.persisted) return;
    busy = false;
    gsap.set(curtain, { visibility: 'hidden' });
    gsap.set(panel, { yPercent: 0 });
    html.classList.remove('arriving');
    startScroll();
  });
}

function finishArrival() {
  try { sessionStorage.removeItem('cclb:arrive'); } catch (_) { /* ignore */ }
  if (!arriving) return;
  arriving = false;
  const curtain = $('#curtain');
  const panel = curtain && $('.curtain__panel', curtain);
  if (surfaces.length || !panel) {
    // The hero's own water is at full cover now: hand over to it.
    html.classList.remove('arriving');
    return;
  }
  gsap.timeline()
    .to(panel, { yPercent: 104, duration: 0.8, ease: ease.inOut })
    .add(() => html.classList.remove('arriving'));
}

/* ---------------------------------------------------------------------
   The parish week: mark today and the next Mass (Lagos time)
   --------------------------------------------------------------------- */
const MASSES = [
  { d: 0, h: 8, m: 30 }, { d: 1, h: 6, m: 30 }, { d: 3, h: 6, m: 30 },
  { d: 5, h: 6, m: 30 }, { d: 6, h: 8, m: 0 },
];
const DAYS = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

function lagosNow() {
  const parts = new Intl.DateTimeFormat('en-GB', { timeZone: 'Africa/Lagos', weekday: 'short', hour: 'numeric', minute: 'numeric', hour12: false }).formatToParts(new Date());
  const get = (t) => parts.find((p) => p.type === t)?.value;
  const d = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'].indexOf(get('weekday'));
  return { d, mins: (parseInt(get('hour'), 10) % 24) * 60 + parseInt(get('minute'), 10) };
}
const fmt = (h, m) => `${((h + 11) % 12) + 1}:${String(m).padStart(2, '0')} ${h < 12 ? 'AM' : 'PM'}`;

function initWeek() {
  const now = lagosNow();
  if (now.d < 0) return;
  $$('[data-day]').forEach((el) => el.classList.toggle('is-today', +el.dataset.day === now.d));
  const nowAbs = now.d * 1440 + now.mins;
  let best = null;
  for (const m of MASSES) {
    let at = m.d * 1440 + m.h * 60 + m.m;
    if (at + 50 < nowAbs) at += 7 * 1440; // a Mass that began under 50 minutes ago is still "now"
    if (!best || at < best.at) best = { ...m, at };
  }
  const el = $('[data-next-mass]');
  if (!el || !best) return;
  const diffDays = Math.floor(best.at / 1440) - now.d;
  const started = best.at <= nowAbs;
  const day = started ? 'On now' : diffDays === 0 ? 'Today' : diffDays === 1 ? 'Tomorrow' : DAYS[best.d];
  el.textContent = started ? `On now, began ${fmt(best.h, best.m)}` : `${day}, ${fmt(best.h, best.m)}`;
}

/* ---------------------------------------------------------------------
   Copy to clipboard + toast (give, harvest)
   --------------------------------------------------------------------- */
let toastTimer;
function toast(msg) {
  const t = $('#toast');
  if (!t) return;
  t.textContent = msg;
  t.classList.add('is-on');
  clearTimeout(toastTimer);
  toastTimer = setTimeout(() => t.classList.remove('is-on'), 2200);
}
window.__toast = toast;

function copyText(text) {
  if (navigator.clipboard?.writeText) return navigator.clipboard.writeText(text).catch(() => legacyCopy(text));
  return legacyCopy(text);
}
function legacyCopy(text) {
  return new Promise((resolve, reject) => {
    const t = document.createElement('textarea');
    t.value = text; t.setAttribute('readonly', ''); t.style.position = 'fixed'; t.style.opacity = '0';
    document.body.appendChild(t); t.select();
    try { document.execCommand('copy') ? resolve() : reject(); } catch (err) { reject(err); }
    t.remove();
  });
}
function initCopy() {
  document.addEventListener('click', (e) => {
    const b = e.target.closest('[data-copy]');
    if (!b) return;
    copyText(b.dataset.copy).then(() => {
      const label = b.querySelector('[data-copy-label]');
      const was = label ? label.textContent : '';
      b.classList.add('is-copied');
      if (label) label.textContent = 'Copied';
      toast('Copied to clipboard');
      setTimeout(() => { b.classList.remove('is-copied'); if (label) label.textContent = was; }, 2200);
    }).catch(() => toast('Press and hold to copy'));
  });
}

/* ---------------------------------------------------------------------
   Lightbox (native dialog: focus is held, Escape closes)
   --------------------------------------------------------------------- */
function initLightbox() {
  const box = $('#lightbox');
  if (!box) return;
  const img = $('img', box);
  let trigger = null;
  document.addEventListener('click', (e) => {
    const b = e.target.closest('[data-lightbox]');
    if (!b) return;
    trigger = b;
    img.src = b.dataset.lightbox;
    img.alt = b.dataset.alt || '';
    box.showModal();
    stopScroll();
    if (!reduced) gsap.fromTo(img, { y: 30, autoAlpha: 0 }, { y: 0, autoAlpha: 1, duration: 0.8, ease: ease.out });
  });
  $('[data-lightbox-close]', box).addEventListener('click', () => box.close());
  box.addEventListener('click', (e) => { if (e.target === box) box.close(); });
  box.addEventListener('close', () => { startScroll(); img.removeAttribute('src'); trigger?.focus(); });
}

/* ---------------------------------------------------------------------
   Gallery: the CMS feed (data/gallery.json) as a strip
   --------------------------------------------------------------------- */
const esc = (s) => String(s ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
const ytId = (url) => (url.match(/(?:embed\/|watch\?v=|youtu\.be\/)([\w-]{6,})/) || [])[1];

async function initGallery() {
  const strip = $('[data-strip]');
  if (!strip) return;
  let items = [];
  try {
    const res = await fetch('/data/gallery.json', { cache: 'no-cache' });
    if (res.ok) items = ((await res.json()).items || []).filter((i) => i.image || i.video);
  } catch (_) { /* offline: leave the strip empty */ }
  const section = strip.closest('section');
  if (!items.length) { if (section) section.hidden = true; return; }
  const play = '<svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M8 5.5v13l10.5-6.5z"/></svg>';
  strip.innerHTML = items.map((it, i) => {
    const alt = esc(it.alt || '');
    if (it.video) {
      const id = ytId(it.video);
      const thumb = id ? `<img src="https://i.ytimg.com/vi/${id}/hqdefault.jpg" alt="" width="480" height="360" loading="lazy" decoding="async">` : '';
      return `<li><figure><div class="media"><button class="media__btn" type="button" data-video="${esc(it.video)}" data-title="${alt}">${thumb}<span class="media__play">${play}Play video<span class="sr-only">: ${alt}</span></span></button></div><figcaption>${alt}</figcaption></figure></li>`;
    }
    return `<li><figure><div class="media" data-img><button class="media__btn" type="button" data-lightbox="${esc(it.image)}" data-alt="${alt}"><img src="${esc(it.image)}" alt="${alt}" width="800" height="450" loading="${i < 2 ? 'eager' : 'lazy'}" decoding="async"><span class="sr-only">Open larger</span></button></div><figcaption>${alt}</figcaption></figure></li>`;
  }).join('');

  $$('[data-img] img', strip).forEach((img) => {
    const check = () => {
      if (!img.naturalHeight) return;
      const r = img.naturalWidth / img.naturalHeight;
      if (r < 1.3 || r > 2.4) img.closest('.media').classList.add('media--contain');
    };
    img.complete ? check() : img.addEventListener('load', check, { once: true });
  });

  strip.addEventListener('click', (e) => {
    const b = e.target.closest('[data-video]');
    if (!b) return;
    const src = b.dataset.video + (b.dataset.video.includes('?') ? '&' : '?') + 'autoplay=1';
    const f = document.createElement('iframe');
    f.src = src; f.title = b.dataset.title || 'Parish video';
    f.allow = 'autoplay; encrypted-media; picture-in-picture; fullscreen'; f.allowFullscreen = true;
    b.replaceWith(f);
    f.focus();
  });

  const prev = $('[data-dir="prev"]');
  const next = $('[data-dir="next"]');
  const step = () => (strip.firstElementChild?.getBoundingClientRect().width || 300) + parseFloat(getComputedStyle(strip).columnGap || '24');
  const sync = () => {
    if (!prev || !next) return;
    prev.disabled = strip.scrollLeft < 8;
    next.disabled = strip.scrollLeft + strip.clientWidth > strip.scrollWidth - 8;
  };
  prev?.addEventListener('click', () => strip.scrollBy({ left: -step(), behavior: reduced ? 'auto' : 'smooth' }));
  next?.addEventListener('click', () => strip.scrollBy({ left: step(), behavior: reduced ? 'auto' : 'smooth' }));
  strip.addEventListener('scroll', () => gsap.delayedCall(0.05, sync), { passive: true });
  sync();
  ScrollTrigger.refresh();
}

/* ---------------------------------------------------------------------
   Tally embeds (harvest, join): load when near, auto-resize
   --------------------------------------------------------------------- */
function initTally() {
  const frames = $$('iframe[data-tally-src]');
  if (!frames.length) return;
  let started = false;
  const load = () => {
    if (window.Tally) { window.Tally.loadEmbeds(); return; }
    $$('iframe[data-tally-src]:not([src])').forEach((f) => { f.src = f.dataset.tallySrc; });
  };
  const start = () => {
    if (started) return;
    started = true;
    io.disconnect();
    const s = document.createElement('script');
    s.src = 'https://tally.so/widgets/embed.js';
    s.onload = load; s.onerror = load;
    document.body.appendChild(s);
  };
  // Only fetch the form when it is about to be seen: saves mobile data and keeps the first scroll smooth.
  const io = new IntersectionObserver((es) => { if (es.some((e) => e.isIntersecting)) start(); }, { rootMargin: '600px 0px' });
  frames.forEach((f) => io.observe(f));
  $$('a[href="#commit"], a[href="#register"]').forEach((a) => a.addEventListener('click', start));
}

/* ---------------------------------------------------------------------
   Boot
   --------------------------------------------------------------------- */
async function boot() {
  const yr = $('[data-year]');
  if (yr) yr.textContent = String(new Date().getFullYear());
  initSurfaces();
  initHeader();
  initMenu();
  initTransitions();
  initWeek();
  initCopy();
  initLightbox();
  initTally();
  initReveals();
  initGallery();
  if (!reduced && surfaces.length) gsap.ticker.add(rippleTick);

  if (document.fonts?.ready) await Promise.race([document.fonts.ready, new Promise((r) => setTimeout(r, 600))]);
  surfaces.forEach((s) => s.measure());
  ScrollTrigger.refresh();
  if (!reduced) surfaces.forEach((s) => { s.intro = 0; apply(s, 0); });
  finishArrival();
  playIntro();
  addEventListener('resize', () => surfaces.forEach((s) => s.measure()));
}

boot();
