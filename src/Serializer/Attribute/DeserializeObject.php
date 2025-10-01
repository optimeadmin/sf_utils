<?php
/**
 * @author Manuel Aguirre
 */

declare(strict_types=1);

namespace Optime\Util\Serializer\Attribute;

use Attribute;

/**
 * @author Manuel Aguirre
 * @deprecated Usar MapToEntityDenormalizer
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class DeserializeObject
{
    public function __construct(
        public readonly string $entity,
        public readonly ?string $property = null,
        public readonly ?string $path = null,
        public readonly ?string $repositoryMethod = null,
    ) {
    }
}