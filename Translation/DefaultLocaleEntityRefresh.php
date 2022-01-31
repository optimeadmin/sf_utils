<?php
/**
 * @author Manuel Aguirre
 */

namespace Optime\Util\Translation;

use Doctrine\Persistence\ManagerRegistry;
use function get_class;

/**
 * @author Manuel Aguirre
 */
class DefaultLocaleEntityRefresh
{
    public function __construct(
        private LocalesProviderInterface $localesProvider,
        private ManagerRegistry $managerRegistry,
        private DefaultLocaleChecker $localeChecker,
    ) {
    }

    public function refresh(TranslationsAwareInterface $entity): void
    {
        if (!$this->localeChecker->isEntityInDefaultLocale($entity)) {
            $entity->setCurrentContentsLocale($this->localesProvider->getDefaultLocale());
            $em = $this->managerRegistry->getManagerForClass(get_class($entity));

            if ($em->contains($entity)) {
                $em->refresh($entity);
            }
        }
    }
}