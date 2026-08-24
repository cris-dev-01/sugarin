# Capability: Glucose Triage Overview

## Purpose

Provides the Administrator-facing dashboard route with aggregated portfolio-wide triage indicators (recent hypoglycemia events, inactive patients, percentage in good control) computed via aggregate queries, plus a risk-ordered patient list and a detail endpoint to drill into each triage criterion.

## Requirements

### Requirement: Indicadores de triage de cartera en la ruta del dashboard
El sistema SHALL calcular, como parte del render Inertia de la ruta `GET /` (`DashboardController@index`), los 3 indicadores agregados sobre todos los `UserPatient` existentes: cantidad de pacientes con evento "Bajo" reciente, cantidad de pacientes sin registro reciente, y porcentaje de pacientes en buen control. El cálculo SHALL hacerse mediante consultas agregadas (sin iterar registro por registro en PHP).

#### Scenario: Solicitud exitosa del dashboard
- **WHEN** un usuario autenticado con la ability `show-dashboard` solicita `GET /`
- **THEN** el sistema retorna los 3 indicadores agregados y el listado de pacientes ordenado por riesgo, como props de la página `Dashboard/Index`

#### Scenario: Cartera sin pacientes
- **WHEN** no existe ningún `UserPatient` registrado en el sistema
- **THEN** el sistema retorna los 3 indicadores en cero y un listado de pacientes vacío

### Requirement: Pacientes con evento "Bajo" reciente
El sistema SHALL contar como "con evento Bajo reciente" a todo `UserPatient` que tenga al menos un `UserGlucoseLog` con status "Bajo - fuera de rango normal" dentro de la ventana `recent_event_window_hours` (parámetro administrable, persistido en base de datos, default 48 horas) contadas desde el momento de la solicitud.

#### Scenario: Paciente con hipoglucemia dentro de la ventana
- **WHEN** un paciente tiene un log con status "Bajo - fuera de rango normal" registrado hace 10 horas
- **THEN** el paciente se cuenta dentro del indicador "pacientes con evento Bajo reciente"

#### Scenario: Paciente con hipoglucemia fuera de la ventana
- **WHEN** el único log "Bajo - fuera de rango normal" de un paciente fue registrado hace 72 horas y `recent_event_window_hours` es 48
- **THEN** el paciente NO se cuenta dentro del indicador "pacientes con evento Bajo reciente"

#### Scenario: Cambio del parámetro afecta el cálculo sin deploy
- **WHEN** un administrador cambia `recent_event_window_hours` de 48 a 24 horas desde la pantalla de administración, y un paciente tiene su único log "Bajo - fuera de rango normal" registrado hace 30 horas
- **THEN** dicho paciente deja de contarse dentro del indicador "pacientes con evento Bajo reciente" en la siguiente solicitud del dashboard, sin requerir un despliegue

### Requirement: Pacientes sin registro reciente
El sistema SHALL contar como "sin registro reciente" a todo `UserPatient` cuyo `UserGlucoseLog` más reciente tenga una antigüedad mayor a `recent_event_window_hours` (parámetro administrable, persistido en base de datos, default 48 horas), o que no tenga ningún registro.

#### Scenario: Paciente inactivo por falta de registros
- **WHEN** el último log de un paciente fue registrado hace 60 horas y `recent_event_window_hours` es 48
- **THEN** el paciente se cuenta dentro del indicador "pacientes sin registro reciente"

#### Scenario: Paciente sin ningún registro histórico
- **WHEN** un paciente no tiene ningún `UserGlucoseLog` asociado
- **THEN** el paciente se cuenta dentro del indicador "pacientes sin registro reciente"

### Requirement: Porcentaje de pacientes en buen control
El sistema SHALL calcular el porcentaje de pacientes en "buen control" como la proporción de `UserPatient` (sobre el total con al menos un registro en el período de referencia) cuyo porcentaje de lecturas en status "Rango normal" en el período de referencia (parámetro administrable `good_control_reference_period_days`, persistido en base de datos, default 30 días) es mayor o igual a `good_control_threshold` (parámetro administrable, persistido en base de datos, default 80%).

#### Scenario: Paciente clasificado en buen control
- **WHEN** un paciente tiene 90% de sus lecturas del período en "Rango normal" y `good_control_threshold` es 80%
- **THEN** el paciente se cuenta dentro del numerador de "pacientes en buen control"

#### Scenario: Paciente sin registros en el período de referencia
- **WHEN** un paciente no tiene ningún registro dentro del período de referencia
- **THEN** el paciente se excluye del cálculo del porcentaje (no cuenta ni en el numerador ni en el denominador)

### Requirement: Listado de pacientes ordenado por riesgo
El sistema SHALL retornar, junto a los 3 indicadores, un listado de todos los `UserPatient` visibles ordenado por la clave compuesta: (1) tiene evento Bajo reciente (descendente), (2) horas desde el último registro (descendente), (3) porcentaje de lecturas en rango normal del período de referencia (ascendente).

#### Scenario: Paciente con hipoglucemia reciente aparece primero
- **WHEN** el paciente A tiene un evento Bajo reciente y el paciente B no tiene eventos recientes pero tiene menor % en rango
- **THEN** el paciente A aparece antes que el paciente B en el listado

#### Scenario: Empate en evento Bajo, ordena por inactividad
- **WHEN** ningún paciente tiene evento Bajo reciente, y el paciente A tiene 40 horas sin registrar mientras el paciente B tiene 5 horas sin registrar
- **THEN** el paciente A aparece antes que el paciente B en el listado

### Requirement: Listado de pacientes por criterio de triage (para modal de detalle)
El sistema SHALL proveer un único endpoint (`GET /dashboard/triage-patients`) que recibe un parámetro `criteria` con uno de tres valores (`low_recent`, `inactive`, `good_control`) y retorna el listado de `UserPatient` que cumplen ese criterio, con el detalle relevante a cada uno, para alimentar un modal de detalle desde las tarjetas de triage.

- `criteria=low_recent`: pacientes con evento "Bajo" dentro de `recent_event_window_hours`, con el valor, `time_block` y fecha de su lectura "Bajo" más reciente, ordenados del más reciente al más antiguo.
- `criteria=inactive`: pacientes sin registro reciente (según la misma regla del indicador "sin registro reciente"), con las horas desde su último registro (o `null` si nunca registró) y la fecha de ese último registro, ordenados de más a menos inactivo.
- `criteria=good_control`: pacientes en buen control (según la misma regla del indicador "% en buen control"), con su porcentaje en rango normal del período de referencia, ordenados de mayor a menor porcentaje.

#### Scenario: Listado exitoso para evento Bajo reciente
- **WHEN** un usuario autorizado solicita `GET /dashboard/triage-patients?criteria=low_recent`
- **THEN** el sistema retorna únicamente los pacientes con evento Bajo dentro de la ventana, cada uno con su última lectura "Bajo" (valor, bloque horario, fecha)

#### Scenario: Listado exitoso para inactividad
- **WHEN** un usuario autorizado solicita `GET /dashboard/triage-patients?criteria=inactive`
- **THEN** el sistema retorna únicamente los pacientes sin registro reciente (incluyendo los que nunca registraron), ordenados del más inactivo al menos inactivo

#### Scenario: Listado exitoso para buen control
- **WHEN** un usuario autorizado solicita `GET /dashboard/triage-patients?criteria=good_control`
- **THEN** el sistema retorna únicamente los pacientes cuyo % en rango normal del período de referencia es mayor o igual al umbral configurado, ordenados de mayor a menor porcentaje

#### Scenario: Criterio inválido o ausente
- **WHEN** el parámetro `criteria` no está presente o no es uno de los 3 valores permitidos
- **THEN** el sistema retorna HTTP 422 con errores de validación

#### Scenario: Usuario sin permiso intenta acceder al listado
- **WHEN** un usuario autenticado sin la ability `show-dashboard` solicita `GET /dashboard/triage-patients`
- **THEN** el sistema retorna código HTTP 403

### Requirement: Permiso para ver el triage de cartera
El sistema SHALL exponer la ruta `GET /` únicamente a usuarios autorizados mediante la ability `show-dashboard` (ya existente, asignada al rol Administrator), validada con `->can('show-dashboard', User::class)`.

#### Scenario: Usuario sin permiso intenta acceder
- **WHEN** un usuario autenticado sin la ability `show-dashboard` solicita `GET /`
- **THEN** el sistema retorna código HTTP 403
