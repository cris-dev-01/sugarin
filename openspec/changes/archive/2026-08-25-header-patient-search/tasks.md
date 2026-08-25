## 1. Backend: endpoint de búsqueda de pacientes

- [x] 1.1 Crear `SearchPatientsRequest` (`app/Http/Requests/Patients/`) validando `q` como string, mínimo 2 caracteres cuando está presente.
- [x] 1.2 Crear `SearchPatientsDto` (`app/DataTransferObjects/Patients/`) con el campo `query`.
- [x] 1.3 Crear `SearchPatientsSrv` (`app/Actions/Patients/`) con `handle(SearchPatientsDto $dto)`: normaliza el query a dígitos, decide búsqueda por documento (si es puramente numérico) o por nombre, limita a 8 resultados ordenados alfabéticamente por `users.name`. Retorna vacío si el query no cumple el mínimo.
- [x] 1.4 Crear `PatientSearchResultResource` (`app/Http/Resources/`) exponiendo `id`, `name`, `formatted_document`.
- [x] 1.5 Agregar ruta `GET /patients/search` (`patients.search`) dentro del grupo `patients.` en `routes/web.php`, ability `show-dashboard`, antes de la ruta con wildcard `{document}` para evitar colisión de rutas.
- [x] 1.6 Agregar método al `PatientController` que construye el Dto, invoca la action y retorna el resource colection.
- [x] 1.7 Tests Feature: búsqueda por nombre parcial, búsqueda por RUT con formato, query puramente numérico ignora nombre, query bajo el mínimo retorna vacío sin tocar BD, sin coincidencias retorna vacío, 403 sin ability `show-dashboard`.

## 2. Backend: preselección de paciente en el dashboard

- [x] 2.1 Revisar `GetTriageOverviewSrv`/`DashboardController@index` y extender el Dto de entrada (o el controller) para aceptar un `patient` id opcional desde `request()->integer('patient')`.
- [x] 2.2 Si el `patient` id corresponde a un `UserPatient` visible, reutilizar la action de resumen existente (la usada por `PatientSummaryController`/`GET /patients/{patient}/summary`) para precargar `summary` con período por defecto (30 días).
- [x] 2.3 Si el id no existe o no es válido, ignorar el parámetro sin lanzar error (mantener el comportamiento actual de `summary`).
- [x] 2.4 Tests Feature: `GET /?patient={id}` válido retorna `summary` precargado; `GET /?patient={id}` inválido no rompe la carga del dashboard; `GET /` sin el parámetro mantiene el comportamiento actual sin regresiones.

## 3. Frontend: buscador en el header

- [x] 3.1 Definir tipo `PatientSearchResult` en `resources/js/src/types/` (mirroring `PatientSearchResultResource`).
- [x] 3.2 Implementar estado de búsqueda en `Header.vue` (`<script setup>`): query, resultados, loading, debounce local (~300ms) sin agregar dependencias nuevas.
- [x] 3.3 Reemplazar el `<input>` estático del buscador (líneas ~36-47) por el campo controlado (`v-model`) que dispara la búsqueda con debounce a partir de 2 caracteres.
- [x] 3.4 Mostrar los resultados en un panel `Popper` (mismo patrón que los dropdowns de notificaciones/cuenta ya existentes en el header), listando nombre y RUT formateado; estado vacío cuando no hay coincidencias.
- [x] 3.5 Al seleccionar un resultado (click o Enter sobre el resultado activo), navegar con `router.visit(route('index', { patient: id }))` y cerrar el panel/limpiar el query.
- [x] 3.6 Manejo básico de teclado (flechas arriba/abajo + Enter) para navegar los resultados del panel.

## 4. Frontend: auto-scroll en el dashboard

- [x] 4.1 Agregar una `ref` al contenedor sticky de resumen en `Dashboard/Index.vue` (bloque de nombre + selector de período).
- [x] 4.2 En `onMounted`, si la URL trae `?patient={id}` y coincide con `summary.value?.patient.id`, hacer `scrollIntoView({ behavior: 'smooth', block: 'start' })` sobre esa `ref` tras `nextTick`.
- [x] 4.3 Verificar que `selectedPatientId` quede sincronizado con el paciente precargado por query param (para que `PatientRiskList` lo resalte igual que con una selección manual).

## 5. Verificación

- [x] 5.1 `vendor/bin/pint` y `vendor/bin/phpunit` (o `--filter` sobre los nuevos tests) en verde.
- [x] 5.2 `npm run build` sin errores de tipos.
- [x] 5.3 Prueba manual: buscar por nombre parcial y por RUT con puntos/guión, seleccionar un resultado, confirmar redirección a `/` con el paciente correcto y scroll automático al bloque de resumen. Verificado manualmente por el usuario en el navegador.
