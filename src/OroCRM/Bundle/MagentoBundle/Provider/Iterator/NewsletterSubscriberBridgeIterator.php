<?php

namespace OroCRM\Bundle\MagentoBundle\Provider\Iterator;

use OroCRM\Bundle\MagentoBundle\Provider\BatchFilterBag;
use OroCRM\Bundle\MagentoBundle\Provider\Dependency\NewsletterSubscriberDependencyManager;
use OroCRM\Bundle\MagentoBundle\Provider\Transport\SoapTransport;

class NewsletterSubscriberBridgeIterator extends AbstractBridgeIterator
{
    /**
     * @var int
     */
    protected $initialId;

    //TODO: this is for debugging purposes. Do not forget to remove this
    protected $mode = self::IMPORT_MODE_INITIAL;

    /**
     * @param SoapTransport $transport
     * @param array $settings
     */
    public function __construct(SoapTransport $transport, array $settings)
    {
        parent::__construct($transport, $settings);

        if (array_key_exists('initial_id', $settings)) {
            $this->initialId = $settings['initial_id'];
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function applyFilter()
    {
        if ($this->isInitialSync()) {
            $initialId = $this->getInitialId();
            if ($this->initialId) {
                $this->filter->addComplexFilter(
                    $this->getIdFieldName(),
                    [
                        'key' => $this->getIdFieldName(),
                        'value' => [
                            'key' => 'to',
                            'value' => $initialId
                        ]
                    ]
                );
            }
        } else {
            $dateField = 'change_status_at';
            $this->filter->addDateFilter($dateField, 'gt', $this->lastSyncDate);
            $this->fixServerTime($dateField);
        }

        $this->applyStoreFilter($this->filter);
        if (null !== $this->predefinedFilters) {
            $this->filter->merge($this->predefinedFilters);
        }

        $this->logAppliedFilters($this->filter);
    }

    /**
     * {@inheritdoc}
     */
    protected function getEntityIds()
    {
        $this->applyFilter();

        $filters = $this->filter->getAppliedFilters();
        $filters['pager'] = ['page' => $this->getCurrentPage(), 'pageSize' => $this->pageSize];

        $result = $this->getNewsletterSubscribers($filters);
        $result = $this->processCollectionResponse($result);
        $resultIds = array_map(
            function ($item) {
                return $item->{$this->getIdFieldName()};
            },
            $result
        );

        $this->entityBuffer = array_combine($resultIds, $result);

        return $resultIds;
    }

    /**
     * {@inheritdoc}
     */
    protected function addDependencyData($result)
    {
        NewsletterSubscriberDependencyManager::addDependencyData($result, $this->transport);
    }

    /**
     * {@inheritdoc}
     */
    protected function getIdFieldName()
    {
        return 'subscriber_id';
    }

    /**
     * @return int|null
     */
    protected function getInitialId()
    {
        if ($this->isInitialSync() && !$this->initialId) {
            $filter = new BatchFilterBag();
            $this->applyStoreFilter($filter);
            $filters = $filter->getAppliedFilters();
            $filters['pager'] = ['page' => 1, 'pageSize' => 1];
            $subscribers = $this->getNewsletterSubscribers($filters);

            if (count($subscribers) > 0) {
                $this->initialId = $subscribers[0]->{$this->getIdFieldName()};
            }
        }

        return $this->initialId;
    }

    /**
     * @param BatchFilterBag $filter
     */
    protected function applyStoreFilter(BatchFilterBag $filter)
    {
        if ($this->websiteId && $this->websiteId !== StoresSoapIterator::ALL_WEBSITES) {
            $filter->addStoreFilter($this->getStoresByWebsiteId($this->websiteId));
        }
    }

    /**
     * @param array $filters
     * @return array|null
     */
    protected function getNewsletterSubscribers(array $filters = [])
    {
        return $this->transport->call(SoapTransport::ACTION_ORO_NEWSLETTER_SUBSCRIBER_LIST, $filters);
    }
}
