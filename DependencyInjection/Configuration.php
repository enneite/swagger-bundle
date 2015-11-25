<?php

namespace Enneite\SwaggerBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('enneite_swagger');

        $rootNode
            ->useAttributeAsKey('name')
            ->requiresAtLeastOneElement()
            ->prototype('array')
                ->children()
                    ->scalarNode('config_file')->cannotBeEmpty()->defaultValue('%kernel.root_dir%/config/swagger.yml')->end()
                    ->enumNode('routing_type')->values(array('yaml', 'annotation'))->defaultValue('yaml')->end()
                    ->scalarNode('routing_prefix')->cannotBeEmpty()->end()
                    ->scalarNode('destination_namespace')->cannotBeEmpty()->defaultValue('api')->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
