<?php
/**
 * @author Manuel Aguirre
 */

namespace Optime\Util\Exception;

use Optime\Util\TranslatableMessage;
use Symfony\Component\Form\Extension\Validator\ViolationMapper\ViolationMapper;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @author Manuel Aguirre
 */
class ValidationException extends DomainException
{
    private null|ConstraintViolationInterface|ConstraintViolation $error = null;
    private string $errorPath;

    public static function fromValidationErrors(
        ConstraintViolationListInterface $errors,
        TranslatorInterface $translator = null,
    ): static {
        $error = $errors->get(0);

        $exception = static::create(
            $translator ? $error->getMessageTemplate() : $error->getMessage(),
            $error->getPropertyPath(),
            $error->getParameters(),
            null,
            $translator,
        );
        $exception->error = $error;

        return $exception;
    }

    public static function create(
        string $message,
        string $propertyPath,
        array $parameters = [],
        string $domain = null,
        ?TranslatorInterface $translator = null,
    ): static {
        $exception = new static(new TranslatableMessage(
            $message,
            $parameters,
            $domain ?? 'validators',
        ));

        $exception->errorPath = $propertyPath;

        if ($translator) {
            $exception->setTranslator($translator);
        }

        return $exception;
    }

    public function getError(): ConstraintViolation|ConstraintViolationInterface|null
    {
        return $this->error;
    }

    public function getFieldError(): string
    {
        return $this->error?->getPropertyPath() ?? $this->errorPath ?? '';
    }

    public function toFormError(TranslatorInterface $translator = null): FormError
    {
        $translator = $translator ?: $this->translator;

        if (!$translator) {
            throw new \LogicException(sprintf(
                "Debe pasar el servicio \"%s\" para poder convertir el error en un FormError",
                TranslatorInterface::class
            ));
        }

        return $this->getDomainMessage()->toFormError($translator);
    }

    public function addFormError(FormInterface $form, TranslatorInterface $translator = null): void
    {
        $translator = $translator ?: $this->translator;

        if (!$translator) {
            throw new \LogicException(sprintf(
                "Debe pasar el servicio \"%s\" para poder agregar el error al formulario",
                TranslatorInterface::class
            ));
        }

        if ($this->error && class_exists(ViolationMapper::class)) {
            (new ViolationMapper())->mapViolation($this->getAdjustedError(), $form);
        } else {
            $form->addError($this->toFormError($translator));
        }
    }

    private function getAdjustedError(): ?ConstraintViolationInterface
    {
        if (!$this->error) {
            return null;
        }

        return new ConstraintViolation(
            $this->error->getMessage(),
            $this->error->getMessageTemplate(),
            $this->error->getParameters(),
            $this->error->getRoot(),
            'data.' . $this->error->getPropertyPath(),
            $this->error->getInvalidValue(),
            $this->error->getPlural(),
            $this->error->getCode(),
            $this->error->getConstraint(),
            $this->error->getCause()
        );
    }
}