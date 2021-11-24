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
    /**
     * @var LocalesProviderInterface
     */
    private $localesProvider;
    /**
     * @var ManagerRegistry
     */
    private $managerRegistry;
    /**
     * @var DefaultLocaleChecker
     */
    private $localeChecker;

    public function __construct(
        LocalesProviderInterface $localesProvider,
        ManagerRegistry $managerRegistry,
        DefaultLocaleChecker $localeChecker
    ) {
        $this->localesProvider = $localesProvider;
        $this->managerRegistry = $managerRegistry;
        $this->localeChecker = $localeChecker;
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