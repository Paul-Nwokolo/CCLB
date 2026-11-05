  /* =========================================================
     RENDERING + BEHAVIOUR  (no need to edit below)
     ========================================================= */
  (function () {
    const $ = (s, r = document) => r.querySelector(s);
    const esc = (s) => String(s).replace(/[&<>"']/g, c => ({ "&":"&amp;","<":"&lt;",">":"&gt;",'"':"&quot;","'":"&#39;" }[c]));
    const groupDigits = (s) => String(s).replace(/\D/g, "").replace(/(\d{3,4})(?=\d)/g, "$1 ");
    const plain = (s) => String(s).replace(/\s[\u2013\u2014]\s/g, ", ").replace(/(\w)'(\w)/g, "$1\u2019$2");
    const digits = (s) => String(s).replace(/\D/g, "");
    const waLink = (num, msg) => "https://wa.me/" + digits(num) + "?text=" + encodeURIComponent(msg);
    const COPY = '%%COPY%%';
    const WA = '%%WA%%';
    const ARROW = '<svg class="arrow" viewBox="0 0 20 20" width="20" height="20" aria-hidden="true" focusable="false"><path d="M4 10h11M11 5.5 15.5 10 11 14.5" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>';

    /* Hero copy from CONFIG (title is split over two lines, both copies + reflection) */
    (function heroFromConfig() {
      const lede = $("#hero-lede");
      if (lede && CONFIG.heroLede) lede.textContent = CONFIG.heroLede;
      const t = String(CONFIG.heroTitle || "").trim();
      const h1 = $("[data-title]");
      if (!t || !h1 || h1.textContent.replace(/\s+/g, " ").includes(t)) return;
      const words = t.split(/\s+/);
      const cut = Math.max(1, Math.ceil(words.length / 2));
      const l1 = words.slice(0, cut).join(" "), l2 = words.slice(cut).join(" ") || "";
      document.querySelectorAll("[data-title] .ln--1, [data-title-wet] .ln--1").forEach(n => n.textContent = l1);
      document.querySelectorAll("[data-title] .ln--2, [data-title-wet] .ln--2").forEach(n => n.textContent = l2);
      document.querySelectorAll("[data-reflection] .reflection__slice").forEach(n => n.textContent = l2 || l1);
    })();

    function accountHTML(acc) {
      return `
        <div class="acct">
          <div class="acct__row"><p class="label">Account name</p><p class="acct__value">${esc(plain(acc.accountName))}</p><button class="chip-btn" type="button" data-copy="${esc(acc.accountName)}"><span data-copy-label>Copy name</span></button></div>
          <div class="acct__row"><p class="label">Bank</p><p class="acct__value">${esc(acc.bankName)}</p></div>
          <div class="acct__row"><p class="label">Account number</p><p class="acct__number">${esc(groupDigits(acc.accountNumber))}</p><button class="chip-btn" type="button" data-copy="${esc(digits(acc.accountNumber))}">${COPY}<span data-copy-label>Copy number</span></button></div>
        </div>`;
    }
    const verifyHTML = (name) => `<p class="verify">Before sending, check the account name reads <strong>${esc(plain(name))}</strong>. The parish never collects gifts through personal accounts.</p>`;
    const attr = (n, v) => " " + n + "=" + JSON.stringify(String(v));
    const confirmHTML = (msg) => `<a class="btn btn--solid btn-wa"${attr("href", waLink(CONFIG.whatsappNumber, msg))} target="_blank" rel="noopener noreferrer">${WA} I have sent my gift<span class="sr-only"> (opens WhatsApp)</span></a>`;

    /* Fund list */
    const list = $("#fund-list");
    const detail = $("#fund-detail");
    let activeId = CONFIG.funds.some(f => f.id === CONFIG.defaultFund) ? CONFIG.defaultFund : CONFIG.funds[0].id;

    CONFIG.funds.forEach(fund => {
      const li = document.createElement("li");
      li.innerHTML = `<button class="fund" type="button" aria-pressed="${fund.id === activeId}" aria-controls="fund-detail">
        <span class="fund__name">${esc(fund.name)}</span>
        ${fund.tag ? `<span class="fund__tag">${esc(fund.tag)}</span>` : ""}
        <span class="fund__blurb">${esc(plain(fund.blurb))}</span>
        <span class="fund__mark" aria-hidden="true"></span>
      </button>`;
      li.firstElementChild.addEventListener("click", () => selectFund(fund.id, true));
      list.appendChild(li);
    });

    function selectFund(id, user) {
      activeId = id;
      [...list.querySelectorAll(".fund")].forEach((b, i) => b.setAttribute("aria-pressed", String(CONFIG.funds[i].id === id)));
      renderFund(CONFIG.funds.find(f => f.id === id));
      if (user && matchMedia("(max-width: 960px)").matches) {
        const reduce = matchMedia("(prefers-reduced-motion: reduce)").matches;
        detail.scrollIntoView({ behavior: reduce ? "auto" : "smooth", block: "start" });
      }
    }

    function renderFund(fund) {
      if (fund.societies && fund.societies.length) { renderSocieties(fund); return; }
      const waMsg = `Hello ${CONFIG.shortName}, I have given to the ${fund.name} fund. Please confirm receipt. God bless.`;
      detail.innerHTML = `
        <div class="account-card">
          <h3 class="account-card__name">${esc(fund.name)}</h3>
          <p class="account-card__blurb">${esc(plain(fund.blurb))}</p>
          ${fund.accounts.map(accountHTML).join("")}
          ${verifyHTML(fund.accounts[0].accountName)}
          ${confirmHTML(waMsg)}
        </div>`;
    }

    function renderSocieties(fund) {
      let idx = 0;
      const options = fund.societies.map((s, i) => `<option value="${i}">${esc(s.name)}</option>`).join("");
      detail.innerHTML = `
        <div class="account-card">
          <h3 class="account-card__name">${esc(fund.name)}</h3>
          <p class="account-card__blurb">${esc(plain(fund.blurb))}</p>
          <div class="society">
            <label for="society-select">Choose a society or ministry</label>
            <select class="select" id="society-select">${options}</select>
            <div class="society__nav">
              <button class="chip-btn" type="button" id="soc-prev">Previous</button>
              <p class="society__count" id="soc-count" aria-live="polite"></p>
              <button class="chip-btn" type="button" id="soc-next">Next</button>
            </div>
          </div>
          <div id="society-account"></div>
        </div>`;
      const sel = $("#society-select", detail), prev = $("#soc-prev", detail), next = $("#soc-next", detail);
      const count = $("#soc-count", detail), region = $("#society-account", detail);
      function paint() {
        const s = fund.societies[idx];
        const acc = { accountName: s.accountName, bankName: s.bankName || "Zenith Bank", accountNumber: s.accountNumber };
        const waMsg = `Hello ${CONFIG.shortName}, I have given to ${s.name} (${s.accountName}). Please confirm receipt. God bless.`;
        region.innerHTML = accountHTML(acc) + verifyHTML(acc.accountName) + confirmHTML(waMsg);
        count.textContent = (idx + 1) + " of " + fund.societies.length;
        prev.disabled = idx === 0;
        next.disabled = idx === fund.societies.length - 1;
        sel.value = String(idx);
      }
      sel.addEventListener("change", () => { idx = parseInt(sel.value, 10) || 0; paint(); });
      prev.addEventListener("click", () => { if (idx > 0) { idx--; paint(); } });
      next.addEventListener("click", () => { if (idx < fund.societies.length - 1) { idx++; paint(); } });
      paint();
    }

    /* Confirmation form -> WhatsApp */
    const cfFund = $("#cf-fund");
    CONFIG.funds.forEach(f => { const o = document.createElement("option"); o.value = f.name; o.textContent = f.name; cfFund.appendChild(o); });
    $("#confirm-form").addEventListener("submit", (e) => {
      e.preventDefault();
      const name = $("#cf-name").value.trim(), amount = $("#cf-amount").value.trim(), ref = $("#cf-ref").value.trim();
      let msg = `Hello ${CONFIG.shortName}, this is a donation confirmation.`;
      if (name) msg += `\nName: ${name}`;
      msg += `\nFund: ${cfFund.value}`;
      if (amount) msg += `\nAmount: ${amount}`;
      if (ref) msg += `\nReference: ${ref}`;
      msg += `\nPlease confirm receipt. I will attach my receipt here. God bless.`;
      window.open(waLink(CONFIG.whatsappNumber, msg), "_blank", "noopener");
    });

    /* Contact facts from CONFIG */
    const c = CONFIG.contact || {};
    const facts = [
      c.sundayMass  && { label: "Sunday Mass", text: c.sundayMass },
      c.weekdayMass && { label: "Weekday Mass", text: c.weekdayMass },
      c.phone       && { label: "Phone", text: c.phone, href: `tel:${c.phone.replace(/[^\d+]/g, "")}` },
      c.email       && { label: "Email", text: c.email, href: `mailto:${c.email}` },
      c.officeHours && { label: "Office hours", text: c.officeHours.replace(/\s[–—-]\s/g, " to ") },
      c.address     && { label: "Address", text: c.address, href: c.mapUrl, ext: true },
    ].filter(Boolean);
    const grid = $("#contact-grid");
    if (grid && facts.length) {
      grid.innerHTML = facts.map(f => `<div class="fact"><p class="label">${esc(f.label)}</p>${
        f.href ? `<a${attr("href", f.href)}${f.ext ? ' target="_blank" rel="noopener noreferrer"' : ""}>${esc(f.text)}${f.ext ? '<span class="sr-only"> (opens Google Maps)</span>' : ""}</a>` : `<p>${esc(f.text)}</p>`
      }</div>`).join("");
    }

    renderFund(CONFIG.funds.find(f => f.id === activeId));
  })();
</script>
