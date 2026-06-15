# Capability: Patient Deletion

## Purpose

Allows authorized users to soft-delete patient records through a confirmation modal, ensuring accidental deletions are prevented and data is preserved in the database.

## Requirements

### Requirement: El usuario puede eliminar un paciente con confirmación
El sistema SHALL permitir a un usuario con el permiso `delete-patients` eliminar un paciente mediante un modal de confirmación. La eliminación SHALL realizarse como soft-delete, preservando el registro en la base de datos.

#### Scenario: Eliminar paciente exitosamente
- **WHEN** el usuario hace clic en "Eliminar" en el menú de un paciente y confirma en el modal
- **THEN** el sistema realiza soft-delete del registro, redirige/actualiza el listado y muestra una notificación de éxito

#### Scenario: Cancelar la eliminación
- **WHEN** el usuario abre el modal de confirmación y hace clic en "Cancelar"
- **THEN** el sistema cierra el modal sin modificar ningún registro

#### Scenario: Usuario sin permiso de eliminación
- **WHEN** el usuario autenticado no tiene el permiso `delete-patients`
- **THEN** el botón "Eliminar" no es visible en el menú de acciones del paciente, en su lugar aparece un botón "No puedes eliminar" sin acciones disponibles.

### Requirement: El backend procesa la eliminación mediante soft-delete
El sistema SHALL exponer una ruta `DELETE /patients/{patient}` protegida por el middleware `can:delete-patients`. El controlador SHALL delegar la eliminación al servicio `DeletePatientSrv`, que SHALL llamar `$patient->delete()` dentro de una transacción de base de datos.

#### Scenario: Solicitud de eliminación válida
- **WHEN** se recibe una solicitud `DELETE /patients/{id}` con un usuario autenticado y con permiso
- **THEN** el servicio realiza soft-delete del registro `UserPatient` y el controlador retorna una respuesta Inertia con redirección al listado de pacientes y mensaje de éxito

#### Scenario: Paciente no encontrado
- **WHEN** se recibe una solicitud `DELETE /patients/{id}` con un ID inexistente o ya eliminado
- **THEN** Laravel retorna 404 automáticamente vía route model binding

### Requirement: El modal de confirmación muestra el nombre del paciente
El sistema SHALL mostrar en el modal de confirmación el nombre completo del paciente a eliminar, para que el usuario pueda verificar antes de confirmar.

#### Scenario: Apertura del modal con datos del paciente
- **WHEN** el usuario hace clic en "Eliminar" para un paciente específico
- **THEN** el modal muestra el nombre completo del paciente y dos opciones: "Confirmar" y "Cancelar"
