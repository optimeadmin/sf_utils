<?php
/**
 * @author Manuel Aguirre
 */

declare(strict_types=1);

namespace Optime\Util\Report\Excel;

use OutOfRangeException;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

/**
 * @author Manuel Aguirre
 */
class PrintedInfo
{
    private readonly int $nextRow;

    public function __construct(
        private readonly int $headersRow,
        private readonly array $headerKeys,
    ) {
    }

    public function setNextRow(int $nextRow): void
    {
        $this->nextRow = $nextRow;
    }

    public function getHeadersRow(): int
    {
        return $this->headersRow;
    }

    public function getNextRow(): int
    {
        return $this->nextRow;
    }

    public function getColIndexByHeader(string|int $headerKey): int
    {
        $headerIndex = $this->headerKeys[$headerKey] ?? null;
        if (null === $headerIndex) {
            throw new OutOfRangeException("No se encontró el key '{$headerKey}'");
        }

        return $headerIndex + 1;
    }

    public function getColNameByHeader(string|int $headerKey): string
    {
        return Coordinate::stringFromColumnIndex($this->getColIndexByHeader($headerKey));
    }
}