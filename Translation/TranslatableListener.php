<?php
/**
 * @author Manuel Aguirre
 */

namespace Optime\Util\Translation;

use Gedmo\Translatable\TranslatableListener as GedmoListener;
use Optime\Util\Translation\Exception\TranslationExceptionNotInstalledException;

/**
 * @author Manuel Aguirre
 */
class TranslatableListener
{
    /**
     * @var GedmoListener|null
     */
    private $listener;

    public function __construct(?GedmoListener $listener = null)
    {
        $this->listener = $listener;
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

        $this->listener->getListenerLocale()();
    }

    private function checkTranslationExtension():void
    {
        if (!$this->hasListener()) {
            throw new TranslationExceptionNotInstalledException();
        }
    }
}