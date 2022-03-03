<?php
/**
 * @author Manuel Aguirre
 */

namespace Optime\Util\Translation\Exception;

use Exception;
use Throwable;

/**
 * @author Manuel Aguirre
 */
class EntityTranslationsNotInstalledException extends Exception
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        $message = $message ?: "No está instalada/configurada la extension de doctrine para traducciones."
            . " Por lo que no se puede usar la utilidad de traducciones de entidades";
        parent::__construct($message, $code, $previous);
    }
}