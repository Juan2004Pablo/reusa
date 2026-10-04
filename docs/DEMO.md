# Guion de demostración (3 a 5 minutos)

Prepara el entorno con `php artisan migrate:fresh --seed` y `composer dev`. Abre dos ventanas del navegador: una normal y otra privada (para el administrador).

## 1. Qué es ReUsa (30 s) — visitante

1. Abre `/`. Muestra la propuesta de valor, los **3 pasos**, las tres modalidades (colores distintos) y las últimas publicaciones.
2. Recalca el mensaje clave: _ReUsa solo conecta personas; el pago y la entrega se acuerdan entre ellas._

## 2. Explorar el catálogo (60 s) — visitante

1. Entra a **Catálogo**. Escribe «bici» en el buscador y observa que la URL cambia (`?q=bici`).
2. Filtra por **Categoría → Deportes y tiempo libre** y luego la **Subcategoría**; cambia la **Modalidad** a _Venta_ y ordena por **precio**.
3. Copia la URL: los filtros viajan en el enlace. Cambia de página: la paginación los conserva.
4. Reduce la ventana (o abre en el celular) para mostrar el **panel de filtros como cajón** y las tarjetas en una columna.
5. Abre una publicación: **galería** (flechas del teclado), datos completos, aviso de pago y entrega y el botón deshabilitado _«Solicitudes disponibles próximamente»_.

## 3. Registrarse y publicar (90 s) — usuario nuevo

1. **Crear cuenta**: muestra que sin aceptar los **términos y condiciones** no avanza (abre la página de términos: ReUsa es solo un canal de contacto).
2. Pulsa **Publicar objeto**. Muestra el aviso de **productos no admitidos**.
3. Completa el formulario: elige la categoría (la subcategoría depende de ella), cambia la modalidad a **Venta** (aparece el precio, con formato `1.500.000`) y a **Intercambio** (aparece «qué buscas a cambio»).
4. Sube 2 o 3 fotos: previsualización, **cambia el orden** con las flechas y quita una. Intenta subir un PDF para ver el error.
5. Publica. Aparece el aviso de éxito y el detalle.

## 4. Gestionar la publicación (45 s)

1. En el detalle (o en **Mis publicaciones**) abre **Cambiar estado**: solo ofrece los cambios válidos (una venta puede pasar a _Reservado_ o _Vendido_; una donación a _Entregado_).
2. **Editar**: quita una foto, agrega otra y guarda.
3. **Eliminar**: aparece el diálogo de confirmación; al confirmar, la publicación sale del catálogo y su enlace responde 404.

## 5. Administración (60 s) — ventana privada con `admin@reusa.test`

1. Entra a **Administración**: contadores y distribución por estado y modalidad.
2. **Usuarios**: busca a «jorge», desactívalo (con confirmación) e intenta iniciar sesión con esa cuenta: no puede. Reactívalo. Muestra que el propio admin tiene los botones deshabilitados.
3. **Publicaciones**: oculta una indicando un motivo. Vuelve a la ventana del visitante: ya no aparece en el catálogo ni abre su detalle. Entra como su dueño para ver el aviso de moderación con el motivo. Vuelve a mostrarla.
4. Como usuario normal visita `/admin`: **403**.

## 6. Cierre (15 s)

- Todo está en español, funciona en móvil y en modo oscuro, y la suite de pruebas cubre los flujos mostrados.
- Lo que sigue: flujo de solicitudes, notificaciones y orientación reutilizar/reparar/reciclar (ver `docs/BACKLOG.md`).
