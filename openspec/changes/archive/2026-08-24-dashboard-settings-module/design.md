## Context

Los 4 parámetros de `config/glucose_dashboard.php` (`recent_event_window_hours`, `good_control_threshold`, `good_control_reference_period_days`, `expected_logs_per_day`) se leen hoy vía `config()` en tres Actions de `app/Actions/GlucoseDashboard/`. El proyecto ya sigue un patrón de capas estricto (Route → FormRequest → DTO → Action `AsAction` → Controller delgado) y permisos `spatie/laravel-permission` con nomenclatura `verbo-recurso`, resueltos directo contra permissions (sin `Gate::define`). El rol `Administrator` recibe automáticamente **todos** los permissions existentes (`RolesPermissionsSeeder`), por lo que basta con declarar los nuevos permissions en `PermissionsSeeder` — no se requiere asignación manual.

Decisiones ya tomadas con el usuario antes de este documento:
- Tabla genérica `settings` (clave/valor tipado), no una tabla de columnas fijas.
- Lectura vía capa de cache (evita golpear la BD en cada carga del dashboard/triage, que se consulta frecuentemente).
- Validación de rangos explícitos por parámetro al guardar desde la UI.

## Goals / Non-Goals

**Goals:**
- Persistir los 4 parámetros del dashboard en base de datos, editables sólo por `Administrator`.
- Mantener el mismo comportamiento/defaults actuales de triage y resumen de paciente; sólo cambia la fuente de verdad.
- Evitar overhead de BD en el camino caliente (triage overview, patient summary) mediante cache invalidada al guardar.
- Dejar una tabla `settings` genérica reutilizable para futuros parámetros administrables (no exclusiva del dashboard de glucosa).

**Non-Goals:**
- Historial/auditoría de cambios de configuración (quién cambió qué y cuándo) — no se agrega tabla de auditoría en este cambio.
- UI genérica de administración de settings arbitrarios (key/value libre desde la UI) — la pantalla es específica para los 4 parámetros del dashboard, aunque la tabla subyacente sea genérica.
- Settings por paciente/usuario o multi-tenant — son globales para toda la aplicación.
- Versionado o rollback de valores desde la UI.

## Decisions

### 1. Esquema de la tabla `settings`

```php
Schema::create('settings', function (Blueprint $table) {
    $table->id();
    $table->string('key')->unique();
    $table->text('value');
    $table->string('type'); // 'integer' | 'float'
    $table->timestamps();
});
```

- `key` usa los mismos nombres que hoy tiene `config/glucose_dashboard.php` (`recent_event_window_hours`, `good_control_threshold`, `good_control_reference_period_days`, `expected_logs_per_day`), sin prefijo de feature: son los únicos settings del sistema por ahora y el nombre ya es descriptivo. Si en el futuro se agregan settings de otro dominio con riesgo de colisión de nombres, se evaluará agregar un prefijo en ese momento (YAGNI).
- `value` se almacena como texto y se castea según `type` al leer (sólo `integer` y `float` son necesarios para estos 4 parámetros).
- Alternativa descartada: fila única con columnas tipadas — más simple para 4 valores fijos, pero el usuario prefirió explícitamente el modelo key-value genérico para no requerir una migración cada vez que se agregue un parámetro nuevo (a costa de tipado más débil, mitigado con el campo `type` + casteo centralizado).

### 2. Modelo y casteo

`App\Models\Setting` expone `casted_value` (accessor) que castea `value` según `type`. No se usan Eloquent casts nativos por fila porque el tipo varía por registro (no por columna).

### 3. Capa de lectura cacheada

Nueva Action de sólo lectura `App\Actions\Settings\GetDashboardSettingsSrv` (sigue el patrón `AsAction`/`handle()` del proyecto) que:
- Consulta las 4 filas de `settings` por `key`.
- Arma y retorna un `DashboardSettingsData` (spatie/laravel-data) tipado: `recent_event_window_hours: int`, `good_control_threshold: float`, `good_control_reference_period_days: int`, `expected_logs_per_day: int`.
- Envuelve el resultado en `Cache::rememberForever('settings.dashboard', ...)`.

Las 3 Actions existentes (`GetTriagePatientsByCriteriaSrv`, `GetTriageOverviewSrv`, `GetPatientSummarySrv`) reemplazan sus llamadas a `config('glucose_dashboard.*')` por `GetDashboardSettingsSrv::run()->{campo}`.

Alternativa descartada: consulta directa sin cache en cada Action — el usuario prefirió explícitamente cache dado que estas Actions se ejecutan en cada carga del dashboard/triage.

### 4. Escritura e invalidación de cache

Flujo estándar del proyecto para el módulo de administración:
- `UpdateDashboardSettingsRequest` (FormRequest) valida cada campo con rangos explícitos:
  - `recent_event_window_hours`: `integer|min:1|max:168` (hasta 1 semana).
  - `good_control_threshold`: `numeric|min:0|max:1`.
  - `good_control_reference_period_days`: `integer|min:1|max:365`.
  - `expected_logs_per_day`: `integer|min:1|max:24`.
- `DashboardSettingsData` (DTO, spatie/laravel-data) construido vía `::from($request->validated())`, reutilizado tanto para el payload de escritura como para el shape que consume la UI (mismo tipado que el de lectura).
- `App\Actions\Settings\UpdateDashboardSettingsSrv::handle(DashboardSettingsData $dto)`: upsert de las 4 filas por `key` y `Cache::forget('settings.dashboard')` al final (la próxima lectura repuebla la cache con los valores nuevos).
- `DashboardSettingsController`: `index()` → `Inertia::render('DashboardSettings/Index', ...)` usando `GetDashboardSettingsSrv::run()`; `update()` → construye el DTO y llama a `UpdateDashboardSettingsSrv::run($dto)`.

### 5. Permisos y rutas

Nuevos permissions en `PermissionsSeeder`: `show-dashboard-settings`, `update-dashboard-settings` (guard `web`). No requieren asignación manual a `Administrator` (recibe todos los permissions automáticamente vía `RolesPermissionsSeeder`); `Patient` no los recibe.

Rutas (bajo `Route::middleware('auth')`):
```php
Route::prefix('dashboard-settings')->name('dashboard-settings.')
    ->controller(DashboardSettingsController::class)
    ->group(function () {
        Route::get('/', 'index')->name('index')->can('show-dashboard-settings', Setting::class);
        Route::put('/', 'update')->name('update')->can('update-dashboard-settings', Setting::class);
    });
```

### 6. Seed de datos iniciales

`SettingsSeeder` (nuevo, agregado a `DatabaseSeeder::call()`) inserta las 4 filas con los valores que hoy son default en `config/glucose_dashboard.php` (48, 0.8, 30, 2), para que el comportamiento no cambie el día del deploy.

### 7. Frontend

- `Pages/DashboardSettings/Index.vue`: formulario con los 4 campos (inputs numéricos con min/max reflejando la validación del backend), visible sólo si `usePermissions` confirma `show-dashboard-settings`; botón guardar deshabilitado sin `update-dashboard-settings`.
- Entrada de navegación (sidebar) condicionada por permiso, siguiendo el mismo patrón que otras secciones admin-only.
- `types/dashboardSettings.d.ts` con la forma `{ recent_event_window_hours, good_control_threshold, good_control_reference_period_days, expected_logs_per_day }`.

## Risks / Trade-offs

- [Cache desincronizada si se edita `settings` directo en BD sin pasar por `UpdateDashboardSettingsSrv`] → Mitigación: es un riesgo operacional aceptado (igual que editar cualquier tabla a mano); no se agrega invalidación por evento de BD ya que toda escritura legítima pasa por la Action.
- [Tabla `settings` vacía o con `key` faltante en un entorno no seedeado] → Mitigación: `SettingsSeeder` se agrega a `DatabaseSeeder`; se documenta en `tasks.md` como paso obligatorio de deploy (`php artisan db:seed --class=SettingsSeeder` en entornos existentes).
- [Cache eterna (`rememberForever`) más `type` mal migrado] → Mitigación: el cast ocurre en cada `handle()` de `GetDashboardSettingsSrv` antes de cachear, no se cachea el valor crudo sin castear.
- [**BREAKING**: eliminar `config/glucose_dashboard.php` rompe cualquier referencia externa no detectada] → Mitigación: se hizo `grep` exhaustivo previo (sólo 3 Actions lo referencian, ya cubiertas en tasks.md).

## Migration Plan

1. Migración `create_settings_table`.
2. `SettingsSeeder` con los 4 valores default (idempotente: `updateOrCreate` por `key`).
3. Agregar permissions a `PermissionsSeeder` y `SettingsSeeder` a `DatabaseSeeder::call()`.
4. Implementar modelo, DTO, FormRequest, Actions (`GetDashboardSettingsSrv`, `UpdateDashboardSettingsSrv`), Controller, rutas.
5. Migrar las 3 Actions consumidoras de `config()` a `GetDashboardSettingsSrv`.
6. Implementar UI de administración + entrada de navegación.
7. Eliminar `config/glucose_dashboard.php` una vez verificado que no queda ninguna referencia (`grep -r "glucose_dashboard" app resources`).
8. En despliegue a entornos existentes: correr la migración y `php artisan db:seed --class=SettingsSeeder` antes de desplegar el código que remueve el config file, para que no haya ventana sin datos.

**Rollback**: si se revierte el cambio completo, restaurar `config/glucose_dashboard.php` desde el historial de git y revertir las Actions consumidoras a `config()`; la migración `down()` elimina la tabla `settings` (sin impacto en otras tablas).

## Open Questions

Ninguna pendiente — decisiones de estructura de tabla, cache y validación ya confirmadas con el usuario.
