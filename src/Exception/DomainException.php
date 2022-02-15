<?php
/**
 * @author Manuel Aguirre
 */

namespace Optime\Util\Exception;

use Optime\Util\TranslatableMessage;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @author Manuel Aguirre
 */
class DomainException extends \Exception
{
    private TranslatableMessage $domainMessage;
    protected TranslatorInterface|null $translator;

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

    public function setTranslator(TranslatorInterface $translator): self
    {
        $this->translator = $translator;

        return $this;
    }

    public function getDomainMessage(): TranslatableMessage
    {
        return $this->domainMessage;
    }
}