<?php

namespace Optime\Util\Translation;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\Mapping\MappingException;
use Gedmo\Translatable\Entity\Repository\TranslationRepository;
use Optime\Util\Translation\Exception\EntityTranslationsNotInstalledException;

class TranslationRepositoryProvider
{
    private bool $checkedInstallation = false;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @throws EntityTranslationsNotInstalledException
     */
    public function get(): TranslationRepository
    {
        if (!$this->checkedInstallation) {
            if (!class_exists(TranslationRepository::class)) {
                throw new EntityTranslationsNotInstalledException();
            }
            try {
                $this->entityManager->getClassMetadata(Translation::class);
            } catch (MappingException $exception) {
                throw new EntityTranslationsNotInstalledException(previous: $exception);
            }
        }

        $this->checkedInstallation = true;

        return $this->entityManager->getRepository(Translation::class);
    }
}