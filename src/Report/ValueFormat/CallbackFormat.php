<?php
/**
 * @author Manuel Aguirre
 */

declare(strict_types=1);

namespace Optime\Util\Report\ValueFormat;

use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use function call_user_func;

/**
 * @author Manuel Aguirre
 */
class CallbackFormat extends StringFormat
{
    private $callback;

    public function __construct(callable $callback, bool $centered = false)
    {
        $this->callback = $callback;

        parent::__construct(null, $centered);
    }

    public function __invoke(Cell $cell): void
    {
        call_user_func($this->callback, $cell);
    }
}