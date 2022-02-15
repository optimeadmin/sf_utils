<?php

namespace Optime\Util\DependencyInjection\Compiler;

use Doctrine\ORM\EntityManagerInterface;
use Gedmo\Translatable\Entity\Repository\TranslationRepository;
use Gedmo\Translatable\Entity\Translation;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class ConfigureTranslatorRepositoryPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if ($container->has(TranslationRepository::class)) {
            return;
        }

        $repository = new Definition(TranslationRepository::class);
        $repository
            ->setFactory([new Reference(EntityManagerInterface::class), 'getRepository'])
            ->setArguments([Translation::class]);

        $container->setDefinition(TranslationRepository::class, $repository);
    }
}
