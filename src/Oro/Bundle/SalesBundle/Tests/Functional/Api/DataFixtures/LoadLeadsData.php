<?php

namespace Oro\Bundle\SalesBundle\Tests\Functional\Api\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\ChannelBundle\Builder\BuilderFactory;
use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\SalesBundle\Entity\B2bCustomer;
use Oro\Bundle\SalesBundle\Entity\Lead;
use Oro\Bundle\SalesBundle\Entity\LeadEmail;
use Oro\Bundle\SalesBundle\Entity\LeadPhone;
use Oro\Bundle\SalesBundle\Entity\Manager\AccountCustomerManager;
use Oro\Bundle\TestFrameworkBundle\Tests\Functional\DataFixtures\LoadOrganization;
use Oro\Bundle\TestFrameworkBundle\Tests\Functional\DataFixtures\LoadUser;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class LoadLeadsData extends AbstractFixture implements ContainerAwareInterface, DependentFixtureInterface
{
    use ContainerAwareTrait;

    /** @var EntityManager */
    protected $em;

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            LoadOrganization::class,
            LoadUser::class
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->em = $manager;
        try {
            $this->createChannel();
            $this->createAccount(1);
            $this->createAccount(2);
            $this->createB2bCustomer(1);
            $this->createB2bCustomer(2);
            $this->createCustomerAssociation();
            $this->createLead(1, 'new');
            $this->createLead(2, 'canceled', true);
        } finally {
            $this->em = null;
        }
    }

    protected function createChannel()
    {
        /** @var BuilderFactory $factory */
        $factory = $this->container->get('oro_channel.builder.factory');

        $channel = $factory
            ->createBuilder()
            ->setName('B2B Channel')
            ->setChannelType('b2b')
            ->setStatus(Channel::STATUS_ACTIVE)
            ->setOwner($this->getReference('organization'))
            ->setEntities()
            ->getChannel();

        $this->em->persist($channel);
        $this->em->flush();
        $this->setReference('channel', $channel);
    }

    /**
     * @param int $number
     */
    protected function createAccount($number)
    {
        $account = new Account();
        $account->setName(sprintf('Account %d', $number));
        $account->setOrganization($this->getReference('organization'));

        $this->em->persist($account);
        $this->em->flush();
        $this->setReference(sprintf('account%d', $number), $account);
    }

    /**
     * @param int $number
     */
    protected function createB2bCustomer($number)
    {
        $customer = new B2bCustomer();
        $customer->setName(sprintf('B2B Customer %d', $number));
        $customer->setOrganization($this->getReference('organization'));
        $customer->setAccount($this->getReference(sprintf('account%d', $number)));
        $customer->setDataChannel($this->getReference('channel'));

        $this->em->persist($customer);
        $this->em->flush();
        $this->setReference(sprintf('b2b_customer%d', $number), $customer);
    }

    protected function createCustomerAssociation()
    {
        /** @var AccountCustomerManager $accountManager */
        $accountManager = $this->container->get('oro_sales.manager.account_customer');

        $customerAssociation = $accountManager->getAccountCustomerByTarget(
            $this->getReference('b2b_customer1')
        );

        $this->em->persist($customerAssociation);
        $this->em->flush();
        $this->setReference('customer_association', $customerAssociation);
    }

    /**
     * @param int    $number
     * @param string $status
     * @param bool   $withoutEmailsAndPhones
     */
    protected function createLead($number, $status, $withoutEmailsAndPhones = false)
    {
        $lead = new Lead();
        $lead->setName(sprintf('Lead %d', $number));
        $lead->setOrganization($this->getReference('organization'));
        $lead->setOwner($this->getReference('user'));
        $lead->setCustomerAssociation($this->getReference('customer_association'));
        $lead->setStatus(
            $this->em->getReference(
                ExtendHelper::buildEnumValueClassName(Lead::INTERNAL_STATUS_CODE),
                $status
            )
        );

        if (!$withoutEmailsAndPhones) {
            $email1 = new LeadEmail(sprintf('lead%d_1@example.com', $number));
            $lead->addEmail($email1);
            $email2 = new LeadEmail(sprintf('lead%d_2@example.com', $number));
            $email2->setPrimary(true);
            $lead->addEmail($email2);

            $phone1 = new LeadPhone(sprintf('555666%d111', $number));
            $lead->addPhone($phone1);
            $phone2 = new LeadPhone(sprintf('555666%d112', $number));
            $phone2->setPrimary(true);
            $lead->addPhone($phone2);
        }

        $this->em->persist($lead);
        $this->em->flush();
        $this->setReference(sprintf('lead%d', $number), $lead);
    }
}
