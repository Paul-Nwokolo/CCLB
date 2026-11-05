/* =====================================================================
   CCLB Waterline: media behaviour. Load as a module AFTER main.js.
   Same clock as main.js (GSAP's ticker; Lenis already feeds ScrollTrigger).
   Contracts: MEDIA.md. Nothing here is needed for the page to read well:
   without it (or under reduced motion) every frame sits at rest.
   ===================================================================== */
const { gsap, ScrollTrigger } = window;
const reduced = matchMedia('(prefers-reduced-motion: reduce)').matches;
const $$ = (s, r = document) => [...r.querySelectorAll(s)];

/* ---------------------------------------------------------------------
   Surfacing: each frame enters under water and the water drains to its
   foot as the frame rises to the reading line. Scrubbed, transform only.
   Idle: the wave on each visible frame keeps travelling, slowly.
   --------------------------------------------------------------------- */
const live = new Set();
let ticking = false;

function initFrame(frame, index) {
  if (frame.dataset.wlReady) return;
  frame.dataset.wlReady = '1';
  const tide = frame.querySelector('.wl-frame__tide');
  const photo = frame.querySelector('.wl-frame__photo');
  if (!tide || !photo || reduced) return;
  frame._wave = tide.querySelector('.wave');
  frame._phase = index * 7;
  // Distance from full cover to rest: the photo's height above its foot.
  const rest = () => {
    const foot = parseFloat(getComputedStyle(frame).getPropertyValue('--foot')) || 0.14;
    return photo.offsetHeight * (1 - foot);
  };
  frame._tween = gsap.fromTo(tide, { y: () => -rest() }, {
    y: 0, ease: 'none', immediateRender: true,
    scrollTrigger: { trigger: frame, start: 'top 96%', end: 'top 52%', scrub: 0.6, invalidateOnRefresh: true },
  });
  ScrollTrigger.create({
    trigger: frame, start: 'top bottom', end: 'bottom top',
    onToggle: (st) => (st.isActive ? live.add(frame) : live.delete(frame)),
  });
  if (!ticking) {
    ticking = true;
    gsap.ticker.add((t) => {
      for (const f of live) {
        if (f._wave) f._wave.style.transform = `translate3d(${(-((t * 1.6 + f._phase) % 50)).toFixed(3)}%,0,0)`;
      }
    });
  }
}

function initFrames(root = document) {
  $$('.wl-frame', root).forEach((f, i) => { if (!f.closest('[hidden]')) initFrame(f, i); });
}

/* ---------------------------------------------------------------------
   Video facade: thumbnail from i.ytimg.com, youtube-nocookie on click.
   Markup: <button data-yt="VIDEO_ID" data-yt-title="...">
   --------------------------------------------------------------------- */
function initVideo() {
  let warmed = false;
  const warm = () => {
    if (warmed) return;
    warmed = true;
    ['https://www.youtube-nocookie.com', 'https://i.ytimg.com'].forEach((href) => {
      const l = document.createElement('link');
      l.rel = 'preconnect'; l.href = href; l.crossOrigin = '';
      document.head.appendChild(l);
    });
  };
  document.addEventListener('pointerover', (e) => { if (e.target.closest?.('[data-yt]')) warm(); }, { passive: true });
  document.addEventListener('focusin', (e) => { if (e.target.closest?.('[data-yt]')) warm(); });
  document.addEventListener('click', (e) => {
    const b = e.target.closest('[data-yt]');
    if (!b) return;
    const id = b.dataset.yt;
    if (!/^[\w-]{6,20}$/.test(id)) return;
    e.preventDefault();
    const f = document.createElement('iframe');
    f.className = 'wl-frame__embed';
    f.src = `https://www.youtube-nocookie.com/embed/${id}?autoplay=1&rel=0&playsinline=1`;
    f.title = b.dataset.ytTitle || 'Parish video';
    f.allow = 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share';
    f.allowFullscreen = true;
    f.referrerPolicy = 'strict-origin-when-cross-origin'; // YouTube refuses to play without a referrer
    const frame = b.closest('.wl-frame');
    b.parentElement.querySelector(':scope > img')?.remove(); // the thumbnail
    if (frame) {
      frame.classList.add('is-playing');
      frame._tween?.scrollTrigger?.kill();
      const tide = frame.querySelector('.wl-frame__tide');
      if (tide && gsap) gsap.set(tide, { y: 0 });
    }
    b.replaceWith(f);
    f.focus();
  });
}

/* ---------------------------------------------------------------------
   The reach: items 7 to 12 wait behind one button (and do not download
   until then). Focus moves to the first new item.
   --------------------------------------------------------------------- */
function initReach() {
  $$('[data-reach-more]').forEach((btn) => {
    const list = document.getElementById(btn.getAttribute('aria-controls'));
    if (!list) return;
    btn.addEventListener('click', () => {
      const more = $$(':scope > li[hidden]', list);
      more.forEach((li) => { li.hidden = false; });
      btn.closest('.reach__more')?.remove();
      initFrames(list);
      ScrollTrigger?.refresh();
      more[0]?.querySelector('button, a')?.focus({ preventScroll: false });
    });
  });
}

function boot() {
  initVideo();
  initReach();
  if (!gsap || !ScrollTrigger) return;
  initFrames();
  // main.js measures and refreshes after fonts; refresh once more when photos have their size.
  addEventListener('load', () => ScrollTrigger.refresh(), { once: true });
}

boot();
