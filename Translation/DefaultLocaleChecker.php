<?php
/**
 * @author Manuel Aguirre
 */

namespace Optime\Util\Translation;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Optime\Util\Translation\Exception\EntityNotLoadedInDefaultLocaleException;
use Optime\Util\Translation\Exception\TranslationExceptionNotInstalledException;
use function get_class;

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
    /**
     * @var ManagerRegistry
     */
    private $managerRegistry;

    public function __construct(
        LocalesProviderInterface $localesProvider,
        ManagerRegistry $managerRegistry,
        TranslatableListener $listener
    ) {
        $this->localesProvider = $localesProvider;
        $this->managerRegistry = $managerRegistry;
        $this->listener = $listener;
    }

    public function throwOnInvalidLocale(TranslationsAwareInterface $entity): void
    {
        $this->checkTranslationExtension();

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
        $this->checkTranslationExtension();

        $defaultLocale = $this->localesProvider->getDefaultLocale();

        if (null === $entity->getCurrentContentsLocale()) {
            /** @var EntityManagerInterface $em */
            $em = $this->managerRegistry->getManagerForClass(get_class($entity));

            if (!$em->getUnitOfWork()->isInIdentityMap($entity)) {
                // Si es un registro nuevo y no tiene locale definido, le ponemos uno.
                $entity->setCurrentContentsLocale($defaultLocale);
            }
        }

        $currentLocale = $entity->getCurrentContentsLocale() ?: $this->listener->getListenerLocale();

        return $currentLocale == $defaultLocale;
    }

    private function checkTranslationExtension():void
    {
        if (!$this->listener->hasListener()) {
            throw new TranslationExceptionNotInstalledException();
        }
    }
}