<?php
/**
 * @author Manuel Aguirre
 */

declare(strict_types=1);

namespace Optime\Util\Serializer\Normalizer;

use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use function is_object;
use function method_exists;

/**
 * @author Manuel Aguirre
 */
class EntityUuidNormalizer implements ContextAwareNormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    public function supportsNormalization(mixed $data, string $format = null, array $context = []): bool
    {
        return isset($context['uuid'])
            && true === $context['uuid']
            && is_object($data) && method_exists($data, 'getUuid');
    }

    public function normalize(mixed $object, string $format = null, array $context = [])
    {
        $context['uuid'] = false;
        $data = $this->normalizer->normalize($object, $format, $context);

        $data['uuid'] ??= $object->getUuid();

        return $data;
    }
}