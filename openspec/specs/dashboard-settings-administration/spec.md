# Capability: Dashboard Settings Administration

## Purpose

Allows an Administrator to view and update, from a UI screen, the parameters that govern the glucose dashboard's triage and adherence calculations (recent event window, good control threshold, good control reference period, expected logs per day). These parameters are persisted in the database instead of a static config file, are cached for read performance, and take effect immediately without a deploy.

## Requirements

### Requirement: Persistencia de parámetros del dashboard en base de datos
El sistema SHALL almacenar los parámetros `recent_event_window_hours`, `good_control_threshold`, `good_control_reference_period_days` y `expected_logs_per_day` en una tabla `settings` (clave/valor tipado), en lugar de un archivo de configuración estático. Cada parámetro SHALL tener siempre un valor persistido (poblado por seeder en el despliegue inicial).

#### Scenario: Lectura de un parámetro existente
- **WHEN** cualquier parte del sistema necesita el valor de `recent_event_window_hours`
- **THEN** el sistema lo obtiene desde la tabla `settings` (directamente o vía cache), sin leer `config()`

### Requirement: Cache de los parámetros del dashboard
El sistema SHALL cachear el conjunto de parámetros del dashboard tras la primera lectura, evitando consultas repetidas a la tabla `settings` en cada carga del dashboard/triage. El sistema SHALL invalidar dicha cache inmediatamente después de que un administrador actualice cualquiera de los parámetros.

#### Scenario: Lecturas sucesivas usan la cache
- **WHEN** se solicitan los parámetros del dashboard dos veces sin que haya una actualización entre medio
- **THEN** la segunda solicitud no genera una nueva consulta a la tabla `settings`

#### Scenario: Actualización invalida la cache
- **WHEN** un administrador actualiza `good_control_threshold` desde la pantalla de administración
- **THEN** la siguiente lectura de los parámetros del dashboard refleja el nuevo valor, no el valor cacheado previamente

### Requirement: Pantalla de administración de parámetros del dashboard
El sistema SHALL proveer una pantalla de administración donde un usuario con la ability `show-dashboard-settings` puede ver los valores actuales de los 4 parámetros del dashboard, y un usuario con la ability `update-dashboard-settings` puede modificarlos.

#### Scenario: Administrador visualiza los parámetros actuales
- **WHEN** un usuario con la ability `show-dashboard-settings` accede a la pantalla de administración de settings
- **THEN** el sistema muestra los valores actualmente persistidos de los 4 parámetros

#### Scenario: Usuario sin permiso intenta ver la pantalla
- **WHEN** un usuario autenticado sin la ability `show-dashboard-settings` solicita la ruta de administración de settings
- **THEN** el sistema retorna código HTTP 403

#### Scenario: Usuario sin permiso intenta actualizar
- **WHEN** un usuario autenticado sin la ability `update-dashboard-settings` intenta enviar una actualización de los parámetros
- **THEN** el sistema retorna código HTTP 403 y no modifica ningún valor

### Requirement: Validación de rangos al actualizar parámetros
El sistema SHALL validar, al actualizar los parámetros del dashboard, que cada valor esté dentro de un rango razonable, rechazando la actualización completa si algún campo es inválido:
- `recent_event_window_hours`: entero entre 1 y 168.
- `good_control_threshold`: numérico entre 0 y 1.
- `good_control_reference_period_days`: entero entre 1 y 365.
- `expected_logs_per_day`: entero entre 1 y 24.

#### Scenario: Actualización exitosa con valores válidos
- **WHEN** un administrador envía `recent_event_window_hours=72`, `good_control_threshold=0.85`, `good_control_reference_period_days=30`, `expected_logs_per_day=3`
- **THEN** el sistema persiste los 4 valores y retorna éxito

#### Scenario: Valor fuera de rango rechaza toda la actualización
- **WHEN** un administrador envía `good_control_threshold=1.5` (fuera del rango 0-1) junto al resto de valores válidos
- **THEN** el sistema retorna HTTP 422 con el error de validación correspondiente y no persiste ningún cambio, incluyendo los campos que sí eran válidos

#### Scenario: Valor no numérico es rechazado
- **WHEN** un administrador envía un valor no numérico para cualquiera de los 4 campos
- **THEN** el sistema retorna HTTP 422 con errores de validación

### Requirement: Valores por defecto iniciales
El sistema SHALL sembrar, en el despliegue inicial de este cambio, los valores actuales de referencia como default: `recent_event_window_hours=48`, `good_control_threshold=0.8`, `good_control_reference_period_days=30`, `expected_logs_per_day=2`, de modo que el comportamiento del dashboard no cambie hasta que un administrador los edite explícitamente.

#### Scenario: Comportamiento sin cambios tras el despliegue
- **WHEN** se despliega este cambio y aún no se ha editado ningún parámetro desde la pantalla de administración
- **THEN** los cálculos de triage y resumen de paciente producen los mismos resultados que con los valores previos de `config/glucose_dashboard.php`
