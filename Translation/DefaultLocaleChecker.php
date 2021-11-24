<?php
/**
 * @author Manuel Aguirre
 */

namespace Optime\Util\Translation;

use Gedmo\Translatable\TranslatableListener;
use Optime\Util\Translation\Exception\EntityNotLoadedInDefaultLocaleException;

/**
 * @author Manuel Aguirre
 */
class DefaultLocaleChecker
{
    /**
     * @var TranslatableListener
     */
    private $listener;
    /**
     * @var LocalesProviderInterface
     */
    private $localesProvider;

    public function __construct(
        TranslatableListener $listener,
        LocalesProviderInterface $localesProvider
    ) {
        $this->listener = $listener;
        $this->localesProvider = $localesProvider;
    }

    public function throwOnInvalidLocale(TranslationsAwareInterface $entity): void
    {
        if (!$this->isEntityInDefaultLocale($entity)) {
            $currentLocale = $entity->getCurrentContentsLocale() ?: $this->listener->getListenerLocale();

            throw new EntityNotLoadedInDefaultLocaleException(
                $entity,
                $this->localesProvider->getDefaultLocale(),
                $currentLocale
            );
        }
    }

    public function isEntityInDefaultLocale(TranslationsAwareInterface $entity): bool
    {
        $defaultLocale = $this->localesProvider->getDefaultLocale();
        $currentLocale = $entity->getCurrentContentsLocale() ?: $this->listener->getListenerLocale();

        return $currentLocale == $defaultLocale;
    }
}