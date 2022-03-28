<?php
/**
 * @author Manuel Aguirre
 */

namespace Optime\Util;

use Symfony\Component\Form\FormError;
use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @author Manuel Aguirre
 */
class TranslatableMessage implements TranslatableInterface
{
    public function __construct(
        private string $message,
        private array $messageParameters = [],
        private ?string $domain = null,
    ) {
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

    public function __toString(): string
    {
        return (string)$this->getMessage();
    }

    public function toFormError(TranslatorInterface $translator): FormError
    {
        return new FormError(
            $this->trans($translator),
            null,
            $this->getMessageParameters()
        );
    }

    public function __serialize(): array
    {
        return [
            $this->message,
            $this->messageParameters,
            $this->domain
        ];
    }

    public function __unserialize(array $data): void
    {
        [
            $this->message,
            $this->messageParameters,
            $this->domain
        ] = $data;
    }

    public function trans(TranslatorInterface $translator, string $locale = null): string
    {
        return $translator->trans($this->getMessage(), $this->getMessageParameters(), $this->getDomain());
    }
}