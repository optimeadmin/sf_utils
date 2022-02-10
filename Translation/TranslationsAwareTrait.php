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
    #[Gedmo\Locale]
    private string|null $currentContentsLocale = null;

    public function getCurrentContentsLocale(): ?string
    {
        return $this->currentContentsLocale;
    }

    public function setCurrentContentsLocale(string $locale): void
    {
        $this->currentContentsLocale = $locale;
    }
}
