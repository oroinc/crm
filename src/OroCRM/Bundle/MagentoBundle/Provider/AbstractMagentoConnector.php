<?php

namespace OroCRM\Bundle\MagentoBundle\Provider;

use Oro\Bundle\IntegrationBundle\Entity\Status;
use Oro\Bundle\ImportExportBundle\Context\ContextInterface;
use Oro\Bundle\IntegrationBundle\Provider\AbstractConnector;

use OroCRM\Bundle\MagentoBundle\Provider\Iterator\PredefinedFiltersAwareInterface;
use OroCRM\Bundle\MagentoBundle\Provider\Iterator\UpdatedLoaderInterface;
use OroCRM\Bundle\MagentoBundle\Provider\Transport\MagentoTransportInterface;

abstract class AbstractMagentoConnector extends AbstractConnector implements MagentoConnectorInterface
{
    /** @var MagentoTransportInterface */
    protected $transport;

    /**
     * {@inheritdoc}
     */
    public function read()
    {
        $item = parent::read();

        if (null !== $item) {
            $this->addStatusData(
                'lastSyncItemDate',
                $this->getMaxUpdatedDate($item, $this->getStatusData('lastSyncItemDate'))
            );
        }

        return $item;
    }

    /**
     * {@inheritdoc}
     */
    protected function initializeFromContext(ContextInterface $context)
    {
        parent::initializeFromContext($context);

        // set start date and mode depending on status
        $status      = $this->channel->getStatusesForConnector($this->getType(), Status::STATUS_COMPLETED)->first();
        $iterator    = $this->getSourceIterator();
        $isForceSync = $context->getOption('force') && $this->supportsForceSync();

        if ($iterator instanceof UpdatedLoaderInterface && !empty($status) && !$isForceSync) {
            /** @var Status $status */
            $iterator->setMode(UpdatedLoaderInterface::IMPORT_MODE_UPDATE);
            $data = $status->getData();

            if (!empty($data['lastSyncItemDate'])) {
                $iterator->setStartDate(new \DateTime($data['lastSyncItemDate']));
            } else {
                $iterator->setStartDate($status->getDate());
            }
        }

        // pass filters from connector
        if ($context->hasOption('filters') || $context->hasOption('complex_filters')) {
            if ($iterator instanceof PredefinedFiltersAwareInterface) {
                $filters           = $context->getOption('filters') ? : [];
                $complexFilters    = $context->getOption('complex_filters') ? : [];
                $predefinedFilters = new BatchFilterBag($filters, $complexFilters);

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

    /**
     * @param array       $item
     * @param string|null $prevDateToCompare
     *
     * @return null|string
     */
    protected function getMaxUpdatedDate(array $item, $prevDateToCompare)
    {
        $itemUpdatedDate = $this->getUpdatedDate($item);

        if (!$prevDateToCompare) {
            return $itemUpdatedDate;
        } elseif (!$itemUpdatedDate) {
            return $prevDateToCompare;
        }

        return strtotime($itemUpdatedDate) > strtotime($prevDateToCompare) ? $itemUpdatedDate : $prevDateToCompare;
    }

    /**
     * @param array $item
     *
     * @return string|null
     */
    protected function getUpdatedDate(array $item)
    {
        switch (true) {
            case !empty($item['updatedAt']):
                return $item['updatedAt'];
                break;
            case !empty($item['updated_at']):
                return $item['updated_at'];
                break;
            default:
                return null;
        }
    }
}
