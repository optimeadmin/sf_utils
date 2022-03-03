<?php
/**
 * @author Manuel Aguirre
 */

namespace Optime\Util\Translation\Persister;

use Gedmo\Translatable\Entity\Repository\TranslationRepository;
use Optime\Util\Translation\DefaultLocaleChecker;
use Optime\Util\Translation\Exception\EntityTranslationsNotInstalledException;
use Optime\Util\Translation\LocalesProviderInterface;
use Optime\Util\Translation\TranslatableListener;
use Optime\Util\Translation\TranslationsAwareInterface;

/**
 * @author Manuel Aguirre
 */
class TranslatableContentPersister
{
    public function __construct(
        private TranslatableListener $translatableListener,
        private LocalesProviderInterface $localesProvider,
        private DefaultLocaleChecker $localeChecker,
        private ?TranslationRepository $repository,
    ) {
    }

    public function prepare(TranslationsAwareInterface $targetEntity): PreparedPersister
    {
        $this->localeChecker->throwOnInvalidLocale($targetEntity);

        if (!$this->repository) {
            throw new EntityTranslationsNotInstalledException();
        }

        return new PreparedPersister(
            $this->repository,
            $this->translatableListener,
            $this->localesProvider,
            $targetEntity,
        );
    }
}