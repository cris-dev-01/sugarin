## 1. Configuración

- [x] 1.1 Crear `config/glucose_dashboard.php` con `recent_event_window_hours` (48), `good_control_threshold` (0.8) y `expected_logs_per_day` (2).

## 2. Backend: triage de cartera (`glucose-triage-overview`)

- [x] 2.1 Crear Action `App\Actions\GlucoseDashboard\GetTriageOverviewSrv` con `handle()`: calcula los 3 indicadores agregados y el listado de pacientes ordenado por riesgo mediante queries agregadas (evitar N+1).
- [x] 2.2 ~~Crear `Http\Resources` para la respuesta del triage~~ — se pasa el array de la Action directamente como prop Inertia, siguiendo la convención existente (`GlucoseLogController@index` ya pasa colecciones sin envolver en `JsonResource` cuando el consumo es Inertia, no JSON API).
- [x] 2.3 Escribir tests Feature para `GET /` cubriendo los escenarios del spec: cartera vacía, evento Bajo dentro/fuera de ventana, inactividad, buen control, orden por riesgo, y 403 sin la ability `show-dashboard`.

## 3. Backend: resumen de paciente (`glucose-patient-summary`)

- [x] 3.1 Crear `App\Http\Requests\GlucoseDashboard\PatientSummaryRequest` validando `period` (`7`, `30`, `90`, default `30`).
- [x] 3.2 Crear DTO `App\DataTransferObjects\GlucoseDashboard\PatientSummaryDto` vía `Dto::from($request->validated())`.
- [x] 3.3 Crear Action `App\Actions\GlucoseDashboard\GetPatientSummarySrv` con `handle(PatientSummaryDto $dto)`: calcula % en rango por `TimeBlock`, conteo Bajo/Elevado, promedio/desviación estándar por `TimeBlock`, adherencia, última lectura, distribución por status.
- [x] 3.4 Implementar el cálculo de racha (streak) como método/objeto de valor propio (ej. `App\Actions\GlucoseDashboard\CalculatePatientStreak`), siguiendo la regla del design.md (día sin registro rompe la racha).
- [x] 3.5 Crear `Http\Resources` para la respuesta del resumen de paciente.
- [x] 3.6 Agregar ruta `GET /patients/{patient}/summary` en `routes/web.php`, protegida con `->can('show-dashboard', User::class)`, manejada por `DashboardController@patientSummary` (respuesta JSON, usada por el drill-down del Administrator).
- [x] 3.7 Escribir tests Feature cubriendo los escenarios del spec: período inválido, sin registros en el período, cálculo separado por `TimeBlock`, racha vigente/rota por status/rota por día sin registro, adherencia parcial, distribución con los 3 estados, y 403 sin la ability `show-dashboard`.
- [x] 3.8 Escribir tests Unit para el cálculo de racha en aislamiento (casos borde: día parcial, límite de zona horaria).

## 4. Backend: controllers existentes

- [x] 4.1 Implementar `DashboardController@index`: obtiene el triage vía `GetTriageOverviewSrv`, determina el paciente por defecto (primero del ranking de riesgo, o el único paciente si hay 1 solo) y su resumen vía `GetPatientSummarySrv`, y retorna `Inertia::render('Dashboard/Index', ...)` con ambos datasets. Reemplaza el contenido mockeado actual.
- [x] 4.2 Implementar `PatientSummaryController@index`: resuelve `auth()->user()->patient`, obtiene su resumen vía `GetPatientSummarySrv` (o retorna resumen nulo si el usuario no tiene paciente asociado) y retorna `Inertia::render('PatientDashboard/Index', ...)`. Reemplaza el contenido mockeado actual.
- [x] 4.3 Escribir tests Feature para `GET /summary`: resumen propio del paciente autenticado, usuario sin paciente asociado, y 403 sin la ability `show-summary`.

## 5. Frontend: tipos y base de gráficos

- [x] 5.1 Instalar `@amcharts/amcharts5` como dependencia del proyecto.
- [x] 5.2 Crear tipos en `resources/js/src/types/` para las respuestas de triage y de resumen de paciente, en sync con los `Http\Resources` del backend.
- [x] 5.3 Crear `components/dashboard/charts/TrendChart.vue` que encapsula el ciclo de vida de amCharts (crear en `onMounted`, `root.dispose()` en `onUnmounted`), reemplazando `vue3-apexcharts`. La distribución por status usa barras de progreso simples (sin librería de gráficos), consistente con el estilo "Visitors by Browser" acordado — ver `StatusDistributionBars.vue` en la sección 6.

## 6. Frontend: componentes del dashboard (reutilizables entre `/` y `/summary`)

- [x] 6.1 Crear `components/dashboard/TriageCards.vue` con las 3 tarjetas de triage, destacando visualmente la de "evento Bajo reciente" cuando su conteo sea mayor a cero. Solo se usa en `Dashboard/Index.vue`.
- [x] 6.2 Crear `components/dashboard/PatientRiskList.vue` (selector de pacientes ordenado por riesgo), que se oculta/colapsa cuando solo existe 1 paciente. Solo se usa en `Dashboard/Index.vue`.
- [x] 6.3 Crear `components/dashboard/PatientMetricsCards.vue` (% en rango por bloque horario, conteo Bajo/Elevado, promedio/variabilidad, adherencia, última lectura) como componente reutilizable entre `Dashboard/Index.vue` y `PatientDashboard/Index.vue`.
- [x] 6.4 Crear `components/dashboard/StreakCard.vue` para la racha de días en rango normal, con tratamiento visual de gamificación (ícono/badge, no sparkline). Reutilizable en ambas páginas.
- [x] 6.5 Crear `components/dashboard/RecentReadingsLog.vue` (lista de últimas lecturas con su status, estilo "activity log", usa `recent_logs` del resumen). Reutilizable en ambas páginas.
- [x] 6.6 Crear `components/dashboard/StatusDistributionBars.vue` (barras de progreso por status, sin librería de gráficos) e integrar `TrendChart.vue` con `recent_logs`. Reutilizables en ambas páginas.

## 7. Frontend: páginas e integración

- [x] 7.1 Reescribir `resources/js/src/Pages/Dashboard/Index.vue` componiendo `TriageCards`, `PatientRiskList` y el desglose del paciente seleccionado (componentes de la sección 6), según el layout de 3 cards superiores + selector + desglose acordado.
- [x] 7.2 Implementar el cambio de paciente seleccionado y de período (7/30/90 días) en `Dashboard/Index.vue`, consultando `GET /patients/{patient}/summary` vía `fetch()` (mismo patrón usado en `GlucoseLogs/Index.vue`) sin recargar toda la página.
- [x] 7.3 Reescribir `resources/js/src/Pages/PatientDashboard/Index.vue` usando los mismos componentes de desglose de la sección 6 (sin `TriageCards` ni `PatientRiskList`), con selector de período (7/30/90 días) sobre su propio resumen vía `router.reload({ only: ['summary'] })` (el paciente no tiene la ability `show-dashboard` para usar el endpoint JSON del drill-down, así que se recarga parcialmente su propia página Inertia en vez de introducir un segundo endpoint).

## 8. Verificación

- [x] 8.1 Ejecutar `vendor/bin/phpunit` y confirmar que todos los tests nuevos y existentes pasan. (49/49 propios pasan; la única falla es `ExampleTest` preexistente, no relacionada — `GET /` ya requería `show-dashboard` antes de este cambio.)
- [x] 8.2 Ejecutar `vendor/bin/pint` sobre los archivos nuevos/modificados.
- [~] 8.3 Probar manualmente en navegador ambas rutas. **Parcial**: se verificó `npm run build` (type-check de `vue-tsc` sin errores) y se ejecutaron ambas Actions vía tinker contra la base de datos real de desarrollo sin errores fatales. No se pudo verificar visualmente en un navegador real (no hay `chromium-cli`/Playwright disponible en este entorno Windows) — pendiente que el usuario lo revise en `http://localhost:8080/`.

## 9. Extensión: modal de detalle por tarjeta de triage

- [x] 9.1 Agregar `good_control_reference_period_days` (30) a `config/glucose_dashboard.php` y migrar `GetTriageOverviewSrv` para leerlo desde config en vez de una constante privada (evita desincronización con la nueva Action).
- [x] 9.2 Crear `App\Http\Requests\GlucoseDashboard\TriagePatientsRequest` validando `criteria` (`low_recent`, `inactive`, `good_control`, requerido) y DTO `TriagePatientsDto`.
- [x] 9.3 Crear Action `App\Actions\GlucoseDashboard\GetTriagePatientsByCriteriaSrv` con `handle()` delgado que despacha a un método privado por criterio (última lectura Bajo más reciente / horas de inactividad / % en rango).
- [x] 9.4 Crear `Http\Resources\TriagePatientsResource` y agregar ruta `GET /dashboard/triage-patients` en `routes/web.php` (ability `show-dashboard`) manejada por `DashboardController@triagePatients`.
- [x] 9.5 Escribir tests Feature: 403 sin permiso, 422 criterio inválido/ausente, y un caso positivo por cada uno de los 3 criterios (incluyendo orden y exclusión de pacientes que no califican).
- [x] 9.6 Crear tipos TS para las 3 formas de respuesta (`TriageCriteria`, y un item por criterio) en `resources/js/src/types/glucoseDashboard.d.ts`.
- [x] 9.7 Crear `components/dashboard/modal/TriagePatientsModal.vue`: recibe `criteria` + `show`, hace `fetch()` a `/dashboard/triage-patients?criteria=...` al abrirse, y renderiza el listado con las columnas correspondientes al criterio.
- [x] 9.8 Hacer clickeables las 3 tarjetas de `TriageCards.vue` (emiten el criterio seleccionado) e integrar el modal en `Dashboard/Index.vue`.
- [x] 9.9 Verificar `vendor/bin/phpunit`, `vendor/bin/pint` y `npm run build` tras la extensión. (57/57 propios pasan; única falla es `ExampleTest` preexistente.)

## 10. Extensión: historial completo de lecturas (drawer)

- [x] 10.1 Crear `App\Http\Requests\GlucoseLogs\PatientLogsRequest` (`page`, `date` formato `Y-m-d`) y DTO `PatientLogsDto`.
- [x] 10.2 Crear Action `App\Actions\GlucoseLogs\GetPatientGlucoseLogsSrv` que retorna un `LengthAwarePaginator` (15 por página) filtrable por fecha exacta.
- [x] 10.3 Crear `Http\Resources\GlucoseLogEntryResource` y agregar `GlucoseLogController@forPatient` + ruta `GET /patients/{patient}/glucose-logs` (ability `show-glucose-logs`, con chequeo de propiedad para el rol Patient).
- [x] 10.4 Escribir tests Feature: 401 guest, 403 sin permiso, 403 Patient viendo a otro paciente, 200 Patient viendo el suyo, 200 Administrator viendo cualquiera, paginación, filtro por fecha, 422 fecha inválida.
- [x] 10.5 Crear tipo TS `PaginatedPatientLogs` en `glucoseDashboard.d.ts`.
- [x] 10.6 Actualizar `components/dashboard/cards/RecentReadingsLog.vue`: limitar a 10 lecturas visibles, cambiar el layout de cada fila a "valor · bloque" (izquierda) y "status · fecha" siempre visible (derecha, sin hover), y agregar botón "Ver más".
- [x] 10.7 Crear `components/dashboard/drawer/PatientLogsDrawer.vue`: lista (no tabla) con paginación (anterior/siguiente) y un campo de búsqueda por fecha, consumiendo `GET /patients/{patient}/glucose-logs`.
- [x] 10.8 Pasar `patient-id` a `RecentReadingsLog` desde `Dashboard/Index.vue` y `PatientDashboard/Index.vue`.
- [x] 10.9 Verificar `vendor/bin/phpunit`, `vendor/bin/pint` y `npm run build`.
