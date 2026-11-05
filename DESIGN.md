# CCLB parish site: design notes

Working document for the redesign on branch `redesign/waterline`. Later passes read this first.

## Phase 0: Brief

- **Subject:** Catholic Church of the Lord's Baptism, Osapa London (off Beach Resort), Lekki, Lagos.
  Named for the Baptism of the Lord. The crest shows John baptising Jesus in the Jordan, a dove
  descending, river blue and white. Motto: "Lord baptise us with your Spirit".
- **Audience:** parishioners on phones (Lagos mobile data), newcomers looking for Mass times,
  givers looking for the right account, families registering.
- **Job:** orient in three seconds (who, where, when is Mass), then route to Give, Harvest, Join.
- **Assets:** crest (PNG), three Harvest 2026 posters, CMS gallery (Decap, `data/gallery.json`),
  two Tally forms, the give page's committee-editable `CONFIG` block.
- **Constraints:** static HTML on Netlify, no build step, strict CSP (scripts from `'self'` only,
  so GSAP and Lenis are vendored in `assets/js/vendor`). Decap CMS must keep working.
  Everything that works today must keep working: copy buttons, WhatsApp confirm, society picker,
  Tally embeds, poster lightbox, gallery with video.
- **Stack:** the project's own: plain static HTML + CSS + ES modules. No framework, no build.
- **Theatre scale:** a parish site, read on phones over mobile data. No loader, no WebGL. The craft
  goes into type, layout, one signature and the transitions.

### Assumptions
- The Matthew 3:16 statement on the home page is an editorial choice (the parish's own feast).
  The owner can swap the passage.
- Tuesday and Thursday show no Mass because none is listed anywhere on the current site.
- No photos of the parish exist in the repo. The gallery stays CMS-driven; the stock photos in it
  should be replaced with real parish photos (their alt text describes things they do not show).

## Phase 1: Concepts

| | A. The waterline (name) | B. Tide table (place) | C. The Ordo (formal) |
|---|---|---|---|
| Idea | The site is set at the surface of the Jordan: air above, water below. Going under and coming up is how you move through it | The parish week as a Lekki tide table: Mass times are the high tides | Set like a missal: red rubrics tell you what to do, black text is the content |
| Anchor | The parish is named for the Baptism of the Lord; its crest is that river | Osapa sits between the lagoon and the Atlantic | Catholic liturgical books |
| Signature | The parish name half submerged; scrolling raises the water through the letters, which turn from ink to light as they go under | a tide curve drawn through the week | rubric annotations that write themselves |
| O / F / Fe / P | 4 / 5 / 4 / 2 | 4 / 3 / 4 / 3 | 3 / 4 / 5 / 4 |

**Pick A.** It comes straight from the name and the crest, and it gives rules for layout (air and
water bands), motion (rise, sink, surface), type (the waterline as baseline), the transition
(going under, coming up) and the 404 (a page that went under). Stolen from C for one detail: the
giving steps are short instructions in the accent colour, like rubrics.

## Concept Card

```
Idea:        The parish site sits at the surface of the Jordan: above is air, below is water, and you move through it the way baptism does, going under and coming up.
Metaphor:    a river surface seen side-on (the crest's own picture)
Signature:   Home hero. "The Lord's Baptism" is set on the waterline. Scrolling raises the water through the letters; each letter turns from navy-on-light to light-on-river as it goes under, while its reflection ripples below. Implemented as two stacked copies masked by a translate-only water layer (no repaint), wave edge loops on the ticker.
Emotion:     calm, clear, held. Morning light on water.
Motion verb: rise and settle like water (things come up from below, ease out long, never bounce)
Screenshot:  the parish name cut by a moving wave, the bottom of every letter already under the river, its reflection breaking below
```

## Phase 2: Art direction

### Palette (from the crest)
| Role | Hex | Use |
|---|---|---|
| `air` (ground) | `#F3F6F7` | sky side of the waterline |
| `ink` | `#0B2A3F` | text on air (13.6:1) |
| `muted` | `#4A6273` | secondary text on air (5.9:1) |
| `water` | `#0D3A5A` | river side, footer, curtain (also ink-dark on air) |
| `water-ink` / `water-muted` | `#F3F6F7` / `#A9C4D6` | text on water (10.9:1 / 6.5:1) |
| `deep` | `#082436` | the deepest band (footer bottom, menu) |
| `halo` (accent) | `#D4AE5A` on water, `#8C6A1E` on air | the crest's halo. Today's day, focus on water, rubrics. Used rarely |
| `line` | ink at 14%, water-ink at 18% | hairlines |

Not the AI blue-purple gradient: two flat fields, no gradients, no glow.

### Type
- **Display:** Gambetta (Fontshare), 400 and 400 italic. A classical contemporary serif that
  agrees with the crest's Roman lettering. Display tracking -0.035em to -0.045em, leading 0.86 to 0.95.
- **Text and UI:** General Sans (Fontshare) 400/500/600. Body 17 to 18 px, leading 1.55.
  UI 13 to 14 px, sentence case. Tabular figures for every time.
- Scale: `--t-mega clamp(3.6rem, 1rem + 11.5vw, 13rem)`, `--t-xl clamp(2.6rem, 1.2rem + 5.4vw, 6.5rem)`,
  `--t-l clamp(1.8rem, 1.2rem + 2.2vw, 3.2rem)`, `--t-m clamp(1.25rem, 1.1rem + .6vw, 1.6rem)`.

### Grid and rhythm
- 12 columns, gutter `clamp(1rem, .6rem + 1.6vw, 2.5rem)`, outer margin = gutter.
- Three hang lines: column 1, column 5, column 9. Every text block starts on one of them.
- Section moods: tight (6u), normal (12u), airy (22u), full viewport (hero).
- Bands alternate air and water. Every boundary is a wave edge (the waterline again), never a hard rule.

### Shape
Actions are pills (999px). Surfaces are square (0). Nothing else.

### Motion language
- Eases: `expo.out` for entrances, `expo.inOut` for the water curtain, `power3.out` for UI.
- Water rises from below: reveals come up (y from 40 to 0) through a line mask; never slide sideways.
- Transition: going under (water rises over the page with a wave edge, the next page's name in it)
  then coming up (the water drains down off the new page). 0.75 s each way.
- Hover treatments (one per role):
  - Primary buttons and door rows: the water level rises inside them from the bottom, label turns light.
  - Nav links: a small wave underline draws in; the current page keeps it.
  - Inline links: underline at rest that thickens.
  - Utility (social, legal): colour only.
- Idle: the hero wave edge travels slowly and the reflection ripples (the concept itself), on the
  GSAP ticker, paused off screen, off under reduced motion. Inner page heroes share the same wave.

### Composition plan (home)

```
1 HERO  (scene, sticky 200svh, air over water)
  [crest] The Lord's Baptism          Give  Harvest  Join  Visit     ig yt
  Catholic Church of
  the Lord's
  Baptism~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ waterline
     (reflection ripples)
  Osapa London, Lekki        Next Mass  Wed 6:30 AM        Sunday 8:30 AM

2 THE PARISH WEEK  (data/timetable, water)
  h2 left (col 1-4)    7 day columns (col 5-12), today marked in halo

3 SURFACING  (statement, wave edge water->air)
  Matthew 3:16 set large across col 1-10, small dove line drawing col 11

4 THREE DOORS  (index rows, air)
  Give      ............ Offertory, tithe, projects, societies        ->
  Harvest   ............ Commitment Sunday 2026                       ->
  Join      ............ Register your household                      ->

5 THROUGH THE MONTH  (asymmetric pair, air)
  sticky h2 col 1-4  |  dl in 2 columns col 5-12

6 FROM THE PARISH  (filmstrip of CMS media, water)

7 VISIT  (split, air): address set large col 1-7 | phone, email, office hours col 9-12

8 FOOTER  (finale, water -> deep): motto at mega size on its own waterline, links, fine print
```

Inner pages (Give, Harvest, Join): a short hero, the title on the same waterline with the lede in
the water below it, then the page's job immediately (fund chooser, commitment form, register form).
404: the water has risen over the whole title. "This page has gone under." Link: come up to the home page.

### Genericness test
- First draft had a centred crest over the name (the current site and every parish template).
  Changed to a left-hung name on the waterline with the crest only in the header.
- First draft for the doors was three equal cards. Changed to index rows that fill with water.
- First draft for the week was a list of times. Changed to a seven-day timetable with today marked,
  because "when is Mass" is the question most visitors arrive with.

### Still-at-rest decision
The home hero and inner heroes keep breathing (wave and reflection). The rest of the page is still at
rest on purpose: a parish site should be calm to read.

---

## Addendum: images and video (WordPress theme v0.3.1, November 2026)

Art direction for the photo and video layer of `wordpress/themes/cclb-waterline`. The contracts
(slots, sizes, markup, CSS, data) are in that folder's `MEDIA.md`; the photos to take next are in its
`SHOTLIST.md`; a working static page is `prototype/media.html`.

### Situation
The site was designed type-first because the parish had no usable photography. The WordPress
media library now has a few real parish photos (the altar, the building, Mass in November 2023),
but most show identifiable people, three show children, and several "church" images are stock.
The repository is public. So the layer must look finished with **no** photos, with the few safe
ones, and with up to twelve, and it must never make a stock or unconsented photo look like the parish.

### The rule: every photograph meets the river
One rule carries the concept into imagery, the same way the wave edge carries it between bands:

> A photograph's foot stands in the water, and its caption is written in the water.

- Each frame (`.wl-frame`) has a band of river at its foot, a little under a seventh of the photo,
  with the travelling wave on top. The photo shows through it, dimmed, as a reflection would be.
  The caption sits below, on solid river blue, so it is always legible and always the same place.
- In a water band (the week, the gallery) the frame's river is the section's river: the photo
  seems to stand in the section itself. In an air band (visit) the river becomes a small pool
  under the photo.
- Square corners (surfaces are square). Pills only for the two actions on a photo: "Open larger"
  and "Play".

### Where images go, and where they do not

| Place | Image | Why there |
|---|---|---|
| **Hero** | A strip of the parish *under* the surface: the area below the waterline at rest is filled with a photo seen through the river (luminosity blend on river blue, 36%). It rides up with the water as you scroll | The signature stays the name on the waterline; the photo deepens the river instead of competing with the type. Decorative, tinted, so no face is ever the first thing anyone sees |
| **Week** | One upright photo of the altar under the heading; the timetable's day columns run down beside it like a tide gauge. After the timetable on phones, so the times stay first | "When is Mass" is the job; the photo answers "where" |
| **Doors** | A small photo rises with the water that floods the row on hover (laptops only) | A reward for the pointer, zero bytes on phones |
| **Gallery ("the reach")** | Up to 12 photos and videos, each at its own shape, hung from the three hang lines in verses of three: large, small set lower, a middle one. 7 to 12 wait behind one button | A stretch of river with things standing in it, not a grid and not a carousel |
| **Visit** | The building or gate as a newcomer will see it, and a first-Mass video, credited | The only place a stranger needs a picture to act |
| Month, footer, Give, Join forms | **No photos** | Calm to read. Harvest keeps its three posters |

### Treatment
- **Crops.** Named slots have fixed shapes (hero strip, week 4:5, doors 3:2, visit 3:2). Gallery
  items keep their own shape within a range set by their position (large 1.25 to 2.4, small 0.8 to
  1.78, middle 1.0 to 1.78), trimmed around an optional focus point. Editors never crop by hand.
- **Water tint.** Hero only: `mix-blend-mode: luminosity` on river blue, so any photo, colour or
  mono, becomes river-coloured. Frames are **not** tinted above the waterline: the parish in daylight
  stays in its own colours; only the foot is under water.
- **Wave-edge mask.** The frame's water top is the same wave as the hero and band edges (`cclb_wave()`),
  scaled down.
- **Reflections.** Photos are **not** mirrored. The title's rippling reflection stays the one
  reflection on the site: mirrored faces look wrong, and a second reflection would cost a second
  decode on phones. The dimmed foot under the water is the reflection, implied.

### Motion (one verb: rise and settle like water)
- **Surfacing:** a frame enters under water; as it rises from the bottom of the screen to just above
  the middle, the water drains to its foot. Scrubbed by scroll, transform only.
- **Hover and focus:** the water rises a quarter of the way up the photo and "Open larger" lifts:
  the same verb as the buttons and the door rows. Keyboard focus gets the same rise plus a halo ring.
- **Idle:** the wave on each visible frame keeps travelling, slowly, on the one GSAP clock.
- **Reduced motion:** frames sit at rest, nothing travels, nothing scales. No content is hidden
  in any state.

### Video
A click-to-load facade: the parish's own thumbnail from `i.ytimg.com` in the same frame, a "Play"
pill, and nothing from YouTube until it is pressed. Then `youtube-nocookie.com` loads in place, with
focus moved to it and the water dropped below the controls. Non-parish videos carry a credit in the
caption ("Video: Ascension Presents, not a parish recording").

### Weight (Lagos mobile data)
Measured on the parish's own photos, WebP quality 58: the first screen adds 92 KB on a laptop and
43 KB on a phone. The whole home page with 6 gallery items loads about 410 KB of images. Door photos
cost nothing on touch. Budget: ≤ 120 KB hero, ≤ 80 KB per other image, ≤ 450 KB per page with 6
gallery items. Details and the size table are in `MEDIA.md` section 3.

### Genericness test
- First idea for the gallery was a masonry grid with a lightbox: every church site. Replaced by the
  reach: verses of three on the hang lines, each photo standing in the river.
- First idea for the hero was a full-bleed congregation photo behind the name: the stock-church look,
  and the faces problem. Replaced by the photo under the surface, tinted, below the name.
- First idea for the week was a photo beside the heading at full colour and full width. Reduced to one
  upright frame so the timetable stays the hero of that section.
- No carousel anywhere; the old horizontal strip is retired.

### Self-score (from the prototype screenshots; not a separate critic)
Design 8, Usability 8, Creativity 8, Content 6. Content is the gap: the prototype can only use the
altar and the building, and the building photos are over-bright with a watermark. The shot list's
first item (water poured at a baptism, hands only) would lift the hero more than any code change.
