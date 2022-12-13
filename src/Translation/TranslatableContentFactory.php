<?php
/**
 * @author Manuel Aguirre
 */

namespace Optime\Util\Translation;

use Gedmo\Translatable\Entity\Repository\TranslationRepository;
use Optime\Util\Translation\Exception\EntityTranslationsNotInstalledException;
use Psr\Container\ContainerInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

/**
 * @author Manuel Aguirre
 */
class TranslatableContentFactory implements ServiceSubscriberInterface
{
    public function __construct(
        private PropertyAccessorInterface $propertyAccessor,
        private LocalesProviderInterface $localesProvider,
        private DefaultLocaleChecker $defaultLocaleChecker,
        private ContainerInterface $container,
    ) {
    }

    public static function getSubscribedServices(): array
    {
        /* Se obtiene el repositorio de esta forma para evitar
         * posibles errores de referencias circulares si
         * se llegan a usar estos servicios en listeners de doctrine.         *
         */
        return [
            '?' . TranslationRepository::class,
        ];
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

        if (!$this->container->has(TranslationRepository::class)) {
            throw new EntityTranslationsNotInstalledException();
        }

        $translations = $this->container
            ->get(TranslationRepository::class)
            ->findTranslations($entity);

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