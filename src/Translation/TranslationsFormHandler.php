<?php
/**
 * @author Manuel Aguirre
 */

namespace Optime\Util\Translation;

use Optime\Util\Form\Type\AutoTransFieldType;
use RecursiveIteratorIterator;
use Symfony\Component\Form\Exception\InvalidArgumentException;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\Util\InheritDataAwareIterator;
use function get_class;
use function is_object;
use function spl_object_id;

/**
 * @author Manuel Aguirre
 */
class TranslationsFormHandler
{
    private array $pendingForPersist;

    public function __construct(
        private Translation $translation,
    ) {
        $this->pendingForPersist = [];
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
        $key = $this->generatePendingKey($entity);

        if (!isset($this->pendingForPersist[$key]['fields'][$propertyPath])) {
            if (!isset($this->pendingForPersist[$key]['object'])) {
                $this->pendingForPersist[$key]['object'] = $entity;
            }

            $this->pendingForPersist[$key]['fields'][$propertyPath] = $translations;
        }
    }

    public function flushAutoSave(): void
    {
        foreach ($this->pendingForPersist as $config) {
            $persister = $this->translation->preparePersist($config['object']);

            foreach ($config['fields'] as $propertyPath => $translations) {
                $persister-> persist($propertyPath, $translations);
            }
        }

        $this->pendingForPersist = [];
    }

    private function generatePendingKey(object $entity): string
    {
        return spl_object_id($entity);
    }
}