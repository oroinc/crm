<?php

namespace OroCRM\Bundle\MagentoBundle\Provider;

use Doctrine\ORM\EntityManager;

use Psr\Log\LoggerAwareTrait;

use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\IntegrationBundle\Manager\TypesRegistry;
use Oro\Bundle\EntityConfigBundle\DependencyInjection\Utils\ServiceLink;

use OroCRM\Bundle\MagentoBundle\Provider\Transport\MagentoTransportInterface;

class CartExpirationProcessor
{
    use LoggerAwareTrait;

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

        if (!$transport->isExtensionInstalled()) {
            throw new \LogicException('Could not retrieve carts via SOAP with out installed Oro Bridge module');
        }

        $websiteId =
        $stores = iterator_to_array($transport->getStores());
        foreach ((array)$stores as $store) {
            if ($store['website_id'] == $websiteId) {
                $stores[] = $store['store_id'];
            }
        }

        if (empty($stores)) {
            throw new \LogicException(sprintf('Could not resolve store dependency for website id: %d', $websiteId));
        }

        $settings  = $channel->getTransport()->getSettingsBag();
        $filterBag = new BatchFilterBag();
        $filterBag->addStoreFilter($this->getStoresByWebsiteId($this->websiteId));
    }
}
