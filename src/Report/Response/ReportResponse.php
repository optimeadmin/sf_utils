<?php
/**
 * @author Manuel Aguirre
 */

namespace Optime\Util\Report\Response;

use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * @author Manuel Aguirre
 */
class ReportResponse extends StreamedResponse
{
    public function __construct(callable $callback, string $filename)
    {
        $disposition = HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            $filename
        );

        parent::__construct($callback, self::HTTP_OK, [
            'Content-Disposition' => $disposition,
        ]);
    }
}