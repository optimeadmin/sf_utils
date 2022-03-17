<?php
/**
 * @author Manuel Aguirre
 */

namespace Optime\Util\Http\Controller;


use Attribute;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Manuel Aguirre
 */
#[Attribute(Attribute::TARGET_METHOD)]
class HandleAjaxForm
{
    public function __construct(
        private ?string $type = null,
        private int $invalidStatus = Response::HTTP_UNPROCESSABLE_ENTITY,
        private bool $preventRedirect = true,
        private bool $replaceRedirectContent = true,
    ) {
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function getInvalidStatus(): int
    {
        return $this->invalidStatus;
    }

    public function isPreventRedirect(): bool
    {
        return $this->preventRedirect;
    }

    public function isReplaceRedirectContent(): bool
    {
        return $this->replaceRedirectContent;
    }
}