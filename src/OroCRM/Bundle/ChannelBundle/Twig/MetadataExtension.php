<?php

namespace OroCRM\Bundle\ChannelBundle\Twig;

use OroCRM\Bundle\ChannelBundle\Provider\MetadataProviderInterface;
use OroCRM\Bundle\ChannelBundle\Provider\SettingsProvider;

class MetadataExtension extends \Twig_Extension
{
    const EXTENSION_NAME = 'orocrm_list_of_integrations_entities';

    /** @var MetadataProviderInterface */
    protected $metaDataProvider;

    /** @var SettingsProvider */
    protected $settingsProvider;

    /**
     * @param MetadataProviderInterface $provider
     * @param SettingsProvider $settingsProvider
     */
    public function __construct(MetadataProviderInterface $provider, SettingsProvider $settingsProvider)
    {
        $this->metaDataProvider = $provider;
        $this->settingsProvider = $settingsProvider;
    }

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return array An array of functions
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
        $integrationMetadataFunction = new \Twig_SimpleFunction(
            'orocrm_channel_integration_metadata',
            [
                $this,
                'getIntegrationEntities'
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
            $integrationMetadataFunction->getName() => $integrationMetadataFunction,
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
    public function getIntegrationEntities()
    {
        return $this->metaDataProvider->getIntegrationEntities();
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return self::EXTENSION_NAME;
    }

    public function getChannelTypeMetadata()
    {
        return $this->settingsProvider->getChannelTypeChoiceList();
    }
}
