<?php
/**
 * @author Manuel Aguirre
 */

namespace Optime\Util\Exception;

use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @author Manuel Aguirre
 */
class ValidationException extends DomainException
{
    public static function fromValidationErrors(ConstraintViolationListInterface $errors): self
    {
        return new static($errors->get(0)->getMessage());
    }

    public function toFormError(TranslatorInterface $translator): FormError
    {
        return $this->getDomainMessage()->toFormError($translator);
    }

    public function addFormError(FormInterface $form, TranslatorInterface $translator): void
    {
        $form->addError($this->toFormError($translator));
    }
}