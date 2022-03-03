<?php

namespace Optime\Util\DependencyInjection\Compiler;

use Doctrine\ORM\EntityManagerInterface;
use Gedmo\Translatable\Entity\Repository\TranslationRepository;
use Gedmo\Translatable\Entity\Translation;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use function class_exists;

class ConfigureTranslatorRepositoryPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if ($container->has(TranslationRepository::class)) {
            return;
        }

        if (!class_exists(TranslationRepository::class)) {
            return;
        }

        if (false === $container->getParameter('optime.sf_utils.use_translations_extension')) {
            return;
        }

        $repository = new Definition(TranslationRepository::class);
        $repository
            ->setFactory([new Reference(EntityManagerInterface::class), 'getRepository'])
            ->setArguments([Translation::class]);

        $container->setDefinition(TranslationRepository::class, $repository);
    }
}
