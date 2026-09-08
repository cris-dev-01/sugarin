## ADDED Requirements

### Requirement: Endpoint de búsqueda de pacientes por nombre o RUT
El sistema SHALL proveer un endpoint (`GET /patients/search`, ability `show-dashboard`) que recibe un parámetro `q` de al menos 2 caracteres y retorna hasta 8 `UserPatient` cuyo nombre de usuario coincida parcialmente (insensible a mayúsculas) o cuyo documento (RUT, ignorando puntos y guión) coincida parcialmente, ordenados alfabéticamente por nombre.

#### Scenario: Búsqueda por coincidencia de nombre
- **WHEN** un usuario autorizado solicita `GET /patients/search?q=mar` y existen pacientes "María Soto" y "Marcos Peña"
- **THEN** el sistema retorna ambos pacientes con su `id`, `name` y `formatted_document`, ordenados alfabéticamente

#### Scenario: Búsqueda por coincidencia de RUT con formato
- **WHEN** un usuario autorizado solicita `GET /patients/search?q=12.345.678` y existe un paciente con documento `123456789`
- **THEN** el sistema normaliza el parámetro a dígitos (`12345678`) y retorna al paciente cuyo documento contiene esos dígitos

#### Scenario: Query numérico busca solo por documento, no por nombre
- **WHEN** el parámetro `q` es puramente numérico (p. ej. `123`)
- **THEN** el sistema busca únicamente contra la columna de documento, sin comparar contra el nombre

#### Scenario: Query bajo el mínimo de caracteres
- **WHEN** el parámetro `q` tiene menos de 2 caracteres (incluyendo vacío)
- **THEN** el sistema retorna una lista vacía con código HTTP 200, sin ejecutar una búsqueda en base de datos

#### Scenario: Sin coincidencias
- **WHEN** ningún paciente coincide con el criterio de búsqueda
- **THEN** el sistema retorna una lista vacía con código HTTP 200

#### Scenario: Usuario sin la ability requerida
- **WHEN** un usuario autenticado sin la ability `show-dashboard` solicita `GET /patients/search?q=ana`
- **THEN** el sistema retorna HTTP 403 y no ejecuta la búsqueda

### Requirement: Buscador en el header con navegación al dashboard
El sistema SHALL mostrar, en el header de la aplicación, un campo de búsqueda que consulta el endpoint de búsqueda de pacientes con debounce mientras el usuario escribe, y SHALL mostrar los resultados en un panel desplegable. Al seleccionar un resultado (click o tecla Enter sobre la coincidencia activa), el sistema SHALL navegar a la ruta del dashboard (`GET /`) incluyendo el id del paciente seleccionado como parámetro de consulta.

#### Scenario: Resultados se muestran mientras se escribe
- **WHEN** el usuario escribe 2 o más caracteres en el buscador del header
- **THEN** tras el debounce, el sistema consulta el endpoint de búsqueda y muestra las coincidencias en un panel desplegable bajo el campo

#### Scenario: Selección de un resultado navega al dashboard
- **WHEN** el usuario hace click (o presiona Enter con un resultado activo) sobre una coincidencia de la lista
- **THEN** el sistema navega a `GET /?patient={id}` del paciente elegido

#### Scenario: Sin resultados no muestra panel de selección
- **WHEN** la búsqueda no retorna coincidencias
- **THEN** el panel desplegable indica que no hay resultados, sin opciones seleccionables
