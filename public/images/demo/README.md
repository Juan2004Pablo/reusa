# Imágenes de demostración

Ilustraciones de los objetos de ejemplo que siembra `DemoPublicationSeeder`. Se copian a
`storage/app/public/publications/{id}/` al ejecutar `php artisan migrate --seed`, así que
funcionan en cualquier entorno sin internet.

- **Origen:** generadas con `scripts/demo-images/generate.mjs`, que compone cada «foto» con
  iconos de [Lucide](https://lucide.dev) (licencia ISC) y formas SVG propias.
- **Nombres:** `{clave}-{n}.webp`. `manifest.json` lista cuántas fotos tiene cada clave.
- **Usar fotos reales:** reemplaza los archivos conservando el nombre (800×600 recomendado,
  menos de 2 MB). El seeder las copiará sin más cambios.
