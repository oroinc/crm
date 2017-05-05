<?php

namespace Oro\Bundle\MagentoBundle\Provider\Connector;

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
        return 'oro.magento.connector.customer_group.label';
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
