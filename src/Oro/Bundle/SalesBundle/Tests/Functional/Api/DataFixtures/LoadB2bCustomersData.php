<?php

namespace Oro\Bundle\SalesBundle\Tests\Functional\Api\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\ChannelBundle\Tests\Functional\Fixture\LoadChannel;
use Oro\Bundle\SalesBundle\Entity\B2bCustomer;
use Oro\Bundle\SalesBundle\Entity\B2bCustomerEmail;
use Oro\Bundle\SalesBundle\Entity\B2bCustomerPhone;
use Oro\Bundle\TestFrameworkBundle\Tests\Functional\DataFixtures\LoadOrganization;
use Oro\Bundle\TestFrameworkBundle\Tests\Functional\DataFixtures\LoadUser;

class LoadB2bCustomersData extends AbstractFixture implements DependentFixtureInterface
{
    /** @var EntityManager */
    protected $em;

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            LoadOrganization::class,
            LoadUser::class,
            LoadChannel::class
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->em = $manager;
        try {
            $this->createCustomer(1);
            $this->createCustomer(2, true);
        } finally {
            $this->em = null;
        }
    }

    /**
     * @param int  $number
     * @param bool $withoutEmailsAndPhones
     */
    protected function createCustomer($number, $withoutEmailsAndPhones = false)
    {
        $customer = new B2bCustomer();
        $customer->setName(sprintf('Customer %d', $number));
        $customer->setOrganization($this->getReference('organization'));
        $customer->setOwner($this->getReference('user'));
        $customer->setDataChannel($this->getReference('default_channel'));

        if (!$withoutEmailsAndPhones) {
            $email1 = new B2bCustomerEmail(sprintf('customer%d_1@example.com', $number));
            $customer->addEmail($email1);
            $email2 = new B2bCustomerEmail(sprintf('customer%d_2@example.com', $number));
            $email2->setPrimary(true);
            $customer->addEmail($email2);

            $phone1 = new B2bCustomerPhone(sprintf('555666%d111', $number));
            $customer->addPhone($phone1);
            $phone2 = new B2bCustomerPhone(sprintf('555666%d112', $number));
            $phone2->setPrimary(true);
            $customer->addPhone($phone2);
        }

        $this->em->persist($customer);
        $this->em->flush();
        $this->setReference(sprintf('customer%d', $number), $customer);
    }
}
