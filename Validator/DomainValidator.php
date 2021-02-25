<?php
/**
 * @author Manuel Aguirre
 */

namespace Optime\Util\Validator;

use Optime\Util\Exception\ValidationException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author Manuel Aguirre
 */
class DomainValidator
{
    /**
     * @var ValidatorInterface
     */
    private $validator;

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    public function handle($value, $groups = null): void
    {
        $errors = $this->validator->validate($value, null, $groups);

        if (0 < count($errors)) {
            throw ValidationException::fromValidationErrors($errors);
        }
    }

    public function isValid($value, $groups = null): bool
    {
        $errors = $this->validator->validate($value, null, $groups);

        return 0 === count($errors);
    }
}