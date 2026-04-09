<?php
/**
 * @author Manuel Aguirre
 */

namespace Optime\Util\Report\ValueFormat;

/**
 * @author Manuel Aguirre
 */
class HeaderFormat extends StringFormat
{
    private bool $readOnly = false;

    public function __construct(
        $value,
        bool $centered = true,
        private readonly ?int $width = null,
    ) {
        parent::__construct($value, $centered);
    }

    public function getWidth(): ?int
    {
        return $this->width;
    }

    public function readOnly(): self
    {
        $this->readOnly = true;

        return $this;
    }

    public function isReadOnly(): bool
    {
        return $this->readOnly;
    }
}