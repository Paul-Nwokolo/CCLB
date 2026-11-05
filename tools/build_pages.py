# =====================================================================
# build_pages.py: writes the five static pages of the CCLB parish site
# from shared parts (head, header, menu, curtain, footer, lightbox).
#
#   python tools/build_pages.py .        (run from the repo root)
#
# Output: index.html, 404.html, give/index.html, harvest/index.html,
#         join/index.html. These files are overwritten.
#
# Give page CONFIG (bank details, funds, societies, WhatsApp number):
#   read from the existing give/index.html if it is there, so edits the
#   committee made to that file are kept. Otherwise tools/give_config.html
#   is used. The rendering code for the give page is tools/give_render.js.
# Python 3.8+, standard library only.
# =====================================================================
import sys, os, json, re
ROOT = sys.argv[1]
SITE = 'https://cclbparish.com'

def wave_path(w=2400, h=24, period=300):
    mid = h / 2; a = h / 2 - 1
    d = f'M0 {mid:.1f} Q{period/4:.0f} {mid-a:.1f} {period/2:.0f} {mid:.1f}'
    x = period / 2
    while x < w:
        x += period / 2
        d += f' T{x:.0f} {mid:.1f}'
    return d + f' V{h} H0 Z'

WAVE = f'<svg class="wave" viewBox="0 0 2400 24" preserveAspectRatio="none" aria-hidden="true" focusable="false"><path d="{wave_path()}"/></svg>'
def edge(flip=False):
    return f'<div class="edge{" edge--flip" if flip else ""}" aria-hidden="true"><svg viewBox="0 0 2400 24" preserveAspectRatio="none" focusable="false"><path d="{wave_path(period=400)}"/></svg></div>'

IG = '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path fill="currentColor" d="M12 2.16c3.2 0 3.58.01 4.85.07 3.25.15 4.77 1.69 4.92 4.92.06 1.27.07 1.65.07 4.85s-.01 3.58-.07 4.85c-.15 3.23-1.66 4.77-4.92 4.92-1.27.06-1.64.07-4.85.07s-3.58-.01-4.85-.07c-3.26-.15-4.77-1.7-4.92-4.92-.06-1.27-.07-1.64-.07-4.85s.01-3.58.07-4.85C2.38 3.92 3.9 2.38 7.15 2.23 8.42 2.18 8.8 2.16 12 2.16zM12 0C8.74 0 8.33.01 7.05.07 2.7.27.27 2.69.07 7.05.01 8.33 0 8.74 0 12s.01 3.67.07 4.95c.2 4.36 2.62 6.78 6.98 6.98C8.33 23.99 8.74 24 12 24s3.67-.01 4.95-.07c4.35-.2 6.78-2.62 6.98-6.98.06-1.28.07-1.69.07-4.95s-.01-3.67-.07-4.95C23.73 2.7 21.31.27 16.95.07 15.67.01 15.26 0 12 0zm0 5.84a6.16 6.16 0 1 0 0 12.32 6.16 6.16 0 0 0 0-12.32zM12 16a4 4 0 1 1 0-8 4 4 0 0 1 0 8zm6.41-11.85a1.44 1.44 0 1 0 0 2.88 1.44 1.44 0 0 0 0-2.88z"/></svg>'
YT = '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path fill="currentColor" d="M23.5 6.19a3.02 3.02 0 0 0-2.12-2.14C19.5 3.55 12 3.55 12 3.55s-7.5 0-9.38.5A3.02 3.02 0 0 0 .5 6.19C0 8.07 0 12 0 12s0 3.93.5 5.81a3.02 3.02 0 0 0 2.12 2.14c1.88.5 9.38.5 9.38.5s7.5 0 9.38-.5a3.02 3.02 0 0 0 2.12-2.14C24 15.93 24 12 24 12s0-3.93-.5-5.81zM9.55 15.57V8.43L15.82 12l-6.27 3.57z"/></svg>'
ARROW = '<svg class="arrow" viewBox="0 0 20 20" width="20" height="20" aria-hidden="true" focusable="false"><path d="M4 10h11M11 5.5 15.5 10 11 14.5" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>'
ARROW_UR = '<svg viewBox="0 0 20 20" aria-hidden="true" focusable="false"><path d="M6 14 14 6M7.5 6H14v6.5" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>'
COPY = '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><rect x="9" y="9" width="12" height="12" rx="1" fill="none" stroke="currentColor" stroke-width="1.8"/><path d="M5 15V4a1 1 0 0 1 1-1h10" fill="none" stroke="currentColor" stroke-width="1.8"/></svg>'

NAV = [('Give', '/give/'), ('Harvest', '/harvest/'), ('Join', '/join/'), ('Visit', '/#visit')]
MAPS = 'https://maps.google.com/?q=Catholic+Church+of+the+Lord%27s+Baptism+Osapa+Lekki+Lagos'

def head(title, desc, path, extra_head='', native_scroll=False, robots=None):
    canon = SITE + path
    ld = {
        "@context": "https://schema.org", "@type": "Church",
        "name": "Catholic Church of the Lord's Baptism", "alternateName": "CCLB Lekki",
        "url": SITE + "/", "logo": SITE + "/assets/img/crest-192.png", "image": SITE + "/og.png",
        "telephone": "+234 913 222 2286", "email": "info@cclblekki.com",
        "address": {"@type": "PostalAddress", "streetAddress": "Osapa London, off Beach Resort", "addressLocality": "Lekki", "addressRegion": "Lagos", "addressCountry": "NG"},
        "sameAs": ["https://www.instagram.com/cclblekki/", "https://www.youtube.com/@cclblekki"],
    }
    return f'''<!DOCTYPE html>
<html lang="en"{' data-native-scroll' if native_scroll else ''}>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title>{title.replace('’', '&rsquo;')}</title>
<meta name="description" content="{desc}">
<link rel="canonical" href="{canon}">
{f'<meta name="robots" content="{robots}">' if robots else ''}
<meta name="theme-color" content="#0D3A5A">
<meta property="og:type" content="website">
<meta property="og:site_name" content="Catholic Church of the Lord's Baptism">
<meta property="og:title" content="{title}">
<meta property="og:description" content="{desc}">
<meta property="og:url" content="{canon}">
<meta property="og:image" content="{SITE}/og.png">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:image:alt" content="The parish name set on a waterline, river blue below">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{title}">
<meta name="twitter:description" content="{desc}">
<meta name="twitter:image" content="{SITE}/og.png">
<link rel="icon" href="/favicon.ico" sizes="48x48">
<link rel="icon" href="/favicon-32.png" type="image/png" sizes="32x32">
<link rel="apple-touch-icon" href="/apple-touch-icon.png">
<link rel="preload" href="/assets/fonts/gambetta-400-normal.woff2" as="font" type="font/woff2" crossorigin>
<link rel="preload" href="/assets/fonts/general-sans-500-normal.woff2" as="font" type="font/woff2" crossorigin>
<link rel="stylesheet" href="/assets/css/site.css">
<script>(function(){{var d=document.documentElement;d.classList.add('js');if(matchMedia('(prefers-reduced-motion: reduce)').matches)return;d.classList.add('motion');try{{if(sessionStorage.getItem('cclb:arrive'))d.classList.add('arriving')}}catch(e){{}}setTimeout(function(){{if(!window.__booted)d.classList.remove('motion','arriving')}},4000)}})();</script>
<script defer src="/assets/js/vendor/gsap.min.js"></script>
<script defer src="/assets/js/vendor/ScrollTrigger.min.js"></script>
<script defer src="/assets/js/vendor/SplitText.min.js"></script>
{'' if native_scroll else '<script defer src="/assets/js/vendor/lenis.min.js"></script>'}
<script type="module" src="/assets/js/main.js"></script>
<script type="application/ld+json">{json.dumps(ld, ensure_ascii=False)}</script>
{extra_head}
</head>'''

def header(current):
    links = ''.join(
        f'<li><a class="nav__link" href="{h}" data-dest="{n}"{" aria-current=\"page\"" if n == current else ""}>{n}</a></li>' for n, h in NAV)
    mlinks = ''.join(
        f'<li><a href="{h}" data-dest="{n}"{" aria-current=\"page\"" if n == current else ""}>{n}</a></li>' for n, h in [('Home', '/')] + NAV)
    return f'''
<a class="skip-link" href="#main">Skip to content</a>
<div id="curtain" aria-hidden="true"><div class="curtain__panel">{WAVE}<span class="curtain__label"></span></div></div>
<header class="site-header">
  <div class="wrap">
    <a class="brand" href="/" data-dest="Home"{' aria-current="page"' if current == 'Home' else ''}><img src="/assets/img/crest-96.webp" alt="" width="40" height="40"><span>The Lord’s Baptism<span class="sr-only">, Catholic Church, home</span></span></a>
    <nav class="nav" aria-label="Main">
      <ul class="nav__list" role="list">{links}</ul>
      <div class="socials">
        <a class="icon-link" href="https://www.instagram.com/cclblekki/" target="_blank" rel="noopener noreferrer" aria-label="Instagram (opens in a new tab)">{IG}</a>
        <a class="icon-link" href="https://www.youtube.com/@cclblekki" target="_blank" rel="noopener noreferrer" aria-label="YouTube (opens in a new tab)">{YT}</a>
      </div>
    </nav>
    <button class="menu-btn" type="button" data-menu-open aria-expanded="false" aria-controls="menu">Menu</button>
  </div>
</header>
<div class="menu" id="menu" role="dialog" aria-modal="true" aria-label="Menu" hidden>
  <div class="menu__top">
    <span class="brand"><img src="/assets/img/crest-96.webp" alt="" width="36" height="36">The Lord’s Baptism</span>
    <button class="menu-btn" type="button" data-menu-close style="display:inline-flex">Close</button>
  </div>
  <nav aria-label="Menu"><ul class="menu__list" role="list">{mlinks}</ul></nav>
  <div class="menu__foot">
    <p>Sunday Mass 8:30&nbsp;AM<br>Osapa London, Lekki</p>
    <div class="socials">
      <a class="icon-link" href="https://www.instagram.com/cclblekki/" target="_blank" rel="noopener noreferrer" aria-label="Instagram (opens in a new tab)">{IG}</a>
      <a class="icon-link" href="https://www.youtube.com/@cclblekki" target="_blank" rel="noopener noreferrer" aria-label="YouTube (opens in a new tab)">{YT}</a>
    </div>
  </div>
</div>'''

FOOTER = f'''
<footer class="site-footer" data-tone="water">
  {edge(True)}
  <div class="wrap">
    <p class="motto"><span>Lord baptise us</span> <span>with your Spirit</span></p>
    <div class="foot">
      <div class="foot__col"><p class="label">Sunday Mass</p><p>Preparation 8:00&nbsp;AM</p><p>Holy Mass 8:30&nbsp;AM</p></div>
      <div class="foot__col"><p class="label">Find us</p><p>Osapa London, off Beach Resort, Lekki, Lagos</p><a class="link" href="{MAPS}" target="_blank" rel="noopener noreferrer">Directions<span class="sr-only"> (opens Google Maps)</span></a></div>
      <div class="foot__col"><p class="label">Reach the office</p><a href="tel:+2349132222286">+234 913 222 2286</a><a href="mailto:info@cclblekki.com">info@cclblekki.com</a></div>
      <div class="foot__col"><p class="label">Follow</p><a href="https://www.instagram.com/cclblekki/" target="_blank" rel="noopener noreferrer">Instagram<span class="sr-only"> (opens in a new tab)</span></a><a href="https://www.youtube.com/@cclblekki" target="_blank" rel="noopener noreferrer">YouTube<span class="sr-only"> (opens in a new tab)</span></a></div>
    </div>
  </div>
  <div class="foot__fine-wrap"><div class="wrap foot__fine"><p>© <span data-year>2026</span> Catholic Church of the Lord’s Baptism, Osapa Lekki</p><p>Built and maintained by CCLB Lekki</p></div></div>
</footer>
<div class="toast" id="toast" role="status" aria-live="polite"></div>'''

LIGHTBOX = '''
<dialog class="lightbox" id="lightbox" aria-label="Image, larger">
  <button class="lightbox__close" type="button" data-lightbox-close>Close</button>
  <img alt="" width="1200" height="900" decoding="async">
</dialog>'''

def surface(mode, pre, l1, l2, below, refl_line=None, tone='air', sid='title', h1=True):
    refl_line = refl_line or l2
    N = 9
    slices = ''.join(
        f'<span class="reflection__slice" style="clip-path:inset({i*100/N:.2f}% -20% {100-(i+1)*100/N:.2f}% -20%)">{refl_line}</span>' for i in range(N))
    pre_html = f'<span class="pre">{pre}</span> ' if pre else ''
    title_inner = f'{pre_html}<span class="ln ln--1">{l1}</span> <span class="ln ln--2">{l2}</span>' if l1 else f'{pre_html}<span class="ln ln--2">{l2}</span>'
    tag = 'h1' if h1 else 'p'
    return f'''
<section class="surface surface--{mode if mode != 'lost' else 'inner'}{' lost' if mode == 'lost' else ''}" data-surface="{mode}" data-tone="{tone}" aria-labelledby="{sid}">
  <div class="surface__stage">
    <span class="surface__probe" aria-hidden="true"></span>
    <div class="surface__air">
      <{tag} class="surface__title" id="{sid}" data-title>{title_inner}</{tag}>
    </div>
    <div class="surface__water" data-water>
      {WAVE}
      <div class="surface__water-inner" data-water-inner>
        <div class="surface__title surface__title--wet" aria-hidden="true" data-title-wet>{title_inner}</div>
        <div class="reflection" aria-hidden="true"><div class="reflection__flip" data-reflection>{slices}</div></div>
        <div class="surface__below is-water">{below}</div>
      </div>
    </div>
  </div>
</section>'''

def page(name, body_html, current, title, desc, path, extra_head='', native=False, scripts='', robots=None):
    out = head(title, desc, path, extra_head, native, robots) + '\n<body>' + header(current) + '\n<main id="main" tabindex="-1">' + body_html + '\n</main>' + FOOTER + LIGHTBOX + scripts + '\n</body>\n</html>\n'
    p = os.path.join(ROOT, name)
    os.makedirs(os.path.dirname(p), exist_ok=True)
    with open(p, 'w', encoding='utf-8', newline='\n') as f:
        f.write(out)
    print('wrote', name, len(out))

# ------------------------------------------------------------------ HOME
DOVE = '<svg class="dove" viewBox="0 0 100 100" aria-hidden="true" focusable="false"><path d="M50 22 47 10M50 22 53 10M50 22c-4 12-4 30 0 44 4-14 4-32 0-44Z"/><path d="M49 34C38 24 22 20 6 24c14 6 28 14 42 24M51 34c11-10 27-14 43-10-14 6-28 14-42 24"/><path d="M50 66c-3 2-4 6-1 9 2 1 4 0 5-2"/><path d="M50 82v6M38 80l-4 5M62 80l4 5M28 72l-6 3M72 72l6 3"/></svg>'

def slot(time, what, quiet=False):
    return f'<p class="slot{" slot--quiet" if quiet else ""}"><span class="slot__time tnum">{time}</span>{f"<span class=\"slot__what\">{what}</span>" if what else ""}</p>'
WEEK = [
    (0, 'Sunday', [('8:00&nbsp;AM', 'Preparation'), ('8:30&nbsp;AM', 'Holy Mass'), ('11:00&nbsp;AM', 'RCIA catechism')]),
    (1, 'Monday', [('6:30&nbsp;AM', 'Mass')]),
    (2, 'Tuesday', [('9&nbsp;AM to 2&nbsp;PM', 'Priest’s office open', True)]),
    (3, 'Wednesday', [('6:30&nbsp;AM', 'Mass'), ('9&nbsp;AM to 2&nbsp;PM', 'Priest’s office open', True)]),
    (4, 'Thursday', [('No Mass listed', '', True)]),
    (5, 'Friday', [('6:30&nbsp;AM', 'Mass'), ('9&nbsp;AM to 2&nbsp;PM', 'Priest’s office open', True)]),
    (6, 'Saturday', [('8:00&nbsp;AM', 'Mass'), ('8:45&nbsp;AM', 'Confession'), ('9:00&nbsp;AM', 'Children’s catechism')]),
]
days = ''.join(
    f'<li class="day" data-day="{d}"><p class="day__name">{n}<span class="day__today">Today</span></p><div>' + ''.join(slot(*s) for s in slots) + '</div></li>' for d, n, slots in WEEK)

MONTH = [
    ('Infant baptism', 'First Saturday, at the 8:00&nbsp;AM Mass'),
    ('Pre-baptism instructions', 'Parents and godparents. Last Saturday, 9:00&nbsp;AM'),
    ('Communion to the sick and aged', 'First Friday, from 9:00&nbsp;AM'),
    ('Eucharistic adoration', 'Last Friday, 6:30&nbsp;PM'),
    ('Small Christian Communities', 'First and third Sundays, 5:00 to 6:00&nbsp;PM'),
    ('Organisations meeting', 'Second Sunday, 10:30&nbsp;AM'),
]
rites = ''.join(f'<div class="rite"><dt>{a}</dt><dd>{b}</dd></div>' for a, b in MONTH)

home_below = '''
          <p class="where"><span class="label">Where</span>Osapa London, off Beach Resort, Lekki, Lagos</p>
          <p class="next"><span class="label">Next Mass</span><span class="next__when" data-next-mass>Sunday, 8:30&nbsp;AM</span></p>
          <a class="btn btn--solid cta" href="#week">See the parish week ''' + ARROW + '</a>'

doors = [
    ('/give/', 'Give', 'Give', 'Offertory, tithe, projects, harvest and societies', 'Every parish account in one place, with one tap to copy the number.'),
    ('/harvest/', 'Harvest', 'Harvest', 'Harvest of God’s Faithfulness, 2026', 'Make your Commitment Sunday pledge and see this year’s theme.'),
    ('/join/', 'Join', 'Join', 'Register your household', 'Add your family to the parish register so we can welcome and serve you.'),
]
doors_html = ''.join(
    f'<li><a class="door" href="{h}" data-dest="{d}"><span class="door__name">{n}</span><span class="door__what"><strong>{a}</strong><span>{b}</span></span><span class="door__go" aria-hidden="true">{ARROW_UR}</span></a></li>' for h, d, n, a, b in doors)

home = surface('home', 'Catholic Church of', 'the Lord’s', 'Baptism', home_below) + f'''
<section class="band band--normal" id="week" data-tone="water" aria-labelledby="week-title">
  <div class="wrap grid">
    <div class="week__head">
      <h2 class="h-xl" id="week-title" data-split>The parish week</h2>
      <p>Mass, confession and the priest’s office, Sunday to Saturday. All times are Lagos time.</p>
    </div>
    <ol class="week__grid" role="list">{days}</ol>
    <p class="week__foot">Public holidays: Mass at 8:00&nbsp;AM.</p>
  </div>
</section>

<section class="band band--airy" data-tone="air" aria-label="Matthew 3:16">
  {edge()}
  <div class="wrap grid">
    {DOVE}
    <blockquote class="statement" data-split>And when Jesus was baptized, <em>he went up immediately from the water,</em> and behold, the heavens were opened and he saw the Spirit of God descending like a dove.</blockquote>
    <p class="statement__cite">Matthew 3:16. The moment this parish is named for.</p>
  </div>
</section>

<section class="band band--tight" data-tone="air" aria-labelledby="doors-title">
  <div class="wrap grid">
    <div class="doors__head"><h2 class="h-l" id="doors-title" data-split>Three ways in</h2></div>
    <ul class="doors__list" role="list">{doors_html}</ul>
  </div>
</section>

<section class="band band--airy" data-tone="air" aria-labelledby="month-title">
  <div class="wrap grid">
    <div class="month__head">
      <h2 class="h-xl" id="month-title" data-split>Through the month</h2>
      <p>Sacraments, devotions and meetings that come round once or twice a month.</p>
    </div>
    <dl class="month__list">{rites}</dl>
  </div>
</section>

<section class="band band--normal" id="gallery" data-tone="water" aria-labelledby="gallery-title">
  {edge()}
  <div class="wrap gallery__head">
    <div><h2 class="h-xl" id="gallery-title" data-split>From the parish</h2><p>Photos and recordings from parish life, posted by the parish office.</p></div>
    <div class="strip-ctrl"><button class="round-btn" type="button" data-dir="prev" aria-label="Previous"><svg viewBox="0 0 20 20" aria-hidden="true"><path d="M16 10H5M9 5.5 4.5 10 9 14.5" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg></button><button class="round-btn" type="button" data-dir="next" aria-label="Next"><svg viewBox="0 0 20 20" aria-hidden="true"><path d="M4 10h11M11 5.5 15.5 10 11 14.5" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg></button></div>
  </div>
  <ul class="strip" role="list" data-strip aria-label="Parish photos and videos"><li class="strip-empty">Loading the parish gallery.</li></ul>
</section>

<section class="band band--airy" id="visit" data-tone="air" aria-labelledby="visit-title">
  {edge(True)}
  <div class="wrap grid">
    <div class="visit__addr">
      <p class="label">Visit</p>
      <h2 class="h-xl" id="visit-title" data-split>Osapa London, off Beach Resort, Lekki</h2>
      <p class="meta" style="margin-top:1rem">Lagos, Nigeria</p>
      <a class="btn" href="{MAPS}" target="_blank" rel="noopener noreferrer">Open in Google Maps {ARROW}<span class="sr-only"> (opens in a new tab)</span></a>
    </div>
    <div class="visit__facts">
      <div class="fact"><p class="label">Phone</p><a href="tel:+2349132222286">+234 913 222 2286</a></div>
      <div class="fact"><p class="label">Email</p><a href="mailto:info@cclblekki.com">info@cclblekki.com</a></div>
      <div class="fact"><p class="label">Priest’s office</p><p>Tuesday, Wednesday and Friday, 9:00&nbsp;AM to 2:00&nbsp;PM</p></div>
    </div>
  </div>
</section>'''

page('index.html', home, 'Home',
     'Catholic Church of the Lord’s Baptism | Osapa, Lekki',
     'A Catholic parish in Osapa London, Lekki, Lagos. Sunday Mass at 8:30 AM, weekday Masses, confession, giving, Harvest 2026 and parish registration.',
     '/', extra_head='<link rel="preload" href="/assets/fonts/gambetta-400-italic.woff2" as="font" type="font/woff2" crossorigin>')

# ------------------------------------------------------------------ 404
lost_below = '''
          <p class="lede">The link may be old, or the page has moved. Everything else is still here.</p>
          <a class="btn btn--solid cta" href="/" data-dest="Home">Come up to the home page ''' + ARROW + '</a>'
page('404.html', surface('lost', 'Page not found', 'This page has', 'gone under', lost_below), '',
     'Page not found | Catholic Church of the Lord’s Baptism',
     'This page could not be found. Return to the home page of the Catholic Church of the Lord’s Baptism, Osapa, Lekki.',
     '/404.html', robots='noindex')

# ------------------------------------------------------------------ JOIN
join_below = '''
          <p class="lede">Add your household to our parish register so we can welcome you, keep in touch, and serve your family well.</p>
          <a class="btn btn--solid cta" href="#register">Start the form ''' + ARROW + '</a>'
join = surface('inner', 'New to the parish?', 'Join the', 'parish register', join_below) + f'''
<section class="band band--normal" id="register" data-tone="air" aria-labelledby="register-title">
  {edge(True)}
  <div class="wrap grid">
    <div class="section-head" style="grid-column:1/span 7;margin-bottom:2.5rem">
      <h2 class="h-l" id="register-title" data-split>Register your household</h2>
      <p>One form for the whole family. It takes about five minutes.</p>
    </div>
    <a class="skip-form" href="#after-form">Skip past the form</a>
    <div class="embed embed--card">
      <iframe data-tally-src="https://tally.so/embed/A7Q55N?alignLeft=1&amp;hideTitle=1&amp;transparentBackground=1&amp;dynamicHeight=1" loading="lazy" width="100%" height="560" title="Parishioner registration form"></iframe>
      <noscript><p>Please enable JavaScript to load the registration form, or <a class="link" href="https://tally.so/r/A7Q55N">open the form in a new tab</a>.</p></noscript>
      <p class="embed__fallback">Form not showing? <a class="link" href="https://tally.so/r/A7Q55N" target="_blank" rel="noopener noreferrer">Open it in a new tab</a>.</p>
    </div>
  </div>
  <div class="wrap grid" style="margin-top:3.5rem" id="after-form" tabindex="-1">
    <div class="notice" role="note">
      <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M12 3 4.5 6v5.5c0 4.6 3.2 8.4 7.5 9.5 4.3-1.1 7.5-4.9 7.5-9.5V6L12 3Z" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/><path d="m8.8 12 2.2 2.2 4.2-4.4" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
      <h2>How we use your information</h2>
      <p>The <strong>Catholic Church of the Lord’s Baptism</strong> collects these details only to keep our parish register and to contact you about parish life. They are stored securely in our parish records and are never sold or shared outside the parish.</p>
      <p>By submitting the form you consent to the parish holding and using these details for those purposes. You can ask to view, correct or delete your information at any time by contacting the parish office at <a class="link" style="display:inline-block;padding-block:0.2rem" href="mailto:info@cclblekki.com">info@cclblekki.com</a>.</p>
    </div>
  </div>
</section>'''
page('join/index.html', join, 'Join',
     'Join the parish register | Catholic Church of the Lord’s Baptism',
     'Register your household with the Catholic Church of the Lord’s Baptism, Osapa, Lekki, so the parish can welcome you and keep in touch.',
     '/join/', native=True)

# ------------------------------------------------------------------ HARVEST
POSTERS = [1, 2, 3]
posters = ''.join(f'''<li><button class="poster" type="button" data-lightbox="/harvest/Harvest-of-Gods-Faithfulness-Poster-{i}.webp" data-alt="Harvest of God’s Faithfulness, poster {i} of 3"><picture><source srcset="/harvest/Harvest-of-Gods-Faithfulness-Poster-{i}.webp" type="image/webp"><img src="/harvest/Harvest-of-Gods-Faithfulness-Poster-{i}.jpg" width="519" height="1280" alt="Harvest of God’s Faithfulness, poster {i} of 3 for the 2026 parish harvest" loading="lazy" decoding="async"></picture><span class="poster__tag">View poster {i}</span></button></li>''' for i in POSTERS)
harvest_below = '''
          <p class="lede">Giving back from God’s abundance. Be the strongest link for God.</p>
          <a class="btn btn--solid cta" href="#commit">Make your commitment ''' + ARROW + '</a>'
harvest = surface('inner', 'Commitment Sunday 2026', 'Harvest of God’s', 'Faithfulness', harvest_below) + f'''
<section class="band band--normal" id="commit" data-tone="air" aria-labelledby="commit-title">
  {edge(True)}
  <div class="wrap grid">
    <div class="section-head" style="grid-column:1/span 7;margin-bottom:2.5rem">
      <h2 class="h-l" id="commit-title" data-split>Make your commitment</h2>
      <p>Complete the form to make your Commitment Sunday pledge towards this year’s Harvest of God’s Faithfulness.</p>
    </div>
    <a class="skip-form" href="#account">Skip past the form</a>
    <div class="embed embed--card">
      <iframe data-tally-src="https://tally.so/embed/VLBLXa?alignLeft=1&amp;hideTitle=1&amp;transparentBackground=1&amp;dynamicHeight=1" loading="lazy" width="100%" height="560" title="Harvest commitment form"></iframe>
      <noscript><p>Please enable JavaScript to load the form, or <a class="link" href="https://tally.so/r/VLBLXa">open the form in a new tab</a>.</p></noscript>
      <p class="embed__fallback">Form not showing? <a class="link" href="https://tally.so/r/VLBLXa" target="_blank" rel="noopener noreferrer">Open it in a new tab</a>.</p>
    </div>
  </div>
</section>

<section class="band band--normal" id="account" data-tone="air" aria-labelledby="acct-title" tabindex="-1">
  <div class="wrap grid">
    <div class="section-head">
      <h2 class="h-l" id="acct-title" data-split>The harvest account</h2>
      <p>Give to this year’s harvest by bank transfer. Tap to copy, then paste into your bank app.</p>
    </div>
    <div class="account-panel">
      <div class="account-card">
        <p class="account-card__name">Annual Harvest</p>
        <div class="acct">
          <div class="acct__row"><p class="label">Account name</p><p class="acct__value">Catholic Church of the Lord’s Baptism</p><button class="chip-btn" type="button" data-copy="Catholic Church of the Lord's Baptism"><span data-copy-label>Copy name</span></button></div>
          <div class="acct__row"><p class="label">Bank</p><p class="acct__value">Zenith Bank</p></div>
          <div class="acct__row"><p class="label">Account number</p><p class="acct__number">1312 4872 04</p><button class="chip-btn" type="button" data-copy="1312487204">{COPY}<span data-copy-label>Copy number</span></button></div>
        </div>
        <p class="verify">Before sending, check the account name reads <strong>Catholic Church of the Lord’s Baptism</strong>. The parish never collects gifts through personal accounts.</p>
      </div>
    </div>
  </div>
</section>

<section class="band band--airy" data-tone="water" aria-labelledby="verse">
  {edge()}
  <div class="wrap grid">
    <blockquote class="verse" id="verse" data-split>His faithfulness endures forever.<cite>Lamentations 3:23, the theme of Harvest 2026</cite></blockquote>
  </div>
</section>

<section class="band band--airy" id="theme" data-tone="air" aria-labelledby="theme-title">
  {edge(True)}
  <div class="wrap grid">
    <div class="section-head">
      <h2 class="h-l" id="theme-title" data-split>The harvest theme</h2>
      <p>Three posters for this year’s celebration. Open one to see it in full.</p>
    </div>
    <ul class="posters" role="list">{posters}</ul>
  </div>
</section>'''
page('harvest/index.html', harvest, 'Harvest',
     'Harvest 2026 | Catholic Church of the Lord’s Baptism, Lekki',
     'Make your Commitment Sunday pledge for Harvest 2026, give to the harvest account, and see the theme posters. Catholic Church of the Lord’s Baptism, Lekki.',
     '/harvest/', native=True)

# ------------------------------------------------------------------ GIVE
give_below = '''
          <p class="lede" id="hero-lede">Every gift sustains the worship, works of mercy, and growth of our parish family. Thank you for giving.</p>
          <a class="btn btn--solid cta" href="#give">See ways to give ''' + ARROW + '</a>'
WA = '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path fill="currentColor" d="M17.47 14.38c-.3-.15-1.76-.87-2.03-.97-.27-.1-.47-.15-.67.15-.2.3-.77.97-.94 1.17-.17.2-.35.22-.64.07-.3-.15-1.26-.46-2.4-1.48-.89-.79-1.49-1.77-1.66-2.07-.17-.3-.02-.46.13-.6.13-.14.3-.35.45-.52.15-.17.2-.3.3-.5.1-.2.05-.37-.03-.52-.07-.15-.67-1.61-.92-2.2-.24-.58-.49-.5-.67-.51h-.57c-.2 0-.52.07-.79.37-.27.3-1.04 1.02-1.04 2.48s1.07 2.88 1.21 3.07c.15.2 2.1 3.2 5.08 4.49.71.31 1.26.49 1.69.63.71.22 1.36.19 1.87.12.57-.09 1.76-.72 2.01-1.41.25-.7.25-1.29.17-1.41-.07-.13-.27-.2-.57-.35zM12.05 21.8h-.01a9.87 9.87 0 0 1-5.03-1.38l-.36-.21-3.74.98 1-3.65-.24-.37a9.86 9.86 0 0 1-1.51-5.26c0-5.45 4.44-9.88 9.89-9.88 2.64 0 5.12 1.03 6.99 2.9a9.82 9.82 0 0 1 2.89 6.99c0 5.45-4.44 9.88-9.88 9.88zm8.41-18.3A11.81 11.81 0 0 0 12.05 0C5.5 0 .16 5.34.16 11.89c0 2.1.55 4.14 1.59 5.95L.06 24l6.3-1.65a11.88 11.88 0 0 0 5.68 1.45h.01c6.55 0 11.89-5.34 11.89-11.89 0-3.18-1.24-6.16-3.48-8.41z"/></svg>'
give = surface('inner', 'Give to the parish', 'Your generosity', 'ripples outward', give_below) + f'''
<section class="band band--tight" data-tone="air" aria-labelledby="how-title" style="padding-top:calc(var(--u)*12)">
  {edge(True)}
  <div class="wrap grid">
    <div class="section-head"><h2 class="h-l" id="how-title" data-split>Giving takes one minute</h2></div>
    <ol class="steps" role="list">
      <li class="step"><h3><span class="rubric">Choose a fund.</span></h3><p>Offertory, a project, the harvest, or one of the parish societies.</p></li>
      <li class="step"><h3><span class="rubric">Copy the account number.</span></h3><p>One tap copies it, so there are no typing mistakes.</p></li>
      <li class="step"><h3><span class="rubric">Transfer in your bank app.</span></h3><p>Paste it into your bank app or USSD and send your gift.</p></li>
    </ol>
  </div>
</section>

<section class="band band--normal" id="give" data-tone="air" aria-labelledby="give-title">
  <div class="wrap grid">
    <div class="section-head" style="grid-column:1/span 6;margin-bottom:2.5rem">
      <h2 class="h-l" id="give-title" data-split>Choose a fund</h2>
      <p>Select a fund to see its bank details.</p>
    </div>
    <ul class="funds" id="fund-list" role="list" aria-label="Parish funds"></ul>
    <div class="account-panel" id="fund-detail" aria-live="polite"></div>
  </div>
</section>

<section class="band band--normal" id="confirm" data-tone="water" aria-labelledby="confirm-title">
  {edge()}
  <div class="wrap grid">
    <div class="section-head">
      <h2 class="h-l" id="confirm-title" data-split>Let us know</h2>
      <p>Already sent your gift? Send the parish a quick confirmation on WhatsApp so we can thank you and keep our records right. This is optional.</p>
    </div>
    <form class="form-grid" id="confirm-form" novalidate>
      <div class="field"><label for="cf-name">Your name</label><input class="input" id="cf-name" type="text" autocomplete="name" placeholder="e.g. Mary Okafor"></div>
      <div class="field"><label for="cf-fund">Which fund</label><select class="input" id="cf-fund"></select></div>
      <div class="field"><label for="cf-amount">Amount (optional)</label><input class="input" id="cf-amount" type="text" inputmode="numeric" placeholder="e.g. 10,000"></div>
      <div class="field"><label for="cf-ref">Transfer reference or teller (optional)</label><input class="input" id="cf-ref" type="text" placeholder="e.g. TRX-48213"></div>
      <button class="btn btn--solid btn-wa" type="submit">{WA} Send confirmation on WhatsApp</button>
      <p class="form-tip">You can attach a photo of your receipt in the WhatsApp chat after it opens.</p>
    </form>
  </div>
</section>

<section class="band band--airy" id="contact" data-tone="air" aria-labelledby="contact-title">
  {edge(True)}
  <div class="wrap grid">
    <div class="section-head"><h2 class="h-l" id="contact-title" data-split>Questions about giving?</h2><p>The parish office can help with receipts, Mass bookings and society accounts.</p></div>
    <div class="visit__facts" id="contact-grid">
      <div class="fact"><p class="label">Phone</p><a href="tel:+2349132222286">+234 913 222 2286</a></div>
      <div class="fact"><p class="label">Email</p><a href="mailto:info@cclblekki.com">info@cclblekki.com</a></div>
      <div class="fact"><p class="label">Priest’s office</p><p>Tuesday, Wednesday and Friday, 9:00&nbsp;AM to 2:00&nbsp;PM</p></div>
    </div>
  </div>
</section>'''
HERE = os.path.dirname(os.path.abspath(__file__))
MARK_START = 'CHURCH COMMITTEE: EDIT ONLY THE CONFIG BLOCK'
MARK_END = '  /* =========================================================\n     RENDERING'
def give_config():
    live = os.path.join(ROOT, 'give', 'index.html')
    if os.path.exists(live):
        s = open(live, encoding='utf-8').read()
        a, b = s.find(MARK_START), s.find(MARK_END)
        a = s.rfind('<!--', 0, a) if a != -1 else -1  # start at the comment that holds the marker
        if a != -1 and b > a:
            return s[a:b]
    return open(os.path.join(HERE, 'give_config.html'), encoding='utf-8').read()
GIVE_SCRIPT = (give_config() + open(os.path.join(HERE, 'give_render.js'), encoding='utf-8').read()).replace('%%COPY%%', COPY).replace('%%WA%%', WA)
page('give/index.html', give, 'Give',
     'Give | Catholic Church of the Lord’s Baptism, Osapa Lekki',
     'Give to the Catholic Church of the Lord’s Baptism, Osapa Lekki: offertory, Mass booking and tithe, donation projects, the annual harvest and parish societies.',
     '/give/', scripts=GIVE_SCRIPT)
