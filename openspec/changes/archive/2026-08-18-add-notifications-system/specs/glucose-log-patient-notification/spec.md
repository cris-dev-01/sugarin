## ADDED Requirements

### Requirement: Notificación al paciente tras registrar una lectura
El sistema SHALL notificar (canales `mail` y `database`) al paciente correspondiente cuando `StoreGlucoseLogSrv` registra exitosamente una nueva lectura de glucosa, incluyendo el valor, el `time_block` y el status calculado.

#### Scenario: Registro exitoso de una lectura
- **WHEN** se registra exitosamente una nueva lectura de glucosa para un paciente
- **THEN** el paciente recibe una notificación `GlucoseLogRegisteredNotification` con el valor, `time_block` y status del registro, por los canales `mail` y `database`

#### Scenario: Registro fallido
- **WHEN** el registro de la lectura falla (ej. el paciente no tiene rango de glucosa configurado)
- **THEN** el sistema NO despacha ninguna notificación

### Requirement: Contenido diferenciado para lecturas fuera de rango
La notificación SHALL destacar en su contenido cuando el status de la lectura no sea "Rango normal", para diferenciarla de una lectura dentro de rango.

#### Scenario: Lectura dentro de rango normal
- **WHEN** la lectura registrada tiene status "Rango normal"
- **THEN** la notificación se genera con un mensaje estándar de confirmación

#### Scenario: Lectura fuera de rango
- **WHEN** la lectura registrada tiene status "Elevado - fuera de rango normal" o "Bajo - fuera de rango normal"
- **THEN** la notificación incluye una indicación explícita de que la lectura está fuera de rango
