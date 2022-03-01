<?php
/**
 * @author Manuel Aguirre
 */

namespace Optime\Util\Twig\Extension\Runtime;


use Optime\Util\Http\Request\AjaxChecker;
use Twig\Extension\RuntimeExtensionInterface;

/**
 * @author Manuel Aguirre
 */
class AjaxViewRuntime implements RuntimeExtensionInterface
{
    private array $partialContents = [];

    public function __construct(
        private AjaxChecker $ajaxChecker,
    ) {
    }

    public function apply(): bool
    {
        return $this->ajaxChecker->isAjax();
    }

    public function setPartialContent(string $ajaxContent, string $sectionName = 'default'): void
    {
        if (!$this->apply()) {
            return;
        }

        $this->partialContents[$sectionName] = $ajaxContent;
    }

    public function getPartialContent(string $sectionName = 'default'): ?string
    {
        return $this->partialContents[$sectionName] ?? null;
    }
}