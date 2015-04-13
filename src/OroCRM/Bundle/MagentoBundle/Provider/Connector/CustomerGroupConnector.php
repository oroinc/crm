<?php

namespace OroCRM\Bundle\MagentoBundle\Provider\Connector;

use OroCRM\Bundle\MagentoBundle\Provider\AbstractMagentoConnector;

class CustomerGroupConnector extends AbstractMagentoConnector implements DictionaryConnectorInterface
{
    const TYPE = 'customer_group_dictionary';

    /**
     * {@inheritdoc}
     */
    protected function getConnectorSource()
    {
        return $this->transport->getCustomerGroups();
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return 'orocrm.magento.connector.customer_group.label';
    }

    /**
     * {@inheritdoc}
     */
    public function getImportJobName()
    {
        return 'mage_customer_group_import';
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return self::TYPE;
    }
}
