## ADDED Requirements

### Requirement: Notificación al actualizar datos básicos de perfil
El sistema SHALL notificar (canal `database`) al usuario cuando `UpdateProfileSrv` actualiza exitosamente su `name` y/o `email`.

#### Scenario: Actualización exitosa de datos básicos
- **WHEN** un usuario autenticado actualiza su nombre o email vía `PUT /profile`
- **THEN** el usuario recibe una notificación `ProfileUpdatedNotification` por el canal `database`

#### Scenario: Actualización fallida por validación
- **WHEN** la actualización de datos básicos falla por errores de validación (ej. email duplicado)
- **THEN** el sistema NO despacha ninguna notificación

### Requirement: Notificación al actualizar la contraseña
El sistema SHALL notificar (canal `database`) al usuario cuando `UpdatePasswordSrv` actualiza exitosamente su contraseña.

#### Scenario: Cambio de contraseña exitoso
- **WHEN** un usuario autenticado cambia su contraseña vía `PUT /profile/password`
- **THEN** el usuario recibe una notificación `PasswordUpdatedNotification` por el canal `database`

#### Scenario: Intento fallido de cambio de contraseña
- **WHEN** el cambio de contraseña falla porque la clave actual ingresada es incorrecta
- **THEN** el sistema NO despacha ninguna notificación
