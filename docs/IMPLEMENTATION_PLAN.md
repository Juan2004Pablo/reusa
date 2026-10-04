# Plan de implementación — Avance funcional de ReUsa

> Plan aprobado el 2026-10-04. Este documento se actualiza con el estado real al cerrar cada fase.

## Estado por fase

| Fase | Alcance                                                           | Estado    |
| ---- | ----------------------------------------------------------------- | --------- |
| 0    | Entorno, idioma es-CO, identidad visual, layout público, docs, CI | En curso  |
| 1    | Usuarios, roles, términos, inactivos, rate limit                  | Pendiente |
| 2    | Categorías                                                        | Pendiente |
| 3    | Publicaciones — backend                                           | Pendiente |
| 4    | Publicaciones — frontend                                          | Pendiente |
| 5    | Datos de demostración                                             | Pendiente |
| 6    | Panel de administración (P1)                                      | Pendiente |
| 7    | README, capturas, QA final                                        | Pendiente |

## Notas de ejecución (desviaciones respecto al plan)

- **Fuente tipográfica autoalojada:** el kit descargaba _Instrument Sans_ desde Bunny Fonts durante `npm run build`. Se reemplazó por el paquete npm `@fontsource-variable/instrument-sans` para que el build no dependa de un CDN de terceros.
- **Tests sin build previo:** `tests/TestCase.php` llama a `withoutVite()`, así que `php artisan test` no exige `npm run build`.

## Contexto

ReUsa es un prototipo de plataforma de economía circular comunitaria (donar, intercambiar, vender objetos en desuso) para una comunidad piloto en Medellín (ODS 12). A mitad del proyecto (semana ~8 de 15) se debe presentar un **avance funcional**: una base sólida, probada y bien presentada, no el MVP completo.

### Estado actual verificado del entorno

- Repo `juan2004pablo/reusa` con un único commit: `chore: initial Laravel + React scaffold` = **starter kit oficial de React**: Laravel 13.34, Inertia 3, React 19 + TS, Tailwind 4, shadcn/ui (new-york), **Fortify** (solo `registration` y `resetPasswords` activos; sin 2FA ni verificación de correo), **Wayfinder** (rutas tipadas en TS), **Vite+** (`vp check` = oxlint + oxfmt, reemplaza ESLint/Prettier), Pest 5, Pint, Larastan nivel 7, CI de GitHub Actions (PHP 8.4).
- `composer.lock` exige **PHP ≥ 8.4** (Pest 5, Symfony 8). El contenedor tiene PHP 8.3.6 y no hay PHP 8.4 en apt; sí hay `dockerd` y Docker Hub es accesible → en este entorno se usará PHP 8.4 vía contenedor Docker (shim `php`/`composer` en el PATH). Node 22 / npm 10 OK (`npm install` ya funciona).
- `laravel.com` está bloqueado por la red: los comandos se validarán contra el código instalado (`vendor/`, `php artisan list`) en lugar de la documentación en línea.

### Decisiones tomadas con el usuario

- **BD local por defecto: MySQL** (`.env.example` con `DB_CONNECTION=mysql`, base `reusa`); README documenta el cambio a SQLite en una línea. Pruebas: SQLite en memoria (ya configurado en `phpunit.xml`).
- **PHP 8.4** como requisito (no se toca el lockfile del kit).
- **Rama**: el entorno exige trabajar en `claude/reusa-functional-platform-gwxmwe` (en lugar de `feature/initial-advance`); commits pequeños y convencionales en ella.

---

## Decisiones técnicas (justificadas)

| Tema                      | Decisión                                                                                                                                                                                                                                                                                                                               | Por qué                                                                                                           |
| ------------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ----------------------------------------------------------------------------------------------------------------- |
| Roles                     | `enum UserRole: string { User='user'; Admin='admin' }` + columna `users.role`                                                                                                                                                                                                                                                          | Dos roles no justifican spatie/permission.                                                                        |
| Autorización              | `PublicationPolicy`, `UserPolicy`; middleware `admin` (`EnsureUserIsAdmin`) para `/admin/*`; `EnsureUserIsActive` en rutas auth (cierra sesión si la cuenta se desactiva)                                                                                                                                                              | Requisito: autorización en cada acción de escritura.                                                              |
| Login de inactivos        | `Fortify::authenticateUsing()` rechaza `is_active=false` con mensaje traducido `auth.inactive`                                                                                                                                                                                                                                         | Punto único y probado.                                                                                            |
| Rate limiting             | `login` (ya existe, 5/min) + limitador `register` (5/min por IP) aplicado a la ruta POST de registro de Fortify                                                                                                                                                                                                                        | Requisito de seguridad.                                                                                           |
| Categorías                | Tabla `categories` autorreferenciada (`parent_id`), `slug` único, `sort_order`, `icon` (nombre lucide). Regla `LeafCategory` (solo microcategorías)                                                                                                                                                                                    | Dos niveles exactos, simple y extensible.                                                                         |
| Enums de dominio          | `PublicationModality` (donation/exchange/sale, `requiresPrice()`), `ItemCondition` (like_new/good/fair), `PublicationStatus` (available/reserved/delivered/sold, `canTransitionTo($to, $modality)`); todos con `label()` en español                                                                                                    | Reglas centralizadas y testeables unitariamente.                                                                  |
| Transiciones de estado    | `available ↔ reserved`; `available                                                                                                                                                                                                                                                                                                     | reserved → sold`**solo** si modalidad`sale`; `available                                                           | reserved → delivered`solo si`donation | exchange`; `delivered`y`sold` son finales | “Simple y probado”; documentado en la UI con las opciones válidas únicamente. |
| Precio                    | Entero en COP (`unsignedBigInteger`, sin decimales), obligatorio y ≥ 1 solo en `sale`; se fuerza a `null` en otras modalidades (igual para `wanted_in_exchange`, solo en `exchange`)                                                                                                                                                   | El peso colombiano no usa centavos.                                                                               |
| URLs                      | `slug` = `Str::slug(title)` + sufijo aleatorio corto (p. ej. `bicicleta-montanera-x7k2qd`), estable aunque cambie el título; route-model binding por `slug`                                                                                                                                                                            | IDs legibles sin colisiones ni enlaces rotos.                                                                     |
| Ocultar (admin)           | Columnas `hidden_at`, `hidden_reason`, `hidden_by` en `publications` + scope `visible()`                                                                                                                                                                                                                                               | Más simple que una tabla de moderación; suficiente para P1.                                                       |
| Imágenes                  | Tabla `publication_images` (`path`, `position`), disco `public`, `publications/{id}/{uuid}.{ext}`; 1–4, `mimes:jpg,jpeg,png,webp`, `max:2048`. En edición el formulario envía `image_order[]` con tokens `existing:{id}` / `new:{i}` → `SyncPublicationImages` borra las quitadas, guarda las nuevas y reordena                        | Cubre agregar, quitar y ordenar con un solo endpoint.                                                             |
| Capa de negocio           | Actions en `app/Actions/Publications/*` (`CreatePublication`, `UpdatePublication`, `UpdatePublicationStatus`, `SyncPublicationImages`, `DeletePublication`) y `app/Actions/Admin/*`; controladores delgados; Form Requests para validación                                                                                             | Lo pedido, sin servicios/repositorios extra.                                                                      |
| Props de Inertia          | API Resources explícitos (`PublicationCardResource`, `PublicationDetailResource`, `CategoryResource`, `PublicUserResource`, `Admin\UserResource`); `auth.user` compartido como array explícito (id, name, email, role, isAdmin)                                                                                                        | No exponer modelos completos; el publicante muestra nombre, comunidad y “miembro desde” (sin correo ni teléfono). |
| Búsqueda                  | `whereLike(..., caseSensitive: false)` sobre título/descripción; orden por precio con `CASE WHEN price IS NULL` (nulos al final)                                                                                                                                                                                                       | SQL portable MySQL/PostgreSQL/SQLite.                                                                             |
| Filtros                   | `CatalogRequest` valida `q`, `category` (slug macro), `subcategory` (slug micro), `modality`, `status` (por defecto `available`; `all` permitido), `sort` (`recent`, `price_asc`, `price_desc`); scope `filter()`; `paginate(12)->withQueryString()`                                                                                   | Filtros en la URL y paginación que los conserva.                                                                  |
| Eloquent                  | `Model::preventLazyLoading(! app()->isProduction())`, `Model::shouldBeStrict` parcial, eager loading en listados, índices en `status`, `modality`, `category_id`, `price`, `created_at`, `hidden_at`; FKs con `constrained()`                                                                                                          | Requisitos de calidad.                                                                                            |
| Idioma                    | `APP_LOCALE=es`, `APP_FALLBACK_LOCALE=es`, `APP_FAKER_LOCALE=es_ES`; `php artisan lang:publish` + `lang/es/{auth,pagination,passwords,validation}.php` y `lang/es.json` escritos a mano (sin paquete externo). UI en español directamente en los TSX (un solo idioma ⇒ sin librería i18n). Fechas relativas y COP con `Intl` (`es-CO`) | Sin textos en inglés y sin dependencias nuevas.                                                                   |
| Layouts                   | Nuevo `PublicLayout` (header con logo, catálogo, “Publicar”, login/registro o menú de usuario; footer con términos) para landing/catálogo/detalle/términos; el `AppLayout` del kit (sidebar) para “Mis publicaciones”, formularios, ajustes y admin. Se elimina la página `dashboard` del kit; `fortify.home` → “Mis publicaciones”    | Reutiliza el kit sin reinventarlo.                                                                                |
| Identidad visual          | Tokens shadcn en `app.css` con primario verde (oklch ~ `0.55 0.14 155`), neutros cálidos, modo claro/oscuro del kit; badges por modalidad: donación = verde, intercambio = azul/índigo, venta = ámbar; contraste AA verificado                                                                                                         | Pedido explícito.                                                                                                 |
| Componentes shadcn nuevos | Se agregan solo los necesarios (textarea, radio-group, alert-dialog, pagination, table, tabs) copiando el patrón del kit                                                                                                                                                                                                               | Mínimo indispensable.                                                                                             |
| Calidad                   | Larastan se mantiene en **nivel 7** (ya configurado por el kit, más estricto que el 5–6 pedido); `declare(strict_types=1)` en archivos PHP propios y tocados; `pint --test`, `vp check`, `tsc --noEmit`, `npm run build`                                                                                                               | Lo que trae el kit.                                                                                               |
| CI                        | `.github/workflows/tests.yml`: forzar `DB_CONNECTION=sqlite` + crear `database/database.sqlite`, porque `.env.example` ahora apunta a MySQL; también correr en push a la rama de trabajo                                                                                                                                               | Que el CI no se rompa por el cambio a MySQL.                                                                      |

---

## Fases (cada una termina con pruebas + Pint + PHPStan + `vp check` + `tsc` + build, y commit convencional)

### Fase 0 — Entorno, idioma e identidad base

Tareas: levantar PHP 8.4 (Docker) y verificar la línea base verde; `APP_NAME=ReUsa`, `.env.example` documentado (MySQL, `FILESYSTEM_DISK=public`, locale es); `lang:publish` + traducciones `es`; traducir todas las pantallas del kit (login, registro, recuperar, ajustes, sidebar, menús); tema verde y `<title>` por página; `PublicLayout`; `docs/IMPLEMENTATION_PLAN.md` y `docs/BACKLOG.md` iniciales; ajuste de CI.
Pruebas: suite del kit pasando; test de que los mensajes de validación salen en español.
Aceptación: `php artisan test`, lint y build verdes; ninguna cadena visible en inglés en pantallas del kit.

### Fase 1 — Usuarios (P0)

Tareas: migración `add_reusa_fields_to_users_table` (`phone` 20 null, `community` 120 null, `role` def. `user` indexado, `is_active` def. true, `terms_accepted_at` null); `UserRole`; modelo `User` (casts, `isAdmin()`, Fillable sin `role`/`is_active`); `CreateNewUser` con `phone`, `community`, `terms` (`accepted`) → `terms_accepted_at`; perfil editable con teléfono y comunidad; página `/terms` con la política exigida (ReUsa solo canal de contacto; sin responsabilidad por estado, calidad, procedencia, legalidad ni acuerdos económicos/entrega) enlazada desde registro y footer; `authenticateUsing` para inactivos; middlewares `admin` y `active`; limitador `register`; `UserFactory` con estados `admin()`, `inactive()`.
Pruebas (Feature): registro feliz (guarda `terms_accepted_at`, campos opcionales), registro sin aceptar términos falla, login, login de inactivo rechazado, sesión de usuario desactivado se cierra, `/admin` → 403 para `user` y 200 para `admin`, invitados redirigidos a login, rate limit de registro, actualización de perfil con teléfono/comunidad. (Unit): `UserRole`.

### Fase 2 — Categorías (P0)

Tareas: migración y modelo `Category` (`parent()`, `children()`, scopes `roots()`, `leaves()`, `isLeaf()`), `CategorySeeder` con las 8 macros y sus micros exactas del enunciado (idempotente con `updateOrCreate` por slug), `CategoryResource` (árbol macro → micros, compartido a formularios y filtros), regla `LeafCategory`.
Pruebas: seeder crea 8 macros y 30 micros sin duplicar al re-ejecutar; scopes; regla rechaza macros e IDs inexistentes.

### Fase 3 — Publicaciones: dominio y backend (P0)

Tareas: migraciones `publications` (user_id, category_id, title 120, slug único, description, modality, condition, price, wanted_in_exchange, location 150, status, hidden_*, timestamps, softDeletes, índices) y `publication_images`; enums; modelo `Publication` (relaciones, casts a enums, scopes `available()`, `visible()`, `ownedBy()`, `filter()`, `search()`, accesor de portada); `PublicationFactory` con estados expresivos (`donation()`, `exchange()`, `sale(int $price)`, `reserved()`, `delivered()`, `sold()`, `hidden()`, `withImages(n)`); `PublicationPolicy` (`update`, `delete`, `changeStatus` = dueño; `view` oculta solo dueño/admin; `moderate` = admin); Form Requests (`StorePublicationRequest`, `UpdatePublicationRequest`, `UpdatePublicationStatusRequest`, `CatalogRequest`); Actions; controladores `PublicationController` (index/show públicos; create/store/edit/update/destroy autenticados), `MyPublicationController@index`, `PublicationStatusController`; Resources.
Rutas (en inglés): `GET /publications`, `GET /publications/{publication:slug}`, `GET /publications/create`, `POST /publications`, `GET /publications/{slug}/edit`, `PUT /publications/{slug}` (multipart vía `_method`), `PATCH /publications/{slug}/status`, `DELETE /publications/{slug}`, `GET /my/publications`.
Pruebas (Feature): crear donación/intercambio/venta; precio obligatorio y > 0 en venta, `null` forzado en otras; `wanted_in_exchange` solo en intercambio; categoría macro rechazada; imágenes 0 y 5 rechazadas, tipo (pdf/gif/svg) y tamaño (> 2 MB) rechazados, con `Storage::fake('public')`; edición con agregar/quitar/reordenar y límite de 4; nadie edita/elimina/cambia estado de publicaciones ajenas (403); soft delete; listado con cada filtro y combinaciones, por defecto solo `available`, ocultas excluidas, paginación conserva query string; orden por precio; detalle de publicación oculta = 404 para terceros; transiciones válidas e inválidas vía HTTP. (Unit): enums (`label`, `requiresPrice`, `canTransitionTo` en tabla de verdad completa), scopes, Actions (`CreatePublication`, `UpdatePublicationStatus`, `SyncPublicationImages`), generación de slug.

### Fase 4 — Publicaciones: frontend (P0)

Páginas: **Landing** (propuesta de valor, cómo funciona en 3 pasos, modalidades, CTA, últimas 8 publicaciones); **Catálogo** (grilla de tarjetas con imagen/placeholder, título, categoría, badge de modalidad, precio COP si aplica, ubicación, fecha relativa; búsqueda con debounce, filtros en panel lateral en escritorio y `Sheet` en móvil, orden, paginación, estado vacío útil, skeletons en navegación); **Detalle** (galería con miniaturas y teclado, ficha completa, publicante, aviso “El pago y la entrega se coordinan directamente entre las partes”, botón deshabilitado “Solicitudes disponibles próximamente”, acciones del dueño); **Formulario crear/editar** (secciones, selects dependientes macro → micro, selector de modalidad que muestra precio o “qué busco a cambio”, aviso de productos no admitidos, carga de imágenes con previsualización/quitar/ordenar, errores por campo y foco al primer error); **Mis publicaciones** (lista/tabla responsive con estado, editar, cambiar estado con solo las transiciones válidas, eliminar con `AlertDialog`); toasts con `Inertia::flash` + `useFlashToast` del kit.
Pruebas: `tsc --noEmit`, `vp check`, `npm run build`; tests feature de que cada página renderiza el componente Inertia correcto con los props esperados (`assertInertia`).

### Fase 5 — Datos de demostración

Tareas: `DatabaseSeeder` → `CategorySeeder`, `DemoUserSeeder` (1 admin `admin@reusa.test`, 5 usuarios `*@reusa.test`, contraseña de desarrollo `password`, solo si `! app()->isProduction()`), `DemoPublicationSeeder` (~20 publicaciones en todas las modalidades/estados/categorías con textos realistas de Medellín). Imágenes generadas con GD (JPEG ligeros con color por categoría y forma simple) en `storage/app/public` — sin internet.
Pruebas: `migrate:fresh --seed` en SQLite (test) y verificación manual en **MySQL 8** (contenedor) para confirmar compatibilidad.

### Fase 6 — Panel de administración (P1, solo con P0 completo y verde)

Tareas: `/admin` dashboard (usuarios totales/activos, publicaciones por estado y por modalidad, ocultas); `/admin/users` (búsqueda, paginación, activar/desactivar, cambiar rol; un admin no puede desactivarse ni quitarse el rol — `UserPolicy` + validación); `/admin/publications` (búsqueda, filtro por estado/ocultas, ocultar con motivo opcional en diálogo, volver a mostrar). Ítem “Administración” en el sidebar solo para admin.
Pruebas: acceso por rol, contadores, toggle de activo, cambio de rol, auto-protección del admin, ocultar/mostrar y su efecto en el catálogo y detalle.

### Fase 7 — Documentación, capturas y QA final

Tareas: `README.md` en español (descripción, stack, requisitos PHP 8.4/Node 22/MySQL, instalación paso a paso, `storage:link`, usuarios demo, comandos de pruebas/calidad, estructura, decisiones); `docs/IMPLEMENTATION_PLAN.md` actualizado con el estado real; `docs/BACKLOG.md`; capturas móvil/escritorio con Playwright (Chromium preinstalado) en `docs/screenshots/`; revisión de accesibilidad básica (alt, foco, labels, contraste); guion de demo de 3–5 min; cobertura si hay driver disponible (pcov/xdebug en la imagen), si no, se omite y se dice.

---

## Arquitectura preparada para “Solicitudes” (fuera de alcance, solo documentada)

Tabla `publication_requests` (`publication_id`, `requester_id`, `message`, `status` enum `RequestStatus` pending/accepted/rejected/cancelled, `responded_at`, timestamps; índice único parcial lógico: una solicitud `pending` por usuario y publicación). `PublicationRequestPolicy` (no solicitar lo propio, solo publicaciones `available` y visibles; solo el dueño acepta/rechaza). Al aceptar: Action `AcceptPublicationRequest` → publicación pasa a `reserved` reutilizando `UpdatePublicationStatus`; las demás pendientes se rechazan. Historial en “Mis solicitudes”. El botón deshabilitado del detalle será el punto de entrada.

## Fuera de alcance (irá a `docs/BACKLOG.md`)

Flujo de solicitudes; orientación reutilizar/reparar/reciclar; notificaciones; chat; pasarelas de pago; app móvil/PWA; IA; múltiples comunidades; verificación de correo y 2FA; reportes de usuarios; favoritos; admin avanzado (auditoría, gestión de categorías por UI); optimización/redimensionado de imágenes; pruebas de componentes frontend/E2E.

## Riesgos

- **PHP 8.4 en este contenedor** depende de poder iniciar `dockerd`; alternativa: binario PHP 8.4 por otra vía. Si nada funciona, lo informo antes de seguir (no se baja la versión sin preguntar).
- MySQL no está instalado localmente: compatibilidad validada con contenedor `mysql:8` si Docker funciona; si no, se valida solo SQLite y se indica.
- Wayfinder genera tipos ejecutando `php artisan` durante `vite build`: depende del shim de PHP.
- Carga de archivos con `PUT` en Inertia requiere `forceFormData` + spoofing `_method`; cubierto en pruebas.
- Tiempo: P1 se hace solo si P0 queda completo y verde.

## Archivos críticos

- Backend: `app/Models/{User,Category,Publication,PublicationImage}.php`, `app/Enums/*`, `app/Actions/Fortify/CreateNewUser.php`, `app/Actions/Publications/*`, `app/Actions/Admin/*`, `app/Policies/*`, `app/Http/Requests/*`, `app/Http/Controllers/{Publication*,MyPublicationController,Admin/*}.php`, `app/Http/Resources/*`, `app/Http/Middleware/{EnsureUserIsAdmin,EnsureUserIsActive,HandleInertiaRequests}.php`, `app/Providers/{AppServiceProvider,FortifyServiceProvider}.php`, `bootstrap/app.php`, `routes/{web,settings,admin}.php`, `database/{migrations,factories,seeders}/*`, `lang/es/*`, `config/fortify.php`.
- Frontend: `resources/css/app.css`, `resources/js/app.tsx`, `resources/js/layouts/public-layout.tsx`, `resources/js/pages/{welcome,terms}.tsx`, `resources/js/pages/publications/{index,show,create,edit}.tsx`, `resources/js/pages/my/publications.tsx`, `resources/js/pages/admin/*`, `resources/js/components/publications/*`, `resources/js/lib/format.ts`, `resources/js/types/*`.
- Reutilizar: `useFlashToast`, `InputError`, `Heading`, `AppLayout`/`AppSidebar`/`NavMain`, `Sheet`, `Select`, `Badge`, `Card`, `Skeleton`, `Spinner`, `Form` de Inertia + rutas Wayfinder, `PasswordValidationRules`/`ProfileValidationRules`.

## Verificación de punta a punta

1. `composer test` (config:clear + `pint --test` + PHPStan + `php artisan test`) verde; `npm run check`, `npm run types:check`, `npm run build` verdes.
2. `php artisan migrate:fresh --seed` en SQLite y en MySQL 8 (contenedor).
3. `composer dev` y recorrido con Playwright (móvil 390px y escritorio 1440px): registro con términos → publicar venta con 2 fotos → editar (quitar/agregar/reordenar) → reservar → vender → eliminar; visitante busca y filtra por macro/micro/modalidad/estado y abre detalle; admin entra a `/admin`, oculta una publicación (desaparece del catálogo) y desactiva un usuario (no puede iniciar sesión); usuario normal recibe 403 en `/admin`. Capturas en `docs/screenshots/`.
4. Resumen final: hecho vs. pendiente, cómo ejecutar, resultado de pruebas (y cobertura si es posible), guion de demo de 3–5 min.
