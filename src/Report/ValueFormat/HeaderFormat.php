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
    private ?int $width;

    public function __construct($value, bool $centered = true, int $width = null)
    {
        parent::__construct($value, $centered);
        $this->width = $width;
    }

    public function getWidth(): ?int
    {
        return $this->width;
    }
}