/**
 * Capturas de ReUsa en escritorio, móvil y modo oscuro.
 *   node screenshots.mjs <carpeta> [ruta[:iniciar-sesion-como][:full] …]
 * Sin rutas, usa un conjunto básico. Ejemplos:
 *   node screenshots.mjs /tmp/ui / /publications /my/publications:ana@reusa.test:full
 * Contraseña de las cuentas de demostración: «password».
 */
import { createRequire } from 'node:module';
import { mkdirSync } from 'node:fs';

const require = createRequire(import.meta.url);
const { chromium } = require(process.env.PLAYWRIGHT_MODULE ?? 'playwright');
const base = process.env.BASE_URL ?? 'http://127.0.0.1:8000';
const [out, ...specs] = process.argv.slice(2);

if (!out) {
    console.error(
        'Uso: node screenshots.mjs <carpeta> [ruta[:correo][:full] …]',
    );
    process.exit(1);
}

mkdirSync(out, { recursive: true });
const routes = specs.length ? specs : ['/', '/publications', '/login'];
const variants = [
    ['escritorio', { width: 1366, height: 860 }, 'light'],
    ['movil', { width: 390, height: 844 }, 'light'],
    ['oscuro', { width: 1366, height: 860 }, 'dark'],
];

const browser = await chromium.launch();
const errors = new Set();

for (const [name, viewport, scheme] of variants) {
    const context = await browser.newContext({
        viewport,
        colorScheme: scheme,
        locale: 'es-CO',
        deviceScaleFactor: name === 'movil' ? 2 : 1,
    });
    if (scheme === 'dark')
        await context.addCookies([
            { name: 'appearance', value: 'dark', url: base },
        ]);
    const page = await context.newPage();
    page.on('pageerror', (e) => errors.add(e.message));
    let loggedIn = false;

    for (const spec of routes) {
        const [path, email, full] = spec
            .split(':')
            .map((part) => (part === 'full' ? 'full' : part));
        if (email && email !== 'full' && !loggedIn) {
            await page.goto(base + '/login');
            await page.fill('#email', email);
            await page.fill('#password', 'password');
            await Promise.all([
                page.waitForURL('**/my/publications'),
                page.click('[data-test="login-button"]'),
            ]);
            loggedIn = true;
        }
        await page.goto(base + path, { waitUntil: 'networkidle' });
        await page.evaluate(() => document.fonts.ready);
        const file = `${out}/${path.replace(/\W+/g, '_') || 'inicio'}-${name}.png`;
        await page.screenshot({
            path: file,
            fullPage: full === 'full' || email === 'full',
        });
    }
    await context.close();
}

await browser.close();
console.log(
    errors.size
        ? `Errores de página: ${[...errors].join(' | ')}`
        : 'Capturas listas, sin errores de página.',
);
