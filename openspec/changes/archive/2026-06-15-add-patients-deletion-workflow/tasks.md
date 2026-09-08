## 1. Backend — Ruta

- [x] 1.1 Agregar la ruta `DELETE /patients/{patient}` en `routes/web.php`

## 2. Backend — Servicio y Controlador

- [x] 2.1 Crear `app/Actions/Patients/DeletePatientSrv.php` con método `handle(UserPatient $patient)` que ejecuta soft-delete dentro de `DB::transaction`. El soft-delete debe ejecutarse sobre el modelo referenciado y en cascada sobre la relación `user` para que aplique en las tablas `user_patients` y `user` 
- [x] 2.2 Agregar método `destroy(UserPatient $patient)` en `PatientController` que llama a `DeletePatientSrv` y retorna redirect con mensaje flash

## 3. Frontend — Componente DeleteModal

- [x] 3.1 Crear `resources/js/src/components/patients/modal/DeleteModal.vue` usando Headless UI Dialog/Transition, mostrando nombre completo del paciente y botones "Confirmar" / "Cancelar"

## 4. Frontend — Integración en Index.vue

- [x] 4.1 Importar y registrar `DeleteModal` en `Patients/Index.vue`
- [x] 4.2 Descomentar y completar el bloque del `<DeleteModal>` en el template, pasando el paciente seleccionado como prop
- [x] 4.3 Actualizar `toggleDeleteModal(patient)` para asignar el paciente activo al estado de eliminación
- [x] 4.4 Actualizar `removePatients()` para enviar `router.delete()` al endpoint correcto y manejar respuesta (éxito/error)
- [x] 4.5 En el menú de acciones de cada paciente, mostrar botón "No puedes eliminar" deshabilitado cuando el usuario no tiene el permiso `delete-patients` (en lugar de ocultar la opción)

## 5. Verificación

- [x] 5.1 Verificar que un usuario con `delete-patients` puede eliminar un paciente y el registro queda con `deleted_at` seteado
- [x] 5.2 Verificar que cancelar el modal no modifica ningún registro
- [x] 5.3 Verificar que el modal muestra el nombre correcto del paciente seleccionado
- [x] 5.4 Verificar que un usuario sin `delete-patients` ve el botón "No puedes eliminar" deshabilitado en lugar del botón de eliminar funcional
