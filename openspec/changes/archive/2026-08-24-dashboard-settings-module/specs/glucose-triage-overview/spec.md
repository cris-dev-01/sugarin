## MODIFIED Requirements

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
