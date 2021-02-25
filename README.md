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
    - { resource: '../vendor/manuelj555/sf_utils/services.yaml' }
```

## Uso

#### `Optime\Util\Exception\DomainException`

Clase para cuando se necesitan lanzar excepciones de dominio, es decir, excepciones
que serán capturadas y controladas como parte del flujo de un
proceso Errores de negocio (aprobar algo ya aprobado, rechazar algo
que no se puede rechazar, salgo insuficiente, etc).

#### `Optime\Util\Exception\ValidationException`