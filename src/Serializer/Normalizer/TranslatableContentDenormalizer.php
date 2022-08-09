<?php
/**
 * @author Manuel Aguirre
 */

declare(strict_types=1);

namespace Optime\Util\Serializer\Normalizer;

use Optime\Util\Translation\TranslatableContent;
use Optime\Util\Translation\TranslatableContentFactory;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Normalizer\ContextAwareDenormalizerInterface;

/**
 * @author Manuel Aguirre
 */
class TranslatableContentDenormalizer implements ContextAwareDenormalizerInterface
{
    public function __construct(private TranslatableContentFactory $contentFactory)
    {
    }

    public function supportsDenormalization($data, string $type, string $format = null, array $context = []): bool
    {
        return $type === TranslatableContent::class;
    }

    public function denormalize($data, string $type, string $format = null, array $context = [])
    {
        if (!($type === TranslatableContent::class)) {
            throw new InvalidArgumentException("El tipo debe ser " . TranslatableContent::class);
        }

        return $this->contentFactory->newInstance($data);
    }
}