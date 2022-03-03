<?php

namespace Optime\Util\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('optime_util');
        $rootNode    = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->arrayNode('locales')
                    ->prototype('scalar')
                        ->defaultValue(['%kernel.default_locale%'])
                    ->end()
                ->end()
                ->scalarNode('default_locale')
                    ->defaultValue('%kernel.default_locale%')
                ->end()
                ->arrayNode('ajax_check')
                    ->addDefaultsIfNotSet()
//                    ->info('')
                    ->children()
                        ->scalarNode('header')->defaultNull()->end()
                        ->scalarNode('param')->defaultNull()->end()
                    ->end()
                ->end()
                ->booleanNode('use_translations_extension')
                    ->defaultTrue()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
