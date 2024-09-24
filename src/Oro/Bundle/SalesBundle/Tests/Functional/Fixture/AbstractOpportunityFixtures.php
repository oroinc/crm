<?php

namespace Oro\Bundle\SalesBundle\Tests\Functional\Fixture;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\SalesBundle\Entity\B2bCustomer;
use Oro\Bundle\TestFrameworkBundle\Tests\Functional\DataFixtures\LoadOrganization;
use Oro\Bundle\TestFrameworkBundle\Tests\Functional\DataFixtures\LoadUser;
use Oro\Bundle\UserBundle\Entity\User;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

abstract class AbstractOpportunityFixtures extends AbstractFixture implements
    ContainerAwareInterface,
    DependentFixtureInterface
{
    use ContainerAwareTrait;

    private ?User $user = null;
    private ?Organization $organization = null;

    #[\Override]
    public function getDependencies(): array
    {
        return [LoadOrganization::class, LoadUser::class];
    }

    #[\Override]
    public function load(ObjectManager $manager): void
    {
        $this->createChannel($manager);
        $this->createAccount($manager);
        $this->createB2bCustomer($manager);
        $this->createOpportunity($manager);
    }

    protected function getUser(): User
    {
        if (null === $this->user) {
            $this->user = $this->getReference(LoadUser::USER);
        }

        return $this->user;
    }

    protected function getOrganization(): Organization
    {
        if (null === $this->organization) {
            $this->organization = $this->getReference(LoadOrganization::ORGANIZATION);
        }

        return $this->organization;
    }

    protected function createChannel(ObjectManager $manager): void
    {
        $channel = $this->container->get('oro_channel.builder.factory')
            ->createBuilder()
            ->setName('Default channel')
            ->setChannelType('b2b')
            ->setStatus(Channel::STATUS_ACTIVE)
            ->setOwner($this->organization)
            ->setEntities()
            ->getChannel();

        $manager->persist($channel);
        $manager->flush();

        $this->setReference('default_channel', $channel);
    }

    protected function createB2bCustomer(ObjectManager $manager): void
    {
        $customer = new B2bCustomer();
        $customer->setAccount($this->getReference('default_account'));
        $customer->setName('Default customer');
        $customer->setOrganization($this->getOrganization());
        $customer->setDataChannel($this->getReference('default_channel'));

        $manager->persist($customer);
        $manager->flush();

        $this->setReference(
            'default_account_customer',
            $this->container->get('oro_sales.manager.account_customer')->getAccountCustomerByTarget($customer)
        );
    }

    protected function createAccount(ObjectManager $manager): void
    {
        $account = new Account();
        $account->setName('Default account');
        $account->setOrganization($this->getOrganization());

        $manager->persist($account);
        $manager->flush();

        $this->setReference('default_account', $account);
    }

    abstract protected function createOpportunity(ObjectManager $manager): void;
}
