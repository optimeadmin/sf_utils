<?php

declare(strict_types=1);

namespace Optime\Util\Report\Event;

use Optime\Util\Report\TableReportInterface;
use Optime\Util\Report\ValueFormat\ReportInfo;
use Symfony\Contracts\EventDispatcher\Event;

class ConfigureReportInfoEvent extends Event
{
    public function __construct(
        public readonly TableReportInterface $report,
        public readonly ReportInfo $reportInfo,
    ) {
    }
}