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
            ->integerNode('client_secret')->isRequired()->end()
            ->integerNode('graph_api_url')->defaultValue('https://graph.microsoft.com/v1.0')->end()
            ->end();

        return $treeBuilder;
    }
}
