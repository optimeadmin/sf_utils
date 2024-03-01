<?php
/**
 * @author Manuel Aguirre
 */

declare(strict_types=1);

namespace Optime\Util\Report\Excel;

use LogicException;
use Optime\Util\Report\ValueFormat\CallbackFormat;
use Optime\Util\Report\ValueFormat\ListDataHeaderFormat;
use Optime\Util\Report\ValueFormat\ReportInfo;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use function array_filter;
use function count;
use function is_numeric;
use function sprintf;

/**
 * @author Manuel Aguirre
 */
class DataListUtils
{
    private const DATA_LIST_SHEET_NAME = '__DATA_LIST__';

    private int $lastDataListCol = 1;
    private array $dataListHeaders = [];

    public function __construct(private readonly ReportGenerationUtils $reportUtils)
    {
    }

    public function initialize(): void
    {
        $this->lastDataListCol = 1;
    }

    public function getDataListHeaders(): array
    {
        return $this->dataListHeaders;
    }

    public function configureDataListsFromHeaders(Spreadsheet $excel, array $headers, ReportInfo $reportInfo): void
    {
        $this->dataListHeaders = [];

        $dataListsHeaders = array_filter($headers, fn($h) => $h instanceof ListDataHeaderFormat);

        if (count($dataListsHeaders) === 0) {
            return;
        }

        if (!$excel->sheetNameExists(self::DATA_LIST_SHEET_NAME)) {
            $activeIndex = $excel->getActiveSheetIndex();
            $sheet = $excel->createSheet();
            $sheet->setTitle(self::DATA_LIST_SHEET_NAME);
            $excel->setActiveSheetIndex($activeIndex);

            if (!$reportInfo->isShowDataListSheet()) {
                $sheet->setSheetState(Worksheet::SHEETSTATE_HIDDEN);
            }
        } else {
            $sheet = $excel->getSheetByName(self::DATA_LIST_SHEET_NAME);
        }

        /**
         * @var ListDataHeaderFormat $header
         */
        foreach ($dataListsHeaders as $index => $header) {
            if (is_numeric($index)) {
                throw new LogicException("El uso de ListDataHeaderFormat no soporta indices númericos para los headers del reporte");
            }

            $values = $header->getValues();
            $col = $this->lastDataListCol++;
            $row = 1;
            $sheet->getCell([$col, $row++])->setValue($header->getValue());

            foreach ($values as $value) {
                $sheet->getCell([$col, $row++])->setValue($value);
            }

            $colName = Coordinate::stringFromColumnIndex($col);
            $formula = sprintf("'%s'!$%s$%s:$%s$%s", self::DATA_LIST_SHEET_NAME, $colName, 2, $colName, $row - 1);

            $this->dataListHeaders[$index] = new CallbackFormat(function (Cell $cell) use ($formula) {
                $v = $cell->getDataValidation();
                $v->setType(DataValidation::TYPE_LIST);
                $v->setShowDropDown(true);
                $v->setAllowBlank(true);
                $v->setFormula1($formula);
            });
        }
    }

    public function fillRow(Worksheet $sheet, int $row, array $indexes = null): void
    {
        foreach ($this->dataListHeaders as $headerIndex => $dataListHeader) {
            $colIndex = $indexes[$headerIndex] ?? null;
            if (null === $colIndex) {
                continue;
            }

            $cell = $sheet->getCell([$colIndex + 1, $row]);
            $this->reportUtils->writeCell($sheet, $cell, $dataListHeader);
        }
    }
}