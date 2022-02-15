<?php
/**
 * @author Manuel Aguirre
 */

namespace Optime\Util;

use Optime\Util\DependencyInjection\Compiler\AddDoctrineTypesPass;
use Optime\Util\DependencyInjection\Compiler\AddFormThemePass;
use Optime\Util\DependencyInjection\Compiler\ConfigureTranslatableListenerPass;
use Optime\Util\DependencyInjection\Compiler\ConfigureTranslatorRepositoryPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @author Manuel Aguirre
 */
class OptimeUtilBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new ConfigureTranslatorRepositoryPass());
        $container->addCompilerPass(new ConfigureTranslatableListenerPass());
        $container->addCompilerPass(new AddFormThemePass());
        $container->addCompilerPass(new AddDoctrineTypesPass());
    }
}