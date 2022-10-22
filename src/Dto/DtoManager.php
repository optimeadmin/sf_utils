<?php
/**
 * @author Manuel Aguirre
 */

declare(strict_types=1);

namespace Optime\Util\Dto;

use LogicException;
use Optime\Util\Dto\Attribute\DtoDependency;
use ReflectionMethod;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Traversable;
use function array_keys;
use function array_map;
use function count;
use function is_iterable;
use function is_subclass_of;
use function iterator_to_array;

/**
 * @author Manuel Aguirre
 */
class DtoManager
{
    private array $loaded = [];

    public function __construct(
        private ValidatorInterface $validator
    ) {
    }

    public function toDto(string $dtoClass, iterable|object $data, array $dependencies = []): null|object|array
    {
        if (!is_subclass_of($dtoClass, DtoInterface::class)) {
            throw new \InvalidArgumentException($dtoClass . " Debe ser una subclase de " . DtoInterface::class);
        }

        if (is_iterable($data)) {
            $data = $data instanceof Traversable ? iterator_to_array($data) : $data;

            return array_map(fn($item) => $this->toDto($dtoClass, $item, $dependencies), $data);
        } elseif ($dtoClass::supportsSource($data)) {
            $dependencies = $this->getDependencies(
                $dtoClass,
                'createFromSource',
                $dependencies,
            );
            return $dtoClass::createFromSource($data, $dependencies);
        }

        return null;
    }

    public function writeSource(DtoInterface $dto, ?object $source, array $dependencies = []): object
    {
        if ($source && !$dto::supportsSource($source)) {
            throw new \InvalidArgumentException($dto::class . " No soporta el source " . $source::class);
        }

        $errors = $this->validator->validate($dto);

        if (0 !== count($errors)) {
            throw new ValidationFailedException($dto, $errors);
        }

        $dependencies = $this->getDependencies(
            $dto::class,
            'writeSource',
            $dependencies,
        );

        $dto->writeSource($source, $dependencies);

        return $source;
    }

    private function getDependencies(string $dtoClass, string $method, array $dependencies): array
    {
        if (isset($this->loaded[$dtoClass][$method])) {
            return $this->loaded[$dtoClass][$method];
        }

        $ref = new ReflectionMethod($dtoClass, $method);
        $attributes = $ref->getAttributes(DtoDependency::class);

        foreach ($attributes as $attribute) {
            /** @var DtoDependency $attr */
            $attr = $attribute->newInstance();

            if (!isset($dependencies[$attr->index])) {
                throw new LogicException(sprintf(
                    "Se requiere la dependencia '%s', Configurada en %s::%s. Dependencies pasadas: %s",
                    $attr->index,
                    $dtoClass,
                    $method,
                    join(', ', array_keys($dependencies)),
                ));
            }

            if ($attr->service && !($dependencies[$attr->index] instanceof $attr->service)) {
                throw new LogicException(sprintf(
                    "La dependencia '%s', configurada en %s::%s, debe ser una instancia de '%s'",
                    $attr->index,
                    $dtoClass,
                    $method,
                    $attr->service,
                ));
            }
        }

        return $this->loaded[$dtoClass][$method] = $dependencies;
    }
}