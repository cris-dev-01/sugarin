## ADDED Requirements

### Requirement: Historial paginado de lecturas de un paciente
El sistema SHALL proveer un endpoint (`GET /patients/{patient}/glucose-logs`) que retorna, paginado (15 por página), el historial completo de `UserGlucoseLog` del paciente indicado, ordenado del más reciente al más antiguo, con su valor, `time_block`, status y fecha.

#### Scenario: Solicitud exitosa
- **WHEN** un usuario autorizado solicita `GET /patients/{patient}/glucose-logs`
- **THEN** el sistema retorna hasta 15 lecturas de esa página junto con metadatos de paginación (`current_page`, `last_page`, `per_page`, `total`)

#### Scenario: Paciente con más de una página de historial
- **WHEN** el paciente tiene 20 lecturas y se solicita `page=2`
- **THEN** el sistema retorna las 5 lecturas restantes

### Requirement: Filtro por fecha del historial
El sistema SHALL permitir filtrar el historial por una fecha exacta (`date`, formato `Y-m-d`), retornando únicamente las lecturas registradas ese día.

#### Scenario: Filtro por fecha con resultados
- **WHEN** se solicita `GET /patients/{patient}/glucose-logs?date=2026-04-20` y el paciente tiene lecturas ese día
- **THEN** el sistema retorna únicamente las lecturas de esa fecha

#### Scenario: Formato de fecha inválido
- **WHEN** el parámetro `date` no tiene el formato `Y-m-d`
- **THEN** el sistema retorna HTTP 422 con errores de validación

### Requirement: Permisos y propiedad del historial
El sistema SHALL exponer la ruta únicamente a usuarios con la ability `show-glucose-logs` (ya existente, asignada a los roles Administrator y Patient). Adicionalmente, SHALL verificar que un usuario con rol distinto de Administrator solo pueda solicitar el historial de su propio `UserPatient`.

#### Scenario: Usuario sin permiso intenta acceder
- **WHEN** un usuario autenticado sin la ability `show-glucose-logs` solicita el endpoint
- **THEN** el sistema retorna código HTTP 403

#### Scenario: Paciente intenta ver el historial de otro paciente
- **WHEN** un usuario con rol Patient solicita el historial de un `UserPatient` que no es el suyo
- **THEN** el sistema retorna código HTTP 403

#### Scenario: Administrator puede ver el historial de cualquier paciente
- **WHEN** un usuario con rol Administrator solicita el historial de cualquier `UserPatient`
- **THEN** el sistema retorna el historial solicitado, sin restricción de propiedad
