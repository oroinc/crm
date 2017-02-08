<?php

namespace Oro\Bundle\SalesBundle\Tests\Functional\Fixture;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\CurrencyBundle\Entity\MultiCurrency;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\SalesBundle\Entity\Opportunity;
use Oro\Bundle\SalesBundle\Entity\B2bCustomer;
use Oro\Bundle\UserBundle\Entity\User;

class LoadClosedOpportunityFixtures extends AbstractFixture implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @var Organization
     */
    private $organization;

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
        $customer->setName("Default customer");
        $customer->setOrganization($this->organization);
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
        $account->setOrganization($this->organization);

        $this->em->persist($account);
        $this->em->flush();

        $this->setReference('default_account', $account);
    }

    /**
     * @return void
     */
    protected function createOpportunity()
    {
        $opportunity = new Opportunity();

        $opportunity->setName('test_opportunity_closed');

        $opportunity->setCustomerAssociation($this->getReference('default_account_customer'));
        $opportunity->setOwner($this->getUser());

        $budgetAmount = MultiCurrency::create(50, 'USD');
        $opportunity->setBudgetAmount($budgetAmount);

        $closeRevenue = MultiCurrency::create(100, 'USD');
        $opportunity->setCloseRevenue($closeRevenue);

        $opportunity->setProbability(100);
        $opportunity->setOrganization($this->organization);
        $opportunity->setCloseDate(new \DateTime());

        $enumClass = ExtendHelper::buildEnumValueClassName(Opportunity::INTERNAL_STATUS_CODE);
        $opportunity->setStatus($this->em->getReference($enumClass, 'lost'));

        $this->em->persist($opportunity);
        $this->em->flush();

        $this->setReference('lost_opportunity', $opportunity);
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->em = $manager;
        $this->organization = $manager->getRepository('OroOrganizationBundle:Organization')->getFirst();
        $this->createChannel();
        $this->createAccount();
        $this->createB2bCustomer();

        $this->createOpportunity();
    }
}
