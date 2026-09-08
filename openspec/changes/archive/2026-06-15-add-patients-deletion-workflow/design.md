## Context

El módulo de pacientes ya cuenta con infraestructura de soft-delete (`SoftDeletes` en `UserPatient`, columna `deleted_at` en la tabla `user_patients`). La vista `Patients/Index.vue` expone un botón "Eliminar" con lógica parcialmente comentada (`toggleDeleteModal`, `removePatients`) y referencia al componente `DeleteModal` que aún no existe. El módulo GlucoseRange implementa el mismo flujo de eliminación de extremo a extremo y sirve como patrón de referencia.

## Goals / Non-Goals

**Goals:**
- Completar el flujo de eliminación de pacientes: confirmación en modal → solicitud al backend → soft-delete → notificación.
- Seguir el patrón ya establecido en GlucoseRange para mantener consistencia arquitectónica.
- Controlar el acceso mediante el permiso `delete-patients` ya referenciado en la UI.

**Non-Goals:**
- Hard-delete o purga de registros.
- Restauración de pacientes eliminados (fuera de alcance por ahora).
- Cambios en la estructura de la base de datos (no se requieren migraciones nuevas).
- Eliminación masiva de varios pacientes a la vez.

## Decisions

### 1. Soft-delete en lugar de hard-delete
La infraestructura de soft-delete ya existe en el modelo y la migración. Usar hard-delete implicaría perder historial de atenciones asociadas. Soft-delete preserva integridad referencial y facilita auditoría futura.

### 2. Servicio dedicado `DeletePatientSrv`
Sigue el patrón del módulo GlucoseRange (`DeleteGlucoseRangeSrv`). Encapsula la lógica en una clase de acción reutilizable y fácil de testear, en lugar de colocar la lógica directamente en el controlador.

### 3. Componente `DeleteModal` independiente
El modal de confirmación se crea como componente propio en `components/patients/modal/DeleteModal.vue`, alineándose con la estructura de carpetas existente y el patrón de GlucoseRange. Usa Headless UI (Dialog/Transition) tal como el módulo de referencia.

## Risks / Trade-offs

- **[Riesgo] Paciente con registros relacionados activos** → El soft-delete no elimina relaciones; datos asociados (mediciones, etc.) permanecen. Mitigación: documentar comportamiento; si se requiere cascada futura, se aborda en una change separada.
- **[Riesgo] Permisos no registrados** → Si el gate no se registra, el botón desaparece pero la ruta sigue accesible. Mitigación: agregar middleware `can:delete-patients` en la ruta y asegurar registro del gate antes del deploy.
- **[Trade-off] Sin confirmación de impacto** → El modal no muestra cuántos registros relacionados se verán afectados. Aceptable para v1; se puede enriquecer en iteraciones posteriores.
