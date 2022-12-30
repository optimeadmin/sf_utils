<?php
/**
 * @author Manuel Aguirre
 */

declare(strict_types=1);

namespace Optime\Util\Serializer\Normalizer;

use Symfony\Component\Form\FormError;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use function dump;
use function is_object;
use function method_exists;

/**
 * @author Manuel Aguirre
 */
class FormErrorNormalizer implements ContextAwareNormalizerInterface
{
    public function supportsNormalization(mixed $data, string $format = null, array $context = []): bool
    {
        return $data instanceof FormError;
    }

    public function normalize(mixed $object, string $format = null, array $context = [])
    {
        return [
            'message' => $object->getMessage(),
            'cause' => $object->getCause(),
            'propertyPath' => $object->getOrigin()?->getName(),
            'fullPath' => $object->getOrigin()?->getPropertyPath(),
        ];
    }
}