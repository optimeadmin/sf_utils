<?php
/**
 * @author Manuel Aguirre
 */

namespace Optime\Util\Translation\Validation;

use Attribute;
use Symfony\Component\Validator\Constraints\All;

/**
 * @Annotation
 *
 * @author Manuel Aguirre
 */
#[Attribute]
class TranslatableConstraint extends All
{
    public string $errorsPath;

    public function __construct(
        mixed $constraints = null,
        string $errorsPath = 'values',
        array $groups = null,
        mixed $payload = null,
    ) {
        parent::__construct($constraints, $groups, $payload);
        $this->errorsPath = $errorsPath;
    }
}