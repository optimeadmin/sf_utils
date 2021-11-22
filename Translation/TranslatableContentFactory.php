<?php
/**
 * @author Manuel Aguirre
 */

namespace Optime\Util\Translation;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NoResultException;
use Gedmo\Translatable\Entity\Repository\TranslationRepository;
use Gedmo\Translatable\TranslatableListener;
use Optime\Util\Entity\Event;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use function Doctrine\ORM\QueryBuilder;
use function get_class;

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
    /**
     * @var TranslatableListener
     */
    private $translatableListener;

    public function __construct(
        EntityManagerInterface $entityManager,
        TranslationRepository $translationRepository,
        PropertyAccessorInterface $propertyAccessor,
        LocalesProviderInterface $localesProvider,
        TranslatableListener $translatableListener
    ) {
        $this->entityManager = $entityManager;
        $this->translationRepository = $translationRepository;
        $this->propertyAccessor = $propertyAccessor;
        $this->localesProvider = $localesProvider;
        $this->translatableListener = $translatableListener;
    }

    public function newInstance(array $contents = []): TranslatableContent
    {
        return new TranslatableContent($contents, $this->localesProvider->getDefaultLocale());
    }

    public function fromString(string $content): TranslatableContent
    {
        $contents = [];

        foreach ($this->localesProvider->getLocales() as $locale) {
            $contents[$locale] = $content;
        }

        return $this->newInstance($contents);
    }

    public function load(object $entity, string $property): TranslatableContent
    {
        $translations = $this->translationRepository->findTranslations($entity);
        $contents = [];

        foreach ($translations as $locale => $translation) {
            $contents[$locale] = $translation[$property] ?? '';
        }

        $defaultLocale = $this->getDefaultLocale();

        if ($this->isEntityInDefaultLocale($entity)) {
            $contents[$defaultLocale] = $this->getDefaultEventLocaleValue(
                $entity,
                $property
            );
        } else {
            // acá tocar cargar el valor desde la base de datos
            $contents[$defaultLocale] = $this->findPropertyOriginalValue(
                $entity,
                $property
            );
        }

        return TranslatableContent::fromExistentData($contents, $entity, $defaultLocale);
    }

    private function getDefaultEventLocaleValue(object $entity, string $property): ?string
    {
        return $this->propertyAccessor->getValue($entity, $property);
    }

    private function findPropertyOriginalValue(object $entity, string $property)
    {
        $identifier = $this->entityManager->getUnitOfWork()->getEntityIdentifier($entity);

        $queryBuilder = $this->entityManager->createQueryBuilder()
            ->select('o.' . $property)
            ->from(get_class($entity), 'o')
            ->setMaxResults(1);

        foreach ($identifier as $col => $value) {
            $queryBuilder->andWhere($queryBuilder->expr()->eq('o.' . $col, $value));
        }

        try {
            $value = $queryBuilder
                ->getQuery()
                ->setHint(TranslatableListener::HINT_TRANSLATABLE_LOCALE, $this->getDefaultLocale())
                ->getSingleScalarResult();
        } catch (NoResultException$exception) {
            return '';
        }

        return $value;
    }

    private function getLoadedEntityLocale(object $entity): string
    {
        return $this->translatableListener->getTranslatableLocale(
            $entity, $this->entityManager->getClassMetadata(get_class($entity))
        );
    }

    private function isEntityInDefaultLocale(object $entity): bool
    {
        return $this->getLoadedEntityLocale($entity) == $this->getDefaultLocale();
    }

    private function getDefaultLocale(): string
    {
        return $this->localesProvider->getDefaultLocale();
    }
}