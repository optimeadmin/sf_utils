# SF Utils
Repo con clases de utilidad para proyectos Symfony >= 5

## Instalación

```
composer require manuelj555/sf-utils @dev
```

## Configuración 

Agregar **al principio** del config/services.yaml lo siguiente:

```yaml
# Añadir esto al principo del archivo:
imports:
    - { resource: '../vendor/manuelj555/sf-utils/services.yaml' }
```

## Uso

#### `Optime\Util\Exception\DomainException`

Clase para cuando se necesitan lanzar excepciones de dominio, es decir, excepciones
que serán capturadas y controladas como parte del flujo de un
proceso Errores de negocio (aprobar algo ya aprobado, rechazar algo
que no se puede rechazar, salgo insuficiente, etc).

#### `Optime\Util\Exception\ValidationException`

Clase para cuando se necesitan lanzar excepciones de validación de dominio, es decir, 
errores de datos al ejecutar procesos de negocio (formato de un string, valor vacio, etc).

Esta clase es util por ejemplo para convertir la exception en un error de un formulario
de Symfony:

```php
try {
    // proceso
}catch(\Optime\Util\Exception\ValidationException $e){
    // agregar error a un form de Symfony:
    $e->addFormError($form, $translator);
    // agregar un flash:
    $this->addFlash("error", $e->getDomainMessage()->trans($translator));
}
```

#### `Optime\Util\Batch\BatchProcessingResult`

Clase de utilidad que sirve para obtener información del resultado de un proceso por lotes.
Por ejemplo al cargar un CSV, podemos reflejar en dicha clase los elementos procesados
correctamente y los que tuvieron algún problema de procesamiento.

#### `Optime\Util\Validator\DomainValidator`

Clase que usa el validador de Symfony y permite facilitar la integración del validador
de Symfony con las Excepciones de Dominio de esta libreria. Puede lanzar un
`ValidationException` si hay errores de validación.

```php
try {
    $domainValidator->handle($model);
} catch (\Optime\Util\Exception\ValidationException $e) {
    $e->addFormError($form, $translator);
}
```

#### `Optime\Util\TranslatableMessage`

Clase de utilidad que permite definir un mensaje traducible. Es usada por
las Excepciones de Dominio de esta libreria. Ejemplo:

```php
try {
    throw new DomainException("error.invalid_value");
} catch (\Optime\Util\Exception\DomainException $e) {
    $this->addFlash('error', $e->getDomainMessage()->trans($translator));
}
// Otro caso:
try {
    throw new DomainException(new TranslatableMessage(
        "error.invalid_value", 
        ['{invalid_value}' => 'aaa'],
        'validators' // este es el domino de traducción.
    ));
} catch (\Optime\Util\Exception\DomainException $e) {
    $this->addFlash('error', $e->getDomainMessage()->trans($translator));
}
```