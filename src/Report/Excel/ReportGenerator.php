<?php
/**
 * @author Manuel Aguirre
 */

namespace Optime\Util\Report\Excel;

use LogicException;
use Optime\Util\Report\FullCustomReportInterface;
use Optime\Util\Report\Response\ReportResponse;
use Optime\Util\Report\TableReportInterface;
use Optime\Util\Report\TabsReportInterface;
use Optime\Util\Report\ValueFormat\DateFormat;
use Optime\Util\Report\ValueFormat\HeaderFormat;
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
    public function __construct(
        private readonly ReportGenerationUtils $reportUtils,
        private readonly DataListUtils $dataListUtils,
    ) {
    }

    public function generate(TableReportInterface $report): void
    {
        $this->dataListUtils->initialize();

        $excel = $this->getSpreadsheet();
        $sheet = $excel->getActiveSheet();

        $this->generateTab($excel, $sheet, $report);

        $this->reportUtils->printExcel($excel);
    }

    public function generateWithTabs(TabsReportInterface $report): void
    {
        $this->dataListUtils->initialize();

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

            $this->generateTab($excel, $excel->createSheet(), $tabReport);
        }

        $excel->setActiveSheetIndex(0);

        $this->reportUtils->printExcel($excel);
    }

    public function generateWithResponse(
        TableReportInterface|TabsReportInterface $report,
        string $filename,
        bool $withProfiler = false
    ): ReportResponse {
        return new ReportResponse(function () use ($report) {
            if ($report instanceof TabsReportInterface) {
                $this->generateWithTabs($report);
            } else {
                $this->generate($report);
            }
        }, $filename, $withProfiler);
    }

    private function generateTab(Spreadsheet $excel, Worksheet $sheet, TableReportInterface $report): void
    {
        $headers = $report->getHeaders();
        $reportInfo = new ReportInfo(new StringFormat("Report"));
        $report->configureInfo($reportInfo);

        $this->configHeadersFromInfo($reportInfo, $headers);

        if (null !== $reportInfo->getTabName()) {
            $sheet->setTitle($reportInfo->getTabName());
        }

        if ($reportInfo->canBePrinted()) {
            $this->fillReportInfo($sheet, $reportInfo);
        }

        $row = $reportInfo->getRowsCount();

        $sheet->getRowDimension($row)->setRowHeight(30);

        $this->fillRow($sheet, $headers, $row);
        $this->reportUtils->adjustColumnWidths($sheet, $headers);

        // importante hacer esto luego de pintar los headers
        $this->dataListUtils->configureDataListsFromHeaders($excel, $headers, $reportInfo);

        $indexes = array_flip(array_keys($headers));
        $row++;
        $printedInfo = new PrintedInfo($row, $indexes);

        foreach ($report->getData() as $rowData) {
            $this->fillRow($sheet, $rowData, $row++, $indexes);
        }

        if ($report instanceof FullCustomReportInterface) {
            $printedInfo->setNextRow($row);
            $report->customize($excel, $sheet, $printedInfo);
        }
    }

    private function fillRow(Worksheet $sheet, array $rowData, int $row, array $indexes = null): void
    {
        $col = 1;

        foreach ($rowData as $index => $value) {
            if (null === $indexes) {
                $cell = $sheet->getCell([$col++, $row]);
            } elseif (isset($indexes[$index])) {
                $cell = $sheet->getCell([$indexes[$index] + 1, $row]);
            } else {
                continue;
            }

            $this->reportUtils->writeCell($sheet, $cell, $value);
        }

        $this->dataListUtils->fillRow($sheet, $row, $indexes);
    }

    private function fillReportInfo(Worksheet $sheet, ReportInfo $reportInfo): void
    {
        $row = 1;

        $this->reportUtils->writeIn($sheet, 1, $row++, $reportInfo->getTitle());

        foreach ($reportInfo->getSubtitles() as $subtitle) {
            $this->reportUtils->writeIn($sheet, 1, $row++, $subtitle);
        }

        if ($date = $reportInfo->getGeneratedAt()) {
            $this->reportUtils->writeIn($sheet, 1, $row++, new StringFormat(
                'Date: ' . (new DateFormat($date))
            ));
        }
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

    private function configHeadersFromInfo(ReportInfo $reportInfo, array $headers): void
    {
        if ($bgColor = $reportInfo->getHeadersBgColor()) {
            /** @var HeaderFormat $header */
            foreach ($headers as $header) {
                if (!$header->getBgColor()) {
                    $header->bgColor($bgColor);
                }
            }
        }
        if ($color = $reportInfo->getHeadersColor()) {
            /** @var HeaderFormat $header */
            foreach ($headers as $header) {
                if (!$header->getColor()) {
                    $header->color($color);
                }
            }
        }
    }
}