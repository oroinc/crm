<?php

namespace Oro\Bundle\ChannelBundle\Twig;

use Symfony\Component\DependencyInjection\ContainerInterface;

use Oro\Bundle\ChannelBundle\Provider\MetadataProviderInterface;

class MetadataExtension extends \Twig_Extension
{
    const EXTENSION_NAME = 'oro_channel_metadata';

    /** @var ContainerInterface */
    protected $container;

    /**
     * @param ContainerInterface $container
     */
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
            new \Twig_SimpleFunction('oro_channel_entities_metadata', [$this, 'getEntitiesMetadata']),
            new \Twig_SimpleFunction('oro_channel_type_metadata', [$this, 'getChannelTypeMetadata'])
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
        return $this->getMetadataProvider()->getChannelTypeMetadata();
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::EXTENSION_NAME;
    }
}
