<?php
/**
 * @author Manuel Aguirre
 */

namespace Optime\Util\Translation;

use Optime\Util\Form\Type\AutoTransFieldType;
use RecursiveIteratorIterator;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\Util\InheritDataAwareIterator;
use function is_object;

/**
 * @author Manuel Aguirre
 */
class TranslationsFormHandler
{
    /**
     * @var Translation
     */
    private $translation;

    public function __construct(Translation $translation)
    {
        $this->translation = $translation;
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
                $this->persistTranslation($data, $child);
            } elseif ($child->getConfig()->getCompound()) {
                $this->persist($child);
            }
        }
    }

    private function persistTranslation(object $entity, FormInterface $form): void
    {
        $translations = $form->getNormData();
        $propertyPath = $form->getPropertyPath();

        if (!$translations instanceof TranslatableContent) {
            return;
        }

        $this->translation->preparePersist($entity)->persist($propertyPath, $translations);
    }
}