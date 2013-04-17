<?php

namespace Oro\Bundle\FilterBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Definition\Processor;

class OroFilterExtension extends Extension
{
    const PARAMETER_TWIG_LAYOUT = 'oro_filter.twig.layout';

    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter(self::PARAMETER_TWIG_LAYOUT, $config['twig']['layout']);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('form_types.yml');
        $loader->load('twig_extensions.yml');
    }
}
