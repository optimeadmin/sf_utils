<?php
/**
 * @author Manuel Aguirre
 */

namespace Optime\Util\Translation;

use Doctrine\ORM\EntityManagerInterface;
use Gedmo\Translatable\Entity\Repository\TranslationRepository;
use Optime\Util\Entity\Event;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * @author Manuel Aguirre
 */
class TranslatableContentFactory
{
    /**
     * @var TranslationRepository
     */
    private $translationRepository;
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var PropertyAccessorInterface
     */
    private $propertyAccessor;
    /**
     * @var LocalesProviderInterface
     */
    private $localesProvider;

    public function __construct(
        EntityManagerInterface $entityManager,
        TranslationRepository $translationRepository,
        PropertyAccessorInterface $propertyAccessor,
        LocalesProviderInterface $localesProvider
    ) {
        $this->entityManager = $entityManager;
        $this->translationRepository = $translationRepository;
        $this->propertyAccessor = $propertyAccessor;
        $this->localesProvider = $localesProvider;
    }

    public function newInstance(array $contents = []): TranslatableContent
    {
        return new TranslatableContent($contents, $this->localesProvider->getDefaultLocale());
    }

    public function load(object $entity, string $property): TranslatableContent
    {
        $translations = $this->translationRepository->findTranslations($entity);
        $contents = [];

        foreach ($translations as $locale => $translation) {
            $contents[$locale] = $translation[$property] ?? '';
        }

        $defaultLocale = $this->localesProvider->getDefaultLocale();
        $contents[$defaultLocale] = $this->getDefaultEventLocaleValue(
            $entity,
            $property
        );

        return TranslatableContent::fromExistentData($contents, $entity, $defaultLocale);
    }

    private function getDefaultEventLocaleValue(object $entity, string $property): ?string
    {
        return $this->propertyAccessor->getValue($entity, $property);
    }
}