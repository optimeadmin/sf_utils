<?php

namespace Optime\Util\DependencyInjection\Compiler;

use Optime\Util\Doctrine\Type\StringUuidType;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class AddDoctrineTypesPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasParameter('doctrine.dbal.connection_factory.types')) {
            return;
        }

        $typeDefinition = $container->getParameter('doctrine.dbal.connection_factory.types');

        if (!isset($typeDefinition['string_uuid'])) {
            $typeDefinition['string_uuid'] = ['class' => StringUuidType::class];
        }

        $container->setParameter('doctrine.dbal.connection_factory.types', $typeDefinition);
    }
}
