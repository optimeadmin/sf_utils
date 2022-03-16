<?php
/**
 * @author Manuel Aguirre
 */

namespace Optime\Util\Twig\Loader;

use Optime\Util\Http\Request\AjaxChecker;
use Twig\Loader\FilesystemLoader;
use Twig\Loader\LoaderInterface;
use Twig\Source;
use function dd;
use function end;
use function explode;
use function preg_replace;
use function str_contains;
use function str_replace;

/**
 * @author Manuel Aguirre
 */
class AjaxFileLoader implements LoaderInterface
{
    public function __construct(
        private FilesystemLoader $loader,
        private AjaxChecker $ajaxChecker,
    ) {
    }

    public function getSourceContext(string $name): Source
    {
        return $this->loader->getSourceContext($this->getName($name));
    }

    public function getCacheKey(string $name): string
    {
        return $this->loader->getCacheKey($this->getName($name));
    }

    public function isFresh(string $name, int $time): bool
    {
        return $this->loader->isFresh($this->getName($name), $time);
    }

    public function exists(string $name)
    {
        return $this->loader->exists($this->getName($name));
    }

    private function getName(string $originalName): string
    {
        if (!$this->ajaxChecker->isAjax()) {
            return $originalName;
        }

        $ajaxName = $this->ajaxName($originalName);

        return $this->loader->exists($ajaxName) ? $ajaxName : $originalName;
    }

    private function ajaxName(string $name): string
    {
        if (str_contains($name, ':')) {
            $parts = explode(':', $name);
        } else {
            $parts = explode('/', $name);
        }

        $file = end($parts);
        $ajaxFile = preg_replace('#^(.+?)\.(.+?)\.(.+?)$#', '$1__ajax.$2.$3', $file);

        return str_replace($file, $ajaxFile, $name);
    }
}