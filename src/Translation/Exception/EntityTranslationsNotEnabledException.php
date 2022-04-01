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
class EntityTranslationsNotEnabledException extends Exception
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        $message = $message ?: "No está activada la opción de Extension de traducciones."
            . " debe setear el parametro 'optime_util.use_translations_extension: true'";
        parent::__construct($message, $code, $previous);
    }
}