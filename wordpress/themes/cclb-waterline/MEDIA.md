# Media layer: slots, markup and CSS contracts

The image and video layer for the CCLB Waterline theme. **Design only**: the CSS and JavaScript are
finished and tested in a static prototype; the PHP that prints this markup is still to be written.
Why it looks the way it does is in `DESIGN.md` at the repo root ("Addendum: images and video").
What the parish should photograph next is in `SHOTLIST.md`.

| File | What it is |
|---|---|
| `assets/css/media.css` | The whole media layer. Load after `site.css` |
| `assets/js/media.js` | Surfacing, idle wave, video facade, "Show more". ES module, load **after** `main.js` |
| `prototype/media.html` | Static home page with every slot filled. Open it through any local server from the theme folder (`python -m http.server`, then `/prototype/media.html`). `?n=0`, `1`, `3`, `6`, `12` sets the gallery count |
| `prototype/img/` | WebP files for the prototype only. Real parish photos with **no identifiable people** |
| `SHOTLIST.md` | Photos to take next, for the parish office |

---

## 1. Slots (what the parish office fills in Parish Details)

Every slot is optional. When a slot is empty, that part of the page looks exactly as it does today.

| Slot | Where it appears | Shape on the page | Upload at least | Alt text | People allowed? | Empty slot |
|---|---|---|---|---|---|---|
| `hero` | Home, under the waterline, behind "Where / Next Mass" | A wide strip just under the surface: about 4:1 on a laptop, about 1:1 on a phone. Tinted river blue | **1920 × 1280** (3:2 landscape). Keep the subject in the middle third, top to bottom | None (decorative, `alt=""`) | Backs or far away only. No crowd shots (too heavy, see budget) | Hero is type only, as today |
| `week` | Home, "The parish week", under the heading (after the timetable on tablets and phones) | **4:5 portrait** | **960 × 1200** | Required. Say what and where, e.g. "The altar in green for Ordinary Time, under the crucifix" | No faces needed: the altar, the sanctuary, an empty church before Mass | Timetable only |
| `door_give` | Home, "Three ways in", Give row. Rises with the water on hover (laptops only) | **3:2 landscape**, small | **720 × 480** | None (decorative; the row's text is the link) | Hands only (offertory, envelopes) | Row as today |
| `door_harvest` | Harvest row, same | **3:2** | **720 × 480** | None | Produce, the altar on Harvest Sunday | Row as today |
| `door_join` | Join row, same | **3:2** | **720 × 480** | None | The welcome desk, the gate, the register | Row as today |
| `visit` | Home, "Visit", under the address | **3:2 landscape** | **1600 × 1067** | Required. What a first-time visitor will see, e.g. "The parish gate on Osapa London road" | Not needed | Address and contacts only, as today |
| `visit_video` | Home, "Visit", beside the photo | **16:9** (YouTube) | A YouTube address | Title + one-line caption + credit (required if the video is not the parish's own) | n/a | Not shown |
| `gallery` (existing repeater) | Home, "From the parish" | Each photo keeps **its own shape**, trimmed to a range set by its position (below) | **1600 px on the long side** (1200 accepted) | Required. It is also the visible caption, 160 characters at most | Only with the permission of everyone recognisable; children only with written guardian consent | Section hidden |

**Gallery: how many.** At most **12** are shown, in the order the office sets (put the newest first).
Items 1 to 6 appear at once; 7 to 12 wait behind a "Show 6 more" button and are not downloaded
until it is pressed. A 13th item is ignored (Parish Details should say so).

**Gallery: how each position trims a photo** (the layout repeats every six items):

| Position | Columns (12-col grid) | Accepted shape (width ÷ height) |
|---|---|---|
| 1, 7 (large) | 1 to 7 | 1.25 to 2.4 (landscape, panoramas fine) |
| 2, 8 (small, set lower) | 9 to 12 | 0.8 to 1.78 (portrait welcome) |
| 3, 9 (middle) | 5 to 10 | 1.0 to 1.78 |
| 4, 10 (small, set lower) | 1 to 4 | 0.8 to 1.78 |
| 5, 11 (large) | 5 to 12 | 1.25 to 2.4 |
| 6, 12 (middle) | 5 to 10 | 1.0 to 1.78 |
| Only one item | 1 to 9 | 1.4 to 1.78 |

A photo outside its range is trimmed from the centre (or from `focus`, if set). Videos are always 16:9.

**Focus point (optional, every image slot).** A field like `50% 35%` (left %, top %) that keeps the
important part in view when the shape trims the photo. Default `50% 50%`.

---

## 2. Data shape (option `cclb_parish`)

Add one key, `media`, and two optional fields on gallery items. Store **attachment IDs**, not URLs,
so WordPress can build `srcset` and report width, height and file size.

```php
'media' => array(
	'hero'         => array( 'id' => 0, 'focus' => '50% 50%' ),
	'week'         => array( 'id' => 0, 'alt' => '', 'focus' => '' ),
	'door_give'    => array( 'id' => 0, 'focus' => '' ),
	'door_harvest' => array( 'id' => 0, 'focus' => '' ),
	'door_join'    => array( 'id' => 0, 'focus' => '' ),
	'visit'        => array( 'id' => 0, 'alt' => '', 'focus' => '' ),
	'visit_video'  => array( 'url' => '', 'title' => '', 'caption' => '', 'credit' => '' ),
),
// gallery items, existing:  array( 'image' => url, 'video' => embed url, 'alt' => text )
// add (optional):           'id' => attachment id, 'credit' => 'Photo: …', 'focus' => '50% 40%'
```

- The plugin's "Choose photo" button already opens the media frame; save `attachment.id` as well as
  the URL. For older items with a URL only, resolve once on save with `attachment_url_to_postid()`.
- `focus`: accept only `/^\d{1,3}% \d{1,3}%$/`, otherwise store `''`.
- Videos: keep the current validation (`youtube.com/embed/ID` or `youtube-nocookie.com/embed/ID`) and
  also accept `youtube.com/watch?v=ID` and `youtu.be/ID`. The theme only needs the 11-character ID.
- Required alt: refuse to save an informative image (`week`, `visit`, gallery) without alt text.
- Parish Details should show each chosen image's generated file size and warn above **120 KB** for
  `hero` and **80 KB** for the rest (read `filesize` from the attachment metadata of the size used).

---

## 3. Image sizes and formats (`functions.php`)

```php
add_action( 'after_setup_theme', function () {
	add_image_size( 'cclb-480', 480, 0 );
	add_image_size( 'cclb-800', 800, 0 );
	add_image_size( 'cclb-1024', 1024, 0 );
	add_image_size( 'cclb-1280', 1280, 0 );
	add_image_size( 'cclb-hero-tall', 900, 1350, true ); // phone hero, hard crop 2:3
} );
// WebP for every generated size (WordPress 5.8+). AVIF saves only 10 to 20% on these photos: optional.
add_filter( 'image_editor_output_format', function ( $f ) {
	return array_merge( $f, array( 'image/jpeg' => 'image/webp', 'image/png' => 'image/webp' ) );
} );
add_filter( 'wp_editor_set_quality', function ( $q, $mime ) { return 'image/webp' === $mime ? 58 : $q; }, 10, 2 );
```

After switching the theme on, run a thumbnail regeneration once (for example the "Regenerate
Thumbnails" plugin) so photos already in the library get these sizes.

### Weight budget (measured on the parish's own photos, WebP quality 58)

| Image | Size used | Typical | Worst case seen | Budget |
|---|---|---|---|---|
| Hero, laptop | `cclb-1280` | 62 to 94 KB | 125 KB (a 40-person crowd) | ≤ 120 KB |
| Hero, phone | `cclb-hero-tall` | 39 to 63 KB | 105 KB | ≤ 120 KB |
| Gallery large | `cclb-1024` | 46 to 69 KB | 93 KB (crowd) | ≤ 80 KB typical |
| Gallery small, week, visit | `cclb-800` | 32 to 48 KB | 66 KB | ≤ 80 KB |
| Door photo | `cclb-480` | 11 to 19 KB | 31 KB | ≤ 40 KB, laptops only |
| YouTube thumbnail | i.ytimg.com | 20 to 61 KB | 66 KB | ≤ 80 KB |

**Page totals (prototype, measured):** first screen adds **92 KB** on a laptop and **43 KB** on a
phone (the hero only). Scrolling the whole home page with 6 gallery items loads **417 KB** of images
on a laptop and **404 KB** on a phone, including the crest and two video thumbnails. "Show 6 more"
adds up to about 350 KB. Door photos cost **0 KB** on touch devices and load only on hover on a laptop.
**Budget: ≤ 120 KB on the first screen, ≤ 450 KB for the home page with 6 gallery items, ≤ 800 KB
with 12.**

---

## 4. Markup contracts

Class names are the contract; `media.css` and `media.js` depend on them. Escape everything
(`esc_attr`, `esc_url`, `esc_html`). `--ar` is the image's own `width/height` from the attachment
metadata (e.g. `style="--ar:1435/812"`). `width` and `height` on `<img>` are the real pixel size.
Layout shift is prevented by the frame's `aspect-ratio`, so the page stays still even before an image arrives.

### 4.1 The frame (every photo except the hero and doors)

Non-interactive (week, visit). The `<img>` carries the alt, so the visible caption is hidden from
screen readers to avoid reading it twice:

```html
<figure class="wl-frame week__photo" style="--ar:925/1156;--focus:50% 40%">
  <div class="wl-frame__photo">
    <img src="…-800.webp" srcset="…-480.webp 480w, …-800.webp 800w" sizes="(SEE 4.8)"
         alt="Where the week begins: the altar in green for Ordinary Time, under the crucifix."
         width="925" height="1156" loading="lazy" decoding="async">
  </div>
  <div class="wl-frame__water" aria-hidden="true"><div class="wl-frame__tide">WAVE_SVG</div></div>
  <figcaption class="wl-frame__cap" aria-hidden="true">Where the week begins: …</figcaption>
</figure>
```

`WAVE_SVG` is `cclb_wave()` from `inc/icons.php`, unchanged.

### 4.2 The frame that opens the lightbox (gallery photos)

The `<img>` is a **sibling before** the button, not inside it (the water sits between them).
The image is described by the caption, so `alt=""` plus `aria-describedby`:

```html
<figure class="wl-frame" style="--ar:2560/1326">
  <div class="wl-frame__photo">
    <img src="…" srcset="…" sizes="(SEE 4.8)" alt="" width="2560" height="1326" loading="lazy" decoding="async">
    <button class="wl-frame__btn" type="button" data-lightbox="LARGE_URL (cclb-1280)" data-alt="CAPTION" aria-describedby="reach-cap-4">
      <span class="wl-frame__hint">Open larger<span class="sr-only"> photo</span></span>
    </button>
  </div>
  <div class="wl-frame__water" aria-hidden="true"><div class="wl-frame__tide">WAVE_SVG</div></div>
  <figcaption class="wl-frame__cap" id="reach-cap-4">CAPTION<small>Photo: NAME</small></figcaption>
</figure>
```

The lightbox itself is the existing one in `footer.php` / `main.js` (`[data-lightbox]`).

### 4.3 The video facade (gallery videos and `visit_video`)

Nothing from YouTube loads until the button is pressed. Then `media.js` swaps in
`https://www.youtube-nocookie.com/embed/ID?autoplay=1&rel=0&playsinline=1` and moves focus to it.

```html
<figure class="wl-frame wl-frame--video visit__video">
  <div class="wl-frame__photo">
    <img src="https://i.ytimg.com/vi/ID/hqdefault.jpg"
         srcset="https://i.ytimg.com/vi/ID/mqdefault.jpg 320w, https://i.ytimg.com/vi/ID/hqdefault.jpg 480w, https://i.ytimg.com/vi/ID/sddefault.jpg 640w"
         sizes="(SEE 4.8)" alt="" width="480" height="360" loading="lazy" decoding="async">
    <button class="wl-frame__btn" type="button" data-yt="ID" data-yt-title="TITLE" aria-describedby="visit-video-cap">
      <span class="wl-play">PLAY_SVG Play<span class="sr-only"> video: TITLE. Loads YouTube.</span></span>
    </button>
  </div>
  <div class="wl-frame__water" aria-hidden="true"><div class="wl-frame__tide">WAVE_SVG</div></div>
  <figcaption class="wl-frame__cap" id="visit-video-cap">CAPTION<small>Video: CREDIT</small></figcaption>
</figure>
```

`PLAY_SVG`: `<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path fill="currentColor" d="M8 5.5v13l10.5-6.5z"/></svg>`.
`ID` must match `/^[\w-]{6,20}$/` (media.js checks again). YouTube's 4:3 thumbnails have black
bars; the 16:9 frame trims them exactly.

### 4.4 Hero (`cclb_surface()` in `inc/parts.php`)

Add an optional `$photo` argument (attachment ID and focus). When set, add `surface--photo` to the
section's classes and print a `<picture>` **inside `.surface__water`, after the wave SVG and before
`.surface__water-inner`**:

```html
<section class="surface surface--home surface--photo" data-surface="home" …>
  …
  <div class="surface__water" data-water>
    WAVE_SVG
    <picture class="surface__photo" aria-hidden="true" style="--focus:50% 62%">
      <source media="(orientation: portrait)" srcset="…-hero-tall.webp 900w" sizes="100vw">
      <img src="…-1280.webp" srcset="…-800.webp 800w, …-1280.webp 1280w" sizes="100vw"
           alt="" width="W" height="H" fetchpriority="high" decoding="async">
    </picture>
    <div class="surface__water-inner" data-water-inner> … unchanged … </div>
  </div>
</section>
```

Never `loading="lazy"` on the hero. Preload it in `wp_head` (home only):

```html
<link rel="preload" as="image" imagesrcset="…-800.webp 800w, …-1280.webp 1280w" imagesizes="100vw" media="(orientation: landscape)" fetchpriority="high">
<link rel="preload" as="image" href="…-hero-tall.webp" media="(orientation: portrait)" fetchpriority="high">
```

The photo rides with the water (it is inside the moving water layer), so `main.js` needs no change.

### 4.5 Week (`front-page.php`)

Add `week--photo` to the section and print the 4.1 frame (with class `week__photo`) **after** the
`<ol class="week__grid">` and before `.week__foot`. CSS places it under the heading on wide
screens and after the timetable on narrow ones.

### 4.6 Doors

Add `door--media` to the `<a class="door">` and this span **before** `.door__go`:

```html
<span class="door__media" aria-hidden="true" style="--focus:50% 50%">
  <img src="…-480.webp" srcset="…-480.webp 480w, …-800.webp 800w" sizes="15rem" alt="" width="W" height="H" loading="lazy" decoding="async">
</span>
```

### 4.7 Gallery ("the reach") replaces the strip

Replace the whole `#gallery` section in `front-page.php` (and drop the `.strip-ctrl` buttons):

```html
<section class="band band--normal reach" id="gallery" data-tone="water" aria-labelledby="gallery-title">
  EDGE (cclb_edge())
  <div class="wrap grid reach__head"><div><h2 class="h-xl" id="gallery-title" data-split>From the parish</h2><p>Photos and recordings from parish life, posted by the parish office.</p></div></div>
  <ol class="wrap grid wl-reach" id="reach-list" role="list" data-count="N">
    <li> FRAME 4.2 or 4.3 </li>            <!-- items 1 to 6 -->
    <li hidden> FRAME 4.2 or 4.3 </li>     <!-- items 7 to 12: the hidden attribute stops the download -->
  </ol>
  <!-- only when N > 6 -->
  <div class="wrap reach__more"><button class="btn" type="button" data-reach-more aria-controls="reach-list">Show M more ARROW_SVG</button></div>
</section>
```

Server-render it (no JSON, no fetch). Then delete `initGallery()` from `main.js` and the `gallery`
key from the `#cclb-data` JSON in `functions.php`; nothing else reads them. Print nothing at all
when there are 0 items (as now).

### 4.8 `sizes` attributes

| Where | `sizes` |
|---|---|
| Gallery position 1, 7 | `(max-width: 767px) 100vw, 58vw` |
| Gallery 2, 8 | `(max-width: 767px) 75vw, 33vw` |
| Gallery 3, 6, 9, 12 | `(max-width: 767px) 84vw, 50vw` |
| Gallery 4, 10 | `(max-width: 767px) 100vw, 33vw` |
| Gallery 5, 11 | `(max-width: 767px) 75vw, 66vw` |
| Gallery, only one item | `(max-width: 767px) 100vw, 75vw` |
| Week | `(max-width: 767px) 90vw, (max-width: 1100px) 40vw, 22vw` |
| Visit photo | `(max-width: 960px) 100vw, 56vw` |
| Visit video | `(max-width: 960px) 100vw, 30vw` |
| Doors | `15rem` |
| Hero | `100vw` |

`srcset` per slot: hero `cclb-800, cclb-1280` (+ `cclb-hero-tall` for portrait); gallery large
`cclb-480, cclb-800, cclb-1024`; everything else `cclb-480, cclb-800`. `wp_get_attachment_image()`
with `array( 'sizes' => …, 'loading' => …, 'decoding' => 'async', 'alt' => … )` prints all of this
if the theme limits `wp_calculate_image_srcset` to the `cclb-*` sizes (filter
`wp_calculate_image_srcset_meta` or pass the `srcset` attribute explicitly).

### 4.9 Loading the files

```php
// wp_enqueue_scripts, after cclb-site:
wp_enqueue_style( 'cclb-media', get_theme_file_uri( 'assets/css/media.css' ), array( 'cclb-site' ), CCLB_VERSION );
// in the wp_head script printer, on the line after main.js:
echo '<script type="module" src="' . $u( 'assets/js/media.js' ) . '"></script>' . "\n";
```

---

## 5. Behaviour (`media.js`)

| Behaviour | Hooks | Notes |
|---|---|---|
| Surfacing | every `.wl-frame` | The water starts over the whole photo and drains to the foot as the frame rises from the bottom of the screen to just above the middle. Scrubbed by ScrollTrigger, transform only (`.wl-frame__tide`). Skipped under reduced motion: frames sit at rest |
| Idle wave | `.wl-frame__tide .wave` | Travels slowly on GSAP's ticker (the one clock), only while the frame is on screen |
| Hover / focus | CSS only | The water rises a quarter of the photo; "Open larger" or "Play" lifts. Same verb as buttons and door rows |
| Video facade | `[data-yt]`, `data-yt-title` | Preconnects to youtube-nocookie on first hover/focus; on click removes the thumbnail, adds the iframe, focuses it, drops the water below the video (`.is-playing`) |
| Show more | `[data-reach-more]` + `aria-controls` | Un-hides items 7 to 12, removes the button, starts their surfacing, focuses the first new item |

Without JavaScript every frame is complete and at rest; the lightbox and video buttons need JS (as today).

---

## 6. Headers and CSP (server or security plugin)

- `img-src` must allow `https://i.ytimg.com` (thumbnails).
- `frame-src` must allow `https://www.youtube-nocookie.com`.
- `Referrer-Policy` must **not** be `no-referrer`: YouTube refuses to play embeds that send no
  referrer (error 153). Use `strict-origin-when-cross-origin`. `media.js` also sets it on the iframe.
- No other origin is added. Photos come from the site's own `/wp-content/uploads/`.

---

## 7. Accessibility

- Informative photos (`week`, `visit`, gallery) have real alt text written by a person. Decorative
  ones (`hero`, doors) have `alt=""` and `aria-hidden` on their wrapper.
- Captions are always real text, on solid river blue: **10.9:1** (water-ink on water).
- Hero text over the photo sits on a deep scrim: about **12:1** at worst (measured against the
  brightest possible pixel at the photo's 36% luminosity).
- Every gallery button has a visible focus ring (halo, 3 px) and the same water rise as hover.
- Reduced motion: no surfacing, no wave travel, no image scale. The layout is identical.
- Checked in the prototype with axe-core: **0 violations** on desktop, phone and reduced motion.

---

## 8. Library audit (cclblekki.com media, checked 5 November 2026)

The repository is **public**, so only the first group is committed (in `prototype/img/`).

| Group | Files | Use |
|---|---|---|
| **Safe: no identifiable people** | `CCLB_Altar` (white lace, flowers); `Photo_1700571450007`, `Photo_1700571402676`, `Photo_1700571450007-1`, `Photo_1700571447451` (parish building, finished and under construction); `DSC_8714` **cropped to the left third** (altar, crucifix, flowers) | Use now. The building photos carry the photographer's camera watermark: keep it, credit the photographer, and ask them for clean files |
| **Needs permission of the adults shown** | `DSC_8689`, `DSC_8690` (the two priests in green; ask them); `DSC_8714` full frame (priests, lector, reader); `DSC_8715` (lector at the ambo); `DSC_8741` (eleven parishioners with the priests); `DSC_8743` (about forty people, hardest to clear); `DSC_8707` (priest with parishioners laughing); `DSC_8719` (couple and priest with a baby: the family's permission); `CCLB_2-Ladies-Smiling` | Only after written permission. `DSC_8714` also shows an altar server who appears to be a child at the right edge: crop him out or get guardian consent |
| **Children: written guardian consent first** | `DSC_8729` (girl receiving flowers, boy beside her); `DSC_8712` (teenagers with the priest); `CCLB_Mother-Daughter` (daughter appears to be under 18) | Do not use until consent is on file |
| **Not parish imagery: never with a parish caption** | `church-religion-…-7583110` (Pixabay interior), `cross-faith-hill-4780154`, `jesus-religion-church-1859687`, `mt-sample-background`, all Elementor demo and elderly-care images, `CamScanner-11-22-2023-*` (document scans), WhatsApp logo previews | Do not use |
| Logos | `Emblem-Logo@4x` (844 px), `Emblem-Logo-with-FSC@4x`, `Logo-Portrait@4x` | Brand only (the theme already uses the crest) |
| Video | `FieewVRZT_8` "CCLB Sunday Mass Homily 22 Feb 2026" (the parish's own channel) | Gallery |
| Video | `RS8NrJ0Y5O8` "Catholic Mass 101" is by **Ascension Presents** (Fr Mike Schmitz), not the parish | `visit_video`, always credited "Video: Ascension Presents" |
