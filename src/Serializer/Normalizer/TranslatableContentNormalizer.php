<?php
/**
 * @author Manuel Aguirre
 */

declare(strict_types=1);

namespace Optime\Util\Serializer\Normalizer;

use Optime\Util\Translation\TranslatableContent;
use Optime\Util\Translation\TranslatableContentFactory;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;

/**
 * @author Manuel Aguirre
 */
class TranslatableContentNormalizer implements ContextAwareNormalizerInterface
{
    public function __construct(private TranslatableContentFactory $contentFactory)
    {
    }

    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        return $data instanceof TranslatableContent;
    }

    public function normalize($object, string $format = null, array $context = [])
    {
        /** @var TranslatableContent $object */

        if ($object->isPending()) {
            $object = $this->contentFactory->load(
                $object->getTarget(),
                $object->getProperty()
            );
        }

        return $object->jsonSerialize();
    }
}