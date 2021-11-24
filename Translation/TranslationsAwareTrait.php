<?php
/**
 * @author Manuel Aguirre
 */

namespace Optime\Util\Translation;

use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @author Manuel Aguirre
 */
trait TranslationsAwareTrait
{
    /**
     * @var string|null
     * @Gedmo\Locale()
     */
    private $currentContentsLocale;

    public function getCurrentContentsLocale(): ?string
    {
        return $this->currentContentsLocale;
        return $this->currentContentsLocale;
    }

    public function setCurrentContentsLocale(string $locale): void
    {
        $this->currentContentsLocale = $locale;
    }
}