## Why

Hoy no existe una vista que resuma el estado clínico de los registros de glucosa ya capturados (`UserGlucoseLog`). Quien hace seguimiento debe revisar registros individuales para saber si un paciente está en riesgo o si dejó de medirse. El caso de uso principal es 1 paciente, pero el sistema ya soporta múltiples pacientes por lo que la vista debe funcionar igual de bien con una cartera de N pacientes, priorizando quién necesita atención primero.

- Nueva página de dashboard (Inertia/Vue) con dos zonas:
  - Fila superior de triage: 3 indicadores agregados sobre toda la cartera de pacientes — pacientes con evento "Bajo" reciente, pacientes sin registro reciente, y % de pacientes en buen control. Basados en `Status`/actividad, no en promedios de `value` crudo (los rangos son personalizados por paciente y no son comparables entre sí).
  - Selector de pacientes ordenado por riesgo (eventos "Bajo" recientes primero, luego inactividad, luego % en rango), que colapsa automáticamente al resumen directo cuando solo existe 1 paciente.
  - Desglose por paciente seleccionado: % de lecturas en rango normal por período y `TimeBlock`, conteo de eventos Bajo/Elevado, promedio y variabilidad del valor por `TimeBlock`, racha de días consecutivos en rango normal (gamificación), adherencia de registro (mediciones esperadas vs. registradas), última lectura con su status, tendencia reciente y distribución por status del período.
- Esta página **reemplaza el contenido mockeado existente** de `Dashboard/Index.vue` (ruta `/`, ability `show-dashboard`, rol Administrator) — no se crean rutas ni permisos nuevos para esta zona.
- El desglose por paciente (capability `glucose-patient-summary`) se reutiliza también en la vista existente de autoservicio del paciente (`PatientDashboard/Index.vue`, ruta `/summary`, ability `show-summary`, rol Patient), mostrando ahí únicamente el desglose de su propio registro, sin triage de cartera ni selector.
- Gráficos de tendencia y distribución implementados con amCharts (versión libre, incluye watermark) en lugar de la librería de charts (ApexCharts) del template UI actual.
- Nuevo endpoint JSON de solo lectura para refrescar el desglose de un paciente (cambio de paciente seleccionado o de período) sin recargar la página completa; sin modificar el flujo de captura de logs existente.
- Tarjetas de triage clickeables que abren un modal con el listado de pacientes detrás de cada indicador (evento Bajo reciente, sin registro reciente, buen control), vía un único endpoint parametrizado por criterio.
- El componente "Lecturas recientes" limita la vista en línea a las últimas 10 lecturas y agrega una opción "Ver más" que abre un drawer con el historial completo del paciente, paginado y con búsqueda por fecha.

## Capabilities

### New Capabilities
- `glucose-triage-overview`: cálculo y exposición de los 3 indicadores agregados de toda la cartera de pacientes, el listado de pacientes ordenado por riesgo, y el detalle de pacientes por cada criterio de triage (para el modal).
- `glucose-patient-summary`: cálculo y exposición de las métricas de un paciente individual (rango, variabilidad, racha, adherencia, última lectura, tendencia, distribución por status) para un período dado.
- `glucose-log-history`: listado paginado y filtrable por fecha del historial completo de `UserGlucoseLog` de un paciente, para el drawer de detalle.

### Modified Capabilities
(ninguna — no se modifican reglas de negocio de captura/clasificación de logs existentes)

## Impact

- Backend: se implementa lógica real en el `DashboardController` existente (ruta `/`) y se agrega una ruta JSON (`GET /patients/{patient}/summary`) para refrescar el desglose por paciente, ambas bajo la ability `show-dashboard` ya existente. El `PatientSummaryController` existente (ruta `/summary`, ability `show-summary`) se implementa para resolver y mostrar el desglose del propio paciente autenticado. Se agregan sus `FormRequest`, DTOs (`spatie/laravel-data`) y Actions (`AsAction`) siguiendo el patrón de capas ya usado por `GlucoseLogs`/`GlucoseRanges`/`Patients`. No se crean permisos nuevos — se reutilizan `show-dashboard` y `show-summary` ya seedeados.
- Consultas de agregación sobre `user_glucose_logs`, `user_patients`, `glucose_ranges` y `statuses` (sin cambios de esquema esperados).
- Frontend: se reemplaza el contenido de `Pages/Dashboard/Index.vue` y `Pages/PatientDashboard/Index.vue`, se agregan componentes en `components/dashboard/` (reutilizados por ambas páginas), se incorpora amCharts como dependencia de gráficos (reemplazando ApexCharts en estas vistas) y se agregan tipos en `resources/js/src/types/` para las nuevas respuestas.
- Nueva ruta `GET /patients/{patient}/glucose-logs` en `GlucoseLogController` (reutiliza la ability `show-glucose-logs` ya existente, con verificación adicional en el controller de que un Patient solo pueda ver su propio historial) para el drawer de historial completo.
