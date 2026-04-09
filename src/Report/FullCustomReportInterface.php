<?php
/**
 * @author Manuel Aguirre
 */

namespace Optime\Util\Report;

use Optime\Util\Report\Excel\DataUtils;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * @author Manuel Aguirre
 */
interface FullCustomReportInterface extends TableReportInterface
{
    public function customize(Spreadsheet $excel, Worksheet $sheet, DataUtils $dataUtils): void;
}