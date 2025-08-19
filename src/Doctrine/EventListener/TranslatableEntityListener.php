<?php
/**
 * @author Manuel Aguirre
 */

namespace Optime\Util\Doctrine\EventListener;

use Doctrine\Bundle\DoctrineBundle\EventSubscriber\EventSubscriberInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Events;
use Optime\Util\Translation\LocalesProviderInterface;
use Optime\Util\Translation\TranslationsAwareInterface;

/**
 * @author Manuel Aguirre
 */
class TranslatableEntityListener implements EventSubscriberInterface
{
    public function __construct(
        private LocalesProviderInterface $localesProvider,
    )
    {
    }

    public function getSubscribedEvents()
    {
        return [
            Events::prePersist,
        ];
    }

    public function prePersist(LifecycleEventArgs|PrePersistEventArgs $event): void
    {
        $this->setDefaultLocaleIfApply($event->getObject());
    }

    private function setDefaultLocaleIfApply($entity): void
    {
        if (!$entity instanceof TranslationsAwareInterface) {
            return;
        }

        if (null !== $entity->getCurrentContentsLocale()) {
            return;
        }

        $entity->setCurrentContentsLocale($this->localesProvider->getDefaultLocale());
    }
}