## Why

La barra de búsqueda del header es actualmente un input decorativo sin funcionalidad (`Header.vue`), y no existe ninguna forma rápida de ubicar a un paciente específico desde el header. El personal clínico necesita encontrar a un paciente por nombre o RUT y llegar directamente a sus datos en el dashboard, en vez de navegar manualmente por el listado de riesgo o abrir el modal de triage.

## What Changes

- Se agrega un endpoint de búsqueda de pacientes por nombre o RUT (coincidencia parcial), reutilizando el patrón de capas existente (Route → FormRequest → Dto → Action → Controller).
- El input del header se convierte en un buscador funcional: al escribir (con debounce), muestra un dropdown con las coincidencias (nombre, RUT formateado); al hacer click/enter sobre una coincidencia, redirige al dashboard.
- El dashboard (`GET /`) acepta un parámetro de consulta con el id del paciente a preseleccionar: si viene informado, el sistema precarga el resumen de ese paciente (igual que `onSelectPatient`) y el frontend hace scroll automático hasta la sección donde comienza la data del paciente (el header sticky con nombre + selector de período), en vez de exigir que el usuario lo seleccione manualmente desde `PatientRiskList`.
- Sin cambios de navegación a una vista de "detalle de paciente" separada: la búsqueda reutiliza el mismo mecanismo de resumen que ya existe en el dashboard.

## Capabilities

### New Capabilities
- `patient-search`: búsqueda de pacientes por nombre o RUT desde el header (endpoint backend + UI de autocompletado), con navegación al dashboard con el paciente elegido.

### Modified Capabilities
- `glucose-triage-overview`: la ruta `GET /` acepta un parámetro de consulta opcional con el id de paciente a preseleccionar, precargando su resumen (mismo mecanismo que el endpoint de summary) para que el frontend pueda hacer scroll automático a su sección sin requerir una segunda petición manual del usuario.

## Impact

- **Backend**: nuevo `SearchPatientsSrv` (o extensión de `ListPatientsSrv`) en `app/Actions/Patients/`, nuevo `FormRequest`/`Dto` de búsqueda, nueva ruta `GET /patients/search` (ability `show-patients`), nuevo `Http\Resources` para resultados de búsqueda (id, nombre, documento formateado). `DashboardController@index` y `GetTriageOverviewSrv` ganan soporte para un parámetro de paciente preseleccionado.
- **Frontend**: `Header.vue` gana estado de búsqueda (query, resultados, debounce, dropdown con `Popper`), navegación vía Inertia/router hacia `/` con el id del paciente. `Dashboard/Index.vue` gana lógica de auto-scroll hacia el bloque sticky de resumen cuando el paciente llega preseleccionado por query param.
- **Sin cambios de base de datos**: se reutilizan `users.name` y `user_patients.document`.
