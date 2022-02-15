<?php
/**
 * @author Manuel Aguirre
 */

namespace Optime\Util\Translation;

use Gedmo\Translatable\Translatable;

/**
 * @author Manuel Aguirre
 */
interface TranslationsAwareInterface extends Translatable
{
    public function getCurrentContentsLocale(): ?string;

    public function setCurrentContentsLocale(string $locale): void;
}