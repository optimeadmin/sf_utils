<?php
/**
 * @author Manuel Aguirre
 */

namespace Optime\Util\Report\Excel;

use LogicException;
use Optime\Util\Report\TableReportInterface;
use Optime\Util\Report\TabsReportInterface;
use Optime\Util\Report\ValueFormat\DateFormat;
use Optime\Util\Report\ValueFormat\ReportInfo;
use Optime\Util\Report\ValueFormat\StringFormat;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use function array_flip;
use function array_keys;
use function class_exists;
use function gettype;
use function is_object;
use function sprintf;

/**
 * @author Manuel Aguirre
 */
class ReportGenerator
{
    public function __construct(private ReportGenerationUtils $reportUtils)
    {
    }

    public function generate(TableReportInterface $report): void
    {
        $excel = $this->getSpreadsheet();
        $sheet = $excel->getActiveSheet();

        $this->generateTab($sheet, $report);

        $this->reportUtils->printExcel($excel);
    }

    public function generateWithTabs(TabsReportInterface $report): void
    {
        $excel = $this->getSpreadsheet();
        $excel->removeSheetByIndex($excel->getActiveSheetIndex());

        foreach ($report->getTabs() as $tabReport) {
            if (!$tabReport instanceof TableReportInterface) {
                throw new \InvalidArgumentException(sprintf(
                    "Se esperaba una instancia de '%s' pero llegó '%s'",
                    TableReportInterface::class,
                    is_object($tabReport) ? $tabReport::class : gettype($tabReport),
                ));
            }

            $this->generateTab($excel->createSheet(), $tabReport);
        }

        $excel->setActiveSheetIndex(0);

        $this->reportUtils->printExcel($excel);
    }

    private function generateTab(Worksheet $sheet, TableReportInterface $report): void
    {
        $headers = $report->getHeaders();
        $reportInfo = new ReportInfo(new StringFormat("Report"));
        $report->configureInfo($reportInfo);

        if (null !== $reportInfo->getTabName()) {
            $sheet->setTitle($reportInfo->getTabName());
        }

        if ($reportInfo->canBePrinted()) {
            $this->fillReportInfo($sheet, $reportInfo);
        }

        $row = $reportInfo->canBePrinted() ? $reportInfo->getRowsCount() + 2 : 1;

        $sheet->getRowDimension($row)->setRowHeight(30);

        $this->fillRow($sheet, $headers, $row);
        $this->reportUtils->adjustColumnWidths($sheet, $headers);

        $indexes = array_flip(array_keys($headers));

        $row++;

        foreach ($report->getData() as $rowData) {
            $this->fillRow($sheet, $rowData, $row++, $indexes);
        }
    }

    private function fillRow(Worksheet $sheet, array $rowData, int $row, array $indexes = null): void
    {
        $col = 1;

        foreach ($rowData as $index => $value) {
            if (null === $indexes) {
                $cell = $sheet->getCellByColumnAndRow($col++, $row);
            } elseif (isset($indexes[$index])) {
                $cell = $sheet->getCellByColumnAndRow($indexes[$index] + 1, $row);
            } else {
                continue;
            }

            $this->reportUtils->writeCell($sheet, $cell, $value);
        }
    }

    private function fillReportInfo(Worksheet $sheet, ReportInfo $reportInfo): void
    {
        $row = 1;

        $this->reportUtils->writeIn($sheet, 1, $row++, $reportInfo->getTitle());

        foreach ($reportInfo->getSubtitles() as $subtitle) {
            $this->reportUtils->writeIn($sheet, 1, $row++, $subtitle);
        }

        $this->reportUtils->writeIn($sheet, 1, $row++, new StringFormat(
            'Date: ' . (new DateFormat($reportInfo->getGeneratedAt()))
        ));
    }

    /**
     * @return Spreadsheet
     */
    private function getSpreadsheet(): Spreadsheet
    {
        if (!class_exists(Spreadsheet::class)) {
            throw new LogicException("Se debe instalar la libreria 'phpoffice/phpspreadsheet' para poder usar el generador de reportes");
        }

        return new Spreadsheet();
    }

}