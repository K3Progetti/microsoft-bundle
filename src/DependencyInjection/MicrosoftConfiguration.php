<?php

namespace K3Progetti\MicrosoftBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class MicrosoftConfiguration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('microsoft');

        $treeBuilder->getRootNode()
            ->children()
            ->scalarNode('client_id')->isRequired()->end()
            ->scalarNode('tenant_id')->isRequired()->end()
            ->scalarNode('client_secret')->isRequired()->end()
            ->scalarNode('graph_api_url')->defaultValue('https://graph.microsoft.com/v1.0')->end()
            ->arrayNode('auth')
            ->addDefaultsIfNotSet()
            ->children()
            ->arrayNode('allowed_groups')
            ->scalarPrototype()->end()
            ->defaultValue([]) // se non specificato, nessun filtro
            ->end()
            ->end()
            ->end()
            ->end();

        return $treeBuilder;
    }
}
