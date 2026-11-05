// Accessibility sweep (axe: WCAG 2 A/AA + best practice) on home, give, harvest, join and 404, at desktop, phone and reduced-motion.
// Run:  npm i playwright axe-core && BASE=http://localhost:8080 node a11y.js   (a local WordPress with the theme and plugin active)
const BASE = process.env.BASE || 'http://localhost:8080';
const { chromium } = require('playwright');
const fs = require('fs');
const axeSrc = fs.readFileSync(require.resolve('axe-core/axe.min.js'),'utf8');
(async () => {
  const b = await chromium.launch();
  const pages = ['/', '/give/', '/harvest/', '/join/', '/nope/'];
  for (const [label, opts] of [['desktop',{viewport:{width:1440,height:900}}],['mobile',{viewport:{width:390,height:844},isMobile:true}],['reduced-motion',{viewport:{width:1440,height:900},reducedMotion:'reduce'}]]) {
    for (const p of pages) {
      const ctx = await b.newContext(opts); const pg = await ctx.newPage();
      const errs=[]; pg.on('pageerror',e=>errs.push(e.message));
      await pg.goto(BASE+p,{waitUntil:'networkidle'}); await pg.waitForTimeout(3500);
      if (p==='/join/') await pg.check('input[name=reg_type][value=family]');
      await pg.evaluate(axeSrc);
      const r = await pg.evaluate(() => axe.run(document, { runOnly: ['wcag2a','wcag2aa','wcag21a','wcag21aa','best-practice'] }));
      const hs = await pg.evaluate(()=>document.documentElement.scrollWidth - document.documentElement.clientWidth);
      console.log(`${label} ${p}: violations=${r.violations.length}${hs>0?' H-SCROLL '+hs:''}${errs.length?' JSERR '+errs:''}`);
      for (const v of r.violations) console.log('   -', v.id, v.impact, v.nodes.length, v.nodes.slice(0,2).map(n=>n.target.join(' ')).join(' | '));
      await ctx.close();
    }
  }
  await b.close();
})();
