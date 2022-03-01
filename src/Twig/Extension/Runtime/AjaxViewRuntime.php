<?php
/**
 * @author Manuel Aguirre
 */

namespace Optime\Util\Twig\Extension\Runtime;


use Optime\Util\Http\Request\AjaxChecker;
use Twig\Extension\RuntimeExtensionInterface;
use function count;

/**
 * @author Manuel Aguirre
 */
class AjaxViewRuntime implements RuntimeExtensionInterface
{
    private array $partialContents = [];
    private bool $activated = false;

    public function __construct(
        private AjaxChecker $ajaxChecker,
    ) {
    }

    public function activate(): void
    {
        $this->activated = true;
    }

    public function apply(): bool
    {
        return $this->activated && $this->ajaxChecker->isAjax();
    }

    public function setPartialContent(string $ajaxContent, string $sectionName = 'default'): void
    {
        if (!$this->apply()) {
            return;
        }

        $this->partialContents[$sectionName] = $ajaxContent;
    }

    public function getPartialContent(string $sectionName = 'default'): string
    {
        return $this->partialContents[$sectionName] ?? '';
    }

    public function hasContents(): bool
    {
        return 0 !== count($this->partialContents);
    }
}