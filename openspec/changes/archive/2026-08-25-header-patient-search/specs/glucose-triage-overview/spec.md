## MODIFIED Requirements

### Requirement: Indicadores de triage de cartera en la ruta del dashboard
El sistema SHALL calcular, como parte del render Inertia de la ruta `GET /` (`DashboardController@index`), los 3 indicadores agregados sobre todos los `UserPatient` existentes: cantidad de pacientes con evento "Bajo" reciente, cantidad de pacientes sin registro reciente, y porcentaje de pacientes en buen control. El cálculo SHALL hacerse mediante consultas agregadas (sin iterar registro por registro en PHP). Adicionalmente, si la request incluye un parámetro de consulta `patient` con el id de un `UserPatient` visible, el sistema SHALL precargar el resumen (mismo cálculo que `GET /patients/{patient}/summary`, período por defecto) de ese paciente como prop `summary` de la página.

#### Scenario: Solicitud exitosa del dashboard
- **WHEN** un usuario autenticado con la ability `show-dashboard` solicita `GET /`
- **THEN** el sistema retorna los 3 indicadores agregados y el listado de pacientes ordenado por riesgo, como props de la página `Dashboard/Index`

#### Scenario: Cartera sin pacientes
- **WHEN** no existe ningún `UserPatient` registrado en el sistema
- **THEN** el sistema retorna los 3 indicadores en cero y un listado de pacientes vacío

#### Scenario: Preselección de paciente vía parámetro de consulta
- **WHEN** un usuario autorizado solicita `GET /?patient={id}` donde `{id}` corresponde a un `UserPatient` visible
- **THEN** el sistema retorna la página del dashboard con el prop `summary` precargado con el resumen de ese paciente (período 30 días por defecto)

#### Scenario: Parámetro de paciente inválido o inexistente
- **WHEN** un usuario solicita `GET /?patient={id}` donde `{id}` no corresponde a ningún `UserPatient` visible
- **THEN** el sistema ignora el parámetro y retorna la página del dashboard con el comportamiento por defecto (sin error HTTP)
