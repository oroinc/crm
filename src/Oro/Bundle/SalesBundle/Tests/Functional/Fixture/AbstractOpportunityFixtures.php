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

    /**
     * @var Organization
     */
    protected $organization;

    /**
     * @var User
     */
    protected $user;

    /**
     * @var ObjectManager
     */
    protected $em;

    /**
     * @return User
     */
    protected function getUser()
    {
        if (empty($this->user)) {
            $this->user = $this->em->getRepository('OroUserBundle:User')->findOneBy(['username' => 'admin']);
        }

        return $this->user;
    }

    /**
     * @return Organization
     */
    protected function getOrganization()
    {
        if (empty($this->organization)) {
            $this->organization = $this->em->getRepository('OroOrganizationBundle:Organization')->getFirst();
        }
        return $this->organization;
    }

    /**
     * @return void
     */
    protected function createChannel()
    {
        $factory = $this->container->get('oro_channel.builder.factory');

        $channel = $factory
            ->createBuilder()
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

    /**
     * @return void
     */
    protected function createB2bCustomer()
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

    /**
     * @return void
     */
    protected function createAccount()
    {
        $account = new Account();
        $account->setName('Default account');
        $account->setOrganization($this->getOrganization());

        $this->em->persist($account);
        $this->em->flush();

        $this->setReference('default_account', $account);
    }

    /**
     * @return void
     */
    abstract protected function createOpportunity();

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->em = $manager;
        $this->createChannel();
        $this->createAccount();
        $this->createB2bCustomer();

        $this->createOpportunity();
    }
}
