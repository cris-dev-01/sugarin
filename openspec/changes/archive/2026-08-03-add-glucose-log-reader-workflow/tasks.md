## 1. Base de Datos

- [x] 1.1 Crear migración para modificar `user_glucose_logs.time_block` de `tinyInteger` a `ENUM('mañana', 'anochecer')` (manejar datos existentes antes del ALTER)
- [x] 1.2 Crear `database/seeders/StatusesSeeder.php` con `firstOrCreate` para los 3 estados: "Rango normal", "Elevado - fuera de rango normal", "Bajo - fuera de rango normal"
- [x] 1.3 Crear `database/sql/seed_statuses.sql` con los INSERTs equivalentes para los 3 estados como alternativa directa
- [x] 1.4 Registrar `StatusesSeeder` en `DatabaseSeeder.php`

## 2. Backend — Modelos y Enum

- [x] 2.1 Crear `App\Models\UserGlucoseLog` con `$fillable` (value, time_block, user_patient_id, status_id), `SoftDeletes`, relaciones `userPatient()` (BelongsTo UserPatient) y `status()` (BelongsTo Status)
- [x] 2.2 Agregar relación `glucoseRange()` (BelongsTo GlucoseRange) al modelo `UserPatient`
- [x] 2.3 Crear `App\Enums\TimeBlock` como BackedEnum string con cases `Manana = 'mañana'` y `Anochecer = 'anochecer'`, con método estático `fromHour(int $hour): self` (04–18 → Manana, 19–03 → Anochecer)

## 3. Backend — Listado

- [x] 3.1 Crear `App\DataTransferObjects\GlucoseLogs\ListGlucoseLogsDto` con Spatie LaravelData (fields, filters, relations, paginated, per_page, page, includeTrashed)
- [x] 3.2 Crear `App\Actions\GlucoseLogs\ListGlucoseLogsSrv` con `use AsAction`, aplica filtros, carga relaciones `userPatient` y `status` por defecto, retorna paginación o colección

## 4. Backend — Creación con determinación de status

- [x] 4.1 Crear `App\DataTransferObjects\GlucoseLogs\StoreGlucoseLogDto` con Spatie LaravelData (user_patient_id, value)
- [x] 4.2 Crear `App\Actions\GlucoseLogs\StoreGlucoseLogSrv` con `use AsAction` y lógica de:
  - Determinar `time_block` con `TimeBlock::fromHour(now()->hour)`
  - Cargar `GlucoseRange` del paciente (lanzar excepción si no tiene rango asignado)
  - Seleccionar umbrales fasting o non_fasting según `time_block`
  - Comparar `value` contra umbrales y buscar el status correspondiente por nombre en `Status`
  - Persistir el `UserGlucoseLog` en transacción DB
- [x] 4.3 Crear `App\Http\Requests\GlucoseLogs\StoreGlucoseLogRequest` con validación: `user_patient_id` (required, integer, exists:user_patients,id), `value` (required, integer, min:1)
- [x] 4.4 Crear `App\Http\Requests\GlucoseLogs\ListGlucoseLogsRequest` con validación: `page`, `per_page`, `filters.user_patient_id` (integer, exists:user_patients,id)
- [x] 4.5 Crear `App\Http\Resources\GlucoseLogResource` que exponga: id, value, time_block, user_patient (nombre), status (nombre), created_at (formateado)

## 5. Backend — Controlador y Rutas

- [x] 5.1 Crear `App\Http\Controllers\GlucoseLogController` con métodos `index(Request $request)` (retorna `Inertia::render('GlucoseLogs/Index')` con los pacientes disponibles) y `store(StoreGlucoseLogRequest $request)` (llama a `StoreGlucoseLogSrv` y retorna el log creado)
- [x] 5.2 Agregar `use App\Http\Controllers\GlucoseLogController;` en `routes/web.php`
- [x] 5.3 Corregir el permiso de la ruta `GET /glucose-logs` a `can('show-glucose-logs', UserGlucoseLog::class)`
- [x] 5.4 Agregar ruta `POST /glucose-logs` → `store` con permiso `can('create-glucose-logs', UserGlucoseLog::class)` dentro del grupo `glucose-logs`
- [x] 5.5 Agregar el permiso `show-glucose-logs` y `create-glucose-logs` al seeder de permisos de la aplicación (ya existían)

## 6. Frontend — Tipos y Dependencias

- [x] 6.1 Instalar `tesseract.js` vía npm: `npm install tesseract.js`
- [x] 6.2 Crear `resources/js/src/types/glucoseLog.d.ts` con interfaces: `GlucoseLog` (id, value, time_block, user_patient, status, created_at) y `GlucoseLogPagination`

## 7. Frontend — Componentes del Flujo

- [x] 7.1 Crear componente `resources/js/src/components/glucose-logs/steps/PatientSelectStep.vue` que recibe la lista de pacientes y emite el paciente seleccionado; si la lista tiene un único elemento, emite auto-selección al montar
- [x] 7.2 Crear componente `resources/js/src/components/glucose-logs/steps/OcrCaptureStep.vue` con:
  - Input de archivo `accept="image/*"` (compatible desktop y mobile vía `capture` attribute)
  - Previsualización de la imagen seleccionada
  - Inicialización de Tesseract.js con spinner de progreso
  - Extracción del texto numérico y emisión del valor al padre
- [x] 7.3 Crear componente `resources/js/src/components/glucose-logs/steps/ConfirmStep.vue` con campo editable del valor, validación de entero positivo, botón de confirmar y emisión del valor final al padre
- [x] 7.4 Crear componente `resources/js/src/components/glucose-logs/steps/SuccessStep.vue` que recibe el log creado y muestra: nombre del paciente, valor (mg/dL), bloque de tiempo, status y fecha/hora del registro

## 8. Frontend — Página Principal

- [x] 8.1 Crear página `resources/js/src/Pages/GlucoseLogs/Index.vue` con:
  - Props Inertia: lista de pacientes del administrador
  - Estado local del stepper: paso actual (`patient-select` | `ocr-capture` | `confirm` | `success`) y datos acumulados entre pasos
  - Renderizado condicional de cada componente Step según el paso activo
  - Llamada fetch a `POST /glucose-logs` en el paso de confirmación para crear el log
  - Avance a `SuccessStep` con los datos retornados al confirmar éxito
- [x] 8.2 Agregar entrada "Registros de Glucosa" al Sidebar (ya existía con enlace `/glucose-logs` y etiqueta "Tomar Muestra")
- [x] 8.3 Crear componente `resources/js/src/components/glucose-logs/StepsIndicator.vue` con indicador visual horizontal (círculos numerados + línea conectora, estados: pending/active/completed) e integrarlo en `Index.vue`

## 9. Indicador de anormalidad en la pantalla de éxito

- [x] 9.1 Agregar método `GlucoseRange::thresholdsFor(TimeBlock $timeBlock): array` (umbrales min/max según bloque de tiempo) y reutilizarlo en `StoreGlucoseLogSrv`
- [x] 9.2 Agregar `is_abnormal` y `range` (min, max) a `GlucoseLogResource`, cargando `userPatient.glucoseRange` en `GlucoseLogController::store`
- [x] 9.3 Agregar `is_abnormal` y `range` a la interfaz `GlucoseLog` en `resources/js/src/types/glucoseLog.d.ts`
- [x] 9.4 Actualizar `SuccessStep.vue` con un banner destacado (ícono + color según severidad) que indique si la lectura está dentro o fuera del rango, mostrando el rango esperado cuando esté fuera de rango

## 10. Pruebas

- [x] 10.1 Crear factories `GlucoseRangeFactory` y `UserPatientFactory` para soportar pruebas de backend
- [x] 10.2 Pruebas unitarias: `TimeBlock::fromHour()` (límites de bloque mañana/anochecer) y `GlucoseRange::thresholdsFor()` (selección de umbrales fasting/non-fasting)
- [x] 10.3 Pruebas de feature para `POST /glucose-logs`: creación exitosa (normal/elevado/bajo), umbrales según bloque de tiempo, validaciones (valor inválido, paciente inexistente, paciente sin rango), permisos (invitado/usuario sin permiso) y forma de la respuesta (`is_abnormal`, `range`)
- [x] 10.4 Pruebas de feature para `GET /glucose-logs`: acceso denegado (invitado/sin permiso) y renderizado de la página Inertia con los pacientes disponibles
- [x] 10.5 Corregir `config/inertia.php` (page_paths apuntaban a `resources/js/Pages` en vez de `resources/js/src/Pages`) para que `assertInertia` pueda ubicar los componentes de página
- [x] 10.6 Configurar Cypress (`cypress.config.js`, soporte, comando `cy.login`) y crear `database/seeders/GlucoseLogE2ESeeder` con datos fijos para pruebas E2E (aislado de la base de datos de desarrollo)
- [x] 10.7 Spec E2E (`cypress/e2e/glucose-logs/capture-flow.cy.js`) que recorre el flujo completo (login → selección automática de paciente único → captura OCR → confirmación manual del valor → pantalla de éxito) y verifica los 3 casos de status (normal/elevado/bajo) y sus colores/indicadores
