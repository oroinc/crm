<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Reader;

use Oro\Bundle\ImportExportBundle\Context\ContextRegistry;
use Oro\Bundle\ImportExportBundle\Reader\AbstractReader;
use Oro\Bundle\ImportExportBundle\Reader\ReaderInterface;
use OroCRM\Bundle\MagentoBundle\Provider\CustomerConnectorInterface;

class CustomerApiReader extends AbstractReader implements ReaderInterface
{
    /** @var CustomerConnectorInterface */
    protected $customerConnector;

    /**
     * @param ContextRegistry $contextRegistry
     * @param CustomerConnectorInterface $customerConnector
     */
    public function __construct(ContextRegistry $contextRegistry, CustomerConnectorInterface $customerConnector)
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

        // advance to the next one

        return null; // no data anymore
    }
}
