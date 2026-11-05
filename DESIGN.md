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
