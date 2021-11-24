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
    /**
     * @var TranslatableContentPersister
     */
    private $contentPersister;
    /**
     * @var TranslatableContentFactory
     */
    private $contentFactory;
    /**
     * @var DefaultLocaleEntityRefresh
     */
    private $localeEntityRefresh;

    public function __construct(
        TranslatableContentPersister $contentPersister,
        TranslatableContentFactory $contentFactory,
        DefaultLocaleEntityRefresh $localeEntityRefresh
    ) {
        $this->contentPersister = $contentPersister;
        $this->contentFactory = $contentFactory;
        $this->localeEntityRefresh = $localeEntityRefresh;
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