# Capability: Glucose Log Store

## Purpose

Provides the backend endpoint and persistence logic for creating glucose log entries, automatically determining the time block and clinical status from the patient's assigned glucose range, and exposing abnormality information in the API response.

## Requirements

### Requirement: Almacenar registro de glucosa
El sistema SHALL proveer el endpoint `POST /glucose-logs` que recibe el `user_patient_id` y el `value` de glucosa (mg/dL), y persiste un nuevo registro en `user_glucose_logs`. El campo `time_block` SHALL ser determinado automáticamente por el backend según la hora del servidor al momento del ingreso.

#### Scenario: Creación exitosa del log
- **WHEN** el usuario autenticado envía `POST /glucose-logs` con `user_patient_id` y `value` válidos
- **THEN** el sistema crea el registro con el `time_block`, `status_id` y timestamps correspondientes, y retorna el log creado con código HTTP 201

#### Scenario: Valor de glucosa inválido
- **WHEN** el campo `value` no es un entero positivo
- **THEN** el sistema retorna HTTP 422 con errores de validación

#### Scenario: Paciente inexistente
- **WHEN** el `user_patient_id` no corresponde a un paciente existente
- **THEN** el sistema retorna HTTP 422 con error de validación

### Requirement: Determinación automática del bloque de tiempo
El sistema SHALL asignar el campo `time_block` automáticamente según la hora del servidor al momento del registro, sin que el cliente lo envíe:
- Si la hora está entre las 04:00 y las 18:59 → `time_block = 'mañana'`
- Si la hora está entre las 19:00 y las 03:59 (del día siguiente) → `time_block = 'anochecer'`

#### Scenario: Ingreso en horario de mañana
- **WHEN** el log se crea a las 10:30 hrs
- **THEN** el campo `time_block` del registro es `'mañana'`

#### Scenario: Ingreso en horario de anochecer (noche)
- **WHEN** el log se crea a las 21:00 hrs
- **THEN** el campo `time_block` del registro es `'anochecer'`

#### Scenario: Ingreso en horario de madrugada (anochecer)
- **WHEN** el log se crea a las 02:00 hrs
- **THEN** el campo `time_block` del registro es `'anochecer'`

### Requirement: Determinación automática del status según rango de glucosa
El sistema SHALL determinar el `status_id` del log comparando el `value` con el rango de glucosa (`GlucoseRange`) asignado al paciente, utilizando los umbrales correspondientes al `time_block`:
- `time_block = 'mañana'` → usar `min_fasting_value` y `max_fasting_value`.
- `time_block = 'anochecer'` → usar `min_non_fasting_value` y `max_non_fasting_value`.

Las reglas de clasificación SHALL ser:
- Dentro del rango (min ≤ value ≤ max) → status "Rango normal".
- Por encima del rango (value > max) → status "Elevado - fuera de rango normal".
- Por debajo del rango (value < min) → status "Bajo - fuera de rango normal".

#### Scenario: Valor dentro del rango normal (mañana)
- **WHEN** el log es de tipo 'mañana' y el value está entre `min_fasting_value` y `max_fasting_value`
- **THEN** el status asignado es "Rango normal"

#### Scenario: Valor elevado (anochecer)
- **WHEN** el log es de tipo 'anochecer' y el value supera `max_non_fasting_value`
- **THEN** el status asignado es "Elevado - fuera de rango normal"

#### Scenario: Valor bajo (mañana)
- **WHEN** el log es de tipo 'mañana' y el value es menor que `min_fasting_value`
- **THEN** el status asignado es "Bajo - fuera de rango normal"

#### Scenario: Paciente sin rango asignado
- **WHEN** el paciente no tiene un `glucose_range_id` asignado
- **THEN** el sistema retorna HTTP 422 con un mensaje indicando que el paciente no tiene un rango de glucosa configurado

### Requirement: Indicador de anormalidad y rango en la respuesta del log creado
La respuesta del endpoint `POST /glucose-logs` SHALL incluir un indicador booleano `is_abnormal` (verdadero cuando el status determinado no sea "Rango normal") y un objeto `range` con los umbrales (`min`, `max`) del `GlucoseRange` del paciente correspondientes al `time_block` del registro, para que el frontend pueda destacar visualmente si la lectura está fuera de rango.

#### Scenario: Respuesta con lectura dentro de rango
- **WHEN** el log creado tiene status "Rango normal"
- **THEN** la respuesta incluye `is_abnormal: false` y el objeto `range` con los umbrales usados para la comparación

#### Scenario: Respuesta con lectura fuera de rango
- **WHEN** el log creado tiene status "Elevado - fuera de rango normal" o "Bajo - fuera de rango normal"
- **THEN** la respuesta incluye `is_abnormal: true` y el objeto `range` con los umbrales usados para la comparación

### Requirement: Columna time_block como ENUM en la base de datos
El sistema SHALL almacenar `time_block` como `ENUM('mañana', 'anochecer')` en la tabla `user_glucose_logs`, reemplazando el `tinyInteger` original.

#### Scenario: Migración aplicada correctamente
- **WHEN** se ejecuta la migración de modificación de columna
- **THEN** la tabla `user_glucose_logs` acepta únicamente los valores `'mañana'` y `'anochecer'` para el campo `time_block`

### Requirement: Estados de glucosa disponibles en la base de datos
El sistema SHALL contar con los siguientes 3 registros en la tabla `statuses` para ser usados en los logs de glucosa: "Rango normal", "Elevado - fuera de rango normal", "Bajo - fuera de rango normal". El seeder SHALL usar `firstOrCreate` para ser idempotente.

#### Scenario: Seeder ejecutado en entorno vacío
- **WHEN** el seeder se ejecuta en una base de datos sin estados previos
- **THEN** se crean los 3 estados de glucosa

#### Scenario: Seeder ejecutado en entorno con estados ya existentes
- **WHEN** el seeder se ejecuta y los estados ya existen
- **THEN** no se crean duplicados y el seeder termina sin errores

### Requirement: Notificación al paciente tras el registro del log
El sistema SHALL despachar, tras la creación exitosa de un `UserGlucoseLog`, una notificación al paciente correspondiente (ver capability `glucose-log-patient-notification`), sin afectar el código de respuesta HTTP ni el contenido de la respuesta del endpoint `POST /glucose-logs`.

#### Scenario: La notificación no bloquea la respuesta del endpoint
- **WHEN** se registra exitosamente una nueva lectura vía `POST /glucose-logs`
- **THEN** el endpoint responde HTTP 201 con el log creado, sin esperar la entrega efectiva de la notificación (despacho encolado)
