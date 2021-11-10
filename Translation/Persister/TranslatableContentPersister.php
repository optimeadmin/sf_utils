<?php
/**
 * @author Manuel Aguirre
 */

namespace Optime\Util\Translation\Persister;

use Doctrine\ORM\EntityManagerInterface;
use Gedmo\Translatable\Entity\Repository\TranslationRepository;
use Optime\Util\Entity\Event;
use Optime\Util\Translation\LocalesProviderInterface;

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

    public function __construct(
        TranslationRepository $repository,
        EntityManagerInterface $entityManager,
        LocalesProviderInterface $localesProvider
    ) {
        $this->repository = $repository;
        $this->entityManager = $entityManager;
        $this->localesProvider = $localesProvider;
    }

    public function prepare(object $targetEntity, bool $autoFlush = false): PreparedPersister
    {
        return new PreparedPersister(
            $this->entityManager,
            $this->repository,
            $targetEntity,
            $this->localesProvider->getLocales(),
            $autoFlush
        );
    }
}