/**
 * Genera las imágenes de demostración de ReUsa (public/images/demo/*.webp).
 *
 * Cada objeto es una ilustración tipo «foto de estudio»: luz suave, sombra en el piso,
 * el objeto dibujado con iconos de Lucide (licencia ISC, tomados de node_modules) y una
 * etiqueta colgante de kraft. No usa internet.
 *
 * Uso (solo para regenerar; los archivos resultantes ya están versionados):
 *   npm ci
 *   npx playwright install chromium            # una vez
 *   node scripts/demo-images/generate.mjs      # requiere ImageMagick (`convert`) con soporte WebP
 *
 * Para usar fotos reales, reemplaza los archivos con el mismo nombre (800×600 recomendado).
 */
import { execFileSync } from 'node:child_process';
import { mkdirSync, readFileSync, rmSync, writeFileSync } from 'node:fs';
import { createRequire } from 'node:module';
import { dirname, join } from 'node:path';
import { fileURLToPath, pathToFileURL } from 'node:url';

const root = join(dirname(fileURLToPath(import.meta.url)), '..', '..');
const outDir = join(root, 'public', 'images', 'demo');
const require = createRequire(import.meta.url);
const { chromium } = require(process.env.PLAYWRIGHT_MODULE ?? 'playwright');

const palettes = {
    ropa: { bg: '#f3d9de', light: '#fdf1f3', ink: '#6f2540' },
    libros: { bg: '#f2e4bd', light: '#fbf4dc', ink: '#5e4108' },
    muebles: { bg: '#efd9c4', light: '#faeee2', ink: '#5f3419' },
    hogar: { bg: '#cfe8e3', light: '#eaf7f4', ink: '#10504a' },
    tecnologia: { bg: '#d8dcf3', light: '#eef0fb', ink: '#2a3380' },
    herramientas: { bg: '#dde2e8', light: '#f1f4f7', ink: '#303b47' },
    deportes: { bg: '#d4ecd6', light: '#eef8ef', ink: '#1b5128' },
    juguetes: { bg: '#f6dbe8', light: '#fcf0f6', ink: '#7c2653' },
};

// clave de archivo, icono de Lucide, paleta, cantidad de fotos
const items = [
    ['bicicleta', 'bike', 'deportes', 3],
    ['escritorio', 'lamp', 'muebles', 2],
    ['novelas', 'book-open', 'libros', 2],
    ['chaqueta', 'shirt', 'ropa', 2],
    ['licuadora', 'microwave', 'hogar', 1],
    ['cuna', 'baby', 'muebles', 3],
    ['portatil', 'laptop', 'tecnologia', 3],
    ['ollas', 'cooking-pot', 'hogar', 2],
    ['balon', 'volleyball', 'deportes', 2],
    ['guitarra', 'guitar', 'deportes', 3],
    ['libros-texto', 'book-open', 'libros', 1],
    ['taladro', 'drill', 'herramientas', 2],
    ['catan', 'dices', 'deportes', 2],
    ['vestido', 'shirt', 'ropa', 2],
    ['lampara', 'lamp-floor', 'muebles', 2],
    ['celular', 'smartphone', 'tecnologia', 1],
    ['juguetes-madera', 'blocks', 'juguetes', 3],
    ['sofa', 'sofa', 'muebles', 3],
    ['herramientas', 'wrench', 'herramientas', 1],
    ['audifonos', 'headphones', 'tecnologia', 1],
    ['enlatados', 'soup', 'hogar', 1],
];

async function iconNodes(name) {
    const mod = await import(pathToFileURL(join(root, 'node_modules', 'lucide-react', 'dist', 'esm', 'icons', `${name}.js`)).href);
    return mod.__iconNode.map(([tag, attrs]) => {
        const { key, ...rest } = attrs;
        return `<${tag} ${Object.entries(rest).map(([k, v]) => `${k}="${v}"`).join(' ')}/>`;
    }).join('');
}

// Variantes: encuadre del objeto para que las fotos de una misma publicación no sean iguales.
const framings = [
    { scale: 15, rotate: 0, dx: 0, dy: 0, flip: 1 },
    { scale: 19, rotate: -6, dx: -30, dy: 20, flip: 1 },
    { scale: 12, rotate: 5, dx: 40, dy: 24, flip: -1 },
];

function scene(nodes, palette, framing, seed) {
    const { bg, light, ink } = palette;
    const size = 24 * framing.scale;
    const cx = 400 + framing.dx;
    const cy = 285 + framing.dy;
    const icon = (extra) => `<g ${extra}>${nodes}</g>`;
    const place = `translate(${cx} ${cy}) rotate(${framing.rotate}) scale(${framing.scale * framing.flip} ${framing.scale}) translate(-12 -12)`;
    const tagX = 560 + (seed % 3) * 18;
    return `<svg xmlns="http://www.w3.org/2000/svg" width="800" height="600" viewBox="0 0 800 600">
<defs>
  <radialGradient id="bg" cx="50%" cy="34%" r="80%"><stop offset="0" stop-color="${light}"/><stop offset="1" stop-color="${bg}"/></radialGradient>
  <filter id="soft" x="-30%" y="-30%" width="160%" height="160%"><feGaussianBlur stdDeviation="16"/></filter>
  <pattern id="grain" width="6" height="6" patternUnits="userSpaceOnUse"><circle cx="1" cy="1" r="0.6" fill="${ink}" opacity="0.05"/></pattern>
</defs>
<rect width="800" height="600" fill="url(#bg)"/>
<rect width="800" height="600" fill="url(#grain)"/>
<path d="M0 455 Q400 400 800 455 V600 H0Z" fill="${ink}" opacity="0.06"/>
<ellipse cx="${cx + 8}" cy="${Math.min(cy + size / 2 + 36, 505)}" rx="${size * 0.62}" ry="26" fill="${ink}" opacity="0.28" filter="url(#soft)"/>
<g stroke-linecap="round" stroke-linejoin="round" fill="none">
  ${icon(`transform="translate(10 12) ${place}" stroke="${ink}" stroke-width="1.5" opacity="0.14"`)}
  ${icon(`transform="${place}" stroke="#ffffff" stroke-width="3.1" opacity="0.7"`)}
  ${icon(`transform="${place}" stroke="${ink}" stroke-width="1.35"`)}
</g>
<g transform="translate(${tagX} 118) rotate(${10 + (seed % 3) * 4})">
  <path d="M0 0 C-30 40 -70 70 -120 120" stroke="${ink}" stroke-width="2" fill="none" opacity="0.45"/>
  <path d="M-22 0 L22 0 L22 74 L-22 74 L-22 0 M-22 0 L0 -26 L22 0" fill="#d9b98a" stroke="#a98652" stroke-width="2" stroke-linejoin="round"/>
  <path d="M-22 0 L0 -26 L22 0Z" fill="#d9b98a" stroke="#a98652" stroke-width="2" stroke-linejoin="round"/>
  <circle cx="0" cy="-8" r="5" fill="${bg}" stroke="#a98652" stroke-width="2"/>
  <rect x="-12" y="22" width="24" height="3" rx="1.5" fill="#7a5a2a" opacity="0.5"/>
  <rect x="-12" y="34" width="18" height="3" rx="1.5" fill="#7a5a2a" opacity="0.4"/>
</g>
</svg>`;
}

mkdirSync(outDir, { recursive: true });
const browser = await chromium.launch();
const page = await browser.newPage({ viewport: { width: 800, height: 600 } });
let total = 0;

for (const [key, icon, macro, count] of items) {
    const nodes = await iconNodes(icon);
    for (let n = 0; n < count; n++) {
        const svg = scene(nodes, palettes[macro], framings[n % framings.length], key.length + n);
        await page.setContent(`<body style="margin:0">${svg}</body>`);
        const png = join(outDir, `${key}-${n + 1}.png`);
        const webp = join(outDir, `${key}-${n + 1}.webp`);
        await page.screenshot({ path: png, clip: { x: 0, y: 0, width: 800, height: 600 } });
        execFileSync('convert', [png, '-strip', '-quality', '80', webp]);
        rmSync(png);
        total++;
    }
}

await browser.close();
writeFileSync(join(outDir, 'manifest.json'), JSON.stringify(Object.fromEntries(items.map(([key, , , count]) => [key, count])), null, 2) + '\n');
console.log(`${total} imágenes generadas en public/images/demo`);
