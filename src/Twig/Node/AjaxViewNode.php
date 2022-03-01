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
    public function __construct(Node $body, int $lineno = 0, string $tag = null)
    {
        parent::__construct(['body' => $body], [], $lineno, $tag);
    }

    public function compile(Compiler $compiler)
    {
        $compiler->addDebugInfo($this)
            ->write("echo '<!--partial-ajax-init-->';\n")
            ->write("ob_start();\n")
            ->subcompile($this->getNode('body'))
            ->write(sprintf(
                "\$ajaxRuntime = \$this->env->getRuntime('%s');\n",
                AjaxViewRuntime::class,
            ))
            ->write("if (\$ajaxRuntime->apply()) {\n")
            ->indent()
            ->write("\$ajaxRuntime->setPartialContent(ob_get_contents());\n")
            ->outdent()
            ->write("}\n")
            ->write("ob_end_flush();\n")
            ->write("echo '<!--end-partial-ajax-init-->';\n");
    }
}