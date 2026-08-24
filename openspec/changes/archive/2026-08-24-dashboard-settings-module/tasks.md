## 1. Base de datos

- [x] 1.1 Crear migración `create_settings_table` (`id`, `key` string unique, `value` text, `type` string, timestamps).
- [x] 1.2 Crear modelo `App\Models\Setting` con accessor `casted_value` que castea `value` según `type` (`integer` | `float`).
- [x] 1.3 Crear `database/seeders/SettingsSeeder.php` que siembra (`updateOrCreate` por `key`) los 4 parámetros con los defaults actuales: `recent_event_window_hours=48` (integer), `good_control_threshold=0.8` (float), `good_control_reference_period_days=30` (integer), `expected_logs_per_day=2` (integer).
- [x] 1.4 Registrar `SettingsSeeder::class` en `database/seeders/DatabaseSeeder.php`.

## 2. Permisos

- [x] 2.1 Agregar permissions `show-dashboard-settings` y `update-dashboard-settings` (guard `web`) en `database/seeders/PermissionsSeeder.php`.
- [x] 2.2 Ejecutar/verificar que `RolesPermissionsSeeder` asigna automáticamente los nuevos permissions a `Administrator` (no requiere cambios de código, sólo re-seed).

## 3. Backend — lectura cacheada

- [x] 3.1 Crear DTO `App\DataTransferObjects\Settings\DashboardSettingsData` (spatie/laravel-data) con `recent_event_window_hours: int`, `good_control_threshold: float`, `good_control_reference_period_days: int`, `expected_logs_per_day: int`.
- [x] 3.2 Crear `App\Actions\Settings\GetDashboardSettingsSrv` (`AsAction`): lee las 4 filas de `settings` por `key`, arma `DashboardSettingsData`, y envuelve el resultado en `Cache::rememberForever('settings.dashboard', ...)`.
- [x] 3.3 Reemplazar en `app/Actions/GlucoseDashboard/GetTriagePatientsByCriteriaSrv.php` las 3 llamadas a `config('glucose_dashboard.*')` por `GetDashboardSettingsSrv::run()->{campo}`.
- [x] 3.4 Reemplazar en `app/Actions/GlucoseDashboard/GetTriageOverviewSrv.php` las 2 llamadas a `config('glucose_dashboard.*')` por `GetDashboardSettingsSrv::run()->{campo}`.
- [x] 3.5 Reemplazar en `app/Actions/GlucoseDashboard/GetPatientSummarySrv.php` la llamada a `config('glucose_dashboard.expected_logs_per_day')` por `GetDashboardSettingsSrv::run()->expected_logs_per_day`.

## 4. Backend — administración (escritura)

- [x] 4.1 Crear `App\Http\Requests\Settings\UpdateDashboardSettingsRequest` con reglas: `recent_event_window_hours` (`integer|min:1|max:168`), `good_control_threshold` (`numeric|min:0|max:1`), `good_control_reference_period_days` (`integer|min:1|max:365`), `expected_logs_per_day` (`integer|min:1|max:24`).
- [x] 4.2 Crear `App\Actions\Settings\UpdateDashboardSettingsSrv` (`AsAction`, `handle(DashboardSettingsData $dto)`): upsert de las 4 filas de `settings` por `key` dentro de una transacción, y `Cache::forget('settings.dashboard')` al finalizar.
- [x] 4.3 Crear `App\Http\Controllers\DashboardSettingsController` con `index()` (Inertia render usando `GetDashboardSettingsSrv::run()`) y `update()` (construye `DashboardSettingsData::from($request->validated())` y llama a `UpdateDashboardSettingsSrv::run($dto)`).
- [x] 4.4 Agregar rutas en `routes/web.php`: `GET /dashboard-settings` (`show-dashboard-settings`) y `PUT /dashboard-settings` (`update-dashboard-settings`), ambas sobre `Setting::class`.

## 5. Frontend

- [x] 5.1 Crear `resources/js/src/types/dashboardSettings.d.ts` con la forma `{ recent_event_window_hours, good_control_threshold, good_control_reference_period_days, expected_logs_per_day }`.
- [x] 5.2 Crear página `resources/js/src/Pages/DashboardSettings/Index.vue` con formulario para los 4 campos (inputs numéricos con `min`/`max` reflejando la validación del backend) y manejo de errores de validación.
- [x] 5.3 Deshabilitar/ocultar el botón de guardar si `usePermissions` no confirma `update-dashboard-settings`; la página completa sólo debe ser alcanzable con `show-dashboard-settings`.
- [x] 5.4 Agregar entrada de navegación al módulo de administración de settings, visible sólo para usuarios con `show-dashboard-settings` (mismo patrón que otras secciones admin-only del sidebar).

## 6. Limpieza y verificación

- [x] 6.1 Eliminar `config/glucose_dashboard.php`.
- [x] 6.2 Verificar con `grep -r "glucose_dashboard" app resources config` que no queda ninguna referencia residual.
- [x] 6.3 Correr `vendor/bin/pint` sobre los archivos backend nuevos/modificados.

## 7. Tests

- [x] 7.1 Test de feature para `GetDashboardSettingsSrv`: retorna los valores seedeados y cachea (segunda llamada no golpea la BD — verificar con `DB::enableQueryLog()` o mock de `Cache`).
- [x] 7.2 Test de feature para `PUT /dashboard-settings`: actualización exitosa con valores válidos, 422 con valores fuera de rango (verificando que no persiste ningún cambio), 403 sin `update-dashboard-settings`.
- [x] 7.3 Test de feature para `GET /dashboard-settings`: 200 con `show-dashboard-settings`, 403 sin el permiso.
- [x] 7.4 Test que verifica que tras actualizar un parámetro vía `UpdateDashboardSettingsSrv`, la siguiente lectura de `GetTriageOverviewSrv`/`GetPatientSummarySrv` refleja el nuevo valor (invalidación de cache correcta).
- [x] 7.5 Actualizar los tests existentes de `GetTriagePatientsByCriteriaSrv`, `GetTriageOverviewSrv` y `GetPatientSummarySrv` que dependían de `config/glucose_dashboard.php` para que sembren/usen la tabla `settings` en su lugar.
