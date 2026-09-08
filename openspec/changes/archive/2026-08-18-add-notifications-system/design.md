## Context

La aplicación (Laravel 13 + Inertia + Vue 3) no tiene ningún mecanismo de notificación. El modelo `User` ya usa el trait `Notifiable` (heredado de `Authenticatable`), pero no existe la tabla `notifications` ni ninguna clase `Notification`. La infraestructura de soporte ya está configurada:

- `QUEUE_CONNECTION=database` (tabla `jobs` ya migrada) → permite notificaciones en cola sin trabajo adicional de infraestructura.
- `MAIL_MAILER=smtp` apuntando a Mailtrap en local → suficiente para probar el canal `mail` sin necesidad de un proveedor real.
- `BROADCAST_CONNECTION=log` → **no** hay infraestructura de tiempo real (WebSockets/Reverb/Pusher) configurada, por lo que el canal `broadcast` queda fuera de alcance.
- El scheduler se declara con el facade `Schedule` directamente en `routes/console.php` (estilo Laravel 11+; equivalente a usar `Application::configure()->withSchedule(...)` en `bootstrap/app.php` — ambos alimentan el mismo singleton `Illuminate\Console\Scheduling\Schedule`, verificado con `schedule:list`). `app/Console/Kernel.php` existe en el repo pero no está enlazado por el bootstrap (confirmado con `app(\Illuminate\Contracts\Console\Kernel::class)`, que resuelve a `Illuminate\Foundation\Console\Kernel`) y su método `schedule()` nunca se ejecuta — es código muerto, no debe usarse para registrar tareas nuevas. Los comandos van en `app/Console/Commands/`.
- `Header.vue` ya tiene una sección de notificaciones (dropdown/campanita) con datos estáticos de demostración (`notifications = ref([...])`) que debe reemplazarse por datos reales.
- El patrón de capas del proyecto (Request → DTO → Action/Srv → Controller) ya está implementado para `Profile` y `GlucoseLogs`; las notificaciones se integran como un efecto adicional al final de esos `handle()` existentes, no como un nuevo flujo paralelo.

## Goals / Non-Goals

**Goals:**
- Entregar la infraestructura de notificaciones (tabla, clases base, endpoints, UI in-app) reutilizable por las 3 fases.
- Notificar al usuario (solo `database`) ante cambios de perfil/clave, al paciente (`database` + `mail`) ante un nuevo registro de glucosa (lo haga él o un administrador), y a los administradores (`database` + `mail`) ante falta de seguimiento o lecturas alteradas recientes de algún paciente.
- Reemplazar el listado estático de notificaciones en `Header.vue` por datos reales del usuario autenticado, con un ícono por tipo y un drawer paginado para ver el historial completo (leídas y no leídas).
- Mantener el patrón de capas existente: el `notify()` se dispara desde los `Srv` ya existentes, no desde los controllers.
- Que el campo `type` de la tabla `notifications` sea un valor de negocio controlado (enum), no el nombre de la clase PHP de la notificación.

**Non-Goals:**
- Notificaciones en tiempo real (WebSockets/broadcast) — requeriría configurar Reverb/Pusher, fuera de alcance de este cambio.
- Preferencias de notificación por usuario (opt-out por canal) — se asume que todos los canales están siempre activos.
- Canales adicionales (SMS, Slack) — solo `mail` y `database`.
- Preferencia de usuario para elegir el ícono/color de cada tipo de notificación — el mapeo tipo→ícono es fijo, definido en un composable del frontend.

## Decisions

**1. Tabla y trait nativos de Laravel, sin modelo custom.**
Se usa `php artisan notifications:table` (migración estándar de Laravel) y el trait `Notifiable` que `User` ya tiene por heredar de `Authenticatable`. No se crea un modelo `Notification` propio; se usa `Illuminate\Notifications\DatabaseNotification` vía las relaciones `notifications()`/`unreadNotifications()` que el trait expone.
*Alternativa descartada*: tabla y modelo propios (`AppNotification`) — más control pero duplica funcionalidad que Laravel ya resuelve (paginación, `markAsRead()`, morph a cualquier notifiable).

**2. Una clase `Notification` por evento y por audiencia, todas `ShouldQueue`, canales fijos (no dinámicos por rol).**
`ProfileUpdatedNotification`, `PasswordUpdatedNotification`, `GlucoseLogRegisteredNotification`, `AdminFollowUpAlertNotification`, `AdminAbnormalReadingAlertNotification` en `App\Notifications\`. Todas implementan `ShouldQueue` para no bloquear la request (aprovechando `QUEUE_CONNECTION=database`), pero **no todas usan los mismos canales**:
- `ProfileUpdatedNotification` y `PasswordUpdatedNotification` → solo `['database']`. Son cambios que el propio usuario acaba de hacer y ya ve confirmados en pantalla; no ameritan un correo adicional.
- `GlucoseLogRegisteredNotification` (al paciente), `AdminFollowUpAlertNotification` y `AdminAbnormalReadingAlertNotification` (a cada `Administrator`) → `['database', 'mail']` fijo, sin lógica condicional en `via()`. Son alertas que su destinatario necesita notar aunque no tenga la app abierta.
- **Cada clase tiene un único destinatario por diseño** (no un `via($notifiable)` que decide el canal según el rol): `AdminFollowUpAlertNotification`/`AdminAbnormalReadingAlertNotification` solo se despachan a usuarios con rol `Administrator`, nunca al paciente — el paciente ya fue/es notificado por su propio evento (`GlucoseLogRegisteredNotification` en tiempo real al registrar una lectura, sea él o un admin quien la registre). Notificar al paciente también desde los comandos diarios sería redundante para lecturas alteradas, y no aporta nada para "sin seguimiento" (no hay nada nuevo que contarle sobre su propia inactividad que él no sepa ya).
*Alternativa descartada*: una clase por condición con `via($notifiable)` dinámico según `hasRole('Administrator')` y despacho tanto al paciente como a los admins — fue el diseño inicial, pero mezclaba dos audiencias con necesidades distintas en una sola clase y terminaba renotificando al paciente información que ya tenía (real-time) o que no le aportaba nada (aviso de su propia falta de seguimiento).

**3. Payload `toDatabase()` estandarizado; el `type` vive en la columna nativa, no duplicado en `data`.**
Todas las notificaciones database devuelven `{ title, message, url? }` en `data` (`url` opcional para deep-link). El "tipo" de negocio (para elegir ícono en el frontend, o para consultar por condición en el backend) **no** se guarda dentro de `data`, sino en la columna nativa `notifications.type` — ver decisión 10. Las notificaciones admin-facing (`AdminFollowUpAlertNotification`/`AdminAbnormalReadingAlertNotification`) agregan además `patient_id` a `data` — no se usa en el frontend, pero permite consultar la tabla `notifications` por `data->patient_id` para la idempotencia diaria (ver decisión 9). Este formato estándar permite que el frontend renderice cualquier notificación con un solo componente, sin `switch` por tipo de evento.

**4. Dispatch dentro de los `Srv`, después del commit de la transacción.**
`UpdateProfileSrv`, `UpdatePasswordSrv` y `StoreGlucoseLogSrv` llaman a `$model->notify(...)` como última línea de `handle()`, **fuera** del cierre de `DB::transaction()` (o vía `DB::afterCommit()` si el `notify()` debe ir dentro del closure por dependencia de datos). Esto evita notificar cambios que luego hacen rollback.
*Alternativa descartada*: Observers de modelo (`UserObserver`, `UserGlucoseLogObserver`) — más "mágico" e implícito; el equipo ya sigue el patrón explícito de Actions, y mezclar dos mecanismos de efectos secundarios (Observers + Actions) añade una segunda fuente de verdad para "qué dispara qué".

**5. Notificaciones in-app vía prop compartida de Inertia, no polling/WebSockets.**
Se añade `notifications` (últimas N no leídas) a `HandleInertiaRequests::share()`, igual que ya existe `auth.user`. Se actualiza en cada navegación de Inertia, consistente con que no hay infraestructura de tiempo real. `Header.vue` reemplaza su `ref` estático por esta prop.
*Alternativa descartada*: polling con `setInterval` a un endpoint JSON — añade complejidad (limpieza de intervalos, manejo de visibilidad de pestaña) para un beneficio marginal dado que Inertia ya refresca props en cada visita.

**6. Endpoints de `NotificationController`: marcar como leídas + listado paginado para el drawer.**
`markAsRead` (`PATCH /notifications/{id}/read`) y `markAllAsRead` (`PATCH /notifications/read-all`) para el dropdown (que sigue viviendo en la prop compartida de Inertia). Se agrega además `index` (`GET /notifications`, JSON, 10 por página) exclusivamente para alimentar el drawer "Ver todas" — devuelve todas las notificaciones del usuario (leídas y no leídas), no solo las no leídas de la prop compartida. Mismo patrón de paginación JSON (`data`+`meta.current_page/last_page/per_page/total`) que ya usa `GlucoseLogController::forPatient` para el drawer de historial de lecturas del dashboard.

**7. Fase estratégica como comando programado + Action dedicada, 100% admin-facing.**
`App\Console\Commands\NotifyPatientsWithoutFollowUpCommand`, registrado en `routes/console.php` vía `Schedule::command('notifications:patient-follow-up')->daily()`, delega la detección a `App\Actions\PatientFollowUp\FindPatientsWithoutRecentLogsSrv` (mismo patrón Action del resto del proyecto) y notifica **solo a los usuarios con rol `Administrator`** (`AdminFollowUpAlertNotification`, `mail`+`database`). El paciente no recibe nada desde este comando. El umbral de días sin registro se configura en `config/notifications.php` (`follow_up_days`, default `3`) en vez de hardcodearse.

**8. Alerta de lecturas alteradas como comando independiente, 100% admin-facing, sin duplicar el aviso en tiempo real al paciente.**
Se crea `App\Console\Commands\NotifyPatientsWithAbnormalReadingsCommand` (señal `notifications:patient-abnormal-readings`), con su propia Action `App\Actions\PatientFollowUp\FindPatientsWithAbnormalReadingsSrv` y su propia notificación `AdminAbnormalReadingAlertNotification` (`mail`+`database`, solo a `Administrator`). El paciente **no** recibe nada desde este comando — ya fue notificado en tiempo real por `GlucoseLogRegisteredNotification` al momento de registrar la lectura (fase media), independientemente de si quien la registró fue el propio paciente o un administrador. Se registra como una entrada adicional en `routes/console.php` junto al comando de seguimiento, corriendo también `->daily()`. Ambos comandos comparten el mismo cron del servidor (`* * * * * php artisan schedule:run`) pero son independientes entre sí: un fallo en uno no afecta al otro, y cada uno mantiene una sola responsabilidad, igual que el resto de las Actions del proyecto.
*Alternativa descartada*: agregar el chequeo de lecturas alteradas como un segundo método dentro de `NotifyPatientsWithoutFollowUpCommand` — mezclaría dos condiciones de negocio distintas ("sin seguimiento" vs. "lectura fuera de rango") en una sola clase, dificultando testear y mantener cada una por separado.
*Alternativa descartada (diseño inicial)*: notificar también al paciente desde ambos comandos, con `via($notifiable)` decidiendo el canal según el rol (`database` solo para admin, `database`+`mail` para el paciente). Se descartó porque duplicaba al paciente un aviso que ya recibe en tiempo real (lecturas alteradas) o que no le aporta nada nuevo (seguimiento).
La ventana de tiempo (24 horas) se configura como `config('notifications.abnormal_reading_window_hours')` (default `24`), no se hardcodea. La detección reutiliza los nombres de `Status` ya existentes ("Elevado - fuera de rango normal", "Bajo - fuera de rango normal"), agrupando por paciente para notificar una sola vez aunque tenga varias lecturas alteradas en la ventana.

**9. Idempotencia diaria vía `data->patient_id`, no vía "el paciente ya fue notificado".**
Como el paciente ya no es notifiable en estos dos comandos, la idempotencia ya no puede chequearse contra `$patient->user->notifications()`. En su lugar, ambas notificaciones agregan `patient_id` a su payload `toDatabase()`, y cada comando consulta directamente `DB::table('notifications')->where('type', NotificationType::X->value)->where('data->patient_id', $patient->id)->whereDate('created_at', today())->exists()` antes de notificar a los administradores — es una comprobación global (por paciente, no por admin), consistente con que todos los admins se notifican juntos en la misma pasada. Nótese que consulta por el **valor del enum**, no por `::class` (ver decisión 10) — antes de la decisión 10 se comparaba contra el nombre de clase, que era lo que Laravel guardaba en `type` por defecto.

**10. `notifications.type` como ENUM de negocio (`App\Enums\NotificationType`), no el nombre de la clase PHP.**
Por defecto, Laravel guarda en la columna `type` el nombre completo de la clase de notificación (ej. `App\Notifications\GlucoseLogRegisteredNotification`) — un string libre, no validado por la base de datos, y acoplado 1:1 a la clase PHP (renombrar la clase rompe silenciosamente cualquier consulta por `type`). Se define `App\Enums\NotificationType` (backed enum de string) con los 4 valores de negocio: `glucose-log`, `abnormal-glucose-log`, `overdue-glucose-log`, `profile-changed`. Cada notificación implementa el método `databaseType($notifiable)` — un hook nativo de `Illuminate\Notifications\Channels\DatabaseChannel` (confirmado leyendo el código fuente del framework instalado) que, si existe, reemplaza el nombre de clase por su valor de retorno en la columna `type`. `GlucoseLogRegisteredNotification::databaseType()` es dinámico (`glucose-log` o `abnormal-glucose-log` según si la lectura es normal o no); las demás retornan un valor fijo. La migración `convert_notifications_type_to_enum` altera la columna a `ENUM(...)` con los 4 valores vía `DB::statement()` (no requiere `doctrine/dbal` al ser SQL crudo, evitando el `Blueprint::change()` que sí lo requeriría). Como la tabla solo tenía datos de desarrollo (la app aún no está en producción) con el nombre de clase antiguo como `type` — incompatible con el nuevo `ENUM` — la migración trunca `notifications` antes de alterar la columna.
*Alternativa descartada*: mantener `type` como `VARCHAR` y solo introducir el enum a nivel de aplicación (sin `ENUM` en la base de datos) — más flexible para agregar valores futuros sin migración, pero no es lo que se pidió explícitamente; se documenta aquí como trade-off a tener presente si el equipo necesita agregar un quinto tipo más adelante (requerirá una nueva migración `ALTER TABLE ... MODIFY type ENUM(...)`).
El frontend consume este mismo valor (`notification.type`, ya no `notification.data.type`) para elegir el ícono de `lucide-vue-next` vía el composable `useNotificationIcon` (`glucose-log`→`Droplets`, `abnormal-glucose-log`→`TriangleAlert`, `overdue-glucose-log`→`ClipboardClock`, `profile-changed`→`UserRound`).

## Risks / Trade-offs

- [Las notificaciones en cola no se envían si no hay un worker corriendo] → Mitigación: documentar en `tasks.md` que producción requiere `php artisan queue:work` (o Supervisor); en local, `QUEUE_CONNECTION=sync` permite probar sin worker.
- [Notificar antes de que el cambio se confirme en base de datos] → Mitigación: `notify()` se llama después del `DB::transaction()`/commit, nunca dentro de un closure que pueda hacer rollback.
- [Crecimiento sin límite de la tabla `notifications`] → Mitigación: fuera de alcance de este cambio; se documenta como pregunta abierta para una futura política de retención/purga.
- [El comando programado no corre si no hay cron configurado en el servidor] → Mitigación: documentar en `tasks.md` el requisito de `* * * * * php artisan schedule:run` en el crontab del servidor.
- [Acoplar el envío de mail a acciones síncronas percibidas como rápidas (ej. registrar una lectura de glucosa)] → Mitigación: al ser `ShouldQueue`, el usuario no percibe latencia adicional; el correo puede demorar segundos en llegar sin bloquear la respuesta HTTP.

## Migration Plan

1. Ejecutar `php artisan notifications:table` y migrar (crea la tabla `notifications`).
2. Implementar infraestructura común: `config/notifications.php`, `NotificationController` (mark as read), prop compartida `notifications` en `HandleInertiaRequests`, componente in-app en `Header.vue`.
3. Fase básica: `ProfileUpdatedNotification` + `PasswordUpdatedNotification`, dispatch desde `UpdateProfileSrv`/`UpdatePasswordSrv`.
4. Fase media: `GlucoseLogRegisteredNotification`, dispatch desde `StoreGlucoseLogSrv`.
5. Fase estratégica: `config/notifications.php` (`follow_up_days`), `FindPatientsWithoutRecentLogsSrv`, `AdminFollowUpAlertNotification` (solo administradores, `mail`+`database`), comando `NotifyPatientsWithoutFollowUpCommand` registrado en el scheduler.
6. Fase estratégica (lecturas alteradas): `config/notifications.php` (`abnormal_reading_window_hours`), `FindPatientsWithAbnormalReadingsSrv`, `AdminAbnormalReadingAlertNotification` (solo administradores, `mail`+`database`), comando independiente `NotifyPatientsWithAbnormalReadingsCommand` registrado en el mismo scheduler.
7. Ajuste posterior: `App\Enums\NotificationType`, migración `convert_notifications_type_to_enum` (trunca `notifications` y altera `type` a `ENUM`), `databaseType()` en las 5 clases de notificación, endpoint `GET /notifications` (`NotificationController::index` + `NotificationResource`), y en el frontend `useNotificationIcon`, `useNotifications`, `components/notifications/card/NotificationCard.vue` y `components/notifications/drawer/NotificationsDrawer.vue`.

**Rollback**: cada fase es aditiva y desacoplada — revertir una fase implica eliminar la línea `notify()` correspondiente en su `Srv` y (opcionalmente) la clase `Notification`, sin afectar a las demás fases ni a otras tablas. La tabla `notifications` puede revertirse con la migración estándar (`down()` hace `Schema::dropIfExists('notifications')`).

## Open Questions

- ¿Debe existir alguna preferencia de usuario para desactivar el canal `mail` (opt-out), o se asume siempre activo para el MVP?
- ¿Cuál es el umbral de días sin seguimiento correcto para la fase estratégica? Se propone `3` días por defecto en `config/notifications.php`, a confirmar con negocio/clínica.
- ¿Los administradores deben recibir una notificación por cada paciente sin seguimiento, o un resumen agregado periódico (ej. diario)? *(Resuelto por ahora: una notificación por paciente detectado, sin agregación; queda abierto si el volumen lo justifica en el futuro).*
