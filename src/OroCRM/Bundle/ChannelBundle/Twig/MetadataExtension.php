<?php

namespace OroCRM\Bundle\ChannelBundle\Twig;

use OroCRM\Bundle\ChannelBundle\Provider\MetadataInterface;

class MetadataExtension extends \Twig_Extension
{
    const EXTENSION_NAME = 'orocrm_list_of_integrations_entities';

    /** @var MetadataInterface */
    protected $metaDataProvider;

    /**
     * @param MetadataInterface $provider
     */
    public function __construct(MetadataInterface $provider)
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
        return [
            'orocrm_channel_metadata_list' => new \Twig_Function_Method($this, 'getListOfIntegrationEntities'),
            'orocrm_integration_entities' => new \Twig_Function_Method($this, 'getMetadataByIntegrationType')
        ];
    }

    /**
     * @return array
     */
    public function getListOfIntegrationEntities()
    {
        return $this->metaDataProvider->getMetadataList();
    }

    /**
     * @return array
     */
    public function getMetadataByIntegrationType()
    {
        return $this->metaDataProvider->getMetadataByIntegrationType('magento');
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
