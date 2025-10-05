# SF Utils
Repo con clases de utilidad para proyectos Symfony >= 5

## Instalación

```
composer require optimeconsulting/sf-utils ~6.0@dev
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
    use_ajax_twig_loader: false # Si es true, activa el ajax_loader para twig
#   mailer:
#      allowed_recipients: '%env(MAILER_ALLOWED_RECIPIENTS)%' ej: MAILER_ALLOWED_RECIPIENTS="*@domain.com,user@domain2.com
#      fallback_recipients: '%env(MAILER_FALLBACK_RECIPIENTS)%' valores separados por comas
```

#### Rutas (opcional)

Si queremos acceso a temas de cache, crear el archivo `config/routes/optime_utils.yaml`:

```yaml
optime_utils:
  resource: "@OptimeUtilBundle/Controller/"
  type: attribute
  prefix: /{_locale}/admin/system
```

#### Configuración adicional para usar traducciones de entidades

**IMPORTANTE** tener en cuenta que para la fecha (Mayo-2024) estas extensiones de doctrine no funcionan bien con doctrine >= 3.0, se debe usar 2.x.

Se debe instalar la libreria https://symfony.com/bundles/StofDoctrineExtensionsBundle/current/installation.html

Configurar la extension de traducciones:

config/packages/doctrine.yaml

```yaml
doctrine:
    orm:
        entity_managers:
            default:
                mappings:
                    gedmo_translatable:
                        type: attribute
                        prefix: Gedmo\Translatable\Entity
                        dir: "%kernel.project_dir%/vendor/gedmo/doctrine-extensions/src/Translatable/Entity"
                        alias: GedmoTranslatable # (optional) it will default to the name set for the mapping
                        is_bundle: false
```

config/packages/stof_doctrine_extensions.yaml

```yaml
stof_doctrine_extensions:
    default_locale: en
    orm:
        default:
            translatable: true
```

<hr>

## Uso

#### `Optime\Util\Exception\DomainException`

Clase para cuando se necesitan lanzar excepciones de dominio, es decir, excepciones
que serán capturadas y controladas como parte del flujo de un
proceso Errores de negocio (aprobar algo ya aprobado, rechazar algo
que no se puede rechazar, salgo insuficiente, etc).

<hr>

### `Optime\Util\Exception\ValidationException`

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

<hr>

### `Optime\Util\Batch\BatchProcessingResult`

Clase de utilidad que sirve para obtener información del resultado de un proceso por lotes.
Por ejemplo al cargar un CSV, podemos reflejar en dicha clase los elementos procesados
correctamente y los que tuvieron algún problema de procesamiento.

<hr>

### `Optime\Util\Validator\DomainValidator`

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

<hr>

### `Optime\Util\TranslatableMessage`

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
<hr>

### Traducciones en formularios:

Hay ciertas clases de utilidad para trabajar con campos traducibles, enfocado
a la extensión de traducción de Doctrine pero que puede usarse de forma
generica.

#### Importante

Para usar las traducciones en entidades, este bundle requiere de las extensiones de doctrine, especificamente la de traducciones, para ello instalar y seguir la documentación del bundle [StofDoctrineExtensionsBundle](https://symfony.com/bundles/StofDoctrineExtensionsBundle/current/index.html).

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
    
    $form = $this->createFormBuilder($entityObject, [
                'auto_save_translations' => true, // activamos guardado automatico.
            ])
                 ->add('title', AutoTransFieldType::class)
                 ->add('description', AutoTransFieldType::class, [
                    'auto_save' => true, // activamos guardado automatico para este campo.
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
    
    $form = $this->createFormBuilder($entityObject)
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

<hr>

### Atributos para controladores

#### `Optime\Util\Http\Controller\HandleAjaxForm`

Clase de tipo atributo que permite cambiar el status de la respuesta de 
un formulario invalido a 400 (Bad Request) cuando la petición es ajax. 
Esto es útil para cuando se está tratando un formulario por medio de 
un ajax y se quiere saber por javascript si hubo errores 
de validación y así mostrar dichos errores en el cliente sin recargar 
la página.

Por otro lado, esta clase permite detener las redirecciones cuando
son peticiones ajax, por ejemplo cuando se guarda un form y se hace
un redirect, podemos detener el redirect y convertir la respuesta a un
status 200 para cuando sea por ajax el envío del form.

##### Ejemplos:

```php
// Controlador:

use Optime\Util\Http\Controller\HandleAjaxForm;

#[HandleAjaxForm]
public function formAction(Request $request)
{
    $form = $this->createForm(FormType::class);
    $form->handleRequest($request);
                 
    if ($form->isSubmitted() and $form->isValid()) {
        $entityManager->persist($form->getData());
        $entityManager->flush();
    }
    
    ...
}
```

```js
$.post('url', $form.serialize()).done(html => {
    // guardado con exito.
}).fail(res => {
    // Usar el atributo #[HandleAjaxForm] en el controlador.
    // hace que la petición ajax devuelva un statusCode 400
    // cuando el formulario es inválido
    if (res.status == 422) {
        // Si es Bad Request, actualizamos html con errores de
        // validación
        $form.html(res.responseText);
    }
})
```

Esta clase recibe varios argumentos en el constructor:

```php
use Optime\Util\Http\Controller\HandleAjaxForm;

#[HandleAjaxForm(
    type: string|null,
    invalidStatus: Response::HTTP_UNPROCESSABLE_ENTITY,
    preventRedirect: true,
    replaceRedirectContent: true
)]
public function formAction(Request $request);
```
 * `type` null por defecto, se puede indicar un tipo de form para que solo 
   se active el handler cuando el form coincida con el tipo. Esto es últil
   solo si la acción está manejando multiples formularios.
 * `invalidStatus` Status http a retornar cuando el form sea invalido.
   por defecto retorna el 400.
 * `preventRedirect` Indica si se debe evitar cualquier redirect al enviar
   y procesar el formulario de form exitosa.
 * `replaceRedirectContent` Indica si se debe reemplazar el contenido de la
   respuesta cuando es un redirect o no. Si está true, el contenido de 
   la respuesta será un "Ok".

#### `Optime\Util\Http\Controller\PartialAjaxView`

Clase de tipo atributo que permite retornar de una respuesta html con
twig solo una parte o varias partes de dicho html y no todo su contenido.

Esto es últil para cuando se tiene una página que puede cargar tanto
de forma directa con la url en un navegador, como por medio de un ajax 
con javascript y que solo necesitemos una parte de dicha página html.

##### Ejemplos:

```php
// Controlador:

use Optime\Util\Http\Controller\PartialAjaxView;

#[HandleAjaxForm]
#[PartialAjaxView]
public function formAction(Request $request)
{
    $form = $this->createForm(FormType::class);
    $form->handleRequest($request);
                 
    if ($form->isSubmitted() and $form->isValid()) {
        $entityManager->persist($form->getData());
        $entityManager->flush();
    }
    
    ...
}
```

```jinja
{% extends 'layout.html.twig' %}

{% block body %}
   <div class="container">
      <h1>Titulo Form</h1>
      
      {% ajax_view %}
         {# 
            El tag ajax_view permite que si la peticióne es ajax,
            solo se devuelva como contenido html lo que haya dentro
            del tag.
         #}
         {{form(form)}}
      {% end_ajax_view %}
       
   </div>
{% endblock body %}
```

```js
$.post('url', $form.serialize()).fail(res => {
    if (res.status == 400) {
        // Si es Bad Request, actualizamos el form.
        // Lo importante acá es que el html retornado solo
        // contiene lo que hay dentro del tag "ajax_view"
        $form.html(res.responseText);
    }
})
```

Esta clase recibe varios argumentos en el constructor:

```php
use Optime\Util\Http\Controller\PartialAjaxView;

#[PartialAjaxView(
    name: 'default' string|array,
    method: null|string,
    ignoreOnEmpty: false,
)]
public function formAction(Request $request);
```
* `name` 'default' por defecto, sirve para indicar la o las secciones 
  que vamos a extraer del twig, contenidas en los tag "ajax_view".
* `method` permite indicar un método http (get, post, etc) para que
  el atributo solo se active si la petición es del tipo indicado.
* `ignoreOnEmpty` Si es true, significa que si no se encontró la etiqueta
  ajax_view en el twig o su contenido está vacio, se debe retornar la
  pagina completa. Si es false, retornaría un string vacio.

#### Otros ejemplos de uso:

Para los siguentes ejemplos vamos a partir del siguiente twig:

```jinja
{% extends 'layout.html.twig' %}

{% block body %}
   <div class="container">
      {% ajax_view %}
      
         {% ajax_view header %}
            <h1>Titulo Form</h1>
         {% end_ajax_view %}
         
         {% ajax_view table %}
            <table>
               ...
            </table>
         {% end_ajax_view %}  
         
      {% end_ajax_view %}  
   </div>
{% endblock body %}
```

Este twig tiene tres etiquetas ajax_view, dos con nombre (header y table)
y una sin nombre, que toma por defecto el nombre "default" al no indicarle
nada.

##### Ejemplo de especificar la sección a retornar:

```php
use Optime\Util\Http\Controller\PartialAjaxView;

#[PartialAjaxView("table")]
public function index(Request $request);
```

En este casi, si la petición es ajax, solo se retornará la parte
envuelta en el tag `{% ajax_view table %}` aunque hayan varias etiquetas.
en dicho twig.

##### Retornar una sección dependiendo del tipo de petición:

```php
use Optime\Util\Http\Controller\PartialAjaxView;

#[PartialAjaxView("table", method: "post")]
#[PartialAjaxView("header")]
public function index(Request $request);

#[PartialAjaxView("table", method: "post")]
#[PartialAjaxView]
public function other(Request $request);
```

Para el método index, si la petición es ajax y de tipo "post", se va
a retornar la sección "table", de resto se va a retornar la sección "header".

Para el método other, si la petición es ajax y de tipo "post", se va
a retornar la sección "table", de resto se va a retornar la sección "default".

##### Retornar varias secciones:

```php
use Optime\Util\Http\Controller\PartialAjaxView;

#[PartialAjaxView(["header", "table"])]
public function index(Request $request);
```

Este es una caso particular, y si se desean retornar varias secciones,
se deben pasar como un arreglo al atributo, esto va hacer que cuando la
petición sea ajax, el resultado va a ser una respuesta de tipo json con
las secciones como indices de un objeto json y los valores van a ser los
contenidos html de dichas secciones.

Esto es útil si por ejemplo tenemos una página con un filtro por ajax, y
necesitamos que al filtrar, se actualize un listado y un contador que está
alejadod e dicho listado, entonces podemos tener dos secciones a retornar,
una con el listado y otra con el contador:

```php
use Optime\Util\Http\Controller\PartialAjaxView;

#[PartialAjaxView(["list_counter", "list_data"])]
public function index(Request $request);
```
```jinja
{% extends 'layout.html.twig' %}

{% block body %}
   <div class="container">
      
         <h1>
            Titulo Form
            {% ajax_view list_counter %}
               <span>#{{ items|count }}</span>
            {% end_ajax_view %}
         </h1>
      
      {% ajax_view list_data %}
         <table>
            ...
         </table>
      {% end_ajax_view %}
      
   </div>
{% endblock body %}
```
```js
$.get('ajax-page').done(json => {
    // podemos hacer lo que consideremos, actualizar esas secciones, etc.
    console.log(json.list_counter); // <span>#24</span>
    console.log(json.list_data); // <table>...</table>
});
```

<hr>

#### RequestDeserializer

Clase de utilidad para metodos `patch` poder actualizar solo algunas propiedades de un recurso.<br/>
Todos los datos se cargan desde el Request de la petición.<br/>
La clase completa es: `Optime\Util\Serializer\RequestDeserializer`

##### Ejemplos:

```php
#[Route('/countries/{id<\d+>}', methods: ['PATCH'])]
public function update(Country $country, RequestDeserializer $deserializer): Response
{
    $dto = CountryDto::fromEntity($country);
    $deserializer->fromPayload($dto);
    // Si se pasa un objeto, este se actualiza
    ...
}

#[Route('/countries/{id<\d+>}', methods: ['PATCH'])]
public function update(Country $country, RequestDeserializer $deserializer): Response
{
    $dto = CountryDto::fromEntity($country);
    $dto = $deserializer->fromPayload($dto);
    // Tambien se puede obtener el valor retornado, que en este caso es el mismo que se pasa como argumento
    ...
}


#[Route('/countries/{id<\d+>}', methods: ['PATCH'])]
public function update(Country $country, RequestDeserializer $deserializer): Response
{
    $dto = $deserializer->fromPayload(CountryDto::class);
    // Si se pasa un string de una clase, se crea y retorna dicha clase con los datos
    ...
}
```

<hr>

#### MapToEntity

Utilidad para mapear datos de una propiedad de un DTO a una entidad.

##### Ejemplos:

```php
class PlayerDto
{
    #[Context(['map_to_entity' => true])]
    private ?Country $country = null;
    
    // Se puede indicar la entidad en el contexto
    #[Context(['map_to_entity' => State::class])]
    private $state = null;
    
    // Se puede indicar la entidad en el contexto
    #[Context([
        'map_to_entity' => City::class,
        'repository_method' => 'findBy' // metodo del repositorio para buscar la entidad, por defecto es 'find' o 'findOneBy' si es un array
        'primary_key' => 'cityId' // campo de clave primaria, por defecto es 'id'
        'error_message' => 'City not found' // mensaje de error si no se encuentra la entidad
    ])]
    private ?City $city = null;
   
    // Tambien funciona con arrays o Collecciones de doctrine
    // IMPORTANTE: si es un array, o iterable debe documentarse el tipo para que el serializer funcione bien.
    
    /**
     * @var Medal[]|null 
     */
    #[Context(['map_to_entity' => Medal::class])]
    private ?array $medals = null;
    
    /**
     * @var Tags[]|null 
     */
    #[Context(['map_to_entity' => Tags::class])]
    private ?ArrayCollection $tags = null;
}

```

