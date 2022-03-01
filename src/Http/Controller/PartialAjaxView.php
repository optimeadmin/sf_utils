<?php
/**
 * @author Manuel Aguirre
 */

namespace Optime\Util\Http\Controller;

use Attribute;

/**
 * @author Manuel Aguirre
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class PartialAjaxView
{
    public function __construct(
        private string|array $name = 'default',
        private string|null $method = null,
        private bool $ignoreOnEmpty = false,
    ) {
    }

    public function getName(): string|array
    {
        return $this->name;
    }

    public function getMethod(): ?string
    {
        return $this->method;
    }

    public function isIgnoreOnEmpty(): bool
    {
        return $this->ignoreOnEmpty;
    }
}