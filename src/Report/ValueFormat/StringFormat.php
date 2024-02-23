<?php
/**
 * @author Manuel Aguirre
 */

namespace Optime\Util\Report\ValueFormat;

use Symfony\Contracts\Translation\TranslatableInterface;

/**
 * @author Manuel Aguirre
 */
class StringFormat
{
    private bool $bold = false;
    private ?string $color = null;
    private ?string $bgColor = null;
    private ?string $alignment = null;

    public function __construct(private $value, private readonly bool $centered = false)
    {
        if (!$this->value instanceof TranslatableInterface) {
            $this->value = (string)$this->value;
        }
    }

    public function __toString()
    {
        return (string)$this->value;
    }

    public function getValue()
    {
        return $this->value;
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

    public function bgColor(string $bgColor): self
    {
        $this->bgColor = $bgColor;

        return $this;
    }

    public function alignment(string $alignment): self
    {
        $this->alignment = $alignment;

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

    public function getBgColor(): ?string
    {
        return $this->bgColor;
    }

    public function getAlignment(): ?string
    {
        return $this->alignment;
    }
}