<?php
/**
 * @author Manuel Aguirre
 */

namespace Optime\Util\Translation;

use Optime\Util\Entity\Language;

/**
 * @author Manuel Aguirre
 */
interface LocalesProviderInterface
{
    public function getLocales(): array;
    public function getDefaultLocale(): string;
}