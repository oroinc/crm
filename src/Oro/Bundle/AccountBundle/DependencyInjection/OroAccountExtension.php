<?php
declare(strict_types=1);

namespace Oro\Bundle\AccountBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class OroAccountExtension extends Extension implements PrependExtensionInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');
        $loader->load('importexport.yml');
        $loader->load('controllers.yml');
        $loader->load('controllers_api.yml');
    }

    /**
     * {@inheritDoc}
     */
    public function prepend(ContainerBuilder $container): void
    {
        if ('test' === $container->getParameter('kernel.environment')) {
            $path = dirname(__DIR__) . '/Tests/Functional/Stub/views';
            $container->prependExtensionConfig('twig', ['paths' => [$path]]);
        }
    }
}
