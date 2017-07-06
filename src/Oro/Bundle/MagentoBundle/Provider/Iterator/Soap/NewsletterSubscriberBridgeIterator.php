<?php

namespace Oro\Bundle\MagentoBundle\Provider\Iterator\Soap;

use Oro\Bundle\IntegrationBundle\Utils\ConverterUtils;
use Oro\Bundle\MagentoBundle\Entity\Website;
use Oro\Bundle\MagentoBundle\Provider\BatchFilterBag;
use Oro\Bundle\MagentoBundle\Provider\Iterator\NewsletterSubscriberBridgeIteratorInterface;
use Oro\Bundle\MagentoBundle\Provider\Transport\ServerTimeAwareInterface;
use Oro\Bundle\MagentoBundle\Provider\Transport\SoapTransport;

class NewsletterSubscriberBridgeIterator extends AbstractBridgeIterator implements
    NewsletterSubscriberBridgeIteratorInterface
{
    /**
     * @var int
     */
    protected $initialId;

    /**
     * {@inheritdoc}
     */
    public function setInitialId($initialId)
    {
        $this->initialId = $initialId;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function applyFilter()
    {
        if ($this->isInitialSync()) {
            $initialId = $this->getInitialId();
            if ($initialId) {
                $this->filter->addComplexFilter(
                    $this->getIdFieldName(),
                    [
                        'key' => $this->getIdFieldName(),
                        'value' => [
                            'key' => 'lt',
                            'value' => $initialId
                        ]
                    ]
                );
            }
        } else {
            $dateField = 'change_status_at';
            $this->filter->addDateFilter($dateField, 'gt', $this->lastSyncDate);
            $fixTime = $this->fixServerTime($dateField);

            if ($fixTime) {
                $this->setStartDate($fixTime);
            }
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
        $result = $this->convertResponseToMultiArray($result);
        $resultIds = [];

        if (is_array($result) && count($result) > 0) {
            $resultIds = array_map(
                function ($item) {
                    return $item[$this->getIdFieldName()];
                },
                $result
            );

            $this->entityBuffer = array_combine($resultIds, $result);
        }

        return $resultIds;
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
            $subscribers = $this->convertResponseToMultiArray($subscribers);

            $subscriber = [];
            if (array_key_exists(0, $subscribers)) {
                $subscriber = $subscribers[0];
            }

            if (array_key_exists($this->getIdFieldName(), $subscriber)) {
                $this->initialId = (int)$subscriber[$this->getIdFieldName()] + 1;
            }
        }

        return $this->initialId;
    }

    /**
     * @param BatchFilterBag $filter
     */
    protected function applyStoreFilter(BatchFilterBag $filter)
    {
        if ($this->websiteId && $this->websiteId !== Website::ALL_WEBSITES) {
            $filter->addStoreFilter($this->getStoresByWebsiteId($this->websiteId));
        }
    }

    /**
     * @param array $filters
     * @return array|null
     */
    protected function getNewsletterSubscribers(array $filters = [])
    {
        $result = $this->transport->call(SoapTransport::ACTION_ORO_NEWSLETTER_SUBSCRIBER_LIST, $filters);

        return ConverterUtils::objectToArray($result);
    }

    /**
     * {@inheritdoc}
     */
    public function current()
    {
        $this->logger->info(sprintf('Loading NewsletterSubscriber by id: %s', $this->key()));

        return $this->current;
    }

    /**
     * Fix time frame if it's possible to retrieve server time.
     *
     * @param string $dateField
     * @return bool | \DateTime
     */
    protected function fixServerTime($dateField)
    {
        if (!$this->isInitialSync() && $this->transport instanceof ServerTimeAwareInterface) {
            $time = $this->transport->getServerTime();
            if (false !== $time) {
                $frameLimit = new \DateTime($time, new \DateTimeZone('UTC'));
                $this->filter->addDateFilter($dateField, 'lte', $frameLimit);

                return $frameLimit;
            }
        }

        return false;
    }
}
