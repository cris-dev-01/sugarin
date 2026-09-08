## ADDED Requirements

### Requirement: Notificación al paciente tras el registro del log
El sistema SHALL despachar, tras la creación exitosa de un `UserGlucoseLog`, una notificación al paciente correspondiente (ver capability `glucose-log-patient-notification`), sin afectar el código de respuesta HTTP ni el contenido de la respuesta del endpoint `POST /glucose-logs`.

#### Scenario: La notificación no bloquea la respuesta del endpoint
- **WHEN** se registra exitosamente una nueva lectura vía `POST /glucose-logs`
- **THEN** el endpoint responde HTTP 201 con el log creado, sin esperar la entrega efectiva de la notificación (despacho encolado)
