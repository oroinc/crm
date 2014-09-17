<?php

namespace OroCRM\Bundle\ChannelBundle\Twig;

use OroCRM\Bundle\ChannelBundle\Provider\MetadataProviderInterface;

class MetadataExtension extends \Twig_Extension
{
    const EXTENSION_NAME = 'orocrm_channel_metadata';

    /** @var MetadataProviderInterface */
    protected $metaDataProvider;

    /**
     * @param MetadataProviderInterface $provider
     */
    public function __construct(MetadataProviderInterface $provider)
    {
        $this->metaDataProvider = $provider;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        $entitiesMetadataFunction    = new \Twig_SimpleFunction(
            'orocrm_channel_entities_metadata',
            [
                $this,
                'getEntitiesMetadata'
            ]
        );
        $channelTypeMetadataFunction = new \Twig_SimpleFunction(
            'orocrm_channel_type_metadata',
            [
                $this,
                'getChannelTypeMetadata'
            ]
        );

        return [
            $entitiesMetadataFunction->getName()    => $entitiesMetadataFunction,
            $channelTypeMetadataFunction->getName() => $channelTypeMetadataFunction
        ];
    }

    /**
     * @return array
     */
    public function getEntitiesMetadata()
    {
        return $this->metaDataProvider->getEntitiesMetadata();
    }

    /**
     * @return array
     */
    public function getChannelTypeMetadata()
    {
        return $this->metaDataProvider->getChannelTypeMetadata();
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::EXTENSION_NAME;
    }
}
