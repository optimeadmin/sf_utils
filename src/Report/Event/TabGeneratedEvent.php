<?php

declare(strict_types=1);

namespace Optime\Util\Report\Event;

use Optime\Util\Report\Excel\DataUtils;
use Optime\Util\Report\TableReportInterface;
use Optime\Util\Report\TabsReportInterface;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Symfony\Contracts\EventDispatcher\Event;

class TabGeneratedEvent extends Event
{
    public function __construct(
        public readonly Spreadsheet $excel,
        public readonly Worksheet $sheet,
        public readonly TabsReportInterface $tabsReportContainer,
        public readonly TableReportInterface $reportItem,
        public readonly DataUtils $dataUtils,
    ) {
    }
}