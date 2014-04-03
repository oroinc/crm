<?php

namespace OroCRM\Bundle\MagentoBundle\Provider;

use Oro\Bundle\ImportExportBundle\Context\ContextInterface;
use Oro\Bundle\IntegrationBundle\Entity\Status;
use Oro\Bundle\IntegrationBundle\Provider\AbstractConnector;

use OroCRM\Bundle\MagentoBundle\Provider\Iterator\FiltersAwareInterface;
use OroCRM\Bundle\MagentoBundle\Provider\Iterator\UpdatedLoaderInterface;
use OroCRM\Bundle\MagentoBundle\Provider\Transport\MagentoTransportInterface;

abstract class AbstractMagentoConnector extends AbstractConnector implements MagentoConnectorInterface
{
    /** @var MagentoTransportInterface */
    protected $transport;

    /**
     * {@inheritdoc}
     */
    protected function initializeFromContext(ContextInterface $context)
    {
        parent::initializeFromContext($context);

        // set start date and mode depending on status
        $status = $this->channel->getStatusesForConnector($this->getType(), Status::STATUS_COMPLETED)->first();
        $iterator = $this->getSourceIterator();
        if ($iterator instanceof UpdatedLoaderInterface && false !== $status) {
            /** @var Status $status */
            $iterator->setMode(UpdatedLoaderInterface::IMPORT_MODE_UPDATE);
            $iterator->setStartDate($status->getDate());
        }

        // pass filters from connector
        if (null !== $context->getOption('filters')) {
            if ($iterator instanceof FiltersAwareInterface) {
                $predefinedFilters = new BatchFilterBag();
                foreach ($context->getOption('filters') as $filterName => $filterValue) {
                    $predefinedFilters->addFilter(
                        $filterName,
                        [
                            'key'   => $filterName,
                            'value' => $filterValue
                        ]
                    );
                }
                $iterator->setPredefinedFiltersBag($predefinedFilters);
            } else {
                throw new \LogicException('Iterator does not support predefined filters');
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function validateConfiguration()
    {
        parent::validateConfiguration();

        if (!$this->transport instanceof MagentoTransportInterface) {
            throw new \LogicException('Option "transport" should implement "MagentoTransportInterface"');
        }
    }
}
