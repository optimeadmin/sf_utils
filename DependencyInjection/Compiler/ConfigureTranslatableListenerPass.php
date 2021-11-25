<?php

namespace Optime\Util\DependencyInjection\Compiler;

use Gedmo\Translatable\TranslatableListener;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ConfigureTranslatableListenerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if ($container->has(TranslatableListener::class)) {
            return;
        }

        if (!$container->has('stof_doctrine_extensions.listener.translatable')) {
            return;
        }

        $container->setAlias(TranslatableListener::class, 'stof_doctrine_extensions.listener.translatable');
    }
}
