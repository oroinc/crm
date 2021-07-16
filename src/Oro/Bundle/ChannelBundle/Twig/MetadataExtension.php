<?php

namespace Oro\Bundle\ChannelBundle\Twig;

use Oro\Bundle\ChannelBundle\Provider\MetadataProviderInterface;
use Psr\Container\ContainerInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Provides Twig functions to retrieve channel metadata associated with an entity:
 *   - oro_channel_entities_metadata
 *   - oro_channel_type_metadata
 */
class MetadataExtension extends AbstractExtension implements ServiceSubscriberInterface
{
    const EXTENSION_NAME = 'oro_channel_metadata';

    /** @var ContainerInterface */
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return MetadataProviderInterface
     */
    protected function getMetadataProvider()
    {
        return $this->container->get('oro_channel.provider.metadata_provider');
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('oro_channel_entities_metadata', [$this, 'getEntitiesMetadata']),
            new TwigFunction('oro_channel_type_metadata', [$this, 'getChannelTypeMetadata'])
        ];
    }

    /**
     * @return array
     */
    public function getEntitiesMetadata()
    {
        return $this->getMetadataProvider()->getEntitiesMetadata();
    }

    /**
     * @return array
     */
    public function getChannelTypeMetadata()
    {
        return array_flip($this->getMetadataProvider()->getChannelTypeMetadata());
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::EXTENSION_NAME;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedServices()
    {
        return [
            'oro_channel.provider.metadata_provider' => MetadataProviderInterface::class,
        ];
    }
}
