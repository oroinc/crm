<?php

namespace OroCRM\Bundle\MagentoBundle\Provider;

use Doctrine\ORM\EntityManager;

use Psr\Log\LoggerAwareTrait;

use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\IntegrationBundle\Manager\TypesRegistry;
use Oro\Bundle\EntityConfigBundle\DependencyInjection\Utils\ServiceLink;

use OroCRM\Bundle\MagentoBundle\Utils\WSIUtils;
use OroCRM\Bundle\MagentoBundle\Provider\Transport\SoapTransport;
use OroCRM\Bundle\MagentoBundle\Provider\Transport\MagentoTransportInterface;

class CartExpirationProcessor
{
    const DEFAULT_PAGE_SIZE = 200;

    /** @var TypesRegistry */
    protected $registry;

    /** @var EntityManager */
    protected $em;

    /** @var MagentoTransportInterface */
    protected $transport;

    /** @var array */
    protected $stores;

    /** @var Int */
    protected $batchSize;

    /**
     * Constructor
     *
     * @param ServiceLink   $registryLink
     * @param EntityManager $em
     * @param int           $batchSize
     */
    public function __construct(ServiceLink $registryLink, EntityManager $em, $batchSize = self::DEFAULT_PAGE_SIZE)
    {
        $this->registryLink = $registryLink;
        $this->em           = $em;
        $this->batchSize    = $batchSize;
    }

    /**
     * Run cart expiration process for given channel
     *
     * @param Channel $channel
     */
    public function process(Channel $channel)
    {
        $this->configure($channel);

        $result = $this->em->getRepository('OroCRMMagentoBundle:Cart')->getCartsByChannelIdsIterator($channel);

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
        $this->em->getRepository('OroCRMMagentoBundle:Cart')->markExpired($removedIds);
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
        $transport = $this->getTransport($channel);
        $settings  = $channel->getTransport()->getSettingsBag();

        if (!$transport->isExtensionInstalled()) {
            throw new \LogicException('Could not retrieve carts via SOAP with out installed Oro Bridge module');
        }

        $websiteId = $settings->get('website_id');

        $stores        = [];
        $magentoStores = iterator_to_array($transport->getStores());
        foreach ($magentoStores as $store) {
            if ($store['website_id'] == $websiteId) {
                $stores[] = $store['store_id'];
            }
        }

        if (empty($stores)) {
            throw new \LogicException(sprintf('Could not resolve store dependency for website id: %d', $websiteId));
        }

        $this->transport = $transport;
        $this->stores    = $stores;
    }

    /**
     * Retrieve and initialize real transport object
     *
     * @param Channel $channel
     *
     * @return MagentoTransportInterface
     */
    protected function getTransport(Channel $channel)
    {
        /** @var MagentoTransportInterface $transport */
        $transport = clone $this->registryLink->getService()
            ->getTransportTypeBySettingEntity($channel->getTransport(), $channel->getType());
        $transport->init($channel->getTransport());

        return $transport;
    }
}
