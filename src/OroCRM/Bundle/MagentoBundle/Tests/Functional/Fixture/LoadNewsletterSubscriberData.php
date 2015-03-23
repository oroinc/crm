<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Functional\Fixture;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Oro\Bundle\UserBundle\Entity\UserManager;
use Oro\Bundle\UserBundle\Migrations\Data\ORM\LoadAdminUserData;
use OroCRM\Bundle\MagentoBundle\Entity\NewsletterSubscriber;
use OroCRM\Bundle\MagentoBundle\Entity\Customer;
use OroCRM\Bundle\MagentoBundle\Entity\Store;

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
            'originId' => '123456'
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

        foreach ($this->subscriberData as $data) {
            $subscriber = new NewsletterSubscriber();

            $date = new \DateTime();

            $subscriber
                ->setEmail($data['email'])
                ->setStatus($data['status'])
                ->setConfirmCode(uniqid())
                ->setStore($store)
                ->setOwner($admin)
                ->setOrganization($organization)
                ->setOriginId($data['originId'])
                ->setChangeStatusAt($date);

            if (!empty($data['customer'])) {
                /** @var Customer $customer */
                $customer = $this->getReference($data['customer']);

                $subscriber->setCustomer($customer);
            }

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
