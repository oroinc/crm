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
    use LoggerAwareTrait;

    const DEFAULT_PAGE_SIZE = 200;

    /** @var TypesRegistry */
    protected $registry;

    /** @var EntityManager */
    protected $em;

    public function __construct(ServiceLink $registryLink, EntityManager $em)
    {
        $this->registryLink = $registryLink;
        $this->em           = $em;
    }

    /**
     * {@inheritdoc}
     */
    public function process(Channel $channel)
    {
        /** @var MagentoTransportInterface $transport */
        $transport = clone $this->registryLink->getService()
            ->getTransportTypeBySettingEntity($channel->getTransport(), $channel->getType());
        $transport->init($channel->getTransport());
        $settings = $channel->getTransport()->getSettingsBag();

        if (!$transport->isExtensionInstalled()) {
            throw new \LogicException('Could not retrieve carts via SOAP with out installed Oro Bridge module');
        }

        $websiteId = $settings->get('website_id');
        $stores    = iterator_to_array($transport->getStores());
        foreach ((array)$stores as $store) {
            if ($store['website_id'] == $websiteId) {
                $stores[] = $store['store_id'];
            }
        }

        if (empty($stores)) {
            throw new \LogicException(sprintf('Could not resolve store dependency for website id: %d', $websiteId));
        }

        $filterBag = new BatchFilterBag();
        $filterBag->addStoreFilter($stores);

        $filters          = $filterBag->getAppliedFilters();
        $filters['pager'] = ['page' => 1, 'pageSize' => self::DEFAULT_PAGE_SIZE];

        $result = $transport->call(SoapTransport::ACTION_ORO_CART_LIST, $filters);
        $result = WSIUtils::processCollectionResponse($result);
    }
}
