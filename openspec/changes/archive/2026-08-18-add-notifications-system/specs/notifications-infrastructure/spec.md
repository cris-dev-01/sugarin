## ADDED Requirements

### Requirement: Tabla de notificaciones y trait Notifiable
El sistema SHALL contar con la tabla `notifications` (generada mediante `php artisan notifications:table`), y el modelo `User` SHALL poder recibir notificaciones a través del trait `Notifiable` ya heredado de `Authenticatable`.

#### Scenario: Migración aplicada correctamente
- **WHEN** se ejecuta la migración de la tabla `notifications`
- **THEN** la base de datos cuenta con una tabla `notifications` con las columnas `id` (uuid), `type`, `notifiable_type`, `notifiable_id`, `data`, `read_at`, `created_at`, `updated_at`

### Requirement: Columna type como ENUM de negocio
El sistema SHALL almacenar en `notifications.type` un valor de negocio controlado (`App\Enums\NotificationType`: `glucose-log`, `abnormal-glucose-log`, `overdue-glucose-log`, `profile-changed`) en vez del nombre de la clase PHP de la notificación. Cada clase de notificación SHALL implementar `databaseType($notifiable)` para producir este valor.

#### Scenario: Notificación despachada persiste el valor del enum
- **WHEN** cualquier notificación del sistema se despacha por el canal `database`
- **THEN** la columna `type` del registro creado contiene uno de los 4 valores del enum, no el nombre de la clase PHP

#### Scenario: Columna restringida a los valores del enum
- **WHEN** se inspecciona la definición de la columna `type` en la base de datos
- **THEN** es un `ENUM` que solo acepta los 4 valores de `App\Enums\NotificationType`

### Requirement: Notificaciones despachadas en cola
Toda clase de notificación del sistema SHALL implementar `Illuminate\Contracts\Queue\ShouldQueue`. El canal `database` SHALL estar presente en todas ellas; el canal `mail` SHALL agregarse únicamente en los eventos donde se justifique avisar al usuario aunque no tenga la aplicación abierta (ver capabilities `glucose-log-patient-notification` y `patient-followup-alerts`).

#### Scenario: Envío de una notificación
- **WHEN** una acción del sistema despacha una notificación a un usuario
- **THEN** el envío se encola (tabla `jobs`) en vez de ejecutarse de forma síncrona dentro de la misma request

### Requirement: Listado paginado de notificaciones
El sistema SHALL proveer el endpoint `GET /notifications` que retorna, paginadas (10 por página), todas las notificaciones (leídas y no leídas) del usuario autenticado, ordenadas de más reciente a más antigua, en el mismo formato de paginación (`data` + `meta.current_page/last_page/per_page/total`) usado por otros listados paginados del sistema.

#### Scenario: Listado paginado exitoso
- **WHEN** el usuario autenticado solicita `GET /notifications`
- **THEN** el sistema retorna hasta 10 notificaciones (leídas y no leídas) junto con los metadatos de paginación

#### Scenario: Solo se listan las notificaciones propias
- **WHEN** el usuario autenticado solicita `GET /notifications`
- **THEN** el listado no incluye notificaciones de otros usuarios

### Requirement: Prop compartida de notificaciones no leídas
El sistema SHALL exponer, en cada respuesta Inertia, las notificaciones no leídas más recientes del usuario autenticado, incluyendo `id`, `type`, `data` (con `title`, `message` y `url` opcional) y `created_at`.

#### Scenario: Usuario autenticado con notificaciones no leídas
- **WHEN** un usuario autenticado con notificaciones no leídas navega a cualquier página
- **THEN** la respuesta Inertia incluye la lista de sus notificaciones no leídas más recientes

#### Scenario: Usuario sin notificaciones
- **WHEN** un usuario autenticado no tiene notificaciones no leídas
- **THEN** la prop de notificaciones se retorna como una lista vacía

### Requirement: Marcar notificaciones como leídas
El sistema SHALL proveer los endpoints `PATCH /notifications/{id}/read` y `PATCH /notifications/read-all` para marcar, respectivamente, una o todas las notificaciones del usuario autenticado como leídas.

#### Scenario: Marcar una notificación como leída
- **WHEN** el usuario autenticado envía `PATCH /notifications/{id}/read` sobre una notificación propia no leída
- **THEN** el sistema establece `read_at` con la fecha/hora actual y la notificación deja de aparecer en la prop de no leídas

#### Scenario: Intentar marcar una notificación de otro usuario
- **WHEN** el usuario autenticado envía `PATCH /notifications/{id}/read` sobre una notificación que no le pertenece
- **THEN** el sistema retorna HTTP 404

#### Scenario: Marcar todas como leídas
- **WHEN** el usuario autenticado envía `PATCH /notifications/read-all`
- **THEN** todas sus notificaciones no leídas quedan con `read_at` establecido

### Requirement: Componente de notificaciones in-app en el header
El frontend SHALL reemplazar el listado estático de notificaciones de `Header.vue` por las notificaciones reales del usuario autenticado, mostrando un ícono según el `type`, `title`, `message` y tiempo relativo. Toda la tarjeta de la notificación (no solo un botón) SHALL ser clickeable para marcarla como leída.

#### Scenario: Notificaciones reales visibles en el dropdown
- **WHEN** el usuario autenticado abre el dropdown de notificaciones del header
- **THEN** se muestran sus notificaciones no leídas reales en vez de los datos de demostración estáticos, cada una con el ícono correspondiente a su `type`

#### Scenario: Click en la tarjeta marca como leída
- **WHEN** el usuario autenticado hace click en cualquier parte de una notificación no leída (dropdown o drawer)
- **THEN** la notificación se marca como leída, sin necesidad de un botón dedicado

#### Scenario: Sin notificaciones pendientes
- **WHEN** el usuario autenticado no tiene notificaciones no leídas
- **THEN** el dropdown muestra el estado vacío ("No hay datos disponibles")

### Requirement: Ícono por tipo de notificación
El frontend SHALL mostrar un ícono de `lucide-vue-next` distinto según el `type` de la notificación: `droplets` para `glucose-log`, `triangle-alert` para `abnormal-glucose-log`, `clipboard-clock` para `overdue-glucose-log`, y `user-round` para `profile-changed`.

#### Scenario: Ícono correspondiente al tipo
- **WHEN** se renderiza una notificación de un `type` dado
- **THEN** se muestra el ícono correspondiente a ese `type` a la izquierda del texto

### Requirement: Drawer "Ver todas" con historial paginado
El frontend SHALL proveer un botón "Ver todas" (junto a "Marcar todas como leídas") que abre un drawer con el historial completo de notificaciones del usuario (leídas y no leídas), paginado de a 10 por página, consumiendo `GET /notifications`. Las notificaciones no leídas dentro del drawer SHALL destacarse con un punto azul al costado derecho.

#### Scenario: Abrir el drawer
- **WHEN** el usuario autenticado hace click en "Ver todas"
- **THEN** se abre un drawer mostrando las primeras 10 notificaciones (leídas y no leídas) más recientes, con paginación para ver el resto

#### Scenario: Notificación no leída destacada en el drawer
- **WHEN** el drawer muestra una notificación cuyo `read_at` es `null`
- **THEN** esa notificación exhibe un punto azul a su derecha

#### Scenario: Notificación leída sin destacar
- **WHEN** el drawer muestra una notificación cuyo `read_at` no es `null`
- **THEN** esa notificación no exhibe el punto azul
