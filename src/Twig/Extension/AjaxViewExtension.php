<?php
/**
 * @author Manuel Aguirre
 */

namespace Optime\Util\Twig\Extension;


use Optime\Util\Twig\TokenParser\AjaxViewTokenParser;
use Twig\Extension\AbstractExtension;

/**
 * @author Manuel Aguirre
 */
class AjaxViewExtension extends AbstractExtension
{
    public function getTokenParsers()
    {
        return [
            new AjaxViewTokenParser(),
        ];
    }
}