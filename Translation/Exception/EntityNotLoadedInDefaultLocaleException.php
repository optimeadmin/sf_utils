<?php
/**
 * @author Manuel Aguirre
 */

namespace Optime\Util\Translation\Exception;

use Exception;
use Optime\Util\Translation\TranslationsAwareInterface;
use function get_class;

/**
 * @author Manuel Aguirre
 */
class EntityNotLoadedInDefaultLocaleException extends Exception
{
    public function __construct(
        TranslationsAwareInterface $entity,
        string $defaultLocale,
        string $currentLocale
    ) {
        parent::__construct(sprintf("Entity %s is not loaded in default locale '%s'.\n"
            . "You can only work with TranslatableContent if you use default locale in your entity.\n"
            . "Current entity locale is '%s'.\n"
            . "You must call DefaultLocaleEntityRefresh::refresh(\$entity) before manage translation.\n",
            get_class($entity), $defaultLocale, $entity->getCurrentContentsLocale() ?: $currentLocale));
    }
}