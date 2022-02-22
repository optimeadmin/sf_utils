<?php
/**
 * @author Manuel Aguirre
 */

namespace Optime\Util\Translation;

use LogicException;
use Optime\Util\Form\Type\AutoTransFieldType;
use RecursiveIteratorIterator;
use Symfony\Component\Form\Exception\InvalidArgumentException;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\Util\InheritDataAwareIterator;
use WeakMap;
use function count;
use function get_class;
use function gettype;
use function is_object;
use const PHP_EOL;

/**
 * @author Manuel Aguirre
 */
class TranslationsFormHandler
{
    private WeakMap $pendingForPersist;

    public function __construct(
        private Translation $translation,
    ) {
        $this->pendingForPersist = new WeakMap();
    }

    public function persist(FormInterface $form): void
    {
        $data = $form->getData();

        if (!is_object($data)) {
            return;
        }

        $forms = new RecursiveIteratorIterator(new InheritDataAwareIterator($form));

        /** @var FormInterface $child */
        foreach ($forms as $child) {
            if (!$child->getConfig()->getMapped()) {
                continue;
            }

            $formType = $child->getConfig()->getType()->getInnerType();

            if ($formType instanceof AutoTransFieldType) {
                $this->forAutoSave($child);
            } elseif ($child->getConfig()->getCompound()) {
                $this->persist($child);
            }
        }

        $this->flushAutoSave();
    }

    public function forAutoSave(FormInterface $form): void
    {
        if ($form->isRoot()) {
            throw new InvalidArgumentException("No se puede pasar un form completo");
        }

        if (!$form->getConfig()->getType()->getInnerType() instanceof AutoTransFieldType) {
            throw new InvalidArgumentException(
                "Solo se aceptan forms de tipo: " . AutoTransFieldType::class .
                " Pero llegó: " . get_class($form->getConfig()->getType()->getInnerType())
            );
        }

        if (!$form->isValid()) {
            return;
        }

        $translations = $form->getNormData();

        if (!$translations instanceof TranslatableContent) {
            return;
        }

        $propertyPath = (string)$form->getPropertyPath();
        $entity = $form->getParent()->getData();

        if (!is_object($entity)) {
            throw new LogicException(
                "No se puede hacer uso del " . $this::class .
                " si el valor del form no es un objeto. Valor Actual: " . gettype($entity) . PHP_EOL .
                "Si se está creando un nuevo registro, se debe pasar una instancia nueva al form al momento " .
                "de crearlo 'createForm(FormType::class, new Entity())'"
            );
        }

        if (!isset($this->pendingForPersist[$entity]['fields'][$propertyPath])) {
            if (!isset($this->pendingForPersist[$entity]['object'])) {
                $this->pendingForPersist[$entity]['object'] = $entity;
            }

            $this->pendingForPersist[$entity]['fields'][$propertyPath] = $translations;
        }
    }

    public function flushAutoSave(): void
    {
        foreach ($this->pendingForPersist as $config) {
            $persister = $this->translation->preparePersist($config['object']);

            foreach ($config['fields'] as $propertyPath => $translations) {
                $persister->persist($propertyPath, $translations);
            }
        }

        if (0 === count($this->pendingForPersist)) {
            $this->pendingForPersist = new WeakMap();
        }
    }
}