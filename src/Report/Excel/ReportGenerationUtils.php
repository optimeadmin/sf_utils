<?php
/**
 * @author Manuel Aguirre
 */

namespace Optime\Util\Report\Excel;

use Optime\Util\Report\ValueFormat\HeaderFormat;
use Optime\Util\Report\ValueFormat\HtmlFormat;
use Optime\Util\Report\ValueFormat\LinkFormat;
use Optime\Util\Report\ValueFormat\StringFormat;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Symfony\Component\Asset\Packages;
use Symfony\Component\HttpFoundation\UrlHelper;
use function dd;
use const PHP_EOL;

/**
 * @author Manuel Aguirre
 */
class ReportGenerationUtils
{
    public function __construct(
        private Packages $packages,
        private UrlHelper $urlHelper
    ) {
    }

    public function printExcel(Spreadsheet $spreadsheet): void
    {
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');
    }

    public function writeCell(Worksheet $sheet, Cell $cell, StringFormat $value): void
    {
        if ($value instanceof HtmlFormat) {
            $cell->setValue($value->getRichText());
            $cell->getStyle()->getAlignment()->setVertical(Alignment::VERTICAL_TOP);
        } else {
            $cell->setValue($value);
        }

        if ($value instanceof HeaderFormat) {
            $this->applyHeaderFormat($cell, $value);
            return;
        }

        if ($value instanceof LinkFormat && $value->getLink()) {
            $cell->getHyperlink()->setUrl($this->buildUrl($value->getLink()));
            $this->applyLinkFormat($cell);
        }

        if ($value->isCentered()) {
            $cell->getStyle()->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        if ($value->isBold()) {
            $cell->getStyle()->getFont()->setBold(true);
        }

        if (null !== $value->getColor()) {
            $cell->getStyle()->getFont()->setColor(new Color($value->getColor()));
        }

        if (null !== $value->getBgColor()) {
            $cell->getStyle()->getFill()->applyFromArray([
                'fillType' => Fill::FILL_SOLID,
                'color' => [
                    'argb' => $value->getBgColor(),
                ]
            ]);
        }
    }

    public function writeIn(Worksheet $sheet, int $col, int $row, StringFormat $value): void
    {
        $this->writeCell($sheet, $sheet->getCellByColumnAndRow($col, $row), $value);
    }

    public function adjustColumnWidths(Worksheet $sheet, iterable $headers, int $from = 1): void
    {
        foreach ($headers as $header) {
            if ($header instanceof HeaderFormat && null !== $header->getWidth()) {
                $sheet->getColumnDimensionByColumn($from++)->setWidth($header->getWidth(), 'px');
            } else {
                $sheet->getColumnDimensionByColumn($from++)->setAutoSize(true);
            }
        }
    }

    public function applyHeaderFormat(Cell $cell, HeaderFormat $value): void
    {
        $cell->getStyle()->applyFromArray([
            'font' => [
                'bold' => true,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'top' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => [
                    'argb' => $value->getBgColor() ?? 'A6A6A6',
                ],
            ],
        ]);
    }

    public function applyLinkFormat(Cell $cell): void
    {
        $cell->getStyle()->applyFromArray([
            'font' => [
                'color' => ['rgb' => '0000FF'],
            ],
            'underline' => 'single',
        ]);
    }

    public function markCellWithError(Worksheet $sheet, Cell $cell, string $message = null): void
    {
        $cell->getStyle()->applyFromArray([
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => [
//                    'argb' => 'A6A6A6',
                    'argb' => 'ff7676',
                ],
            ],
        ]);

        if (null !== $message) {
            $sheet->getComment($cell->getCoordinate())
                ->getText()->createTextRun(trim($message) . PHP_EOL);
        }
    }

    private function buildUrl(string $path): string
    {
        return $this->urlHelper->getAbsoluteUrl($this->packages->getUrl($path));
    }
}