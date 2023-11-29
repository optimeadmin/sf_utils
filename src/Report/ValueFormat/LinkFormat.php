<?php
/**
 * @author Manuel Aguirre
 */

namespace Optime\Util\Report\ValueFormat;

/**
 * @author Manuel Aguirre
 */
class LinkFormat extends StringFormat
{
    public function __construct(string $value, private string $link)
    {
        parent::__construct($value);
    }

    public function getLink(): string
    {
        return $this->link;
    }
}