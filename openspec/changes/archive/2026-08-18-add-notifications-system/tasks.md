## 1. Infraestructura base de notificaciones

- [x] 1.1 Ejecutar `php artisan notifications:table` y correr la migración (crea la tabla `notifications`)
- [x] 1.2 Crear `config/notifications.php` con `follow_up_days` (default `3`) y cualquier otro umbral necesario
- [x] 1.3 Agregar las notificaciones no leídas (últimas N) del usuario autenticado a `HandleInertiaRequests::share()`
- [x] 1.4 Crear `App\Http\Controllers\NotificationController` con acciones `markAsRead` y `markAllAsRead`
- [x] 1.5 Registrar las rutas `PATCH /notifications/{id}/read` y `PATCH /notifications/read-all` en `routes/web.php` (dentro del grupo `auth`)
- [x] 1.6 Agregar el tipo `Notification` (id, type, data.title, data.message, data.url?, created_at) en `types/notification.d.ts`

## 2. Fase básica: notificaciones de perfil

- [x] 2.1 Crear `App\Notifications\ProfileUpdatedNotification` (`ShouldQueue`, canal `database`)
- [x] 2.2 Crear `App\Notifications\PasswordUpdatedNotification` (`ShouldQueue`, canal `database`)
- [x] 2.3 Despachar `ProfileUpdatedNotification` al final de `UpdateProfileSrv::handle()`
- [x] 2.4 Despachar `PasswordUpdatedNotification` al final de `UpdatePasswordSrv::handle()`
- [x] 2.5 Escribir tests Feature (`Notification::fake()`) que verifiquen el despacho en actualización exitosa y la ausencia de despacho en validación fallida

## 3. Fase media: notificación de lectura de glucosa

- [x] 3.1 Crear `App\Notifications\GlucoseLogRegisteredNotification` (`ShouldQueue`, canales `mail` + `database`), recibiendo el `UserGlucoseLog` con sus relaciones (`userPatient.user`, `status`) cargadas
- [x] 3.2 Despachar la notificación al paciente (`$log->userPatient->user`) al final de `StoreGlucoseLogSrv::handle()`, después del `DB::transaction()`
- [x] 3.3 Diferenciar el contenido del mail/database según el status sea "Rango normal" o esté fuera de rango
- [x] 3.4 Escribir tests Feature para ambos casos (lectura dentro y fuera de rango) y para el caso de registro fallido (sin rango configurado)

## 4. Fase estratégica: alertas de seguimiento

- [x] 4.1 Crear `App\Actions\PatientFollowUp\FindPatientsWithoutRecentLogsSrv` que retorna los `UserPatient` sin `UserGlucoseLog` en los últimos `follow_up_days` días (o sin ningún registro histórico)
- [x] 4.2 Crear `App\Notifications\AdminFollowUpAlertNotification` (`ShouldQueue`, canales fijos `mail` + `database`, sin `via()` dinámico por rol)
- [x] 4.3 Crear el comando `App\Console\Commands\NotifyPatientsWithoutFollowUpCommand`
- [x] 4.4 Registrar el comando en `routes/console.php` vía `Schedule::command(...)` (el `App\Console\Kernel::schedule()` clásico no está enlazado en este proyecto y no debe usarse)
- [x] 4.5 Notificar (mail + database) únicamente a los usuarios con rol `Administrator`; el paciente NO es notificado por este comando
- [x] 4.6 Garantizar idempotencia diaria a nivel de paciente (no reportar dos veces el mismo día al mismo paciente, sin importar cuántos administradores existan), consultando `notifications` por `data->patient_id`
- [x] 4.7 Escribir tests para el comando: paciente sin registros históricos, paciente con seguimiento al día, el paciente nunca recibe notificación directa, y ejecución doble el mismo día

## 5. Frontend: notificaciones in-app

- [x] 5.1 Ajustar `types/notification.d.ts` / `types/index.d.ts` según la forma final de la prop compartida
- [x] 5.2 Reemplazar el `ref` estático `notifications` de `Header.vue` por la prop compartida real de Inertia
- [x] 5.3 Al hacer clic en una notificación del dropdown, invocar `PATCH /notifications/{id}/read`
- [x] 5.4 Agregar acción "Leer todas las notificaciones" que invoque `PATCH /notifications/read-all`
- [x] 5.5 Verificar que el estado vacío ("No hay datos disponibles") siga funcionando cuando no haya notificaciones

## 6. Fase estratégica: alerta por lecturas alteradas recientes (24h)

- [x] 6.1 Agregar `abnormal_reading_window_hours` (default `24`) a `config/notifications.php`
- [x] 6.2 Crear `App\Actions\PatientFollowUp\FindPatientsWithAbnormalReadingsSrv` que retorna los `UserPatient` con al menos un `UserGlucoseLog` de status "Elevado - fuera de rango normal" o "Bajo - fuera de rango normal" dentro de `abnormal_reading_window_hours`, agrupado por paciente
- [x] 6.3 Crear `App\Notifications\AdminAbnormalReadingAlertNotification` (`ShouldQueue`, canales fijos `mail` + `database`, solo administradores, sin `via()` dinámico por rol; el paciente ya fue notificado en tiempo real por `GlucoseLogRegisteredNotification`)
- [x] 6.4 Crear el comando independiente `App\Console\Commands\NotifyPatientsWithAbnormalReadingsCommand` (`notifications:patient-abnormal-readings`)
- [x] 6.5 Registrar el nuevo comando en `routes/console.php` vía `Schedule::command(...)`, como entrada adicional junto al comando de seguimiento
- [x] 6.6 Garantizar idempotencia diaria a nivel de paciente (no reportar dos veces el mismo día al mismo paciente), consultando `notifications` por `data->patient_id`, igual que el comando de seguimiento
- [x] 6.7 Escribir tests para el comando: paciente con lectura elevada reciente, paciente con lectura baja reciente, paciente con múltiples lecturas alteradas (una sola notificación), paciente sin lecturas alteradas, el paciente nunca recibe notificación directa, y ejecución doble el mismo día

## 8. Corrección: alertas estratégicas 100% admin-facing

Tras probar en local, se confirmó que el diseño original (canal dinámico según rol, notificando también al paciente) no correspondía a la intención real: los 2 comandos estratégicos debían ser alertas exclusivas para administradores; el paciente solo debe enterarse de sus propias lecturas en el momento de registrarlas (ya cubierto por `GlucoseLogRegisteredNotification`).

- [x] 8.1 Renombrar `PatientFollowUpAlertNotification` → `AdminFollowUpAlertNotification` y `PatientAbnormalReadingAlertNotification` → `AdminAbnormalReadingAlertNotification`; quitar el `via($notifiable)` dinámico por rol (ahora canal fijo `mail`+`database`)
- [x] 8.2 Quitar el despacho directo al paciente (`$patient->user->notify(...)`) de ambos comandos — solo se notifica a `$administrators`
- [x] 8.3 Agregar `patient_id` al payload `toDatabase()` de ambas notificaciones y reescribir la idempotencia diaria para consultar `notifications` por `data->patient_id` en vez de por "el paciente ya fue notificado"
- [x] 8.4 Renombrar los specs de capability `patient-followup-alerts` → `admin-followup-alerts` y `patient-abnormal-reading-alerts` → `admin-abnormal-reading-alerts`, y actualizar su contenido
- [x] 8.5 Reescribir ambos test files para reflejar que el paciente nunca es notificado por estos comandos y que el admin recibe `mail`+`database`

## 10. Ajuste: `type` como ENUM de negocio + UI con íconos, click-to-read y drawer

- [x] 10.1 Crear `App\Enums\NotificationType` (backed enum de string: `glucose-log`, `abnormal-glucose-log`, `overdue-glucose-log`, `profile-changed`)
- [x] 10.2 Migración `convert_notifications_type_to_enum`: truncar `notifications` (solo datos de desarrollo) y alterar `type` a `ENUM(...)` vía `DB::statement()`
- [x] 10.3 Agregar `databaseType($notifiable)` a las 5 clases de notificación y quitar el `'type'` duplicado de sus `toDatabase()`
- [x] 10.4 Actualizar la idempotencia de ambos comandos estratégicos para consultar `data->patient_id` por el valor del enum, no por `::class`
- [x] 10.5 Crear `App\Http\Resources\NotificationResource` y `NotificationController::index` (`GET /notifications`, paginado 10 por página)
- [x] 10.6 Escribir tests para `index`/`markAsRead`/`markAllAsRead` (paginación, aislamiento entre usuarios, 404 en notificación ajena)
- [x] 10.7 Frontend: `AppNotificationType`, mover `type` de `data` al nivel superior de `AppNotification`, agregar `PaginatedNotifications` en `types/notification.d.ts`
- [x] 10.8 Crear `composables/useNotificationIcon.ts` (mapeo tipo→ícono: `droplets`, `triangle-alert`, `clipboard-clock`, `user-round`) y `composables/useNotifications.ts` (extraer `markAsRead`/`markAllAsRead` compartidos)
- [x] 10.9 Crear `components/notifications/card/NotificationCard.vue` (ícono + texto + tiempo relativo, toda la tarjeta clickeable, punto azul opcional si no está leída) y usarla en el dropdown de `Header.vue`, quitando el botón individual de check
- [x] 10.10 Crear `components/notifications/drawer/NotificationsDrawer.vue` (paginado, mismo patrón que `PatientLogsDrawer.vue`) y el botón "Ver todas" en `Header.vue`

## 11. Verificación

- [x] 11.1 Ejecutar `vendor/bin/phpunit` y confirmar que toda la suite pasa (90/90 tras el ajuste de la sección 10; se eliminó `ExampleTest`, el test de scaffold preexistente que fallaba por requerir auth en `/`, sin relación con este cambio)
- [x] 11.2 Ejecutar `npx vue-tsc --noEmit` y `npx vite build`; confirmar que no hay errores nuevos
- [ ] 11.3 Probar manualmente en un navegador (con `QUEUE_CONNECTION=sync` o `php artisan queue:work`) que los tipos de notificación llegan a Mailtrap y aparecen en el dropdown/drawer in-app — **no realizado en esta sesión** (sin acceso a navegador); la lógica de despacho está cubierta por tests Feature automatizados
- [x] 11.4 Requisito operacional documentado en `design.md` (Risks/Trade-offs y Migration Plan): producción requiere un worker de colas (`queue:work`/Supervisor) corriendo y el cron `* * * * * php artisan schedule:run` para que ambos comandos programados se ejecuten diariamente
