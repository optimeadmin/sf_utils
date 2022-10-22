<?php
/**
 * @author Manuel Aguirre
 */

namespace Optime\Util\Translation;

use Optime\Util\Entity\Language;

/**
 * @author Manuel Aguirre
 */
class ContentToSerialize extends TranslatableContent
{
    public function __construct(
        private TranslationsAwareInterface $translationsAware,
        private string $property,
    ) {
    }
}