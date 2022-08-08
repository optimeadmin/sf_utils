<?php
/**
 * @author Manuel Aguirre
 */

declare(strict_types=1);

namespace Optime\Util\Dto\Attribute;


use Attribute;

/**
 * @author Manuel Aguirre
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class DtoDependency
{
    public function __construct(public string $index, public ?string $service = null)
    {
    }
}