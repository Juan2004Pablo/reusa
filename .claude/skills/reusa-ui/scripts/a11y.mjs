/**
 * Auditoría de accesibilidad (axe-core, WCAG 2.1 A/AA) en claro y oscuro.
 *   npm i --no-save axe-core && node a11y.mjs
 * Recorre las pantallas públicas, las de una persona usuaria (ana@reusa.test) y las del admin.
 * Termina con código 1 si hay violaciones.
 */
import { createRequire } from 'node:module';
import { readFileSync } from 'node:fs';

const require = createRequire(import.meta.url);
const { chromium } = require(process.env.PLAYWRIGHT_MODULE ?? 'playwright');
const axeSource = readFileSync(require.resolve('axe-core/axe.min.js'), 'utf8');
const base = process.env.BASE_URL ?? 'http://127.0.0.1:8000';
const tags = ['wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa'];

const groups = [
    {
        login: null,
        paths: [
            '/',
            '/publications',
            '/publications?modality=sale',
            '/terms',
            '/login',
            '/register',
            'DETAIL',
        ],
    },
    {
        login: 'ana@reusa.test',
        paths: [
            '/my/publications',
            '/publications/create',
            '/settings/profile',
        ],
    },
    {
        login: 'admin@reusa.test',
        paths: ['/admin', '/admin/users', '/admin/publications'],
    },
];

const browser = await chromium.launch();
let total = 0;

for (const scheme of ['light', 'dark']) {
    for (const { login, paths } of groups) {
        const context = await browser.newContext({
            viewport: { width: 1280, height: 900 },
            colorScheme: scheme,
            locale: 'es-CO',
        });
        if (scheme === 'dark')
            await context.addCookies([
                { name: 'appearance', value: 'dark', url: base },
            ]);
        const page = await context.newPage();
        if (login) {
            await page.goto(base + '/login');
            await page.fill('#email', login);
            await page.fill('#password', 'password');
            await Promise.all([
                page.waitForURL('**/my/publications'),
                page.click('[data-test="login-button"]'),
            ]);
        }
        for (const path of paths) {
            let url = path;
            if (path === 'DETAIL') {
                await page.goto(base + '/publications', {
                    waitUntil: 'networkidle',
                });
                url = await page
                    .locator(
                        'main ul a[href^="/publications/"]:not([href$="/create"])',
                    )
                    .first()
                    .getAttribute('href');
            }
            await page.goto(base + url, { waitUntil: 'networkidle' });
            await page.evaluate(axeSource);
            const { violations } = await page.evaluate(
                (runTags) =>
                    axe.run(document, {
                        runOnly: { type: 'tag', values: runTags },
                    }),
                tags,
            );
            total += violations.length;
            console.log(
                `${scheme.padEnd(5)} ${url.padEnd(34)} ${violations.length} violaciones`,
            );
            for (const v of violations)
                console.log(
                    `   - ${v.id} (${v.impact}): ${v.help} → ${v.nodes
                        .slice(0, 3)
                        .map((n) => n.target.join(' '))
                        .join(' | ')}`,
                );
        }
        await context.close();
    }
}

await browser.close();
console.log(`TOTAL: ${total} violaciones`);
process.exit(total ? 1 : 0);
