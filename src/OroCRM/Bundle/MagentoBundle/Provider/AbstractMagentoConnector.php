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
        $item     = parent::read();
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

            if (!empty($data['lastSyncItemData'])) {
                $iterator->setStartDate(new \DateTime($data['lastSyncItemData']));
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
     * @param string $mode
     * @param object $item
     *
     * @return string|null
     */
    protected function getDateAccordingWithTheMode($mode, $item)
    {
        $date = null;

        if ($mode === UpdatedLoaderInterface::IMPORT_MODE_INITIAL) {
            $date = $this->getInitDate($item);
        } elseif ($mode === UpdatedLoaderInterface::IMPORT_MODE_UPDATE) {
            $date = $this->getUpdateDate($item);
        }

        return $date;
    }

    /**
     * @param object $item
     *
     * @return string|null
     */
    protected function getInitDate($item)
    {
        $date = null;

        if (!empty($item['createdAt'])) {
            $date = $item['createdAt'];
        } elseif (!empty($item['created_at'])) {
            $date = $item['created_at'];
        }

        return $date;
    }

    /**
     * @param $item
     *
     * @return string|null
     */
    protected function getUpdateDate($item)
    {
        $date = null;

        if (!empty($item['updatedAt'])) {
            $date = $item['updatedAt'];
        } elseif (!empty($item['updated_at'])) {
            $date = $item['updated_at'];
        }

        return $date;
    }
}
