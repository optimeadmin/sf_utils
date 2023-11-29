<?php
/**
 * @author Manuel Aguirre
 */

namespace Optime\Util\Report\ValueFormat;

/**
 * @author Manuel Aguirre
 */
class DateFormat extends StringFormat
{
    public function __construct(\DateTimeInterface $date)
    {
        parent::__construct($date->format('Y-m-d'), true);
    }
}