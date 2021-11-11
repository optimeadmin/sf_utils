<?php
/**
 * @author Manuel Aguirre
 */

namespace Optime\Util\Translation\Persister;

use Doctrine\ORM\EntityManagerInterface;
use Gedmo\Translatable\Entity\Repository\TranslationRepository;
use Optime\Util\Entity\Event;
use Optime\Util\Translation\TranslatableContent;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * @author Manuel Aguirre
 */
class PreparedPersister
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var TranslationRepository
     */
    private $repository;
    /**
     * @var PropertyAccessorInterface
     */
    private $propertyAccessor;
    /**
     * @var object
     */
    private $entity;
    /**
     * @var array
     */
    private $locales;
    /**
     * @var string
     */
    private $defaultLocale;
    /**
     * @var bool
     */
    private $flush;

    public function __construct(
        EntityManagerInterface $entityManager,
        TranslationRepository $repository,
        PropertyAccessorInterface $propertyAccessor,
        object $entity,
        array $locales,
        string $defaultLocale,
        bool $flush = false
    ) {
        $this->entityManager = $entityManager;
        $this->repository = $repository;
        $this->propertyAccessor = $propertyAccessor;
        $this->entity = $entity;
        $this->locales = $locales;
        $this->defaultLocale = $defaultLocale;
        $this->flush = $flush;
    }

    public function persist(string $property, TranslatableContent $translations): void
    {
        foreach ($this->locales as $locale) {
            $value = $translations->byLocale($locale);

            if ($locale == $this->defaultLocale) {
                if ($this->propertyAccessor->isWritable($this->entity, $property)) {
                    $this->propertyAccessor->setValue($this->entity, $property, $value);
                }
            } else {
                $this->repository->translate($this->entity, $property, $locale, $value);
            }
        }

        $this->flush and $this->entityManager->flush();
    }
}