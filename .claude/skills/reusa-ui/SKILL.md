---
name: reusa-ui
description: Sistema de diseño «Mercado de barrio» de ReUsa. Úsalo al crear o modificar cualquier pantalla, componente, color, tipografía o imagen de la interfaz de ReUsa (React/Inertia/Tailwind), o al revisar si la UI se ve genérica.
---

# ReUsa UI · «Mercado de barrio»

La interfaz se inspira en el mercado de pulgas de Medellín: papel con un leve tinte verde, tinta
verde botella, **etiquetas colgantes de kraft** para los precios y **sellos de caucho** para los
estados. Debe sentirse como una tienda de barrio cuidada, no como una plantilla de SaaS.

Documentación larga: `docs/DESIGN.md`. Tokens: `resources/css/app.css`.

## Principios

1. **El objeto manda.** La foto es lo más grande; no se le pone caja, sombra ni borde decorativo.
2. **Cada recurso significa algo.** Kraft = precio. Sello = «algo le pasó al objeto» (reservado,
   entregado, vendido). Punto de color = modalidad. Mono = dato (precio, barrio, fecha, cifra).
3. **La estructura es información.** Numera solo secuencias reales (pasos, partes de un
   formulario). Un rótulo en mono sobre un filete fino encabeza cada sección.
4. **Una sola voz tipográfica fuerte.** Titulares anchos (`.type-display`); el resto, sobrio.
5. **Tinta, no color, para el texto.** El texto nunca toma el color de la modalidad ni del
   estado; el color acompaña (punto, barra, sello) y el significado siempre está en palabras.

## Tokens (en `resources/css/app.css`)

| Rol           | Token / clase                                                                  | Uso                                                                                    |
| ------------- | ------------------------------------------------------------------------------ | -------------------------------------------------------------------------------------- |
| Papel / tinta | `--background`, `--paper`, `--foreground`                                      | Fondo, zonas hundidas, texto                                                           |
| Acento        | `--primary` (verde botella)                                                    | Acciones, enlaces de marca, barras de estado                                           |
| Kraft         | `--kraft`, `--kraft-ink`, clase `.price-tag`                                   | **Solo** etiquetas de precio                                                           |
| Sellos        | `--stamp-blue` (reservado), `--stamp-red` (entregado/vendido), `.status-stamp` | Estado no disponible                                                                   |
| Nota pegada   | `--note`, `--note-ink`, `.paper-note`                                          | Avisos importantes (pago y entrega, productos no admitidos)                            |
| Modalidades   | `--donation`, `--exchange`, `--sale`                                           | Puntos y barras. Validadas con `dataviz/scripts/validate_palette.js` en claro y oscuro |
| Titular       | `.type-display` (Archivo, ancho 122 %, peso 780)                               | h1/h2; úsalo con moderación                                                            |
| Rótulo        | `.type-label` (IBM Plex Mono, mayúsculas, 0.72 rem)                            | Etiquetas, categorías, barrio, fecha                                                   |
| Ficha         | `.label-row` + `.leader`                                                       | Filas «Dato ······ Valor»                                                              |

Radio base 6 px (`--radius`); sin sombras por defecto; bordes solo donde separan algo.
Tipografías autoalojadas (`@fontsource`), sin CDN.

## Componentes (`resources/js/components/`)

- `market/price-tag.tsx`: precio en COP, «Gratis» o «Intercambio». `size="lg"` en el detalle.
- `market/status-stamp.tsx`: sello con la palabra del estado; `tilt` lo inclina sobre fotos.
  **No lo uses para «Disponible»**: ese estado va como `type-label` simple.
- `market/modality-label.tsx`: punto de color + palabra.
- `market/section-heading.tsx`: rótulo + filete + titular; `index` solo si es secuencia.
- `publications/publication-card.tsx`: tarjeta del catálogo (sin caja).
- `publications/cover-image.tsx`: foto o «etiqueta vacía» si no hay foto.
- `brand.tsx`, `app-logo-icon.tsx`: marca de etiqueta colgante + «ReUsa» en Archivo ancha.
- `lib/location.ts` → `neighborhood()`: el barrio va primero en cualquier ubicación.

## Qué evitar (señales de «hecho por IA» en este proyecto)

- Tarjetas con borde + radio + sombra para todo; iconos dentro de cuadros tintados.
- Píldoras de color como etiquetas; badges con icono para cada atributo.
- Héroe con «chip» superior, manchas difuminadas o degradados morados/azules.
- Números gigantes decorativos en «Cómo funciona»; banners redondeados de cierre.
- Cremas + serif + terracota; Inter/Space Grotesk; emojis como marcadores.
- Centrar todo; usar el mismo radio y espaciado en cada bloque.
- Texto con el color de la modalidad o del estado (falla contraste y daltonismo).

## Reglas de datos (panel de administración)

Aplican las de la skill `dataviz`: cifra protagonista sin caja de KPI; barras horizontales sobre
una escala común, extremo de datos redondeado 4 px anclado a la base, rótulos directos en tinta
de texto, tooltip por barra, nunca doble eje. Colores categóricos solo si pasan el validador:

```bash
node <dataviz>/scripts/validate_palette.js "#22864a,#2c5dbd,#c68206" --mode light --surface "#f9fcfa"
node <dataviz>/scripts/validate_palette.js "#36ac62,#5889e6,#c68405" --mode dark  --surface "#121915"
```

## Imágenes de demostración

`public/images/demo/*.webp` (versionadas). El seeder las copia a `storage/app/public`. Para
regenerarlas: `node scripts/demo-images/generate.mjs`. Para fotos reales, reemplaza los archivos
con el mismo nombre.

## Antes de dar por terminado un cambio de UI

1. `npm run check`, `npm run types:check`, `npm run build` y `php artisan test` en verde.
2. Capturas de escritorio, móvil (390 px) y oscuro, y **mirarlas**:
   `node .claude/skills/reusa-ui/scripts/screenshots.mjs <carpeta> [rutas…]`
3. Accesibilidad WCAG 2.1 A/AA con axe, en claro y oscuro (debe dar 0 violaciones):
   `node .claude/skills/reusa-ui/scripts/a11y.mjs`
4. Revisar el contraste de cualquier color nuevo y que ningún estado dependa solo del color.

Los scripts necesitan la app corriendo (`composer dev` o `php artisan serve --port=8000`) y
Playwright (`npx playwright install chromium`; usa `PLAYWRIGHT_MODULE` si está instalado global).
`BASE_URL` cambia la dirección (por defecto `http://127.0.0.1:8000`).
