## Context

El header (`Header.vue`) trae un input de búsqueda decorativo heredado de la plantilla base, sin lógica. El dashboard (`Dashboard/Index.vue`) ya resuelve el "ver datos de un paciente" mediante `selectedPatientId` + `loadSummary()`, que hace `fetch('/patients/{id}/summary?period=...')` y renderiza un bloque sticky (nombre + selector de período) seguido de métricas. Todos los `UserPatient` existentes ya aparecen en `props.triage.patients` (spec `glucose-triage-overview`, requirement "Listado de pacientes ordenado por riesgo"), por lo que cualquier paciente encontrado por búsqueda ya tiene una fila correspondiente en `PatientRiskList`.

No existe hoy ningún endpoint de búsqueda combinada nombre+RUT; `PatientController@checkPatient` sólo valida existencia de un documento exacto para formularios de alta.

## Goals / Non-Goals

**Goals:**
- Búsqueda por nombre (parcial, insensible a mayúsculas) o RUT (parcial, ignorando puntos/guión) desde el header, disponible en todas las páginas autenticadas.
- Al elegir un resultado, aterrizar en el dashboard con ese paciente ya seleccionado y con scroll automático al bloque de su resumen, sin pasos manuales adicionales.
- Reutilizar el mecanismo de resumen ya existente (`loadSummary`/`GET /patients/{id}/summary`) en vez de crear una vista de detalle nueva.

**Non-Goals:**
- No se crea una página de "detalle de paciente" separada del dashboard.
- No se busca por otros campos (email, teléfono, etc.) en esta iteración.
- No se pagina el dropdown de resultados; se limita a un top-N.
- No se toca la lógica de cálculo de triage/métricas existente.

## Decisions

### 1. Endpoint dedicado `GET /patients/search`, no reutilizar `ListPatientsSrv`
`ListPatientsSrv` está pensado para listados administrables genéricos vía `Filterable`/`Purity` (filtros por query string arbitrarios). La búsqueda combinada (nombre OR documento normalizado, con límite y orden por relevancia) es un caso de uso distinto y más acotado. Se crea `SearchPatientsSrv` (`app/Actions/Patients/`) con su propio `SearchPatientsRequest` y `SearchPatientsDto` (`query: string`), siguiendo el patrón de capas estándar del proyecto.

- Ruta: `GET /patients/search`, dentro del grupo `patients.` existente, `->name('search')`.
- Ability: `show-dashboard` (no `show-patients`) — el resultado de esta búsqueda sólo sirve para navegar al dashboard, así que se gatea con la misma ability que la ruta de destino (`GET /`). Alternativa descartada: `show-patients`, que permitiría buscar a alguien sin poder ver el dashboard donde aterrizaría el resultado.
- Normalización del RUT: se elimina todo carácter no numérico del `query` antes de hacer `LIKE` contra la columna `user_patients.document` (que guarda solo dígitos, sin dígito verificador). El nombre se busca con `LIKE %query% ` sobre `users.name` vía join/whereHas. Si el query normalizado a dígitos es igual al query original (o sea, es puramente numérico), se busca sólo por documento; si no, sólo por nombre — evita falsos positivos de un nombre que contenga números.
- Resultado: máx. 8 coincidencias, con `id`, `name`, `formatted_document`, ordenadas alfabéticamente. Nuevo `PatientSearchResultResource`.
- Longitud mínima de búsqueda: 2 caracteres (validado en `SearchPatientsRequest`), devuelve `[]` si no se cumple en vez de error.

### 2. Query param `patient` en `GET /` para preselección
`DashboardController@index` ya construye `triage` vía `GetTriageOverviewSrv`. Se agrega: si la request trae `?patient={id}` y ese id corresponde a un `UserPatient` visible, `GetTriageOverviewSrv` (o el controller) calcula también el `summary` inicial para ese paciente (reutilizando el mismo cálculo que `PatientSummaryController`/`GET /patients/{patient}/summary`, vía el action de resumen ya existente) y lo pasa como prop `summary`, igual que hoy hace `props.summary` en `Index.vue`. Si el id no existe o no pertenece a un paciente visible, se ignora el parámetro (no error, cae al comportamiento actual: `summary: null` o el primer paciente, según la lógica ya implementada — no se modifica ese fallback).
- Alternativa descartada: resolver el summary sólo en el cliente (fetch tras montar) — se prefiere precargarlo server-side porque es el mismo patrón que ya usa la carga inicial del dashboard (evita un parpadeo "sin paciente seleccionado" al entrar desde la búsqueda).

### 3. Navegación desde el header: recarga completa vía Inertia `router.visit`
Al hacer click en un resultado, el header navega con `router.visit(route('index', { patient: id }))` (full Inertia visit, no `preserveState`) porque el destino es una ruta distinta (`/`) a la actual en el caso general (el usuario puede estar en cualquier página). Si el usuario ya está en `/`, Inertia igual revisita y refresca `triage`/`summary` con el paciente nuevo.

### 4. Auto-scroll en el cliente
`Dashboard/Index.vue` agrega una `ref` al contenedor sticky del resumen (línea ~83) y, en `onMounted`, si `route().params` (o `usePage().props.selectedPatientId`/query string) trae un `patient` y coincide con `summary.value?.patient.id`, hace `el.scrollIntoView({ behavior: 'smooth', block: 'start' })` tras `nextTick`. No se usa el hash de la URL (`#patient-...`) para no interferir con el manejo de query params existente.

### 5. UI de búsqueda en el header
Se reemplaza el `<input>` estático por un componente con `v-model` + `watchDebounced` (o `setTimeout` manual, ya que no hay librería de debounce instalada — se evalúa agregar `@vueuse/core` vs. implementar un debounce mínimo local; se opta por un debounce local de ~300ms para no sumar una dependencia nueva sólo para esto) que dispara `fetch('/patients/search?q=...')` y muestra los resultados en un panel `Popper` (mismo patrón que los dropdowns de notificaciones/cuenta ya existentes en `Header.vue`), con manejo de teclado básico (flechas + enter) opcional según esfuerzo disponible.

## Risks / Trade-offs

- [Ambigüedad ability `show-dashboard` para búsqueda] → Documentado explícitamente arriba; si en la práctica algún rol necesita buscar pacientes sin acceso al dashboard, se debe revisar antes de implementar (ver Open Questions).
- [Falsos negativos en búsqueda por RUT con formato inesperado] → Normalización simple (strip no-dígitos) cubre los formatos usuales (`12.345.678-9`, `123456789`); RUTs con letra "K" como dígito verificador no afectan porque se busca sobre `document` (sin DV).
- [Dropdown sin paginación] → limitar a 8 resultados es una limitación conocida; si el volumen de pacientes crece, se puede revisar sin cambiar el contrato del endpoint (agregar `limit`/paginación después).
- [Doble cómputo de resumen] → si el usuario navega a `/` con `?patient=X` y luego cambia de período, se recalcula igual que hoy; no hay caching adicional en este cambio.

## Migration Plan

Sin migración de base de datos. Despliegue estándar (nueva ruta, nueva action, cambios de frontend); no requiere pasos de rollback especiales más allá de revertir el commit/PR.

## Open Questions

- ¿La ability correcta para `GET /patients/search` es `show-dashboard` o debería existir una ability propia (p. ej. `search-patients`) si a futuro se reutiliza este buscador fuera del dashboard? Se opta por `show-dashboard` por ahora dado el único caso de uso de este cambio.
