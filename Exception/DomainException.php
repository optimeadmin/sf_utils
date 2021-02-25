<?php
/**
 * @author Manuel Aguirre
 */

namespace Optime\Util\Exception;

use Optime\Util\TranslatableMessage;

/**
 * @author Manuel Aguirre
 */
class DomainException extends \Exception
{
    /**
     * @var TranslatableMessage
     */
    private $domainMessage;

    public function __construct($message, ...$replaceValues)
    {
        if (is_string($message) && 0 !== count($replaceValues)) {
            $message = sprintf($message, ...$replaceValues);
        }

        if (!$message instanceof TranslatableMessage) {
            $message = new TranslatableMessage($message);
        }

        $this->domainMessage = $message;

        parent::__construct((string)$message);
    }

    public function getDomainMessage(): TranslatableMessage
    {
        return $this->domainMessage;
    }
}