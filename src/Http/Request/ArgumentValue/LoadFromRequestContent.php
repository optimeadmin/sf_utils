<?php
/**
 * @author Manuel Aguirre
 */

declare(strict_types=1);

namespace Optime\Util\Http\Request\ArgumentValue;

use Attribute;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;

/**
 * @author Manuel Aguirre
 * @deprecated Use MapRequestPayload instead.
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