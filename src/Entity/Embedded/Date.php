<?php
/**
 * Optime Consulting
 * User: maguirre@optimeconsulting.com
 * Date: 19/02/2020
 * Time: 12:01 PM
 */

namespace Optime\Util\Entity\Embedded;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

/**
 * @author Manuel Aguirre
 */
#[ORM\Embeddable]
class Date
{
    #[ORM\Column(
        name: 'created_at',
        updatable: false,
        generated: 'INSERT',
    )]
    protected DateTimeImmutable $createdAt;

    #[ORM\Column(
        name: 'updated_at',
        nullable: true,
        insertable: false,
        updatable: false,
        columnDefinition: 'timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
        generated: "ALWAYS",
    )]
    protected DateTimeImmutable $updatedAt;

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }
}