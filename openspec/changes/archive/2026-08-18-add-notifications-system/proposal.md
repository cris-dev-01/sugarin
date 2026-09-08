## Why

La aplicación no cuenta con ningún mecanismo de notificación hacia los usuarios: los cambios de perfil, el registro de lecturas de glucosa y la falta de seguimiento de pacientes ocurren sin ningún aviso al usuario afectado ni a los administradores, dificultando la trazabilidad de cambios sensibles (ej. cambio de contraseña) y el seguimiento clínico oportuno. Introducir el sistema de notificaciones nativo de Laravel (canales `mail` y `database`) permite cerrar este vacío de forma incremental, reutilizando la infraestructura de colas (`QUEUE_CONNECTION=database`) y correo (`MAIL_MAILER=smtp`) ya configurada en el proyecto.

## What Changes

- Se agrega la tabla `notifications` (vía `php artisan notifications:table`) y la infraestructura base de notificaciones en cola (`ShouldQueue`) con canales `mail` + `database`.
- Se añade un componente de notificaciones in-app (campanita/dropdown) en `Header.vue`, reemplazando el listado estático de demostración actual por datos reales (`unreadNotifications`) expuestos vía props compartidas de Inertia.
- **Fase básica**: se notifica al usuario (database) cuando actualiza sus datos básicos de perfil (nombre/email) o su contraseña, desde `UpdateProfileSrv` y `UpdatePasswordSrv`.
- **Fase media**: se notifica al paciente (mail + database) cada vez que se registra una nueva lectura de glucosa, incluyendo el status y el rango calculado, desde `StoreGlucoseLogSrv`.
- **Fase estratégica**: se agrega un comando Artisan programado (registrado vía `Schedule::command()` en `routes/console.php`) que identifica pacientes sin lecturas registradas en un periodo configurable y notifica (mail + database) **únicamente a los administradores** — el paciente no recibe aviso por este comando, ya que no existe ningún registro nuevo que notificarle.
- **Fase estratégica (alerta de lecturas alteradas)**: se agrega un segundo comando Artisan programado, independiente del anterior, que detecta pacientes con al menos una lectura "Elevada" o "Baja" (fuera de rango) en las últimas 24 horas (configurable) y notifica (mail + database) **únicamente a los administradores** — el paciente ya fue notificado en tiempo real al momento de registrar esa lectura (fase media), por lo que este comando no lo vuelve a notificar.
- Se agregan endpoints para listar (paginado) y marcar como leídas las notificaciones in-app del usuario autenticado.
- La columna `notifications.type` deja de guardar el nombre de la clase PHP de la notificación y pasa a ser un `ENUM` de base de datos con 4 valores de negocio (`glucose-log`, `abnormal-glucose-log`, `overdue-glucose-log`, `profile-changed`), respaldado por el enum `App\Enums\NotificationType` y el hook `databaseType()` de Laravel.
- En el frontend, cada notificación muestra un ícono de `lucide-vue-next` según su tipo; se elimina el botón individual de "marcar como leída" (toda la tarjeta es clickeable); y se agrega un botón "Ver todas" que abre un drawer paginado (10 por página) con el historial completo (leídas y no leídas), destacando las no leídas con un punto azul.

## Capabilities

### New Capabilities
- `notifications-infrastructure`: tabla `notifications`, notificaciones en cola con canales `mail`/`database`, endpoints de listado/marcado de leídas, y el componente de notificaciones in-app en el frontend.
- `profile-change-notifications`: notificar al usuario al actualizar sus datos básicos de perfil o su contraseña.
- `glucose-log-patient-notification`: notificar al paciente tras el registro de una nueva lectura de glucosa.
- `admin-followup-alerts`: alerta a los administradores (mail + database) sobre pacientes sin lecturas recientes, vía comando programado. El paciente no es notificado por este comando.
- `admin-abnormal-reading-alerts`: alerta a los administradores (mail + database) sobre pacientes con lecturas "Elevada" o "Baja" en las últimas 24 horas, vía un segundo comando programado independiente. El paciente no es notificado por este comando (ya recibió el aviso en tiempo real al registrar la lectura).

### Modified Capabilities
- `glucose-log-store`: se agrega el requisito de que, tras crear exitosamente un log, el sistema despache una notificación al paciente correspondiente.

## Impact

- **Backend**: nueva migración para la tabla `notifications` + migración posterior que convierte `type` a `ENUM`; nuevo `App\Enums\NotificationType`; nuevas clases `App\Notifications\*` (`ProfileUpdatedNotification`, `PasswordUpdatedNotification`, `GlucoseLogRegisteredNotification`, `AdminFollowUpAlertNotification`, `AdminAbnormalReadingAlertNotification`), todas con `databaseType()`; modificaciones en `UpdateProfileSrv`, `UpdatePasswordSrv` y `StoreGlucoseLogSrv` para despachar las notificaciones; dos comandos Artisan independientes (`app/Console/Commands/`), admin-facing (mail + database), registrados vía `Schedule::command()` en `routes/console.php`; nuevo `NotificationController` con `index` (listado paginado), `markAsRead` y `markAllAsRead`; nuevo `App\Http\Resources\NotificationResource`.
- **Frontend**: `Header.vue` consume notificaciones reales vía `NotificationCard`; nuevos componentes `components/notifications/card/NotificationCard.vue` y `components/notifications/drawer/NotificationsDrawer.vue`; nuevos composables `useNotifications` (marcar como leída/todas) y `useNotificationIcon` (mapeo tipo→ícono); nuevos tipos en `types/notification.d.ts` (`AppNotificationType`, `PaginatedNotifications`).
- **Infraestructura**: reutiliza `QUEUE_CONNECTION=database` (tabla `jobs` ya migrada) y `MAIL_MAILER=smtp` (Mailtrap en local); no se requieren nuevas dependencias de Composer ni NPM.
- Sin cambios breaking en endpoints existentes; el efecto de notificar es aditivo (side-effect) sobre las acciones ya implementadas.
