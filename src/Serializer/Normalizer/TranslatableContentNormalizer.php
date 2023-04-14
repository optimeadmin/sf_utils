<?php
/**
 * @author Manuel Aguirre
 */

declare(strict_types=1);

namespace Optime\Util\Serializer\Normalizer;

use ArrayObject;
use Optime\Util\Translation\DefaultLocaleEntityRefresh;
use Optime\Util\Translation\TranslatableContent;
use Optime\Util\Translation\TranslatableContentFactory;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use function count;
use function dump;
use function json_encode;

/**
 * @author Manuel Aguirre
 */
class TranslatableContentNormalizer implements ContextAwareNormalizerInterface
{
    const AUTO_REFRESH = 'auto_refresh_locale';

    public function __construct(
        private readonly TranslatableContentFactory $contentFactory,
        private readonly DefaultLocaleEntityRefresh $entityRefresh,
    ) {
    }

    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        return $data instanceof TranslatableContent;
    }

    public function normalize($object, string $format = null, array $context = [])
    {
        /** @var TranslatableContent $object */

        if ($object->isPending()) {

            if ($context[self::AUTO_REFRESH] ?? false) {
                $this->entityRefresh->refresh($object->getTarget());
            }

            $object = $this->contentFactory->load(
                $object->getTarget(),
                $object->getProperty()
            );
        }

        $data = $object->jsonSerialize();

        if (0 === count($data)) {
            return null;
        }

        return $data;
    }
}