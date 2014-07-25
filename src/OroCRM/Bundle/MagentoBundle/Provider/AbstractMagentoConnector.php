<?php

namespace OroCRM\Bundle\MagentoBundle\Provider;

use Oro\Bundle\ImportExportBundle\Exception\LogicException;
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
    protected function initializeFromContext(ContextInterface $context)
    {
        parent::initializeFromContext($context);

        // set start date and mode depending on status
        $status      = $this->channel->getStatusesForConnector($this->getType(), Status::STATUS_COMPLETED)->first();
        $iterator    = $this->getSourceIterator();
        $isForceSync = $context->getOption('force') && $this->supportsForceSync();

        if ($iterator instanceof UpdatedLoaderInterface && false !== $status && !$isForceSync) {
            /** @var Status $status */
            $iterator->setMode(UpdatedLoaderInterface::IMPORT_MODE_UPDATE);
            $data = $status->getData();

            if (!empty($data['lastSyncItemData'])) {
                $iterator->setStartDate(new \DateTime($data['lastSyncItemData']));
            } else {
                $iterator->setStartDate($status->getDate());
            }

        } elseif (!empty($status)) {
            if ($status->hasLastSyncedItemDate()) {
                $iterator->setStartDate($status->getLastSyncedItemDate());
            }
        }

        // pass filters from connector
        if ($context->hasOption('filters') || $context->hasOption('complex_filters')) {
            if ($iterator instanceof PredefinedFiltersAwareInterface) {
                $filters        = $context->getOption('filters') ? : [];
                $complexFilters = $context->getOption('complex_filters') ? : [];

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
     * {@inheritdoc}
     */
    public function read()
    {
        $item = parent::read();

        $iterator = $this->getSourceIterator();

        if (!$iterator->valid()) {
            $data = null;

            if ($iterator instanceof UpdatedLoaderInterface) {
                $mode = $iterator->getMode();
                $data = $this->getDateAccordingWithTheMode($mode, $item);
            }

            if (!empty($data)) {
                $this->addStatusData('lastSyncItemData', $data);
            }
        }

        return $item;
    }

    /**
     * @param string $mode
     * @param object $item
     *
     * @return null|string
     */
    protected function getDateAccordingWithTheMode($mode, $item)
    {
        if ($mode === UpdatedLoaderInterface::IMPORT_MODE_INITIAL) {
            return $this->getInitDate($item);
        } elseif ($mode === UpdatedLoaderInterface::IMPORT_MODE_UPDATE) {
            return $this->getUpdateDate($item);
        }

        return null;
    }

    /**
     * @param object $item
     *
     * @return string|null
     */
    protected function getInitDate($item)
    {
        if (!empty($item['createdAt'])) {
            return $item['createdAt'];
        } elseif ($item['created_at']) {
            return $item['created_at'];
        }
        return null;
    }

    /**
     * @param $item
     *
     * @return string|null
     */
    protected function getUpdateDate($item)
    {
        if (!empty($item['updatedAt'])) {
            return $item['updatedAt'];
        } elseif ($item['updated_at']) {
            return $item['updated_at'];
        }

        return null;
    }
}
