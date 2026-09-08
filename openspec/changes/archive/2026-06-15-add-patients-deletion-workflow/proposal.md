## Why

La interfaz de listado de pacientes ya expone un botón de "Eliminar" y lógica parcial de modal, pero carece del backend completo (ruta, controlador, servicio) y del componente `DeleteModal` en el frontend. Completar este flujo permite a los usuarios con el permiso `delete-patients` dar de baja pacientes de forma controlada mediante soft-delete.

## What Changes

- Agregar ruta `DELETE /patients/{patient}` en `routes/web.php`.
- Crear `DeletePatientSrv` (servicio de eliminación con soft-delete).
- Agregar método `destroy()` en `PatientController`.
- Crear el componente Vue `DeleteModal.vue` para pacientes.
- Activar y completar la lógica de eliminación en `Patients/Index.vue` (actualmente comentada/incompleta).

## Capabilities

### New Capabilities

- `patient-deletion`: Flujo completo de eliminación de pacientes con confirmación en modal, soft-delete en base de datos y notificación de resultado.

### Modified Capabilities

<!-- No hay specs existentes que cambien requisitos a nivel de spec. -->

## Impact

- **Backend**: `PatientController`, `routes/web.php`, nuevo `DeletePatientSrv`, permisos/gates.
- **Frontend**: `Patients/Index.vue`, nuevo componente `components/patients/modal/DeleteModal.vue`.
- **Base de datos**: Sin migraciones nuevas; la columna `deleted_at` ya existe en `user_patients`.
- **Patrón de referencia**: Sigue el mismo flujo implementado en el módulo GlucoseRange.
