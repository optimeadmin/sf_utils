<?php
/**
 * @author Manuel Aguirre
 */

namespace Optime\Util\Exception;

use Optime\Util\TranslatableMessage;
use Symfony\Contracts\Translation\TranslatorInterface;
use function sprintf;

/**
 * @author Manuel Aguirre
 */
class DomainException extends \Exception
{
    private TranslatableMessage $domainMessage;
    protected TranslatorInterface|null $translator = null;

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

    public function trans(TranslatorInterface $translator = null): string
    {
        $translator ??= $this->translator;

        if (!$translator) {
            throw new \LogicException(sprintf(
                "Debe pasar el servicio \"%s\" para poder traducir directamente la exception",
                TranslatorInterface::class
            ));
        }

        return $this->getDomainMessage()->trans($translator);
    }
}