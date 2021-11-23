<?php
/**
 * @author Manuel Aguirre
 */

namespace Optime\Util\Translation\Persister;

use Gedmo\Translatable\Entity\Repository\TranslationRepository;
use Gedmo\Translatable\TranslatableListener;
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
     * @var TranslatableListener
     */
    private $translatableListener;
    /**
     * @var LocalesProviderInterface
     */
    private $localesProvider;

    public function __construct(
        TranslationRepository $repository,
        TranslatableListener $translatableListener,
        LocalesProviderInterface $localesProvider
    ) {
        $this->repository = $repository;
        $this->translatableListener = $translatableListener;
        $this->localesProvider = $localesProvider;
    }

    public function prepare(object $targetEntity): PreparedPersister
    {
        return new PreparedPersister(
            $this->repository,
            $this->translatableListener,
            $targetEntity,
            $this->localesProvider->getLocales(),
            $this->localesProvider->getDefaultLocale()
        );
    }
}