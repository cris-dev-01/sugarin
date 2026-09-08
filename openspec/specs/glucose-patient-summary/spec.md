# Capability: Glucose Patient Summary

## Purpose

Provides the per-patient glucose summary used both by the Administrator drill-down and by the patient's own self-service view: period-scoped metrics (in-range percentage by time block, low/high event counts, average/variability, adherence, streak) plus the latest reading and recent readings for trend/log display.

## Requirements

### Requirement: Endpoint de resumen de un paciente para el drill-down del Administrator
El sistema SHALL proveer un endpoint (`GET /patients/{patient}/summary`, ability `show-dashboard`) que recibe un período (`7`, `30` o `90` días, default `30`) y retorna el resumen completo de métricas del `UserPatient` indicado por ruta para ese período.

#### Scenario: Solicitud exitosa del resumen
- **WHEN** un usuario autorizado con `show-dashboard` solicita el resumen de un paciente con `period=30`
- **THEN** el sistema retorna las métricas calculadas sobre los últimos 30 días, con código HTTP 200

#### Scenario: Período inválido
- **WHEN** el parámetro `period` no es uno de los valores permitidos (`7`, `30`, `90`)
- **THEN** el sistema retorna HTTP 422 con errores de validación

#### Scenario: Paciente sin registros en el período
- **WHEN** el paciente solicitado no tiene ningún `UserGlucoseLog` dentro del período seleccionado
- **THEN** el sistema retorna el resumen con las métricas de conteo/porcentaje en cero y los campos de tendencia/última lectura en `null`, con código HTTP 200

### Requirement: Resumen propio del paciente en la ruta de autoservicio
El sistema SHALL calcular, como parte del render Inertia de la ruta `GET /summary` (`PatientSummaryController@index`, ability `show-summary`), el resumen del `UserPatient` asociado al usuario autenticado (`auth()->user()->patient`), sin aceptar un identificador de paciente distinto por parámetro de ruta o query.

#### Scenario: Paciente autenticado ve su propio resumen
- **WHEN** un usuario con rol Patient autenticado solicita `GET /summary`
- **THEN** el sistema retorna el resumen calculado sobre su propio `UserPatient`, sin exponer datos de otros pacientes

#### Scenario: Usuario sin paciente asociado
- **WHEN** un usuario autenticado con la ability `show-summary` no tiene ningún `UserPatient` asociado
- **THEN** el sistema retorna la página con el resumen vacío/nulo, sin error

### Requirement: Porcentaje de lecturas en rango normal por bloque horario
El sistema SHALL calcular, para el período seleccionado, el porcentaje de lecturas con status "Rango normal" sobre el total de lecturas, desagregado por `TimeBlock` (`mañana` y `anochecer`) y también el porcentaje combinado.

#### Scenario: Cálculo separado por bloque horario
- **WHEN** el paciente tiene 8 de 10 lecturas "mañana" en rango normal y 3 de 5 lecturas "anochecer" en rango normal
- **THEN** el resumen retorna 80% para "mañana", 60% para "anochecer" y 73% (11/15) para el combinado

### Requirement: Conteo de eventos Bajo y Elevado del período
El sistema SHALL retornar, para el período seleccionado, el conteo de lecturas con status "Bajo - fuera de rango normal" y el conteo de lecturas con status "Elevado - fuera de rango normal", desagregado también por `TimeBlock`.

#### Scenario: Conteo con eventos de ambos tipos
- **WHEN** el paciente tiene 3 lecturas "Bajo - fuera de rango normal" y 2 lecturas "Elevado - fuera de rango normal" en el período
- **THEN** el resumen retorna `bajo: 3` y `elevado: 2`

### Requirement: Promedio y variabilidad del valor por bloque horario
El sistema SHALL calcular, para el período seleccionado, el promedio y la desviación estándar del campo `value`, desagregados por `TimeBlock`.

#### Scenario: Cálculo de promedio y desviación estándar
- **WHEN** el paciente tiene lecturas "mañana" con valores [90, 100, 110]
- **THEN** el resumen retorna promedio 100 y la desviación estándar correspondiente para el bloque "mañana"

#### Scenario: Bloque horario sin lecturas en el período
- **WHEN** el paciente no tiene ninguna lectura "anochecer" en el período
- **THEN** el resumen retorna `null` para el promedio y la desviación estándar de "anochecer", sin error

### Requirement: Racha de días consecutivos en rango normal
El sistema SHALL calcular la racha actual de días consecutivos (hasta el día de hoy, en zona horaria de la aplicación) en que el paciente registró al menos una lectura y **todas** sus lecturas de ese día tienen status "Rango normal". Un día sin ningún registro SHALL romper la racha.

#### Scenario: Racha vigente
- **WHEN** el paciente tiene todas sus lecturas en "Rango normal" durante los últimos 5 días consecutivos, incluyendo hoy
- **THEN** el resumen retorna una racha de 5 días

#### Scenario: Racha rota por una lectura fuera de rango
- **WHEN** el paciente tuvo un día, dentro de la racha en curso, con al menos una lectura "Elevado - fuera de rango normal"
- **THEN** la racha se corta en ese día y solo cuentan los días posteriores consecutivos en rango normal

#### Scenario: Racha rota por un día sin registros
- **WHEN** el paciente no registró ninguna lectura en un día dentro de lo que sería la racha en curso
- **THEN** la racha se corta en ese día, aunque los días anteriores y posteriores estén en rango normal

#### Scenario: El día de hoy sin lecturas todavía no rompe la racha
- **WHEN** el día de hoy el paciente aún no ha registrado ninguna lectura, pero los días anteriores consecutivos están en rango normal
- **THEN** el sistema retorna la racha calculada hasta ayer, sin considerarla rota por el día de hoy en curso

### Requirement: Adherencia de registro
El sistema SHALL calcular la adherencia de registro del período como el porcentaje de lecturas efectivamente registradas sobre las lecturas esperadas, donde las lecturas esperadas son `expected_logs_per_day` (parámetro administrable, persistido en base de datos, default 2) multiplicado por la cantidad de días del período.

#### Scenario: Adherencia parcial
- **WHEN** el período es de 30 días, `expected_logs_per_day` es 2 (60 lecturas esperadas) y el paciente registró 45 lecturas
- **THEN** el resumen retorna una adherencia de 75%

#### Scenario: Cambio del parámetro afecta el cálculo sin deploy
- **WHEN** un administrador cambia `expected_logs_per_day` de 2 a 3 desde la pantalla de administración
- **THEN** los siguientes cálculos de adherencia usan 3 lecturas esperadas por día, sin requerir un despliegue

### Requirement: Última lectura y su status
El sistema SHALL retornar el `UserGlucoseLog` más reciente del paciente (sin restringirse al período seleccionado) junto con su `Status` y su `TimeBlock`.

#### Scenario: Paciente con registros históricos
- **WHEN** el paciente tiene registros previos aunque ninguno dentro del período seleccionado
- **THEN** el resumen retorna la última lectura histórica junto a su status, independientemente del período

### Requirement: Lecturas recientes para tendencia y bitácora
El sistema SHALL retornar una lista con las últimas `20` lecturas del paciente (independiente del período seleccionado), cada una con su valor, `time_block`, status y fecha, para alimentar el gráfico de tendencia y la bitácora de actividad reciente en el frontend.

#### Scenario: Paciente con más de 20 lecturas históricas
- **WHEN** el paciente tiene más de 20 lecturas registradas
- **THEN** el resumen retorna únicamente las 20 más recientes, ordenadas de más reciente a más antigua

#### Scenario: Paciente sin lecturas históricas
- **WHEN** el paciente no tiene ninguna lectura registrada
- **THEN** el resumen retorna una lista vacía para las lecturas recientes

### Requirement: Distribución por status del período
El sistema SHALL retornar el conteo y porcentaje de lecturas del período agrupadas por cada `Status` existente ("Rango normal", "Elevado - fuera de rango normal", "Bajo - fuera de rango normal").

#### Scenario: Distribución con los 3 estados presentes
- **WHEN** el paciente tiene 10 lecturas "Rango normal", 3 "Elevado - fuera de rango normal" y 2 "Bajo - fuera de rango normal" en el período
- **THEN** el resumen retorna la distribución con 66.7%, 20% y 13.3% respectivamente

### Requirement: Permisos para ver el resumen de un paciente
El sistema SHALL exponer `GET /patients/{patient}/summary` únicamente a usuarios con la ability `show-dashboard`, y `GET /summary` únicamente a usuarios con la ability `show-summary` (ambas ya existentes vía `spatie/laravel-permission`).

#### Scenario: Usuario sin permiso intenta acceder al drill-down
- **WHEN** un usuario autenticado sin la ability `show-dashboard` solicita `GET /patients/{patient}/summary`
- **THEN** el sistema retorna código HTTP 403

#### Scenario: Usuario sin permiso intenta acceder a su resumen
- **WHEN** un usuario autenticado sin la ability `show-summary` solicita `GET /summary`
- **THEN** el sistema retorna código HTTP 403
