<?php

namespace Oro\Bundle\ChannelBundle\DependencyInjection\CompilerPass;

use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use Oro\Component\Config\Loader\CumulativeConfigLoader;
use Oro\Component\Config\Loader\YamlCumulativeFileLoader;
use Oro\Bundle\ChannelBundle\DependencyInjection\ChannelConfiguration;

class SettingsPass implements CompilerPassInterface
{
    const SETTINGS_PROVIDER_ID = 'oro_channel.provider.settings_provider';

    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        $settingsProvider = $container->getDefinition(self::SETTINGS_PROVIDER_ID);
        $configs          = [];
        $configLoader     = new CumulativeConfigLoader(
            'oro_channel_settings',
            new YamlCumulativeFileLoader('Resources/config/oro/channels.yml')
        );
        $resources        = $configLoader->load($container);
        foreach ($resources as $resource) {
            $configs[] = $resource->data[ChannelConfiguration::ROOT_NODE_NAME];
        }

        $processor = new Processor();
        $config    = $processor->processConfiguration(new ChannelConfiguration(), $configs);
        $settingsProvider->replaceArgument(0, $config);
    }
}
