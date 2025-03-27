<?php

namespace K3Progetti\MicrosoftBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;

class MicrosoftExtension extends Extension
{
    /**
     * @param array $configs
     * @param ContainerBuilder $container
     * @return void
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new MicrosoftConfiguration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('microsoft.client_id', $config['client_id']);
        $container->setParameter('microsoft.tenant_id', $config['tenant_id']);
        $container->setParameter('microsoft.client_secret', $config['client_secret']);
        $container->setParameter('microsoft.graph_api_url', $config['graph_api_url']);

    }

    /**
     * @return string
     */
    public function getAlias(): string
    {
        return 'microsoft';
    }
}
