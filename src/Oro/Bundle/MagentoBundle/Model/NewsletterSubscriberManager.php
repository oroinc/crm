<?php

namespace Oro\Bundle\MagentoBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\EntityExtendBundle\Entity\AbstractEnumValue;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\MagentoBundle\Entity\Customer;
use Oro\Bundle\MagentoBundle\Entity\NewsletterSubscriber;

class NewsletterSubscriberManager
{
    /**
     * @var DoctrineHelper
     */
    protected $doctrineHelper;

    /**
     * @param DoctrineHelper $doctrineHelper
     */
    public function __construct(DoctrineHelper $doctrineHelper)
    {
        $this->doctrineHelper = $doctrineHelper;
    }

    /**
     * @param Customer $customer
     *
     * @return NewsletterSubscriber[]|Collection
     */
    public function getOrCreateFromCustomer(Customer $customer)
    {
        $newsletterSubscribers = $customer->getNewsletterSubscribers();

        if (count($newsletterSubscribers) === 0) {
            $newsletterSubscriber = new NewsletterSubscriber();
            $newsletterSubscriber
                ->setCustomer($customer)
                ->setEmail($customer->getEmail())
                ->setChannel($customer->getChannel())
                ->setStore($customer->getStore())
                ->setOrganization($customer->getOrganization())
                ->setOwner($customer->getOwner())
                ->setStatus($this->getStatus(NewsletterSubscriber::STATUS_UNSUBSCRIBED))
                ->setDataChannel($customer->getDataChannel());
            $newsletterSubscribers = new ArrayCollection([$newsletterSubscriber]);
        }

        return $newsletterSubscribers;
    }

    /**
     * @param int $identifier
     *
     * @return AbstractEnumValue
     */
    protected function getStatus($identifier)
    {
        $className = ExtendHelper::buildEnumValueClassName('mage_subscr_status');

        return $this->doctrineHelper->getEntityRepository($className)->find($identifier);
    }
}
