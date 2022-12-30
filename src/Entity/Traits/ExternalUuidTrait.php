<?php
/**
 * @author Manuel Aguirre
 */

namespace Optime\Util\Entity\Traits;

use Doctrine\ORM\Mapping\Column;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;

/**
 * @author Manuel Aguirre
 */
trait ExternalUuidTrait
{
    #[Groups("Uuid")]
    #[Column(name: 'uuid', type: 'string_uuid', unique: true, insertable: true, updatable: false)]
    private Uuid $uuid;

    public function getUuid(): Uuid
    {
        return $this->uuid ??= Uuid::v4();
    }
}