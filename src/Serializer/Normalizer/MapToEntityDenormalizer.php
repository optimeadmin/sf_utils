<?php
/**
 * @author Manuel Aguirre
 */

declare(strict_types=1);

namespace Optime\Util\Serializer\Normalizer;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;
use Optime\Util\Exception\ValidationException;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Serializer\Normalizer\ContextAwareDenormalizerInterface;
use function array_key_exists;
use function is_array;

/**
 * @author Manuel Aguirre
 */
#[AutoconfigureTag("serializer.normalizer", ['priority' => 10])]
class MapToEntityDenormalizer implements ContextAwareDenormalizerInterface
{
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
        $isArray = str_ends_with($type, '[]');

        if (!$data) {
            if ($isArray) {
                return [];
            }

            return null;
        }

        $entityClass = is_string($context[self::KEY]) ? $context[self::KEY] : $type;
        $repository = $this->entityManager->getRepository($entityClass);
        $method = $context[self::REPOSITORY_METHOD] ?? 'find';
        $idKey = $context[self::PRIMARY_KEY] ?? 'id';

        if ($isArray) {
            $items = $this->getValues($repository, $data, $method, $context);

            if (is_a($type, ArrayCollection::class, true)) {
                return new ArrayCollection($items);
            }

            return $items;
        }

        if (is_array($data) && array_key_exists($idKey, $data)) {
            // Si existe el key de id, entonces es un array asociativo
            // y el id viene como data['id'], por lo que lo tratamos como un unico valor
            return $this->getValue($repository, $data[$idKey], $method, $context);
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

    private function getValues(ObjectRepository $repository, array $data, string $method, array $context): array
    {
        $idKey = $context[self::PRIMARY_KEY] ?? 'id';
        $method = $context[self::REPOSITORY_METHOD] ?? 'findBy';

        $formattedData = array_map(function ($item) use ($idKey) {
            // Si es un array asociativo, buscamos el id
            // Si existe el key de id, entonces retornamos ese valor
            if (is_array($item) && array_key_exists($idKey, $item)) {
                return $item[$idKey];
            }

            return $item;
        }, $data);

        $items = $repository->{$method}([$idKey => $formattedData]);

        if (count($items) >= count(($data))) {
            return $items;
        }

        $itemsValues = array_map(fn($item) => $this->propertyAccessor->getValue($item, $idKey), $items);
        $missingValues = array_diff($formattedData, $itemsValues);

        $errorMessage = $context[self::ERROR_MESSAGE] ?? 'Values ('.join(', ', $missingValues).') not found';
        throw ValidationException::create($errorMessage, $context['deserialization_path'] ?? '');
    }
}
