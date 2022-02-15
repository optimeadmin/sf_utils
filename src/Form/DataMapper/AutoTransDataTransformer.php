<?php
/**
 * @author Manuel Aguirre
 */

namespace Optime\Util\Form\DataMapper;

use Optime\Util\Translation\Translation;
use Optime\Util\Translation\TranslationsAwareInterface;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * @author Manuel Aguirre
 */
class AutoTransDataTransformer implements DataTransformerInterface
{
    private TranslationsAwareInterface|null $targetObject;

    public function __construct(
        private Translation $translation,
        private string $propertyPath,
        private bool $mapped
    ) {
    }

    public function setTargetObject(?TranslationsAwareInterface $targetObject): void
    {
        $this->targetObject = $targetObject;
    }

    public function hasTargetObject(): bool
    {
        return null !== $this->targetObject;
    }

    public function transform($value)
    {
        if ($this->mapped && $this->hasTargetObject()) {
            return $this->translation->loadContent($this->targetObject, $this->propertyPath);
        }

        if (null !== $value) {
            return $this->translation->fromString($value);
        }

        return $this->translation->newContent();
    }

    public function reverseTransform($value)
    {
        if (null == $value) {
            return null;
        }

        return (string)$value;
    }
}