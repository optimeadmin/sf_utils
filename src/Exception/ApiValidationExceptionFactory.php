<?php
/**
 * @author Manuel Aguirre
 */

declare(strict_types=1);

namespace Optime\Util\Exception;

use ApiPlatform\Symfony\Validator\Exception\ValidationException;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Throwable;

/**
 * @author Manuel Aguirre
 */
final class ApiValidationExceptionFactory
{
    public static function fromError(Throwable $error, string $property = null): ValidationException
    {
        return self::fromMessage($error->getMessage(), $property);
    }

    public static function fromMessage(string $message, string $property = null): ValidationException
    {
        $violationList = new ConstraintViolationList([
            new ConstraintViolation($message, $message, [], null, $property, null)
        ]);

        return self::fromViolations($violationList);
    }

    public static function fromViolations(ConstraintViolationListInterface $list): ValidationException
    {
        return new ValidationException($list);
    }
}