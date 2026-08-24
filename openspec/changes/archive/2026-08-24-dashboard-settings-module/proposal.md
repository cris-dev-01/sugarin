## Why

Los parámetros que gobiernan el triage y los cálculos del dashboard (`recent_event_window_hours`, `good_control_threshold`, `good_control_reference_period_days`, `expected_logs_per_day`) hoy están hardcodeados en `config/glucose_dashboard.php`. Cualquier ajuste clínico (ej. cambiar la ventana de "evento reciente" de 48 a 72 horas) requiere un deploy. El diseño original del dashboard (`2026-08-11-glucose-dashboard`) dejó esto explícitamente fuera de alcance ("no se expone UI de configuración en esta versión"); este cambio implementa ese follow-up, dando a los administradores control sobre estos parámetros sin intervención técnica.

## What Changes

- Nueva tabla `settings` (clave/valor genérico, tipada) para almacenar parámetros configurables, comenzando con los 4 del dashboard de glucosa.
- Nuevo módulo de administración (sólo rol `Administrator`) para ver y editar estos parámetros desde la UI, con validación de rangos razonables por parámetro.
- Nuevos permisos `show-dashboard-settings` y `update-dashboard-settings`, seedeados y asignados sólo a `Administrator`.
- Capa de acceso a settings con cache (invalidada al guardar) que reemplaza las llamadas a `config('glucose_dashboard.*')` en `GetTriagePatientsByCriteriaSrv`, `GetTriageOverviewSrv` y `GetPatientSummarySrv`.
- Seeder que carga los valores actuales de `config/glucose_dashboard.php` como defaults iniciales en la tabla `settings`.
- **BREAKING**: se elimina `config/glucose_dashboard.php` como fuente de verdad; estos parámetros pasan a vivir exclusivamente en base de datos.

## Capabilities

### New Capabilities
- `dashboard-settings-administration`: permite a un administrador ver y actualizar los parámetros configurables del dashboard de glucosa (ventana de evento reciente, umbral de buen control, período de referencia, lecturas esperadas por día) desde una pantalla de administración, con validación de rangos y persistencia en base de datos.

### Modified Capabilities
- `glucose-triage-overview`: los requisitos que referencian `recent_event_window_hours` y `good_control_threshold` como "(config, default X)" pasan a describirlos como parámetros administrables persistidos en base de datos (mismo comportamiento y defaults, distinta fuente de verdad y posibilidad de cambio en caliente).
- `glucose-patient-summary`: el requisito de adherencia de registro que referencia `expected_logs_per_day` como "(config, default 2)" pasa a describirlo como parámetro administrable persistido en base de datos.

## Impact

- **Backend**: nueva migración `settings`, nuevo modelo `Setting`, nuevo `SettingsSeeder`, nuevo servicio de acceso/cache `DashboardSettings`, nuevas rutas/FormRequest/DTO/Action/Controller para el módulo de administración, actualización de `PermissionsSeeder`, actualización de las 3 Actions de `GlucoseDashboard` que hoy usan `config()`.
- **Frontend**: nueva página `Pages/DashboardSettings/Index.vue`, componente(s) de formulario, entrada de menú visible sólo para `Administrator` (via `usePermissions`), nuevo type `dashboardSettings.d.ts`.
- **Config**: eliminación de `config/glucose_dashboard.php`.
- **Specs**: nueva capability `dashboard-settings-administration`; delta specs sobre `glucose-triage-overview` y `glucose-patient-summary`.
