## ADDED Requirements

### Requirement: Selección de paciente al iniciar el flujo
El sistema SHALL mostrar, como primer paso del flujo, una lista de pacientes disponibles cuando el administrador tiene más de un paciente asignado. Si solo existe un paciente, el sistema SHALL seleccionarlo automáticamente y avanzar al siguiente paso sin interacción del usuario.

#### Scenario: Administrador con múltiples pacientes
- **WHEN** el usuario autenticado accede a `/glucose-logs` y tiene más de un paciente asignado
- **THEN** el sistema muestra el paso "Selección de paciente" con la lista de pacientes disponibles

#### Scenario: Administrador con un único paciente
- **WHEN** el usuario autenticado accede a `/glucose-logs` y tiene exactamente un paciente asignado
- **THEN** el sistema selecciona automáticamente ese paciente y avanza al paso de captura OCR sin mostrar el listado

#### Scenario: Paciente seleccionado del listado
- **WHEN** el usuario selecciona un paciente de la lista
- **THEN** el sistema avanza al paso de captura OCR con ese paciente como contexto del flujo

### Requirement: Carga y previsualización de imagen del lector
El sistema SHALL proveer un control de selección de archivo (compatible con escritorio y mobile) para cargar una fotografía del lector de glucosa. Una vez seleccionada, el sistema SHALL mostrar una previsualización de la imagen antes de ejecutar el OCR.

#### Scenario: Selección de imagen desde escritorio
- **WHEN** el usuario hace clic en el control de carga y selecciona un archivo de imagen (JPG, PNG, WEBP)
- **THEN** el sistema muestra una previsualización de la imagen seleccionada

#### Scenario: Selección de imagen desde mobile (cámara o galería)
- **WHEN** el usuario en un dispositivo móvil usa el control de carga (que permite acceder a cámara o galería)
- **THEN** el sistema muestra una previsualización de la imagen capturada o seleccionada

#### Scenario: No se ha seleccionado imagen
- **WHEN** el usuario intenta avanzar sin haber cargado una imagen
- **THEN** el sistema muestra un mensaje de error indicando que se debe seleccionar una imagen

### Requirement: Extracción OCR del valor de glucosa
El sistema SHALL utilizar Tesseract.js para procesar la imagen cargada y extraer el valor numérico mostrado en el lector de glucosa. Durante el procesamiento, el sistema SHALL mostrar un indicador de progreso.

#### Scenario: OCR exitoso con número legible
- **WHEN** la imagen contiene un número claramente visible
- **THEN** el sistema extrae el valor numérico y lo precarga en el campo de confirmación

#### Scenario: OCR con resultado incierto
- **WHEN** Tesseract.js no puede identificar un número con certeza
- **THEN** el sistema deja el campo de confirmación vacío y el usuario puede ingresar el valor manualmente

#### Scenario: Progreso de inicialización de Tesseract.js
- **WHEN** Tesseract.js se está inicializando por primera vez (carga del modelo WASM)
- **THEN** el sistema muestra un spinner con mensaje de progreso para informar al usuario de la espera

### Requirement: Confirmación del valor extraído
El sistema SHALL mostrar un paso de confirmación en el que se presenta el valor extraído por OCR en un campo editable. El usuario SHALL poder corregir el valor antes de enviarlo. El sistema SHALL validar que el valor sea un número entero positivo antes de permitir el envío.

#### Scenario: Confirmación del valor sin modificación
- **WHEN** el valor extraído por OCR es correcto y el usuario confirma sin modificarlo
- **THEN** el sistema procede al envío del log con ese valor

#### Scenario: Corrección del valor extraído
- **WHEN** el valor extraído por OCR es incorrecto y el usuario lo modifica manualmente
- **THEN** el sistema envía el log con el valor corregido por el usuario

#### Scenario: Valor inválido en confirmación
- **WHEN** el usuario intenta enviar con un valor no numérico o vacío
- **THEN** el sistema muestra un error de validación y no envía el formulario

### Requirement: Pantalla de éxito con resumen del log
El sistema SHALL mostrar una pantalla de éxito luego de crear el log correctamente. Esta pantalla SHALL incluir: nombre del paciente, valor de glucosa (mg/dL), bloque de tiempo ('mañana' o 'anochecer'), el status determinado y la fecha/hora del registro.

#### Scenario: Creación exitosa del log
- **WHEN** el backend crea el log correctamente
- **THEN** el sistema muestra la pantalla de éxito con el resumen completo del log creado

#### Scenario: Error en la creación del log
- **WHEN** el backend retorna un error (por ejemplo, paciente sin rango de glucosa asignado)
- **THEN** el sistema muestra el mensaje de error en el paso de confirmación y no avanza a la pantalla de éxito

### Requirement: Indicador visual de lectura anormal en la pantalla de éxito
La pantalla de éxito SHALL destacar, mediante un banner visual (ícono y color diferenciados), si la lectura registrada está dentro o fuera del rango de glucosa establecido para el paciente. Cuando la lectura esté fuera de rango, el banner SHALL mostrar además el rango esperado (mínimo y máximo, en mg/dL) correspondiente al bloque de tiempo del registro.

#### Scenario: Lectura dentro del rango normal
- **WHEN** el log creado tiene status "Rango normal"
- **THEN** la pantalla de éxito muestra un banner en tono de éxito indicando que la lectura está dentro del rango normal

#### Scenario: Lectura elevada fuera de rango
- **WHEN** el log creado tiene status "Elevado - fuera de rango normal"
- **THEN** la pantalla de éxito muestra un banner en tono de alerta (peligro) indicando que la lectura está fuera de rango, junto con el rango esperado del bloque de tiempo correspondiente

#### Scenario: Lectura baja fuera de rango
- **WHEN** el log creado tiene status "Bajo - fuera de rango normal"
- **THEN** la pantalla de éxito muestra un banner en tono de advertencia indicando que la lectura está fuera de rango, junto con el rango esperado del bloque de tiempo correspondiente
