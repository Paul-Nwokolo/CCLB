// Mobile menu keyboard check: opening moves focus into the menu, Escape closes it and returns focus to the Menu button.
const BASE = process.env.BASE || 'http://localhost:8080';
const { chromium } = require('playwright');
(async()=>{const b=await chromium.launch();
for (const mode of ['enter','click']) {
const ctx=await b.newContext({viewport:{width:390,height:844},isMobile:true,hasTouch:true}); const pg=await ctx.newPage();
await pg.goto(BASE+'/',{waitUntil:'networkidle'}); await pg.waitForTimeout(3000);
const st=()=>pg.evaluate(()=>({hidden:document.getElementById('menu').hidden,exp:document.querySelector('[data-menu-open]').getAttribute('aria-expanded'),active:(document.activeElement.textContent||'').trim().slice(0,20),inMenu:!!document.activeElement.closest('#menu')}));
if(mode==='enter'){await pg.focus('[data-menu-open]');await pg.keyboard.press('Enter');} else await pg.tap('[data-menu-open]');
await pg.waitForTimeout(1200); console.log(mode,'after open',JSON.stringify(await st()));
await pg.keyboard.press('Escape'); await pg.waitForTimeout(1200); console.log(mode,'after Escape',JSON.stringify(await st()));
await ctx.close();}
await b.close();})();
