<?php
/**
 * @author Manuel Aguirre
 */

declare(strict_types=1);

namespace Optime\Util\Http\Request\ArgumentValue;

use Attribute;

/**
 * @author Manuel Aguirre
 */
#[Attribute(Attribute::TARGET_PARAMETER)]
class LoadFromRequestContent
{
    public function __construct(
        private ?string $class = null
    ) {
    }

    public function getClass(): ?string
    {
        return $this->class;
    }
}