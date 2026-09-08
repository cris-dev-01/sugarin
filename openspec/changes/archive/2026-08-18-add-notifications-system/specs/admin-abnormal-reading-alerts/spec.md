## ADDED Requirements

### Requirement: Comando programado de detección de lecturas alteradas recientes
El sistema SHALL contar con un comando Artisan programado, independiente del comando de seguimiento, que se ejecuta diariamente y detecta a los `UserPatient` con al menos un `UserGlucoseLog` de status "Elevado - fuera de rango normal" o "Bajo - fuera de rango normal" registrado en las últimas `abnormal_reading_window_hours` horas (config, default 24).

#### Scenario: Ejecución diaria del comando
- **WHEN** el scheduler ejecuta el comando de lecturas alteradas
- **THEN** el sistema identifica a todos los pacientes con al menos una lectura "Elevada" o "Baja" registrada dentro de la ventana configurada

#### Scenario: Paciente con múltiples lecturas alteradas en la ventana
- **WHEN** un paciente tiene más de una lectura fuera de rango dentro de las últimas 24 horas
- **THEN** los administradores reciben una sola alerta por esa ejecución, no una por cada lectura

#### Scenario: Paciente sin lecturas alteradas recientes
- **WHEN** un paciente no tiene ninguna lectura "Elevada" o "Baja" dentro de la ventana configurada
- **THEN** no se genera ninguna notificación para ese paciente

### Requirement: Notificación de alerta de lectura alterada solo a administradores
El sistema SHALL notificar (canales `mail` y `database`) a cada usuario con rol `Administrator` sobre el paciente detectado con lecturas alteradas recientes. El sistema SHALL NOT notificar directamente al paciente desde este comando: el paciente ya fue notificado en tiempo real al momento de registrar esa lectura (ver capability `glucose-log-patient-notification`), y notificarlo de nuevo aquí sería redundante.

#### Scenario: Paciente con lectura elevada reciente
- **WHEN** un paciente registró una lectura con status "Elevado - fuera de rango normal" dentro de la ventana configurada
- **THEN** cada administrador recibe una `AdminAbnormalReadingAlertNotification` por los canales `mail` y `database`, y el paciente no recibe ninguna notificación de este comando

#### Scenario: Paciente con lectura baja reciente
- **WHEN** un paciente registró una lectura con status "Bajo - fuera de rango normal" dentro de la ventana configurada
- **THEN** cada administrador recibe una `AdminAbnormalReadingAlertNotification` por los canales `mail` y `database`

### Requirement: Idempotencia de la alerta diaria de lecturas alteradas
El sistema SHALL evitar reportar al mismo paciente más de una vez por día por la misma condición de lecturas alteradas recientes, sin importar cuántos administradores existan.

#### Scenario: Comando ejecutado dos veces el mismo día
- **WHEN** el comando de lecturas alteradas se ejecuta dos veces dentro del mismo día para el mismo paciente con lecturas alteradas
- **THEN** los administradores reciben una sola alerta sobre ese paciente ese día
