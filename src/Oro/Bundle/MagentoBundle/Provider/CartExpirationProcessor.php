<?php

namespace Oro\Bundle\MagentoBundle\Provider;

use Doctrine\ORM\EntityManager;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\IntegrationBundle\Provider\ConnectorContextMediator;
use Oro\Bundle\IntegrationBundle\Provider\SyncProcessorInterface;
use Oro\Bundle\MagentoBundle\Entity\Website;
use Oro\Bundle\MagentoBundle\Exception\ExtensionRequiredException;
use Oro\Bundle\MagentoBundle\Provider\Transport\MagentoSoapTransportInterface;
use Oro\Bundle\MagentoBundle\Provider\Transport\SoapTransport;
use Oro\Bundle\MagentoBundle\Utils\WSIUtils;
use Symfony\Component\HttpFoundation\ParameterBag;

class CartExpirationProcessor implements SyncProcessorInterface
{
    const DEFAULT_PAGE_SIZE = 200;

    /** @var ConnectorContextMediator */
    protected $helper;

    /** @var EntityManager */
    protected $em;

    /** @var MagentoSoapTransportInterface */
    protected $transport;

    /** @var array */
    protected $stores;

    /** @var Int */
    protected $batchSize;

    /**
     * Constructor
     *
     * @param ConnectorContextMediator $helper
     * @param EntityManager            $em
     * @param int                      $batchSize
     */
    public function __construct(
        ConnectorContextMediator $helper,
        EntityManager $em,
        $batchSize = self::DEFAULT_PAGE_SIZE
    ) {
        $this->helper    = $helper;
        $this->em        = $em;
        $this->batchSize = $batchSize;
    }

    /**
     * {@inheritdoc}
     */
    public function process(Channel $channel, $connector = null, array $connectorParameters = [])
    {
        $this->configure($channel);

        $result = $this->em->getRepository('OroMagentoBundle:Cart')->getCartsByChannelIdsIterator($channel);

        $ids   = [];
        $count = 0;
        foreach ($result as $data) {
            $ids[$data['originId']] = $data['id'];
            $count++;

            if (0 === $count % $this->batchSize) {
                $this->processBatch($ids);
                $ids = [];
            }
        }

        if (!empty($ids)) {
            $this->processBatch($ids);
        }
    }

    /**
     * Process search for removal carts in CRM and mark them as "expired"
     *
     * @param array $ids
     */
    protected function processBatch($ids)
    {
        $filterBag = new BatchFilterBag();
        $filterBag->addStoreFilter($this->stores);
        $filterBag->addComplexFilter(
            'entity_id',
            [
                'key'   => 'entity_id',
                'value' => [
                    'key'   => 'in',
                    'value' => implode(',', array_keys($ids))
                ]
            ]
        );
        $filters          = $filterBag->getAppliedFilters();
        $filters['pager'] = ['page' => 1, 'pageSize' => $this->batchSize];

        $result     = $this->transport->call(SoapTransport::ACTION_ORO_CART_LIST, $filters);
        $result     = WSIUtils::processCollectionResponse($result);
        $resultIds  = array_map(
            function (&$item) {
                return (int)$item->entity_id;
            },
            $result
        );
        $resultIds  = array_flip($resultIds);
        $removedIds = array_values(array_diff_key($ids, $resultIds));
        $this->em->getRepository('OroMagentoBundle:Cart')->markExpired($removedIds);
    }

    /**
     * Configure processor
     *
     * @param Channel $channel
     *
     * @throws \LogicException
     */
    protected function configure(Channel $channel)
    {
        /** @var MagentoSoapTransportInterface $transport */
        $transport = $this->helper->getTransport($channel);
        $transport->init($channel->getTransport());

        /** @var ParameterBag $settings */
        $settings = $channel->getTransport()->getSettingsBag();

        if (!$transport->isSupportedExtensionVersion()) {
            throw new ExtensionRequiredException();
        }

        $websiteId = $settings->get('website_id');
        $stores    = $this->getStores($transport, $websiteId);

        if (empty($stores)) {
            throw new \LogicException(sprintf('Could not resolve store dependency for website id: %d', $websiteId));
        }

        $this->transport = $transport;
        $this->stores    = $stores;
    }

    /**
     * @param MagentoSoapTransportInterface $transport
     * @param int                       $websiteId
     *
     * @return array
     */
    protected function getStores(MagentoSoapTransportInterface $transport, $websiteId)
    {
        $stores = [];
        foreach ($transport->getStores() as $store) {
            if ($store['website_id'] == $websiteId || $websiteId === Website::ALL_WEBSITES) {
                $stores[] = $store['store_id'];
            }
        }

        return $stores;
    }
}
