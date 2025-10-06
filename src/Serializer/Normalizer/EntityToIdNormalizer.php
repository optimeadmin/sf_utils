<?php
/**
 * @author Manuel Aguirre
 */

declare(strict_types=1);

namespace Optime\Util\Serializer\Normalizer;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author Manuel Aguirre
 * @method array getSupportedTypes(?string $format)
 */
#[AutoconfigureTag("serializer.normalizer", ['priority' => 10])]
class EntityToIdNormalizer implements NormalizerInterface
{
    public const KEY = 'entity_to_id';

    public function __construct(
        private readonly PropertyAccessorInterface $propertyAccessor,
    ) {
    }

    public function normalize(mixed $object, ?string $format = null, array $context = []): int|string|array|null
    {
        $idKey = is_string($context[self::KEY] ?? null) ? $context[self::KEY] : 'id';

        if (!$object && is_iterable($object)) {
            return [];
        }

        if (!$object) {
            return null;
        }

        if (is_iterable($object)) {
            return $this->mapItems($object, $idKey);
        }

        return $this->propertyAccessor->getValue($object, $idKey);
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {;
        return isset($context[self::KEY]);
    }

    private function mapItems(iterable $data, string $idKey): array
    {
        $ids = [];

        foreach ($data as $item) {
            $ids[] = $this->propertyAccessor->getValue($item, $idKey);
        }

        return $ids;
    }
}
