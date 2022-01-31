<?php
/**
 * @author Manuel Aguirre
 */

namespace Optime\Util\Translation\Persister;

use Gedmo\Translatable\Entity\Repository\TranslationRepository;
use Optime\Util\Translation\TranslatableListener;
use Optime\Util\Entity\Event;
use Optime\Util\Translation\DefaultLocaleChecker;
use Optime\Util\Translation\LocalesProviderInterface;
use Optime\Util\Translation\TranslationsAwareInterface;

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
    /**
     * @var DefaultLocaleChecker
     */
    private $localeChecker;

    public function __construct(
        TranslationRepository $repository,
        TranslatableListener $translatableListener,
        LocalesProviderInterface $localesProvider,
        DefaultLocaleChecker $localeChecker
    ) {
        $this->repository = $repository;
        $this->translatableListener = $translatableListener;
        $this->localesProvider = $localesProvider;
        $this->localeChecker = $localeChecker;
    }

    public function prepare(TranslationsAwareInterface $targetEntity): PreparedPersister
    {
        $this->localeChecker->throwOnInvalidLocale($targetEntity);

        return new PreparedPersister(
            $this->repository,
            $this->translatableListener,
            $targetEntity,
            $this->localesProvider->getLocales(),
            $this->localesProvider->getDefaultLocale()
        );
    }
}