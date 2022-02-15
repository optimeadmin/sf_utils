# SF Utils
Repo con clases de utilidad para proyectos Symfony >= 5

## Instalación

```
composer require manuelj555/sf-utils ^6.0@dev
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

### Traducciones en formularios:

Hay ciertas clases de utilidad para trabajar con campos traducibles, enfocado
a la extensión de traducción de Doctrine pero que puede usarse de forma
generica.

#### Clases implicadas:

##### `Optime\Util\Translation\TranslationsAwareInterface`

Esta interfaz debe ser implementada por toda entidad y objeto que contenga y quiera manejar
propiedades traducibles. Se deben implementar dos métodos para obtener o establecer el locale
con el que se cargó la entidad o el objeto desde la fuente de datos.

La idea es que esta interfaz va a manejar el atributo en clase que contiene la anotación `@Gedmo\Locale`.
Ver documentación del atributo del locale [acá](https://github.com/doctrine-extensions/DoctrineExtensions/blob/main/doc/translatable.md#translatable-annotations).

Se puede simplificar la implementación de la interfaz usando el Trait `TranslationsAwareTrait`:

```php
<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Optime\Util\Translation\TranslationsAwareInterface;
use Optime\Util\Translation\TranslationsAwareTrait;

/**
 * @ORM\Entity()
 */
class Entidad implements TranslationsAwareInterface
{
    use TranslationsAwareTrait;
    
    ...
}
```

Con el trait y se incorporan los métodos de la interfaz y el atributo
con la anotación `@Gedmo\Locale`.

##### `Optime\Util\Translation\TranslatableContent`

Esta clase es un objeto "value object" que contiene un arreglo con un texto
en distintos idiomas, cada indice del arreglo es un locale y su valor
es el texto en dicho locale.

##### `Optime\Util\Translation\Translation`

Esta es la clase principal para gestionar las traducciones de una entidad.
Ofrece varios métodos para crear o persistir un `TranslatableContent`:

```php
<?php

$translation = ... obtenemos el servicio Optime\Util\Translation\Translation

########## Carga/Creación de un TranslatableContent #########

// traduccion nueva:
$newContent = $translation->newContent([
    'en' => 'Hi',
    'es' => 'Hola',
]);

// traduccion nueva a partir de un unico string:
$newContent = $translation->fromString('Hi'); // todos los locales tendrán el mismo texto

// Obtener traducciones existentes en una entidad.
$object = $repository->find(1); // object debe implementar TranslationsAwareInterface
$translation->refreshInDefaultLocale($object); // importante refrescar el objeto en el locale por defecto de la app.
$titleTranslations = $translation->loadContent($object, 'title');
$descriptionTranslations = $translation->loadContent($object, 'description');

// Todos los métodos anteriores retornan una instancia de TranslatableContent

########## Persitencia de un TranslatableContent #########

$titleContent = $translation->newContent([
    'en' => 'Hi',
    'es' => 'Hola',
]);
$newObject = new EntityClass(); // EntityClass debe implementar TranslationsAwareInterface
$translation->refreshInDefaultLocale($newObject); // importante refrescar el objeto en el locale por defecto de la app.
$newObject->setTitle((string)$titleContent); // castear a string retorna el valor en el locale por defecto.
$persister = $translation->preparePersist($newObject);
$persister->persist('title', $titleContent);
$entityManager->persist($newObject);
$entityManager->flush();

// Actualizando traducciones

$object = $repository->find(1); // object debe implementar TranslationsAwareInterface
$translation->refreshInDefaultLocale($object); // importante refrescar el objeto en el locale por defecto de la app.
$titleTranslations = $translation->loadContent($object, 'title');

$titleTranslations->setValues(['en' => 'Other title', 'es' => 'Otro titulo']);
$descriptionTranslations = $translation->fromString('Other Description');

$persister = $translation->preparePersist($object);
$persister->persist('title', $titleTranslations);
$persister->persist('description', $titleTranslations);
$entityManager->persist($object);
$entityManager->flush();

```

##### Otras clases que se pueden usar y que están dentro de `Optime\Util\Translation\Translation` son:

 * Optime\Util\Translation\TranslatableContentFactory
    * `newInstance(array $contents = []): TranslatableContent`
    * `fromString(string $content): TranslatableContent`
    * `load(TranslationsAwareInterface $entity, string $property): TranslatableContent`
 * Optime\Util\Translation\Persister\TranslatableContentPersister
    * `prepare(TranslationsAwareInterface $targetEntity): PreparedPersister`
 * Optime\Util\Translation\Persister\PreparedPersister
    * `persist(string $property, TranslatableContent $translations): void`
    
Las clases y métodos anteriores se pueden usar directamente desde el servicio `Optime\Util\Translation\Translation`.

#### Uso en formularios:

Para poder implementar formularios con campos de traducción tenemos dos opciones.

#### `Optime\Util\Form\Type\TranslatableContentType`

Este tipo de formulario trabaja en conjunto con la clase `TranslatableContent` y lo que permite
es renderizar tantos campos como locales tenga configurada la plataforma.

**Se usa para cuando no estamos trabajando directamente con una entidad de doctrine.**

Ejemplo de uso:

```php
<?php

public function formAction(Request $request) 
{
    $data = [
        'title' => null,
        'description' => $translation->newContent(),
    ];
    
    $form = $this->createFormBuilder($data)
                 ->add('title', TranslatableContentType::class)
                 ->add('description', TranslatableContentType::class, [
                    'type' => TextareaType::class,
                 ])
                 ->getForm();
                 
    if ($form->isSubmitted()) {
        dump($form['title']->getData()); // TranslatableContent con los datos en cada idioma.
        dump($form['description']->getData()); // TranslatableContent con los datos en cada idioma.
    }
} 
```

#### `Optime\Util\Form\Type\AutoTransFieldType`

Este tipo de formulario se usa para trabajar directamente con entidades de doctrine, internamente
se encarga de cargar las traducciones del campo traducible y cuando se envia el form.
es posible persistir dichas traducciones del campo.

**Se usa para cuando estamos trabajando con una entidad de doctrine.**

Ejemplo de uso:

```php
<?php

use Optime\Util\Translation\TranslationsFormHandler;

public function formAction(Request $request, TranslationsFormHandler $formHandler) 
{
    $entityObject = new EntityClass();
    
    $form = $this->createFormBuilder($entityObject)
                 ->add('title', AutoTransFieldType::class)
                 ->add('description', AutoTransFieldType::class, [
                    'type' => TextareaType::class,
                 ])
                 ->getForm();
    $form->handleRequest($request);
                 
    if ($form->isSubmitted()) {
        dump($form['title']->getData()); // retorna el string en locale por defecto
        dump($form['description']->getData()); // retorna el string en locale por defecto
        
        // para persistir las traducciones se debe llamar a:
        $formHandler->persist($form); // si no se llama a esté método, no se guardarán las traducciones.
        $entityManager->persist($entityObject);
        $entityManager->flush();
    }
} 

public function formActionAutoSave(Request $request) 
{
    $entityObject = new EntityClass();
    
    $form = $this->createFormBuilder($entityObject)
                 ->add('title', AutoTransFieldType::class)
                 ->add('description', AutoTransFieldType::class, [
                    'auto_save' => true, // activamos guardado automatico.
                 ])
                 ->getForm();
    $form->handleRequest($request);
                 
    if ($form->isSubmitted()) {
        // No hay que hacer nada con las traducciones, el auto_save ya
        // hace el trabajo de persistirlas.
        $entityManager->persist($entityObject);
        $entityManager->flush();
    }
} 

public function formActionManualAutoSave(Request $request, TranslationsFormHandler $formHandler) 
{
    $entityObject = new EntityClass();
    
    $form = $this->createFormBuilder($entityObject, [
                      'auto_save_translations' => false, // detenemos auto save
                 ])
                 ->add('title', AutoTransFieldType::class)
                 ->add('description', AutoTransFieldType::class, [
                    'auto_save' => true,
                 ])
                 ->getForm();
    $form->handleRequest($request);
                 
    if ($form->isSubmitted()) {
        // Hacemos flush del auto save.
        // Util cuando no tenemos acceso al form y queremos
        // hacer la persitencia de los AutoTransFieldType
        // en un sitio especifico.
        $formHandler->flushAutoSave();
        $entityManager->persist($entityObject);
        $entityManager->flush();
    }
} 

```

### Consideraciones importantes al usar traducciones

Cuando estamos cargando o persistiendo traducciones es importante que las
entidades estén cargadas en el locale por defecto de la plataforma y no en el locale
de la url. Ya que de lo contrario se van a guardar los valores traducidos en locales diferentes
a los esperados.

Por lo que para poder cargar o persistir las traducciones se debe haber cargado
la entidad en el locale por defecto o usar el siguiente código para que la 
entidad se refresque en el locale por defecto:

```php
<?php

$translation = ... obtenemos el servicio Optime\Util\Translation\Translation

$object = $repository->find(1);

// importante refrescar el objeto en el locale por defecto de la app.
$translation->refreshInDefaultLocale($object);
// Se debe refrescar el objeto antes de hacerle algún cambio, ya que al refrescar
// se revierten todos los posibles cambios no guardados en la entidad.

$newContent = $translation->newContent([
    'en' => 'Hi',
    'es' => 'Hola',
]);

$object->setTitle((string)$titleContent);
$persister = $translation->preparePersist($object);
$persister->persist('title', $titleContent);
$entityManager->flush();
```

Si se intentar cargar o persistir traducciones y la entidad no está en el locale
por defecto, la app lanzará una excepción indicando el error.
