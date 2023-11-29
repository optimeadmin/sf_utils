<?php
/**
 * @author Manuel Aguirre
 */

namespace Optime\Util\Report;

/**
 * @author Manuel Aguirre
 */
interface TabsReportInterface
{
    /**
     * @return \Generator|TableReportInterface[]
     */
    public function getTabs(): \Generator;
}