<?php
/**
 * @author Manuel Aguirre
 */

namespace Optime\Util\Exception;

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

    public static function fromValidationErrors(
        ConstraintViolationListInterface $errors,
        TranslatorInterface $translator = null,
    ): self {
        $error = $errors->get(0);
        $exception = new static($error->getMessage());
        $exception->error = $error;

        if ($translator) {
            $exception->setTranslator($translator);
        }

        return $exception;
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