<?php
/**
 * Optime Consulting
 * User: maguirre@optimeconsulting.com
 * Date: 19/02/2020
 * Time: 12:24 PM
 */

namespace App\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;
use Optime\Util\Entity\Embedded\Date;

trait DatesTrait
{
    #[ORM\Embedded(class: Date::class, columnPrefix: false)]
    protected Date $dates;

    public function getDates(): Date
    {
        return $this->dates ??= new Date();
    }
}