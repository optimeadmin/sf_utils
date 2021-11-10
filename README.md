# SF Utils
Repo con clases de utilidad para proyectos Symfony >= 5

## Instalación

```
composer require manuelj555/sf-utils @dev
```

## Configuración 

Agregar como un bundle en el `config/bundles.php`:

```php
<?php

return [
    ...
    Optime\Util\OptimeUtilBundle::class => ['all' => true],
];
```

#### Configuración de opciones:

Crear/Ajustar el archivo `config/packages/optime_utils.yaml`:

```yaml
optime_util:
    locales: [en, es, pt] # Configuración opcional
    default_locale: "%kernel.default_locale%" # Configuración opcional 
```

La idea es definir en ese parametro los locales que va a trabajar la aplicación.

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