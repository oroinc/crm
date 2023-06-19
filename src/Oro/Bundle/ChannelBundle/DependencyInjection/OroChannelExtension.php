<?php

namespace Oro\Bundle\ChannelBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class OroChannelExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');
        $loader->load('commands.yml');
        $loader->load('controllers.yml');
        $loader->load('controllers_api.yml');
        $loader->load('mq_topics.yml');
        $loader->load('mq_processors.yml');

        if ('test' === $container->getParameter('kernel.environment')) {
            $loader->load('services_test.yml');
        }
    }
}
