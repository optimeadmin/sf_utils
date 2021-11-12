<?php
/**
 * @author Manuel Aguirre
 */

namespace Optime\Util\Translation\Persister;

use Gedmo\Translatable\Entity\Repository\TranslationRepository;
use Optime\Util\Entity\Event;
use Optime\Util\Translation\LocalesProviderInterface;

/**
 * @author Manuel Aguirre
 */
class TranslatableContentPersister
{
    /**
     * @var TranslationRepository
     */
    private $repository;
    /**
     * @var LocalesProviderInterface
     */
    private $localesProvider;

    public function __construct(
        TranslationRepository $repository,
        LocalesProviderInterface $localesProvider
    ) {
        $this->repository = $repository;
        $this->localesProvider = $localesProvider;
    }

    public function prepare(object $targetEntity): PreparedPersister
    {
        return new PreparedPersister(
            $this->repository,
            $targetEntity,
            $this->localesProvider->getLocales(),
            $this->localesProvider->getDefaultLocale()
        );
    }
}