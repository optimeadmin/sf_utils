<?php
/**
 * @author Manuel Aguirre
 */

namespace Optime\Util\Http\Controller;

use Attribute;

/**
 * @author Manuel Aguirre
 */
#[Attribute(Attribute::TARGET_METHOD)]
class PartialAjaxView
{
    public function __construct(
        private string $name = 'default'
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }
}