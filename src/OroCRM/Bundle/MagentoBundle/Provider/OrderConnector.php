<?php

namespace OroCRM\Bundle\MagentoBundle\Provider;

use Oro\Bundle\IntegrationBundle\Entity\Status;

use OroCRM\Bundle\MagentoBundle\Provider\Iterator\UpdatedLoaderInterface;

class OrderConnector extends AbstractMagentoConnector
{
    const CONNECTOR_TYPE = 'order';

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return 'orocrm.magento.connector.order.label';
    }

    /**
     * {@inheritdoc}
     */
    public function getImportEntityFQCN()
    {
        return self::ORDER_TYPE;
    }

    /**
     * {@inheritdoc}
     */
    public function getImportJobName()
    {
        return 'mage_order_import';
    }

    /**
     * {@inheritdoc}
     */
    protected function getConnectorSource()
    {
        $iterator = $this->transport->getOrders();

        // set start date and mode depending on status
        $status = $this->channel->getStatusesForConnector(self::CONNECTOR_TYPE, Status::STATUS_COMPLETED)->first();
        if ($iterator instanceof UpdatedLoaderInterface && false !== $status) {
            /** @var Status $status */
            $iterator->setMode(UpdatedLoaderInterface::IMPORT_MODE_UPDATE);
            $iterator->setStartDate($status->getDate());
        }

        return $iterator;
    }
}
