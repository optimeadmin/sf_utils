<?php
/**
 * @author Manuel Aguirre
 */

namespace Optime\Util\Translation\Persister;

use Doctrine\ORM\EntityManagerInterface;
use Gedmo\Translatable\Entity\Repository\TranslationRepository;
use Optime\Util\Entity\Event;
use Optime\Util\Translation\TranslatableContent;

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
     * @var object
     */
    private $entity;
    /**
     * @var array
     */
    private $locales;
    /**
     * @var bool
     */
    private $flush;

    public function __construct(
        EntityManagerInterface $entityManager,
        TranslationRepository $repository,
        object $entity,
        array $locales,
        bool $flush = false
    ) {
        $this->entityManager = $entityManager;
        $this->repository = $repository;
        $this->entity = $entity;
        $this->locales = $locales;
        $this->flush = $flush;
    }

    public function persist(string $property, TranslatableContent $translations): void
    {
        foreach ($this->locales as $locale) {
            $value = $translations->byLocale($locale);
            $this->repository->translate($this->entity, $property, $locale, $value);
        }

        $this->flush and $this->entityManager->flush();
    }
}