<?php

namespace OroCRM\Bundle\MagentoBundle\Provider;

class RegionConnector extends AbstractMagentoConnector
{
    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return 'orocrm.magento.connector.region.label';
    }

    /**
     * {@inheritdoc}
     */
    public function getImportEntityFQCN()
    {
        return self::REGION_TYPE;
    }

    /**
     * {@inheritdoc}
     */
    public function getImportJobName()
    {
        return 'mage_region_import';
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return 'region';
    }

    /**
     * {@inheritdoc}
     */
    protected function getConnectorSource()
    {
        return $this->transport->getRegions();
    }
}
