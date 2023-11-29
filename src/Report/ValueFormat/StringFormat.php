<?php
/**
 * @author Manuel Aguirre
 */

namespace Optime\Util\Report\ValueFormat;

/**
 * @author Manuel Aguirre
 */
class StringFormat
{
    private bool $bold = false;
    private ?string $color = null;

    public function __construct(private $value, private readonly bool $centered = false)
    {
        $this->value = (string)$this->value;
    }

    public function __toString()
    {
        return (string)$this->value;
    }

    /**
     * @return bool
     */
    public function isCentered(): bool
    {
        return $this->centered;
    }

    public function textBold(): self
    {
        $this->bold = true;

        return $this;
    }

    public function color(string $color): self
    {
        $this->color = $color;

        return $this;
    }

    public function isBold(): bool
    {
        return $this->bold;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }
}