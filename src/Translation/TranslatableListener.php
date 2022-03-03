<?php
/**
 * @author Manuel Aguirre
 */

namespace Optime\Util\Translation;

use Gedmo\Translatable\TranslatableListener as GedmoListener;
use Optime\Util\Translation\Exception\EntityTranslationsNotInstalledException;

/**
 * @author Manuel Aguirre
 */
class TranslatableListener
{
    public function __construct(
        private ?GedmoListener $listener = null,
    ) {
    }

    public function hasListener(): bool
    {
        return null !== $this->listener;
    }

    public function setTranslatableLocale(string $locale): void
    {
        $this->checkTranslationExtension();

        $this->listener->setTranslatableLocale($locale);
    }

    public function getListenerLocale(): string
    {
        $this->checkTranslationExtension();

        return $this->listener->getListenerLocale();
    }

    private function checkTranslationExtension(): void
    {
        if (!$this->hasListener()) {
            throw new EntityTranslationsNotInstalledException();
        }
    }
}
