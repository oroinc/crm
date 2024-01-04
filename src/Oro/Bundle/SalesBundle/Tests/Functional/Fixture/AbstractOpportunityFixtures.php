<?php

namespace Oro\Bundle\SalesBundle\Tests\Functional\Fixture;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\SalesBundle\Entity\B2bCustomer;
use Oro\Bundle\UserBundle\Entity\User;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

abstract class AbstractOpportunityFixtures extends AbstractFixture implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    private ?User $user = null;
    private ?Organization $organization = null;
    protected ObjectManager $em;

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager): void
    {
        $this->em = $manager;
        $this->createChannel();
        $this->createAccount();
        $this->createB2bCustomer();
        $this->createOpportunity();
    }

    protected function getUser(): User
    {
        if (null === $this->user) {
            $this->user = $this->em->getRepository(User::class)->findOneBy(['username' => 'admin']);
        }

        return $this->user;
    }

    protected function getOrganization(): Organization
    {
        if (null === $this->organization) {
            $this->organization = $this->em->getRepository(Organization::class)->getFirst();
        }

        return $this->organization;
    }

    protected function createChannel(): void
    {
        $factory = $this->container->get('oro_channel.builder.factory');
        $channel = $factory->createBuilder()
            ->setName('Default channel')
            ->setChannelType('b2b')
            ->setStatus(Channel::STATUS_ACTIVE)
            ->setOwner($this->organization)
            ->setEntities()
            ->getChannel();
        $this->em->persist($channel);
        $this->em->flush();

        $this->setReference('default_channel', $channel);
    }

    protected function createB2bCustomer(): void
    {
        $customer = new B2bCustomer();
        $account  = $this->getReference('default_account');
        $customer->setAccount($account);
        $customer->setName('Default customer');
        $customer->setOrganization($this->getOrganization());
        $customer->setDataChannel($this->getReference('default_channel'));
        $this->em->persist($customer);
        $this->em->flush();

        $accountManager = $this->container->get('oro_sales.manager.account_customer');
        $accountCustomer = $accountManager->getAccountCustomerByTarget($customer);
        $this->setReference('default_account_customer', $accountCustomer);
    }

    protected function createAccount(): void
    {
        $account = new Account();
        $account->setName('Default account');
        $account->setOrganization($this->getOrganization());
        $this->em->persist($account);
        $this->em->flush();

        $this->setReference('default_account', $account);
    }

    abstract protected function createOpportunity(): void;
}
