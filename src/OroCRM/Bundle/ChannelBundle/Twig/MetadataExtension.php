<?php

namespace OroCRM\Bundle\ChannelBundle\Twig;

use OroCRM\Bundle\ChannelBundle\Provider\MetadataProviderInterface;

class MetadataExtension extends \Twig_Extension
{
    const EXTENSION_NAME = 'orocrm_list_of_integrations_entities';

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

        return [
            $entitiesMetadataFunction->getName()    => $entitiesMetadataFunction,
            $integrationMetadataFunction->getName() => $integrationMetadataFunction
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
}
