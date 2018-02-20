<?php

namespace Oro\Bundle\MagentoBundle\Migrations\Data\Demo\ORM;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\MagentoBundle\Entity\Customer;
use Oro\Bundle\MagentoBundle\Entity\NewsletterSubscriber;

class LoadMagentoNewsletterSubscriberData extends AbstractFixture implements DependentFixtureInterface
{
    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            __NAMESPACE__ . '\LoadMagentoData'
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $newsletterSubscribers = [];

        $customers = new ArrayCollection($manager->getRepository('OroMagentoBundle:Customer')->findAll());

        $className = ExtendHelper::buildEnumValueClassName('mage_subscr_status');
        $statuses = new ArrayCollection($manager->getRepository($className)->findAll());

        /** @var Customer $customer */
        foreach ($customers as $customer) {
            $newsletterSubscriber = new NewsletterSubscriber();
            $newsletterSubscriber
                ->setOwner($customer->getOwner())
                ->setOrganization($customer->getOrganization())
                ->setChangeStatusAt($customer->getCreatedAt())
                ->setCreatedAt($customer->getCreatedAt())
                ->setUpdatedAt($customer->getUpdatedAt())
                ->setConfirmCode(uniqid('', true))
                ->setChannel($customer->getChannel())
                ->setEmail($customer->getEmail())
                ->setStore($customer->getStore())
                ->setStatus($statuses->get(array_rand($statuses->toArray())))
                ->setDataChannel($customer->getDataChannel());

            if (rand(0, 1)) {
                $newsletterSubscriber->setCustomer($customer);
            }

            $newsletterSubscribers[] = $newsletterSubscriber;
        }

        foreach ($newsletterSubscribers as $newsletterSubscriber) {
            $manager->persist($newsletterSubscriber);
        }

        if ($newsletterSubscribers) {
            $manager->flush($newsletterSubscribers);
        }
    }
}
