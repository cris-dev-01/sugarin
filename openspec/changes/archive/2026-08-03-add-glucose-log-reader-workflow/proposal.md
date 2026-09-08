## Why

La tabla `user_glucose_logs` ya existe en la base de datos y la ruta `/glucose-logs` está definida, pero no existe ningún modelo, controlador, servicio ni interfaz para que los administradores puedan visualizar/tomar(crear) los registros de glucosa de sus pacientes.

## What Changes

- Nuevo modelo `UserGlucoseLog` con sus relaciones a `UserPatient` y `Status`.
- Migración para modificar `time_block` de `tinyInteger` a `ENUM('mañana', 'anochecer')`. El valor se determina automáticamente según la hora de ingreso (04:00–18:59 → 'mañana'; 19:00–03:59 → 'anochecer').
- Seeder y archivo SQL para poblar los 3 estados de glucosa: "Rango normal", "Elevado - fuera de rango normal", "Bajo - fuera de rango normal".
- Nuevo controlador `GlucoseLogController` con acciones `index` (página de inicio del flujo) y `store` (creación del log).
- Servicio `StoreGlucoseLogSrv` que almacena el log y determina automáticamente el status en función del rango de glucosa asignado al paciente (`GlucoseRange`).
- Servicio de listado `ListGlucoseLogsSrv` con filtrado y paginación.
- DTOs, Form Requests y Resource API para ambas acciones.
- Flujo Vue multi-paso:
  1. **Selección de paciente**: listado si hay más de uno, auto-selección si solo hay uno.
  2. **Captura OCR**: selector de imagen (desktop y mobile) con previsualización; integración con Tesseract.js para extraer el número del lector de glucosa.
  3. **Confirmación**: el usuario confirma el valor extraído antes de enviarlo.
  4. **Éxito**: pantalla con el resumen del log creado.
- Tipos TypeScript `glucoseLog.d.ts`.
- Wiring correcto de la ruta `/glucose-logs` (agregar `use` statement y corregir permiso).
- Entrada en el Sidebar para acceder al flujo.

## Capabilities

### New Capabilities

- `glucose-log-capture-workflow`: Flujo frontend multi-paso para registrar un log de glucosa: selección de paciente → captura de imagen con OCR (Tesseract.js) → confirmación → pantalla de éxito.
- `glucose-log-store`: Endpoint backend `POST /glucose-logs` que almacena el registro de glucosa y determina automáticamente el status comparando el valor con el `GlucoseRange` del paciente.

### Modified Capabilities

<!-- Sin cambios en capacidades existentes -->

## Impact

- **Base de datos**: Migración que altera `user_glucose_logs.time_block` de `tinyInteger` a `ENUM('mañana', 'anochecer')`. Seeder + query SQL para los 3 estados.
- **Backend**: Nuevos archivos en `app/Models/`, `app/Http/Controllers/`, `app/Actions/GlucoseLogs/`, `app/DataTransferObjects/GlucoseLogs/`, `app/Http/Requests/GlucoseLogs/`, `app/Http/Resources/`.
- **Frontend**: Nuevos archivos en `resources/js/src/Pages/GlucoseLogs/`, `resources/js/src/components/glucose-logs/`, `resources/js/src/types/`. Nueva dependencia: `tesseract.js`.
- **Rutas**: Corrección del `use` de `GlucoseLogController` y del permiso en `routes/web.php`. Agregar ruta `POST /glucose-logs`.
- **Dependencias**: Tesseract.js (frontend, OCR en el navegador).
