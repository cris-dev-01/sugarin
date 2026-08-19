# Capability: Admin Follow-up Alerts

## Purpose

Detecta diariamente, mediante un comando Artisan programado, a los pacientes que no han registrado lecturas de glucosa dentro de un umbral configurado de días, y alerta a los administradores (no al paciente) por los canales `mail` y `database`, evitando duplicar alertas para el mismo paciente el mismo día.

## Requirements

### Requirement: Comando programado de detección de pacientes sin seguimiento
El sistema SHALL contar con un comando Artisan programado que se ejecuta diariamente y detecta a los `UserPatient` sin ningún `UserGlucoseLog` registrado en los últimos `follow_up_days` días (config, default 3).

#### Scenario: Ejecución diaria del comando
- **WHEN** el scheduler ejecuta el comando de seguimiento
- **THEN** el sistema identifica todos los pacientes cuyo último registro de glucosa (o ausencia total de registros) supera el umbral configurado

### Requirement: Notificación de alerta de seguimiento solo a administradores
El sistema SHALL notificar (canales `mail` y `database`) a cada usuario con rol `Administrator` sobre el paciente detectado sin seguimiento, indicando el paciente afectado y los días transcurridos desde su último registro. El sistema SHALL NOT notificar directamente al paciente desde este comando: el paciente solo recibe notificaciones en el momento en que se registra una lectura de glucosa (ver capability `glucose-log-patient-notification`), no por la ausencia de registros.

#### Scenario: Paciente sin registros recientes
- **WHEN** un paciente no tiene registros de glucosa en los últimos `follow_up_days` días
- **THEN** cada administrador recibe una `AdminFollowUpAlertNotification` por los canales `mail` y `database`, y el paciente no recibe ninguna notificación de este comando

#### Scenario: Paciente sin ningún registro histórico
- **WHEN** un paciente nunca ha registrado una lectura de glucosa
- **THEN** el paciente se considera "sin seguimiento" y se reporta a los administradores igual que en el caso de inactividad reciente

#### Scenario: Paciente con seguimiento al día
- **WHEN** un paciente tiene al menos un registro de glucosa dentro de los últimos `follow_up_days` días
- **THEN** no se genera ninguna notificación para ese paciente

### Requirement: Idempotencia de la alerta diaria
El sistema SHALL evitar reportar al mismo paciente más de una vez por día por la misma condición de falta de seguimiento, sin importar cuántos administradores existan.

#### Scenario: Comando ejecutado dos veces el mismo día
- **WHEN** el comando de seguimiento se ejecuta dos veces dentro del mismo día para el mismo paciente sin seguimiento
- **THEN** los administradores reciben una sola alerta sobre ese paciente ese día
