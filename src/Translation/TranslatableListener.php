<?php
/**
 * @author Manuel Aguirre
 */

namespace Optime\Util\Translation;

use Gedmo\Translatable\TranslatableListener as GedmoListener;
use Optime\Util\Translation\Exception\EntityTranslationsNotEnabledException;
use Optime\Util\Translation\Exception\EntityTranslationsNotInstalledException;
use Psr\Container\ContainerInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

/**
 * @author Manuel Aguirre
 */
class TranslatableListener implements ServiceSubscriberInterface
{
    public function __construct(
        private bool $enabledExtension,
        private ContainerInterface $container,
    ) {
    }

    public static function getSubscribedServices(): array
    {
        /* Se obtiene el listener de esta forma para evitar
         * posibles errores de referencias circulares si
         * se llegan a usar estos servicios en listeners de doctrine.         *
         */
        return [
            '?' . GedmoListener::class,
        ];
    }

    public function hasListener(): bool
    {
        if (!$this->enabledExtension) {
            throw new EntityTranslationsNotEnabledException();
        }

        return null !== $this->getGedmoListener();
    }

    public function setTranslatableLocale(string $locale): void
    {
        $this->checkTranslationExtension();

        $this->getGedmoListener()->setTranslatableLocale($locale);
    }

    public function getListenerLocale(): string
    {
        $this->checkTranslationExtension();

        return $this->getGedmoListener()->getListenerLocale();
    }

    private function checkTranslationExtension(): void
    {
        if (!$this->enabledExtension) {
            throw new EntityTranslationsNotEnabledException();
        }

        if (!$this->hasListener()) {
            throw new EntityTranslationsNotInstalledException();
        }
    }

    private function getGedmoListener(): ?GedmoListener
    {
        if (!$this->container->has(GedmoListener::class)) {
            return null;
        }

        return $this->container->get(GedmoListener::class);
    }
}
