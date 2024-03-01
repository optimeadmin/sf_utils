<?php
/**
 * @author Manuel Aguirre
 */

namespace Optime\Util\Report\Excel;

use Optime\Util\Report\ValueFormat\CallbackFormat;
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
use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use function dump;
use const PHP_EOL;

/**
 * @author Manuel Aguirre
 */
class ReportGenerationUtils
{
    public function __construct(
        private readonly Packages $packages,
        private readonly UrlHelper $urlHelper,
        private readonly ?TranslatorInterface $translator,
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
        } elseif ($value instanceof CallbackFormat) {
            $value($cell);

            if (null === $cell->getValue()) {
                return;
            }
        } else {
            $content = $value->getValue();

            if ($content instanceof TranslatableInterface && $this->translator) {
                $cell->setValue($content->trans($this->translator));
            } else {
                $cell->setValue($content);
            }

        }

        if ($value instanceof HeaderFormat) {
            $this->applyHeaderFormat($cell, $value);
            return;
        }

        if ($value instanceof LinkFormat && $value->getLink()) {
            $cell->getHyperlink()->setUrl($this->buildUrl($value->getLink()));
            $this->applyLinkFormat($cell);
        }

        $this->applyDefaultStyles($value, $cell);
    }

    public function writeIn(Worksheet $sheet, int $col, int $row, StringFormat $value): void
    {
        $this->writeCell($sheet, $sheet->getCell([$col, $row]), $value);
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
        $style = $cell->getStyle();
        $alignment = $value->getAlignment() ?? ($value->isCentered()
            ? Alignment::HORIZONTAL_CENTER
            : Alignment::HORIZONTAL_LEFT
        );

        $style->applyFromArray([
            'font' => [
                'bold' => true,
            ],
            'alignment' => [
                'horizontal' => $alignment,
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

        if (null !== $value->getColor()) {
            $style->getFont()->setColor(new Color($value->getColor()));
        }
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

    private function applyDefaultStyles(StringFormat $value, Cell $cell): void
    {
        if (null !== $value->getAlignment()) {
            $cell->getStyle()->getAlignment()->setHorizontal($value->getAlignment());
        } elseif ($value->isCentered()) {
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
}