<?php
/**
 * @author Manuel Aguirre
 */

namespace Optime\Util\Translation\Persister;

use Doctrine\ORM\EntityManagerInterface;
use Gedmo\Translatable\Entity\Repository\TranslationRepository;
use Optime\Util\Entity\Event;
use Optime\Util\Translation\LocalesProviderInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

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
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var LocalesProviderInterface
     */
    private $localesProvider;
    /**
     * @var PropertyAccessorInterface
     */
    private $propertyAccessor;

    public function __construct(
        TranslationRepository $repository,
        EntityManagerInterface $entityManager,
        LocalesProviderInterface $localesProvider,
        PropertyAccessorInterface $propertyAccessor
    ) {
        $this->repository = $repository;
        $this->entityManager = $entityManager;
        $this->localesProvider = $localesProvider;
        $this->propertyAccessor = $propertyAccessor;
    }

    public function prepare(object $targetEntity, bool $autoFlush = false): PreparedPersister
    {
        return new PreparedPersister(
            $this->entityManager,
            $this->repository,
            $this->propertyAccessor,
            $targetEntity,
            $this->localesProvider->getLocales(),
            $this->localesProvider->getDefaultLocale(),
            $autoFlush
        );
    }
}