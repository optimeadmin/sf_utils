<?php
/**
 * @author Manuel Aguirre
 */

namespace Optime\Util\Translation;

use Gedmo\Translatable\Entity\Repository\TranslationRepository;
use Optime\Util\Translation\Exception\EntityTranslationsNotInstalledException;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * @author Manuel Aguirre
 */
class TranslatableContentFactory
{
    public function __construct(
        private PropertyAccessorInterface $propertyAccessor,
        private LocalesProviderInterface $localesProvider,
        private DefaultLocaleChecker $defaultLocaleChecker,
        private ?TranslationRepository $translationRepository,
    ) {
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

    public function load(TranslationsAwareInterface $entity, string $property): TranslatableContent
    {
        $this->defaultLocaleChecker->throwOnInvalidLocale($entity);

        if (!$this->translationRepository) {
            throw new EntityTranslationsNotInstalledException();
        }

        $translations = $this->translationRepository->findTranslations($entity);
        $contents = [];

        foreach ($translations as $locale => $translation) {
            $contents[$locale] = $translation[$property] ?? '';
        }

        $defaultLocale = $this->getDefaultLocale();
        $contents[$defaultLocale] = $this->getDefaultLocaleValue(
            $entity,
            $property
        );

        return TranslatableContent::fromExistentData($contents, $entity, $defaultLocale);
    }

    private function getDefaultLocaleValue(object $entity, string $property): ?string
    {
        return $this->propertyAccessor->isReadable($entity, $property)
            ? $this->propertyAccessor->getValue($entity, $property)
            : null;
    }

    private function getDefaultLocale(): string
    {
        return $this->localesProvider->getDefaultLocale();
    }
}