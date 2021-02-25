<?php
/**
 * @author Manuel Aguirre
 */

namespace Optime\Util;

use Symfony\Component\Form\FormError;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @author Manuel Aguirre
 */
class TranslatableMessage implements \Serializable
{
    /**
     * @var string
     */
    private $message;
    /**
     * @var array
     */
    private $messageParameters;
    /**
     * @var string
     */
    private $domain;

    public function __construct(string $message, array $messageParameters = [], string $domain = null)
    {
        $this->message = $message;
        $this->messageParameters = $messageParameters;
        $this->domain = $domain;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getMessageParameters(): array
    {
        return $this->messageParameters;
    }

    public function getDomain(): ?string
    {
        return $this->domain;
    }

    public function __toString():string
    {
        return (string)$this->getMessage();
    }

    public function serialize()
    {
        return serialize([
            $this->message,
            $this->messageParameters,
            $this->domain
        ]);
    }

    public function unserialize($serialized)
    {
        [
            $this->message,
            $this->messageParameters,
            $this->domain
        ] = unserialize($serialized);
    }

    public function toFormError(TranslatorInterface $translator): FormError
    {
        return new FormError(
            $this->trans($translator),
            null,
            $this->getMessageParameters()
        );
    }

    public function trans(TranslatorInterface $translator): string
    {
        return $translator->trans($this->getMessage(), $this->getMessageParameters(), $this->getDomain());
    }
}