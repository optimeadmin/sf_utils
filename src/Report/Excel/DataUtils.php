<?php

declare(strict_types=1);

namespace Optime\Util\Report\Excel;

use Optime\Util\Report\ValueFormat\FormulaFormat;

class DataUtils
{
    private array $cachedFormulas = [];

    public function __construct(
        public readonly PrintedInfo $printedInfo,
    ) {
    }

    public function getColIndexByHeader(string|int $headerKey): int
    {
        return $this->printedInfo->getColIndexByHeader($headerKey);
    }

    public function getColNameByHeader(string|int $headerKey): string
    {
        return $this->printedInfo->getColNameByHeader($headerKey);
    }

    public function coordinate(string $headerKey, ?int $row = null): string
    {
        $col = $this->getColNameByHeader($headerKey);
        $row = $row ?? '{row}';

        return "{$col}{$row}";
    }

    /**
     * Permite construir una formula usando placeholders para los headers
     *
     * Ejemplo
     *
     * =({price} * {quantity})
     *
     * Esto se convierte en algo como: =(B{row} * E{row})
     *
     * @param string $formula
     * @return string
     */
    public function parseFormula(string $formula): string
    {
        return $this->cachedFormulas[$formula] ??= preg_replace_callback(
            '/\{([^}]+)\}/',
            function (array $matches) use (&$formula) {
                $headerKey = $matches[1];

                return $this->coordinate($headerKey);
            },
            $formula
        );
    }
}