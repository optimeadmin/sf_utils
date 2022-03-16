<?php
/**
 * @author Manuel Aguirre
 */

namespace Optime\Util\Http\Request;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @author Manuel Aguirre
 */
class AjaxChecker
{
    private ?bool $cachedResult = null;

    public function __construct(
        private RequestStack $requestStack,
        private array $checkConfig,
    ) {
    }

    public function isAjax(Request $request = null): bool
    {
        if (null !== $this->cachedResult) {
            return $this->cachedResult;
        }

        $request ??= $this->requestStack->getMainRequest();

        if ($request->isXmlHttpRequest()) {
            return $this->cachedResult = true;
        }

        if ($header = $this->checkConfig['header'] ?? false) {
            if ($request->headers->has($header)) {
                return $this->cachedResult = true;
            }
        }

        if ($param = $this->checkConfig['param'] ?? false) {
            if ($request->query->has($param) || $request->request->has($param)) {
                return $this->cachedResult = true;
            }
        }

        return $this->cachedResult = false;
    }
}