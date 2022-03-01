<?php
/**
 * @author Manuel Aguirre
 */

namespace Optime\Util\Twig\Node;


use Optime\Util\Twig\Extension\Runtime\AjaxViewRuntime;
use Twig\Compiler;
use Twig\Node\Node;

/**
 * @author Manuel Aguirre
 */
class AjaxViewNode extends Node
{
    public function __construct(Node $body, string $partialName, int $lineno = 0, string $tag = null)
    {
        parent::__construct(['body' => $body], ['name' => $partialName], $lineno, $tag);
    }

    public function compile(Compiler $compiler)
    {
        $compiler->addDebugInfo($this)
            ->write("echo '<!-- partial-ajax-init -->';\n")
            ->write("ob_start();\n")
            ->subcompile($this->getNode('body'))
            ->write(sprintf(
                "\$ajaxRuntime = \$this->env->getRuntime('%s');\n",
                AjaxViewRuntime::class,
            ))
            ->write("if (\$ajaxRuntime->apply()) {\n")
            ->indent()
            ->write(sprintf(
                "\$ajaxRuntime->setPartialContent(ob_get_contents(), '%s');\n",
                $this->getAttribute('name'),
            ))
            ->outdent()
            ->write("}\n")
            ->write("ob_end_flush();\n")
            ->write("echo '<!-- partial-ajax-end -->';\n");
    }
}