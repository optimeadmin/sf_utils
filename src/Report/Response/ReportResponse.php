<?php
/**
 * @author Manuel Aguirre
 */

namespace Optime\Util\Report\Response;

use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\StreamedResponse;
use function ob_get_clean;
use function ob_start;

/**
 * @author Manuel Aguirre
 */
class ReportResponse extends StreamedResponse
{
    public function __construct(callable $callback, string $filename, bool $withProfiler = false)
    {
        $disposition = HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            $filename
        );

        if ($withProfiler) {
            ob_start();
            $callback();
            $content = ob_get_clean();

            $callback = function () use ($content) {
                echo $content;
            };
        }

        parent::__construct($callback, self::HTTP_OK, [
            'Content-Disposition' => $disposition,
        ]);
    }
}