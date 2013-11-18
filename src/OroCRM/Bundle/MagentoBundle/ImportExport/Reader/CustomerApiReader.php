<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Reader;

use Oro\Bundle\ImportExportBundle\Context\ContextRegistry;
use Oro\Bundle\ImportExportBundle\Reader\AbstractReader;
use Oro\Bundle\ImportExportBundle\Reader\ReaderInterface;
use Oro\Bundle\IntegrationBundle\Provider\ConnectorInterface;

use OroCRM\Bundle\MagentoBundle\Provider\CustomerConnector;

class CustomerApiReader extends AbstractReader implements ReaderInterface
{
    /** @var CustomerConnector */
    protected $customerConnector;

    /**
     * @param ContextRegistry $contextRegistry
     * @param ConnectorInterface $customerConnector
     */
    public function __construct(ContextRegistry $contextRegistry, ConnectorInterface $customerConnector)
    {
        $this->contextRegistry = $contextRegistry;
        $this->customerConnector = $customerConnector;
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
