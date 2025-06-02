<?php

declare(strict_types=1);

namespace Optime\Util\Http\Controller;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD)]
class HandleDomainException
{
    public function __construct(
        public readonly ?int $statusCode = null,
    ) {
    }
}