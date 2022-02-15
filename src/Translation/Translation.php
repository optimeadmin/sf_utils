<?php
/**
 * @author Manuel Aguirre
 */

namespace Optime\Util\Translation;

use Optime\Util\Translation\Persister\PreparedPersister;
use Optime\Util\Translation\Persister\TranslatableContentPersister;

/**
 * @author Manuel Aguirre
 */
class Translation
{
    public function __construct(
        private TranslatableContentPersister $contentPersister,
        private TranslatableContentFactory $contentFactory,
        private DefaultLocaleEntityRefresh $localeEntityRefresh,
    ) {
    }

    public function preparePersist(TranslationsAwareInterface $entity): PreparedPersister
    {
        return $this->contentPersister->prepare($entity);
    }

    public function loadContent(TranslationsAwareInterface $entity, string $property): TranslatableContent
    {
        return $this->contentFactory->load($entity, $property);
    }

    public function newContent(array $values = []): TranslatableContent
    {
        return $this->contentFactory->newInstance($values);
    }

    public function fromString(string $content): TranslatableContent
    {
        return $this->contentFactory->fromString($content);
    }

    public function refreshInDefaultLocale(TranslationsAwareInterface $entity): void
    {
        $this->localeEntityRefresh->refresh($entity);
    }
}