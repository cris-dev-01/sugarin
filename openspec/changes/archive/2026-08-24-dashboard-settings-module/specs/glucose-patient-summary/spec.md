## MODIFIED Requirements

### Requirement: Adherencia de registro
El sistema SHALL calcular la adherencia de registro del período como el porcentaje de lecturas efectivamente registradas sobre las lecturas esperadas, donde las lecturas esperadas son `expected_logs_per_day` (parámetro administrable, persistido en base de datos, default 2) multiplicado por la cantidad de días del período.

#### Scenario: Adherencia parcial
- **WHEN** el período es de 30 días, `expected_logs_per_day` es 2 (60 lecturas esperadas) y el paciente registró 45 lecturas
- **THEN** el resumen retorna una adherencia de 75%

#### Scenario: Cambio del parámetro afecta el cálculo sin deploy
- **WHEN** un administrador cambia `expected_logs_per_day` de 2 a 3 desde la pantalla de administración
- **THEN** los siguientes cálculos de adherencia usan 3 lecturas esperadas por día, sin requerir un despliegue
