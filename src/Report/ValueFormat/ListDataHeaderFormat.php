<?php
/**
 * @author Manuel Aguirre
 */

namespace Optime\Util\Report\ValueFormat;

/**
 * @author Manuel Aguirre
 */
class ListDataHeaderFormat extends HeaderFormat
{
    public function __construct(
        $title,
        private readonly iterable $values,
        private readonly bool $restrictValues = false,
        bool $centered = true,
        ?int $width = null,
    ) {
        parent::__construct($title, $centered, $width);
    }

    public function getValues(): iterable
    {
        return $this->values;
    }

    public function isRestrictValues(): bool
    {
        return $this->restrictValues;
    }
}