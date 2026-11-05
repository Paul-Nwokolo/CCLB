# CCLB Waterline (WordPress theme)

Design for cclblekki.com. Ported from the static `redesign/waterline` site; the concept and palette are in `DESIGN.md` of that repo.

**Design only.** Mass times, contact details, social links, the monthly rites and the gallery are read from `inc/parish.php` (defaults) and, when the *CCLB Parish* plugin is active, from Settings > Parish Details. Parish staff never edit theme files.

## Pages
Activating the theme creates the pages `give`, `harvest` and `join` (if the slug is free). `page-give.php`, `page-harvest.php`, `page-join.php` are used automatically. Give reads its funds from Parish Details > Giving and renders them with `assets/js/give.js` (a plain list is shown if JavaScript is off). The harvest account is the fund with the short code `harvest`, so the Give and Harvest pages cannot disagree.

## Files
- `front-page.php` home, `404.php` "This page has gone under", `index.php` fallback for editor-made pages.
- `header.php` / `footer.php` curtain, header, mobile menu, footer, lightbox.
- `inc/parish.php` the single source of truth for times and contacts. `inc/parts.php` the waterline hero. `inc/icons.php` SVGs.
- `assets/js/main.js` all behaviour. It reads Mass times and gallery from `<script type="application/json" id="cclb-data">`. `assets/js/vendor/` is GSAP, ScrollTrigger, SplitText (GSAP no-charge licence) and Lenis (MIT).
- `assets/css/site.css` the whole design. Fonts are in `assets/fonts/`.
- Media layer (photos and video), **designed, not yet wired**: `assets/css/media.css`, `assets/js/media.js`, the contracts in `MEDIA.md`, a static `prototype/media.html`, and `SHOTLIST.md` for the parish office.

## Must know
- Do **not** let LiteSpeed Cache (or any plugin) combine, defer or delay JavaScript on this site. Order matters: vendor scripts, then `main.js`.
- The theme prints its own `<script>` tags in `functions.php` (`wp_head`) so the order is exact.
- Page transitions skip `/wp-admin`, `/wp-login.php`, `/wp-json`, links with a query string, and the admin bar.
- Changing Mass times: edit them in the plugin settings, not in `main.js`.

- Join and Harvest forms come from the CCLB Parish plugin (`cclb_form()`); Tally is no longer used. Without the plugin, those pages show the office phone and email instead.
