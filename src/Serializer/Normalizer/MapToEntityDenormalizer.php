<?php
/**
 * @author Manuel Aguirre
 */

declare(strict_types=1);

namespace Optime\Util\Serializer\Normalizer;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\Persistence\ObjectRepository;
use Optime\Util\Exception\ValidationException;
use Optime\Util\Serializer\Attribute\DeserializeObject;
use Optime\Util\Serializer\DeserializeObjectsAwareInterface;
use ReflectionClass;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\ContextAwareDenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use function array_key_exists;
use function array_keys;
use function is_array;
use function is_subclass_of;

/**
 * @author Manuel Aguirre
 */
#[AutoconfigureTag("serializer.normalizer", ['priority' => 10])]
class MapToEntityDenormalizer implements ContextAwareDenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    public const KEY = 'map_to_entity';
    public const REPOSITORY_METHOD = 'repository_method';
    public const PRIMARY_KEY = 'primary_key';
    public const ERROR_MESSAGE = 'error_message';

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly PropertyAccessorInterface $propertyAccessor,
    ) {
    }

    public function supportsDenormalization($data, string $type, string $format = null, array $context = []): bool
    {
        return isset($context[self::KEY]);
    }

    public function denormalize($data, string $type, string $format = null, array $context = [])
    {
        if (!$data) {
            return is_array($data) ? [] : null;
        }

        $entityClass = is_string($context[self::KEY]) ? $context[self::KEY] : $type;
        $repository = $this->entityManager->getRepository($entityClass);
        $method = $context[self::REPOSITORY_METHOD] ?? 'find';

        if (is_array($data)) {
            $items = $this->getValues($repository, $data, $method, $context);

            if (is_a($type, ArrayCollection::class, true)) {
                return new ArrayCollection($items);
            }

            return $items;
        }

        return $this->getValue($repository, $data, $method, $context);
    }

    private function getValue(ObjectRepository $repository, $data, string $method, array $context): mixed
    {
        $object = $repository->{$method}($data);

        if (!$object) {
            $errorMessage = $context[self::ERROR_MESSAGE] ?? 'Value "'.$data.'" not found';
            throw ValidationException::create($errorMessage, $context['deserialization_path'] ?? '');
        }

        return $object;
    }

    private function getValues(ObjectRepository $repository, $data, string $method, array $context): array
    {
        $idKey = $context[self::PRIMARY_KEY] ?? 'id';
        $method = $context[self::REPOSITORY_METHOD] ?? 'findBy';

        $items = $repository->{$method}([$idKey => $data]);

        if (count($items) >= count(($data))) {
            return $items;
        }

        $itemsValues = array_map(fn($item) => $this->propertyAccessor->getValue($item, $idKey), $items);
        $missingValues = array_diff($data, $itemsValues);

        $errorMessage = $context[self::ERROR_MESSAGE] ?? 'Values ('.join(', ', $missingValues).') not found';
        throw ValidationException::create($errorMessage, $context['deserialization_path'] ?? '');
    }
}
