<?php

declare(strict_types=1);

namespace Optime\Util\Report\Event;

use Optime\Util\Report\Excel\DataUtils;
use Optime\Util\Report\TableReportInterface;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Symfony\Contracts\EventDispatcher\Event;

class ReportGeneratedEvent extends Event
{
    public function __construct(
        public readonly Worksheet $sheet,
        public readonly TableReportInterface $report,
        public readonly DataUtils $dataUtils,
    ) {
    }
}