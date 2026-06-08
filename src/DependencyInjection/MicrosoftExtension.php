<?php

namespace K3Progetti\MicrosoftBundle\DependencyInjection;

use Exception;
use K3Progetti\MicrosoftBundle\Contract\UserInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class MicrosoftExtension extends Extension implements PrependExtensionInterface
{
    public function prepend(ContainerBuilder $container): void
    {
        $configs = $container->getExtensionConfig($this->getAlias());
        $config = $this->processConfiguration(new MicrosoftConfiguration(), $configs);

        $container->prependExtensionConfig('doctrine', [
            'orm' => [
                'resolve_target_entities' => [
                    UserInterface::class => $config['user_class'],
                ],
            ],
        ]);
    }

    /**
     * @throws Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new MicrosoftConfiguration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('microsoft.user_class', $config['user_class']);
        $container->setParameter('microsoft.client_id', $config['client_id']);
        $container->setParameter('microsoft.tenant_id', $config['tenant_id']);
        $container->setParameter('microsoft.client_secret', $config['client_secret']);
        $container->setParameter('microsoft.graph_api_url', $config['graph_api_url']);
        $container->setParameter('microsoft.auth.allowed_groups', $config['auth']['allowed_groups'] ?? []);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../../resources/config'));
        $loader->load('services.yaml');
    }

    public function getAlias(): string
    {
        return 'microsoft';
    }
}
