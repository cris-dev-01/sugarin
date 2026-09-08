## Context

El sistema ya captura `UserGlucoseLog` (value, `time_block`, `status_id`, `user_patient_id`) clasificado contra el `GlucoseRange` propio de cada `UserPatient`. No existe hoy ninguna capa de agregación: todo el análisis es registro por registro. Este cambio agrega dos endpoints de solo lectura (triage de cartera y resumen por paciente) más la página Inertia que los consume, sin tocar el flujo de captura/clasificación ya existente (`glucose-log-store`).

Volumen esperado: pocos pacientes por cuenta clínica (el caso principal es 1) y 1-2 registros por paciente por día, por lo que no se justifica una capa de cacheo o tablas materializadas en esta primera versión.

## Goals / Non-Goals

**Goals:**
- Calcular los 3 indicadores de triage y el ranking de pacientes por riesgo con consultas de agregación (sin N+1 por paciente).
- Calcular el resumen completo de un paciente (rango, variabilidad, racha, adherencia, última lectura, tendencia, distribución) para un período configurable (7/30/90 días).
- Definir una regla de "racha" (streak) que no incentive dejar de medir para no romperla.
- Integrar amCharts 5 (versión libre) como única librería de gráficos de esta página, aislada en componentes propios.

**Non-Goals:**
- Soporte de datos continuos (CGM) — se sigue trabajando sobre lecturas puntuales.
- Cacheo/precomputo de métricas o tablas de agregación materializadas.
- Umbrales de triage configurables por UI (quedan como config de backend en esta versión).
- Exportación/reportes (PDF, email).
- Actualización en tiempo real (websockets/polling) de los indicadores.

## Decisions

**1. Cálculo on-demand vía Eloquent, sin tablas nuevas.**
Dado el volumen esperado (pocos pacientes, pocos registros/día), se calculan las métricas en cada request con queries agregadas (`GROUP BY user_patient_id`, `COUNT`/`AVG`/`STDDEV` vía SQL) en lugar de traer todos los logs a PHP y agregarlos en memoria. Evita mantenimiento de una capa de cache/materialización que no se justifica todavía.
- Alternativa descartada: job programado que precalcula métricas en una tabla `patient_glucose_metrics`. Se descarta por complejidad innecesaria al volumen actual; queda como opción futura si el número de pacientes crece mucho.

**2. Regla de "racha" (streak) — un día cuenta como "en rango" solo si TODAS sus lecturas son "Rango normal" Y hubo al menos 1 lectura ese día.**
Un día sin registros **rompe** la racha (no la mantiene neutral). Se decide así para no premiar dejar de medirse como forma de "proteger" la racha — la gamificación debe reforzar la adherencia, no solo el buen control.
- Alternativa descartada: día sin registros se ignora (no rompe ni suma). Se descarta porque abriría un incentivo perverso a no medir tras una lectura mala.
- Precisión de implementación: el día de **hoy**, si todavía no tiene ninguna lectura, no rompe la racha retroactivamente (no se penaliza un día que aún está en curso) — el cálculo simplemente aún no lo cuenta como sumado. Si hoy ya tiene alguna lectura fuera de rango, sí rompe la racha de inmediato. Días pasados sin registro siempre rompen, sin excepción.

**3. Ranking de riesgo por clave compuesta, no por score ponderado.**
El listado de pacientes se ordena por tupla `(tiene evento Bajo reciente DESC, días de inactividad DESC, % en rango ASC)` en vez de un score numérico único. Es más trazable para el equipo clínico ("por qué este paciente está primero") que un score opaco que mezcla factores con pesos arbitrarios.
- Alternativa descartada: score ponderado único (ej. `riesgo = a*bajo + b*inactividad + c*(1-%rango)`). Se descarta por falta de justificación clínica para los pesos y por ser más difícil de auditar.

**4. Ventanas de tiempo como config, no hardcodeadas ni configurables por UI todavía.**
Se agregan constantes en `config/glucose_dashboard.php`: `recent_event_window_hours` (default 48h, usado para "evento Bajo reciente" e "inactividad"), `good_control_threshold` (default 0.8, usado para "% pacientes en buen control") y `expected_logs_per_day` (default 2, uno por `TimeBlock`, usado para calcular adherencia de registro). Quedan en config para poder ajustarlas sin tocar código, pero no se expone UI de configuración en esta versión (ver Non-Goals).

**5. Reutilizar las abilities `show-dashboard` y `show-summary` ya existentes, sin crear un permiso nuevo.**
Al revisar `routes/web.php` se encontró que ya existen `/` (ability `show-dashboard`, rol Administrator, hoy con contenido mockeado en `Dashboard/Index.vue`) y `/summary` (ability `show-summary`, rol Patient, hoy mockeado en `PatientDashboard/Index.vue`). El triage de cartera + selector + desglose se implementa reemplazando el contenido de `/` bajo `show-dashboard` (quien administra la cartera). El desglose de un paciente individual (capability `glucose-patient-summary`) se reutiliza también en `/summary` bajo `show-summary`, pero ahí SIN triage ni selector — el paciente autenticado solo ve su propio desglose, resuelto vía `auth()->user()->patient` sin aceptar un identificador de paciente por parámetro.
- Alternativa descartada: crear una ability nueva `show-glucose-dashboard` y rutas nuevas (`/dashboard`), como se planteó en la primera versión de este documento antes de revisar las rutas existentes. Se descarta porque duplicaría permisos y páginas ya presentes en el sistema.

**5.1 El endpoint de refresco por paciente exige explícitamente que el paciente solicitado le pertenezca al usuario cuando se accede vía `show-summary`.**
La ruta `GET /patients/{patient}/summary` (usada por el drill-down del Administrator) queda gateada por `show-dashboard`. La vista `/summary` del propio paciente NO usa esa ruta param-based — resuelve su `UserPatient` desde la sesión, evitando que un paciente pueda solicitar el resumen de otro cambiando un ID en la URL.

**6. amCharts aislado en `components/dashboard/charts/`.**
Se crean componentes Vue dedicados (ej. `TrendChart.vue`, `StatusDistributionChart.vue`) que encapsulan el ciclo de vida de amCharts (`root.dispose()` en `onUnmounted`, creación en `onMounted`), para que el resto de la página no dependa directamente de la API de amCharts y sea más fácil reemplazarla a futuro si se resuelve el tema de la licencia/watermark.

**7. Actions independientes por capability, consumidas desde los controllers existentes.**
`GetTriageOverviewSrv` y `GetPatientSummarySrv` son Actions independientes (cada una con su DTO). `DashboardController@index` (ruta `/`) las combina para el render inicial (Inertia): triage completo + resumen del paciente por defecto (primero del ranking de riesgo, o el único paciente si hay 1 solo). `DashboardController@patientSummary` (ruta `GET /patients/{patient}/summary`, ability `show-dashboard`) expone solo `GetPatientSummarySrv` en JSON para refrescar el desglose al cambiar de paciente o de período sin recargar la página. `PatientSummaryController@index` (ruta `/summary`, ability `show-summary`) usa únicamente `GetPatientSummarySrv` sobre `auth()->user()->patient`, sin triage ni selector.

**8. Un único endpoint parametrizado por `criteria` para el detalle de triage, en vez de 3 endpoints separados.**
`GET /dashboard/triage-patients?criteria=low_recent|inactive|good_control` centraliza en `GetTriagePatientsByCriteriaSrv` la consulta de detalle detrás de cada tarjeta de triage (para el modal que lista los pacientes que la componen). Cada criterio retorna campos distintos (última lectura Bajo, horas de inactividad, o % en rango) porque cada tarjeta necesita mostrar información distinta — no se fuerza una forma de respuesta común. El umbral de "buen control" (`good_control_threshold`) y la ventana de eventos recientes (`recent_event_window_hours`) se reutilizan desde el mismo config que usa `GetTriageOverviewSrv`, y el período de referencia para buen control se movió de una constante privada a `good_control_reference_period_days` en config para que ambas Actions no puedan desincronizarse.
- Alternativa descartada: 3 rutas/endpoints separados (uno por criterio). Se descarta porque el usuario pidió explícitamente un solo endpoint con 3 criterios de búsqueda.

**9. Historial completo de lecturas vía la ability `show-glucose-logs` existente, con chequeo de propiedad en el controller.**
El drawer de historial (`GET /patients/{patient}/glucose-logs`) necesita funcionar tanto para el drill-down del Administrator (cualquier paciente) como para la vista de autoservicio del Patient (solo su propio paciente) — a diferencia del resumen, aquí conviene una sola ruta/Action en lugar de duplicar controllers, porque `show-glucose-logs` ya la tienen ambos roles (se usa hoy para `POST /glucose-logs`). Se agrega una verificación explícita en `GlucoseLogController@forPatient`: si el usuario no es Administrator, `$patient->id` debe coincidir con `auth()->user()->patient->id`, o retorna 403.
- Alternativa descartada: reusar `ListGlucoseLogsSrv`/`ListGlucoseLogsDto` ya existentes en el código (pensados para listar logs con filtros vía `laravel-purity`). Se descarta porque ese Action no está enganchado a ninguna ruta hoy, `UserGlucoseLog` no usa el trait `Filterable` que su método `filter()` requiere, y no hay certeza de que soporte filtrar por fecha exacta sin antes configurar Purity para este modelo — se prefiere una Action nueva, acotada y testeada, en vez de depender de código no verificado en producción.

## Risks / Trade-offs

- **[Riesgo] Cálculo on-demand puede volverse lento si la cartera de pacientes crece mucho** → Mitigación: todo el cálculo agregado va en SQL (no loops en PHP); si se detecta degradación, la Decisión 1 ya deja la puerta abierta a precomputar sin cambiar el contrato de los endpoints.
- **[Riesgo] La definición de "racha" (día sin registro = rompe) puede no calzar con la expectativa clínica del usuario** → Mitigación: quedará documentada explícitamente como escenario en el spec antes de implementar, para validarla con el usuario antes de codear.
- **[Riesgo] Zona horaria / límite de "día" para la racha y para "inactividad"** → Mitigación: usar siempre `config('app.timezone')` y medianoche local como límite de día, de forma consistente en ambos cálculos.
- **[Riesgo] amCharts libre deja watermark visible a usuarios clínicos finales** → Aceptado explícitamente por el usuario; no requiere mitigación en esta versión.
- **[Trade-off] Reutilizar `show-dashboard`/`show-summary`** simplifica la implementación pero acopla "ver triage de cartera" con "ver detalle de cualquier paciente" en una sola ability (`show-dashboard`) → Aceptado como alcance de v1, ya que hoy solo el rol Administrator la tiene; se puede separar en el futuro si un rol necesita una sin la otra.
