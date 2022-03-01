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
    public function __construct(private RequestStack $requestStack)
    {
    }

    public function isAjax(Request $request = null): bool
    {
        $request ??= $this->requestStack->getMainRequest();

        if ($request->isXmlHttpRequest()) {
            return true;
        }

        return false;
    }
}