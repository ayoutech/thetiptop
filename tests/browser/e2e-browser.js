/**
 * TESTS END-TO-END NAVIGATEUR (Chromium piloté par Playwright).
 * Parcours réels dans un vrai navigateur : affichage, responsive 360 px, formulaire d'inscription,
 * participation avec un code, connexion, accessibilité de base, absence d'erreurs JavaScript.
 *
 * Variables : BASE_URL (défaut http://127.0.0.1:8080), CHROMIUM_PATH (optionnel), REPORT_DIR (défaut ./reports)
 * Sortie : console + reports/e2e-browser.xml (JUnit, lu par Jenkins).
 */
const { chromium } = require('playwright');
const fs = require('fs');
const path = require('path');

const BASE = process.env.BASE_URL || 'http://127.0.0.1:8080';
const REPORT_DIR = process.env.REPORT_DIR || path.join(process.cwd(), 'reports');
const results = [];

async function scenario(name, fn) {
  const t0 = Date.now();
  try {
    await fn();
    results.push({ name, ok: true, time: (Date.now() - t0) / 1000 });
    console.log('  ✔', name);
  } catch (e) {
    results.push({ name, ok: false, time: (Date.now() - t0) / 1000, error: String(e.message || e) });
    console.log('  ✘', name, '\n     ', String(e.message || e).split('\n')[0]);
  }
}

function expect(cond, msg) { if (!cond) throw new Error(msg); }

(async () => {
  const browser = await chromium.launch({
    headless: true,
    executablePath: process.env.CHROMIUM_PATH || undefined,
    args: ['--no-sandbox'],
  });
  const email = `browser+${Date.now()}@example.test`;

  console.log(`Tests navigateur sur ${BASE}`);

  await scenario("Accueil : titre, H1, logo avec texte alternatif, bandeau cookies refusable", async () => {
    const ctx = await browser.newContext(); const page = await ctx.newPage();
    const jsErrors = []; page.on('pageerror', (e) => jsErrors.push(e.message));
    await page.goto(BASE + '/');
    expect((await page.title()).includes('Thé Tip Top'), 'titre de page');
    expect(await page.locator('h1').count() === 1, 'un seul H1 attendu');
    expect(await page.locator('img.ttt-logo-img[alt="Thé Tip Top"]').count() === 1, 'logo avec alt');
    const banner = page.locator('#cookieBanner');
    await banner.waitFor({ state: 'visible', timeout: 5000 });
    await page.locator('.btn-cookie-refuser').click();
    await banner.waitFor({ state: 'hidden', timeout: 5000 });
    expect(jsErrors.length === 0, 'erreurs JS : ' + jsErrors.join(' | '));
    await ctx.close();
  });

  await scenario('Accessibilité de base : langue, alt des images, titres de page', async () => {
    const ctx = await browser.newContext(); const page = await ctx.newPage();
    for (const p of ['/', '/pages/connexion.php', '/pages/inscription.php', '/pages/reglement.php']) {
      await page.goto(BASE + p);
      expect(await page.locator('html[lang="fr"]').count() === 1, `${p} : attribut lang`);
      expect((await page.title()).trim().length > 5, `${p} : titre vide`);
      const noAlt = await page.locator('img:not([alt])').count();
      expect(noAlt === 0, `${p} : ${noAlt} image(s) sans alt`);
    }
    await ctx.close();
  });

  await scenario('Responsive 360 px : aucun défilement horizontal', async () => {
    const ctx = await browser.newContext({ viewport: { width: 360, height: 740 }, isMobile: true });
    const page = await ctx.newPage();
    for (const p of ['/', '/pages/connexion.php', '/pages/inscription.php']) {
      await page.goto(BASE + p);
      const overflow = await page.evaluate(() => document.documentElement.scrollWidth - window.innerWidth);
      expect(overflow <= 1, `${p} : débordement horizontal de ${overflow}px à 360px`);
    }
    await ctx.close();
  });

  await scenario('Parcours complet : inscription, participation, gain affiché', async () => {
    const ctx = await browser.newContext(); const page = await ctx.newPage();
    await page.goto(BASE + '/pages/inscription.php');
    await page.fill('input[name=prenom]', 'Camille');
    await page.fill('input[name=nom]', 'Durand');
    await page.fill('input[name=email]', email);
    await page.fill('input[name=age]', '29');
    await page.selectOption('select[name=sexe]', 'Femme');
    await page.fill('input[name=mot_de_passe]', 'MotDePasse123');
    await page.fill('input[name=mot_de_passe2]', 'MotDePasse123');
    await page.check('#rgpd'); // consentement RGPD obligatoire
    await Promise.all([page.waitForURL('**/pages/participation.php'), page.locator('form button[type=submit], form input[type=submit]').first().click()]);

    await page.fill('#code-input', 'ZZZZZZZZZZ');
    await page.locator('form button[type=submit]').first().click();
    await page.waitForSelector('text=invalide');

    await page.fill('#code-input', 'BROWSER001');
    await page.locator('form button[type=submit]').first().click();
    await page.waitForSelector('text=Infuseur à thé', { timeout: 5000 });

    await page.goto(BASE + '/pages/mon-compte.php');
    expect((await page.content()).includes('BROWSER001'), 'le code utilisé apparaît dans Mon compte');
    await ctx.close();
  });

  await scenario('Connexion : mauvais mot de passe refusé puis connexion réussie', async () => {
    const ctx = await browser.newContext(); const page = await ctx.newPage();
    await page.goto(BASE + '/pages/connexion.php');
    await page.fill('input[name=email]', email);
    await page.fill('input[name=mot_de_passe]', 'incorrect');
    await page.locator('form button[type=submit]').first().click();
    await page.waitForSelector('text=incorrect');
    await page.fill('input[name=email]', email);
    await page.fill('input[name=mot_de_passe]', 'MotDePasse123');
    await Promise.all([page.waitForURL('**/pages/mon-compte.php'), page.locator('form button[type=submit]').first().click()]);
    await ctx.close();
  });

  await scenario('Espaces protégés : un visiteur anonyme est renvoyé vers l\'accueil', async () => {
    const ctx = await browser.newContext(); const page = await ctx.newPage();
    await page.goto(BASE + '/pages/admin.php');
    expect(new URL(page.url()).pathname === '/', 'redirection admin');
    await page.goto(BASE + '/pages/participation.php');
    expect(page.url().includes('/pages/connexion.php'), 'redirection participation');
    await ctx.close();
  });

  await browser.close();

  // ---- rapport JUnit pour Jenkins ----
  fs.mkdirSync(REPORT_DIR, { recursive: true });
  const failed = results.filter((r) => !r.ok).length;
  const esc = (s) => String(s).replace(/[<>&"]/g, (c) => ({ '<': '&lt;', '>': '&gt;', '&': '&amp;', '"': '&quot;' }[c]));
  const xml = `<?xml version="1.0" encoding="UTF-8"?>\n<testsuite name="e2e-browser" tests="${results.length}" failures="${failed}">\n` +
    results.map((r) => `  <testcase classname="e2e-browser" name="${esc(r.name)}" time="${r.time}">` +
      (r.ok ? '' : `<failure message="${esc(r.error)}"/>`) + '</testcase>').join('\n') + '\n</testsuite>\n';
  fs.writeFileSync(path.join(REPORT_DIR, 'e2e-browser.xml'), xml);
  console.log(`\n${results.length - failed}/${results.length} scénarios réussis`);
  process.exit(failed ? 1 : 0);
})();
