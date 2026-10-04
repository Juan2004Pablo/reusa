# Backlog de ReUsa

Ideas y trabajo pendiente que **no** forma parte del avance funcional actual. Se ordena por valor aproximado dentro de cada bloque.

## Siguiente hito: solicitudes

- Flujo de solicitudes sobre una publicación: solicitar, aceptar/rechazar, cancelar e historial ("Mis solicitudes").
- Tabla `publication_requests` (`publication_id`, `requester_id`, `message`, `status`, `responded_at`), enum `RequestStatus` (`pending`, `accepted`, `rejected`, `cancelled`) y `PublicationRequestPolicy`.
- Al aceptar una solicitud, la publicación pasa a `reserved` reutilizando `UpdatePublicationStatus`, y las demás solicitudes pendientes se rechazan.
- En el detalle de la publicación, el botón deshabilitado "Solicitudes disponibles próximamente" es el punto de entrada.

## Producto

- Orientación reutilizar / reparar / reciclar para objetos que no se pueden donar.
- Notificaciones (correo y dentro de la app) y chat entre las partes.
- Favoritos y alertas de nuevas publicaciones por categoría.
- Reportes de publicaciones o usuarios por parte de la comunidad.
- Soporte para varias comunidades/sectores (hoy `community` es texto libre).
- Calificación o reputación de usuarios.

## Administración

- Gestión de categorías desde la interfaz.
- Auditoría de acciones de moderación.
- Exportación de métricas de impacto (objetos reutilizados, kg desviados).

## Plataforma y calidad

- Verificación de correo electrónico y autenticación en dos pasos (el kit ya las soporta con Fortify; hoy están desactivadas).
- Redimensionado/optimización de imágenes al subirlas y almacenamiento en S3 o similar.
- Pruebas de componentes (Vitest) y E2E (Playwright) en CI.
- PWA / aplicación móvil.
- Pasarelas de pago (hoy ReUsa solo expone el precio; el pago se acuerda entre las partes).
- Inteligencia artificial para sugerir categoría y descripción a partir de la foto.
