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
    public function __construct(
        private RequestStack $requestStack,
        private array $checkConfig,
    ) {
    }

    public function isAjax(Request $request = null): bool
    {
        $request ??= $this->requestStack->getMainRequest();

        if ($request->isXmlHttpRequest()) {
            return true;
        }

        if ($header = $this->checkConfig['header'] ?? false) {
            if ($request->headers->has($header)) {
                return true;
            }
        }

        if ($param = $this->checkConfig['param'] ?? false) {
            if ($request->query->has($param) || $request->request->has($param)) {
                return true;
            }
        }

        return false;
    }
}