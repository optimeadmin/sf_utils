<?php
/**
 * @author Manuel Aguirre
 */

declare(strict_types=1);

namespace Optime\Util\Serializer\Normalizer;

use Doctrine\ORM\EntityManagerInterface;
use Optime\Util\Serializer\Attribute\DeserializeObject;
use Optime\Util\Serializer\DeserializeObjectsAwareInterface;
use ReflectionClass;
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
class RequestObjectDenormalizer implements ContextAwareDenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    private const DENORMALIZING = '__request_object_denormalization__';

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly PropertyAccessorInterface $propertyAccessor,
    ) {
    }

    public function supportsDenormalization($data, string $type, string $format = null, array $context = []): bool
    {
        if (isset($context[self::DENORMALIZING])) {
            return false;
        }

        if (!is_array($data)) {
            return false;
        }

        return is_subclass_of($type, DeserializeObjectsAwareInterface::class, true);
    }

    public function denormalize($data, string $type, string $format = null, array $context = [])
    {
        $attributes = $this->getAttributes($type);

        $object = $this->denormalizer->denormalize($data, $type, $format, $context + [
                self::DENORMALIZING => true,
                AbstractNormalizer::IGNORED_ATTRIBUTES => array_keys($attributes),
            ]
        );

        foreach ($attributes as $property => $attribute) {
            if (!array_key_exists($property, $data)) {
                continue;
            }
            if (!$this->propertyAccessor->isWritable($object, $property)) {
                continue;
            }

            $this->propertyAccessor->setValue($object, $property, $this->loadValue($attribute, $data[$property]));
        }

        return $object;
    }

    private function getAttributes(string $class): array
    {
        $attributes = [];
        $reflection = new ReflectionClass($class);

        foreach ($reflection->getProperties() as $property) {
            if ($propertyAttributes = $property->getAttributes(DeserializeObject::class)) {
                $attributes[$property->getName()] = $propertyAttributes[0]->newInstance();
            }
        }

        return $attributes;
    }

    private function loadValue(DeserializeObject $config, mixed $value): mixed
    {
        $repository = $this->entityManager->getRepository($config->entity);
        $index = $config->path ?? $config->property ?? 'id';

        if (is_array($value)) {
            $id = $value[$index] ?? null;
        } else {
            $id = (string)$value;
        }

        if (!$id) {
            return null;
        }

        if ($config->repositoryMethod) {
            return $repository->{$config->repositoryMethod}($id);
        }

        $idName = $config->property ?? 'id';

        return $repository->findOneBy([$idName => $id]);
    }
}
