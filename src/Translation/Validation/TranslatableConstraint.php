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

}