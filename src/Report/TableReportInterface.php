<?php
/**
 * @author Manuel Aguirre
 */

namespace Optime\Util\Report;

use Optime\Util\Report\ValueFormat\ReportInfo;
use Optime\Util\Report\ValueFormat\StringFormat;

/**
 * @author Manuel Aguirre
 */
interface TableReportInterface
{
    /**
     * @param ReportInfo $reportInfo
     */
    public function configureInfo(ReportInfo $reportInfo): void;

    /**
     * @return array|StringFormat[]
     */
    public function getHeaders(): array;

    /**
     * @return \Generator
     */
    public function getData(): \Generator;
}