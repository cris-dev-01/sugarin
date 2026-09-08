## Context

Sugarin es un sistema de monitoreo de glucemia. La tabla `user_glucose_logs` ya existe con los campos `value` (smallInteger), `time_block` (tinyInteger — a migrar), `user_patient_id` y `status_id`. La ruta `GET /glucose-logs` está declarada pero sin controlador implementado. Los estados de glucosa aún no están sembrados en la tabla `statuses`.

`UserPatient` tiene FK `glucose_range_id` hacia `GlucoseRange`, que define cuatro umbrales: `min_fasting_value`, `max_fasting_value`, `min_non_fasting_value`, `max_non_fasting_value`. El bloque 'mañana' usa los valores fasting; 'anochecer' usa non_fasting.

## Goals / Non-Goals

**Goals:**
- Implementar el flujo completo de captura de un log de glucosa: selección de paciente → OCR → confirmación → éxito.
- Determinar el status del log automáticamente en el backend según el rango asignado al paciente.
- Migrar `time_block` a ENUM y sembrarlo automáticamente según la hora del servidor.
- Proveer seeder + SQL para los 3 estados necesarios.

**Non-Goals:**
- Historial/listado de logs anteriores (fuera de esta iteración).
- Integración con dispositivos externos (solo OCR sobre foto cargada manualmente).
- Edición o eliminación de logs ya creados.
- Autenticación / gestión de usuarios.

## Decisions

### 1. OCR en el navegador con Tesseract.js
**Decisión:** Correr Tesseract.js completamente en el cliente (sin servidor). Se muestra la previsualización de la imagen y luego se extrae el texto. El resultado se carga en un campo editable que el usuario puede corregir antes de confirmar.

**Alternativas consideradas:** OCR en el servidor (Laravel). Descartado: requiere extensiones PHP adicionales (Tesseract CLI) y mayor complejidad de deploy. El dispositivo del usuario típicamente tiene suficiente CPU para Tesseract.js en modo WASM.

### 2. Flujo multi-paso como páginas Inertia independientes vs. componente con estado local
**Decisión:** Componente Vue con estado local multi-paso (stepper) dentro de una sola página Inertia (`GlucoseLogs/Index.vue`). Los pasos son: `patient-select`, `ocr-capture`, `confirm`, `success`.

**Alternativas consideradas:** Páginas Inertia separadas por paso. Descartado: el estado OCR (imagen, valor extraído) es efímero y no debe persistir entre navegaciones. Un stepper local es más liviano y no requiere flash de datos entre páginas.

### 3. Determinación del status en el backend
**Decisión:** El `StoreGlucoseLogSrv` carga el `GlucoseRange` del paciente y aplica la lógica:
- Si `time_block = 'mañana'` → comparar con `min_fasting_value` / `max_fasting_value`.
- Si `time_block = 'anochecer'` → comparar con `min_non_fasting_value` / `max_non_fasting_value`.
- El status se busca por nombre en la tabla `statuses` para obtener su ID.

**Alternativas consideradas:** Enviar el status desde el frontend. Descartado: la lógica de negocio pertenece al backend; el frontend no tiene acceso al rango del paciente.

### 4. `time_block` como ENUM en la BD
**Decisión:** Migrar la columna `time_block` de `tinyInteger` a `ENUM('mañana', 'anochecer')`. El backend determina el valor automáticamente según `now()->hour`: 04–18 → 'mañana', 19–03 → 'anochecer'. El frontend no envía este campo.

**Alternativas consideradas:** Mantener tinyInteger con un Enum PHP. Descartado: la migración a ENUM en BD es más expresiva y elimina valores inválidos a nivel de base de datos.

### 5. Seeder de estados
**Decisión:** Crear `StatusesSeeder` que inserta los 3 estados de glucosa si no existen (usando `firstOrCreate`). También proveer un archivo `database/sql/seed_statuses.sql` como alternativa directa para entornos donde el seeder no se puede ejecutar.

## Risks / Trade-offs

- **Paciente sin GlucoseRange asignado** → Si `glucose_range_id` es null en el paciente, la determinación de status falla. Mitigación: validar en el `StoreGlucoseLogRequest` que el paciente tenga rango asignado antes de procesar.
- **OCR con baja precisión en fotos de mala calidad** → Tesseract puede extraer texto incorrecto. Mitigación: el paso de confirmación permite al usuario editar el valor antes de enviarlo; la validación backend asegura que sea un número entero positivo.
- **Migración de `time_block` con datos existentes** → Si hay filas previas con valores tinyInteger, la migración fallará. Mitigación: en la migración, borrar o nullear datos previos de la columna antes de alterar el tipo (o usar `MODIFY COLUMN` con ENUM que incluya los enteros si hay datos).
- **Tesseract.js WASM en mobile** → Primera carga puede ser lenta (~10MB). Mitigación: mostrar un spinner con mensaje de progreso durante la inicialización.

## Migration Plan

1. Ejecutar `StatusesSeeder` (o correr `seed_statuses.sql`) para poblar los 3 estados.
2. Ejecutar la migración que altera `user_glucose_logs.time_block` a ENUM.
3. Corregir `routes/web.php` (agregar `use` statement, corregir permiso, agregar ruta POST).
4. Implementar backend (Model, DTO, Services, Request, Resource, Controller).
5. Instalar `tesseract.js` vía npm.
6. Implementar frontend (tipos TS, stepper multi-paso, Sidebar).
7. Verificar manualmente el flujo completo en desktop y mobile.
