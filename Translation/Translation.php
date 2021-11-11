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

    public function __construct(
        TranslatableContentPersister $contentPersister,
        TranslatableContentFactory $contentFactory
    ) {
        $this->contentPersister = $contentPersister;
        $this->contentFactory = $contentFactory;
    }

    public function preparePersist(object $entity, bool $autoFlush = false): PreparedPersister
    {
        return $this->contentPersister->prepare($entity, $autoFlush);
    }

    public function loadContent(object $entity, string $property): TranslatableContent
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
}