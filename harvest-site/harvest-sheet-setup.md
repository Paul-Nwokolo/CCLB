# Harvest Vote — Google Sheets backend setup

This guide explains how to turn the raw responses Tally pushes into Google Sheets
into a clean, deduplicated, real-time dashboard for the **2026 Annual Church
Harvest Celebration** theme vote.

> **How Tally connects to Sheets:** In Tally, open the form → **Integrations** →
> **Google Sheets** → connect the parish Google account and pick a spreadsheet.
> Tally appends one row per submission to a tab (this guide assumes it is named
> **\`Form Responses\`**). Do **not** type into that tab — Tally owns it. All the
> formulas below live on **separate tabs** so a new submission never overwrites them.

---

## 0. Assumed columns on \`Form Responses\`

Tally writes a header row, then one row per vote. Adjust the letters if your
column order differs.

| Column | Field | Example |
|--------|-------|---------|
| A | Submitted at (timestamp) | \`2026-06-20 14:03:11\` |
| B | Full Name | \`Ada Obi\` |
| C | Phone Number | \`0803 123 4567\` |
| D | Email Address | \`ada@example.com\` |
| E | Theme Selection | \`Harvest of Love and Unity\` |
| F | Optional Donation Amount | \`5000\` |

The three valid theme strings (must match the form options **exactly**):

1. \`Harvest of Divine Possibility\`
2. \`Harvest of Love and Unity\`
3. \`Harvest of God's Faithfulness\`

---

## 1. Normalize phone numbers (\`Clean\` tab)

Nigerian numbers arrive in many shapes — \`0803...\`, \`+234803...\`,
\`234803...\`, with spaces or dashes. Normalize every number to a single
canonical 13-digit form \`234XXXXXXXXXX\` so two spellings of the same number
collapse to one identity.

Create a tab named **\`Clean\`**. In **A1** paste this **single**
\`ARRAYFORMULA\` — it fills the whole column automatically as new votes arrive,
so never copy it down:

\`\`\`text
=ARRAYFORMULA(
  IF('Form Responses'!A2:A="",,
    {
      "Phone (normalized)",
      "Email (normalized)",
      "Theme",
      "Donation"
    }
  )
)
\`\`\`

That only labels the headers. Put the real work in the columns beside it.
In **A2** of \`Clean\`:

\`\`\`text
=ARRAYFORMULA(
  IF('Form Responses'!C2:C="","",
    LET(
      digits, REGEXREPLACE(TO_TEXT('Form Responses'!C2:C), "[^0-9]", ""),
      IF(LEFT(digits,4)="2340", "234" & MID(digits,5,20),
      IF(LEFT(digits,3)="234", digits,
      IF(LEFT(digits,1)="0",   "234" & MID(digits,2,20),
                               "234" & digits)))
    )
  )
)
\`\`\`

In **B2** of \`Clean\` (lower-cased, trimmed email — the secondary identity key):

\`\`\`text
=ARRAYFORMULA(IF('Form Responses'!D2:D="","",LOWER(TRIM('Form Responses'!D2:D))))
\`\`\`

In **C2** and **D2** carry the theme and donation across, coercing a blank
donation to 0:

\`\`\`text
=ARRAYFORMULA(IF('Form Responses'!E2:E="","",TRIM('Form Responses'!E2:E)))
\`\`\`
\`\`\`text
=ARRAYFORMULA(IF('Form Responses'!A2:A="","",N('Form Responses'!F2:F)))
\`\`\`

You now have a tidy table \`Clean!A:D\` = normalized phone, normalized email,
theme, donation.

---

## 2. Deduplicate — one vote per person (\`Votes\` tab)

Policy: **a person's _latest_ submission wins.** Because Tally appends in time
order, the last matching row is the most recent. We dedup on the normalized
**phone** (primary identity); switch to email if you prefer.

Create a tab named **\`Votes\`**. In **A1**:

\`\`\`text
=SORTN(
  SORT(
    FILTER({Clean!A2:D, ROW(Clean!A2:A)}, Clean!A2:A<>""),
    5, FALSE
  ),
  9^9, 2, 1, TRUE
)
\`\`\`

How it works, inside-out:

- \`FILTER(..., Clean!A2:A<>"")\` drops empty rows and appends \`ROW(...)\` as a
  5th column (a strict recency rank — higher row = later submission).
- \`SORT(..., 5, FALSE)\` sorts newest-first.
- \`SORTN(..., 9^9, 2, 1, TRUE)\` keeps **all** groups but only the **first row
  per distinct value of column 1** (the normalized phone). Mode \`2\` = "remove
  duplicates," and because we pre-sorted newest-first, the survivor is the
  latest vote.

Result: \`Votes\` columns A–D = one clean row per unique phone
(phone, email, theme, donation), newest vote retained.

> **Dedup on email instead?** Change the group column from \`1\` to \`2\` in the
> \`SORTN\` call. **Dedup on phone _and_ email?** Add a helper key in \`Clean\`
> (\`=A2&"|"&B2\`) and group on that.

---

## 3. Real-time dashboard (\`Dashboard\` tab)

Create a tab named **\`Dashboard\`**. Lay out a small table:

| | A | B | C |
|---|---|---|---|
| **1** | Theme | Votes | Donations pledged (₦) |
| **2** | Harvest of Divine Possibility | _formula_ | _formula_ |
| **3** | Harvest of Love and Unity | _formula_ | _formula_ |
| **4** | Harvest of God's Faithfulness | _formula_ | _formula_ |
| **5** | **Total** | _formula_ | _formula_ |

Type the three theme names into **A2:A4** exactly as they appear in the form.

### 3a. Votes per theme (\`SUMIFS\`-style count via \`COUNTIFS\`)

In **B2**, then fill down to B4:

\`\`\`text
=COUNTIFS(Votes!$C:$C, $A2)
\`\`\`

### 3b. Donations pledged per theme (\`SUMIFS\`)

In **C2**, then fill down to C4:

\`\`\`text
=SUMIFS(Votes!$D:$D, Votes!$C:$C, $A2)
\`\`\`

### 3c. Totals

In **B5** and **C5**:

\`\`\`text
=SUM(B2:B4)
\`\`\`
\`\`\`text
=SUM(C2:C4)
\`\`\`

### 3d. One-shot leaderboard with \`QUERY\` (alternative to 3a/3b)

If you would rather have a self-building, sorted leaderboard that needs no
hard-typed theme names, put this single formula in **E1**:

\`\`\`text
=QUERY(
  Votes!C2:D,
  "select C, count(C), sum(D)
   where C is not null
   group by C
   order by count(C) desc
   label C 'Theme', count(C) 'Votes', sum(D) 'Donations (₦)'",
  0
)
\`\`\`

This returns Theme · Votes · Donations, ranked from most to least popular, and
updates the instant a new (deduplicated) vote lands.

### 3e. Winner + headline numbers

\`\`\`text
=INDEX(Votes!C2:C, MATCH(MAX(B2:B4), B2:B4, 0))
\`\`\`
(leading theme by votes)

\`\`\`text
="Total voters: " & B5 & "  •  Total pledged: ₦" & TEXT(C5, "#,##0")
\`\`\`

---

## 4. Operational notes

- **Voting closes 28 June 2026.** To freeze results afterward, in Tally set the
  form to stop accepting responses, or add a closing date in the form settings.
- **Theme strings must match.** The dashboard joins on the exact theme text. If
  you ever rename an option in Tally, update \`Dashboard!A2:A4\` to match.
- **Never edit \`Form Responses\` by hand** — Tally may rewrite it. All analysis
  lives on \`Clean\`, \`Votes\`, and \`Dashboard\`.
- **Privacy.** This sheet holds names, phones and emails. Keep sharing
  restricted to the parish team that needs it; do not make it public.
