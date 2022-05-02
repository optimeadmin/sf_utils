<?php
/**
 * @author Manuel Aguirre
 */

namespace Optime\Util\Entity\Traits;

use LogicException;
use function method_exists;

/**
 * @author Manuel Aguirre
 */
trait EqualEntityIdTrait
{
    public function equalTo(object $otherEntity): bool
    {
        if (!$otherEntity instanceof self) {
            return false;
        }

        if (!method_exists($this, 'getId')) {
            throw new LogicException(
                "El trait " . EqualEntityIdTrait::class . " Necesita que exista el método getId donde sea usado"
            );
        }

        return $this->getId() === $otherEntity->getId();
    }
}