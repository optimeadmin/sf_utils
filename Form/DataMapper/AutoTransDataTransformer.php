<?php
/**
 * @author Manuel Aguirre
 */

namespace Optime\Util\Form\DataMapper;

use Optime\Util\Translation\Translation;
use Symfony\Component\Form\DataTransformerInterface;
use function dd;
use function get_call_stack;

/**
 * @author Manuel Aguirre
 */
class AutoTransDataTransformer implements DataTransformerInterface
{
    /**
     * @var Translation
     */
    private $translation;
    /**
     * @var string
     */
    private $propertyPath;
    /**
     * @var object|null
     */
    private $targetObject;
    /**
     * @var bool
     */
    private $mapped;

    public function __construct(
        Translation $translation,
        string $propertyPath,
        bool $mapped
    ) {
        $this->translation = $translation;
        $this->propertyPath = $propertyPath;
        $this->mapped = $mapped;
    }

    public function setTargetObject(?object $targetObject): void
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