<?php
/**
 * @author Manuel Aguirre
 */

namespace Optime\Util\DependencyInjection\Compiler;


use Optime\Util\Twig\Loader\AjaxFileLoader;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author Manuel Aguirre
 */
class ConfigureAjaxTwigLoaderPass implements CompilerPassInterface
{

    public function process(ContainerBuilder $container)
    {
        if (!$container->getParameter('optime.sf_utils.use_ajax_twig_loader')) {
            return;
        }

        if (!$container->has('twig.loader.native_filesystem')) {
            return;
        }

        $container
            ->getDefinition(AjaxFileLoader::class)
            ->setArgument(0, new Reference(AjaxFileLoader::class.'.inner'))
            ->setDecoratedService('twig.loader.native_filesystem');
    }
}