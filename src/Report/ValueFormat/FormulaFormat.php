<?php
/**
 * @author Manuel Aguirre
 */

declare(strict_types=1);

namespace Optime\Util\Report\ValueFormat;

use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Stringable;
use function call_user_func;
use function dump;
use function str_replace;

/**
 * @author Manuel Aguirre
 */
class FormulaFormat extends CallbackFormat
{

    public function __construct(string|Stringable $formula, bool $centered = false)
    {
        $callback = function (Cell $cell) use ($formula) {
            $row = $cell->getRow();

            $value = str_replace('{row}', (string)$row, $formula);
            $cell->setValue($value);
        };

        parent::__construct($callback, $centered);
    }
}