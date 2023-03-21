<?php
/**
 * @author Manuel Aguirre
 */

declare(strict_types=1);

namespace Optime\Util\Translation\Exception;


use Optime\Util\Translation\TranslatableContent;

/**
 * @author Manuel Aguirre
 */
class ErrorLoadingPendingContentsException extends \LogicException
{
    public function __construct(TranslatableContent $content)
    {
        parent::__construct('El  contenido no se puede cargar ya que no tiene Target Asociado');
    }
}