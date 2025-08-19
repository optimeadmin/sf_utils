<?php
/**
 * @author Manuel Aguirre
 */

namespace Optime\Util\Doctrine\EventListener;

use Doctrine\Bundle\DoctrineBundle\EventSubscriber\EventSubscriberInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadata;
use Optime\Util\Entity\Embedded\Date;
use Optime\Util\Entity\Traits\DatesTrait;
use Optime\Util\Entity\Traits\ExternalUuidTrait;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use function class_uses;

/**
 * @author Manuel Aguirre
 */
class ExternalUuidOrDatesAwareEntityListener implements EventSubscriberInterface
{
    private array $entities = [];

    public function __construct(
        private PropertyAccessorInterface $propertyAccessor
    ) {
    }

    public function getSubscribedEvents()
    {
        return [
            Events::prePersist,
        ];
    }

    public function prePersist(LifecycleEventArgs|PrePersistEventArgs $event): void
    {
        $entity = $event->getObject();
        $metadata = $event->getObjectManager()->getClassMetadata($entity::class);
        $entityClass = $metadata->getName();

        $this->loadEntityInfo($metadata);
        $this->initializeExternalUuid($entity, $entityClass);
        $this->initializeDates($entity, $entityClass);
    }

    private function initializeExternalUuid(object $entity, string $key): void
    {
        if (!$this->entities[$key]['external_uuid']) {
            return;
        }

        $entity->getUuid();
    }

    private function initializeDates(object $entity, string $key): void
    {
        if (null === ($datesProperty = $this->entities[$key]['dates'])) {
            return;
        }

        if (!$this->propertyAccessor->isReadable($entity, $datesProperty)) {
            return;
        }

        $date = $this->propertyAccessor->getValue($entity, $datesProperty);

        if (!$date instanceof Date) {
            return;
        }

        $date->getCreatedAt();
    }

    private function loadEntityInfo(ClassMetadata $metadata): void
    {
        $entityClass = $metadata->getName();

        if (isset($this->entities[$entityClass])) {
            return;
        }

        $traits = class_uses($entityClass);

        $this->entities[$entityClass] = [
            'external_uuid' => isset($traits[ExternalUuidTrait::class]),
            'dates' => isset($traits[DatesTrait::class]) ? 'dates' : null,
        ];

        if (null !== ($this->entities[$entityClass]['dates'])) {
            return;
        }

        foreach ($metadata->embeddedClasses as $property => ['class' => $class]) {
            if (Date::class === $class) {
                break;
            }

            $this->entities[$entityClass]['dates'] = $property;
        }
    }
}
