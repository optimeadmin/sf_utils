<?php

namespace Optime\Util\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class AddFormThemePass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasParameter('twig.form.resources')){
            return;
        }

        $resources = $container->getParameter('twig.form.resources');
        array_unshift($resources, '@OptimeUtil/form.html.twig');
        $container->setParameter('twig.form.resources', $resources);

//        if ($container->hasDefinition(TwigRendererEngine::class)) {
//            $definition = $container->getDefinition(TwigRendererEngine::class);
//        }elseif ($container->hasDefinition('twig.form.engine')){
//            $definition = $container->getDefinition('twig.form.engine');
//        }else{
//            return;
//        }
    }
}
