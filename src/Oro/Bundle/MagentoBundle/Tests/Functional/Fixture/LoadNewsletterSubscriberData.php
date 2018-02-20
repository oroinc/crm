<?php

namespace Oro\Bundle\MagentoBundle\Tests\Functional\Fixture;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\EntityExtendBundle\Entity\AbstractEnumValue;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\IntegrationBundle\Entity\Channel as Integration;
use Oro\Bundle\MagentoBundle\Entity\Customer;
use Oro\Bundle\MagentoBundle\Entity\NewsletterSubscriber;
use Oro\Bundle\MagentoBundle\Entity\Store;
use Oro\Bundle\UserBundle\Entity\UserManager;
use Oro\Bundle\UserBundle\Migrations\Data\ORM\LoadAdminUserData;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadNewsletterSubscriberData extends AbstractFixture implements
    DependentFixtureInterface,
    ContainerAwareInterface
{
    /**
     * @var array
     */
    protected $subscriberData = [
        [
            'customer' => 'customer',
            'email' => 'subscriber@example.com',
            'status' => NewsletterSubscriber::STATUS_SUBSCRIBED,
            'originId' => '123456',
            'reference' => 'newsletter_subscriber'
        ],
        [
            'email' => 'subscriber2@example.com',
            'status' => NewsletterSubscriber::STATUS_UNSUBSCRIBED,
            'originId' => '1234567',
            'reference' => 'newsletter_subscriber2'
        ],
        [
            'customer' => 'customer',
            'email' => 'subscriber3@example.com',
            'status' => NewsletterSubscriber::STATUS_UNSUBSCRIBED,
            'originId' => '1234567',
            'reference' => 'newsletter_subscriber3'
        ]
    ];

    /** @var ContainerInterface */
    protected $container;

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        /** @var UserManager $userManager */
        $userManager = $this->container->get('oro_user.manager');
        $admin = $userManager->findUserByEmail(LoadAdminUserData::DEFAULT_ADMIN_EMAIL);
        $organization = $manager->getRepository('OroOrganizationBundle:Organization')->getFirst();

        /** @var Store $store */
        $store = $this->getReference('store');

        /** @var Channel $channel */
        $channel = $this->getReference('default_channel');

        /** @var Integration $integration */
        $integration = $this->getReference('integration');

        $className = ExtendHelper::buildEnumValueClassName('mage_subscr_status');
        $enumRepo = $manager->getRepository($className);

        foreach ($this->subscriberData as $data) {
            $subscriber = new NewsletterSubscriber();

            $date = new \DateTime();
            $date->modify('-1 day');

            /** @var AbstractEnumValue $status */
            $status = $enumRepo->find($data['status']);

            $subscriber
                ->setEmail($data['email'])
                ->setStatus($status)
                ->setConfirmCode(uniqid())
                ->setStore($store)
                ->setOwner($admin)
                ->setOrganization($organization)
                ->setOriginId($data['originId'])
                ->setChangeStatusAt($date)
                ->setCreatedAt($date)
                ->setUpdatedAt($date)
                ->setChannel($integration)
                ->setDataChannel($channel);

            if (!empty($data['customer'])) {
                /** @var Customer $customer */
                $customer = $this->getReference($data['customer']);

                $subscriber->setCustomer($customer);
            }

            $this->setReference($data['reference'], $subscriber);

            $manager->persist($subscriber);
        }

        $manager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [__NAMESPACE__.'\LoadMagentoChannel'];
    }
}
