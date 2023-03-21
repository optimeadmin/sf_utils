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
class PendingContentsException extends \LogicException
{
    public function __construct(TranslatableContent $content)
    {
        parent::__construct(sprintf(
            'Los contenidos de %s::%s no están cargados aún',
            $content->getTarget()::class,
            $content->getProperty(),
        ));
    }
}