# CCLB Parish (WordPress plugin)

Adds **Parish Details** to the WordPress menu. Editors and Administrators can change Mass times, contacts, the monthly rites, giving accounts, form codes and the photo gallery. The CCLB Waterline theme reads the saved values; with this plugin off, the theme falls back to the defaults in `inc/parish.php`.

- One option: `cclb_parish`. The last 10 versions are kept in `cclb_parish_history` ("Undo the last save").
- Account numbers must be 10 digits; bad lines are refused with a warning on the screen.
- Only Editors and Administrators can open the screen (capability `edit_pages`). Because bank accounts are on it, give Editor accounts only to people the parish trusts with that.
- **Forms** (replace Tally): Join the parish register and Harvest commitment, same fields as the Tally forms. Entries are stored in table `{prefix}cclb_submissions` in the site database and listed under Parish Details > Entries (read, mark dealt with, delete, download CSV). The parish email gets a notice with a link only, never the personal details. Spam guards: hidden trap field, signed time stamp (no nonce, so cached pages work), 6 entries per hour per connection. CSV cells that look like formulas are neutralised.
- Do not cache the pages `/join/` and `/harvest/` with query strings (`?cclb_sent=`).
- Planned: the Parish DB (members and households) as a separate screen, stored in the site's own MySQL database.
