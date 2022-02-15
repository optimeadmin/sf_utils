<?php
/**
 * @author Manuel Aguirre
 */

namespace App\Entity\Traits;

use Doctrine\ORM\Mapping\Column;
use Symfony\Component\Uid\Uuid;

/**
 * @author Manuel Aguirre
 */
trait ExternalUuidTrait
{
    #[Column(name: 'uuid', type: 'string_uuid', unique: true, insertable: true, updatable: false)]
    private Uuid $uuid;

    public function getUuid(): Uuid
    {
        return $this->uuid ??= Uuid::v4();
    }
}