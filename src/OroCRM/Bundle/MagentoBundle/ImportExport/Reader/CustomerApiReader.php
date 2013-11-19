<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Reader;

use Oro\Bundle\BatchBundle\Step\StepExecutionAwareInterface;
use Oro\Bundle\ImportExportBundle\Context\ContextInterface;
use Oro\Bundle\ImportExportBundle\Context\ContextRegistry;
use Oro\Bundle\ImportExportBundle\Reader\AbstractReader;
use Oro\Bundle\ImportExportBundle\Reader\ReaderInterface;
use Oro\Bundle\IntegrationBundle\Entity\Connector as ConnectorEntity;
use Oro\Bundle\IntegrationBundle\Provider\ConnectorInterface;

use OroCRM\Bundle\MagentoBundle\Provider\CustomerConnector;

class CustomerApiReader extends AbstractReader implements ReaderInterface, StepExecutionAwareInterface
{
    /** @var CustomerConnector */
    protected $customerConnector;

    /**
     * @param ContextRegistry $contextRegistry
     * @param ConnectorInterface $customerConnector
     */
    public function __construct(ContextRegistry $contextRegistry, ConnectorInterface $customerConnector)
    {
        parent::__construct($contextRegistry);

        $this->customerConnector = $customerConnector;
    }

    /**
     * {@inheritdoc}
     */
    protected function initializeFromContext(ContextInterface $context)
    {
        $channelName = $context->getOption('channelName');

        /** @var ConnectorEntity $connector */
        $connectorEntity = $context->getOption('connector');

        $this->customerConnector->setConnectorEntity($connectorEntity);

        /*
        $now = new \DateTime('now', new \DateTimeZone('UTC'));
        $settings = [
            'last_sync_date' => $now->sub(\DateInterval::createFromDateString('1 month')),
            'sync_range'     => '1 week',
            'api_user'       => 'api_user',
            'api_key'        => 'api_user',
            'wsdl_url'       => 'http://mage.dev.lxc/index.php/api/v2_soap/?wsdl=1',
        ];
        */
    }

    /**
     * {@inheritdoc}
     */
    public function read()
    {
        // read peace of data
        $data = $this->customerConnector->read();

        if (empty($data)) {
            return null; // no data anymore
        }
        var_dump($data);

        // customer connector knows how to advance
        // batch counter/boundaries to the next ones
    }
}
