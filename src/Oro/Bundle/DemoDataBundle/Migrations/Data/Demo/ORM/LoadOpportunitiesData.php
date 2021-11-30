<?php

namespace Oro\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\CurrencyBundle\Entity\MultiCurrency;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\SalesBundle\Entity\B2bCustomer;
use Oro\Bundle\SalesBundle\Entity\Manager\AccountCustomerManager;
use Oro\Bundle\SalesBundle\Entity\Opportunity;
use Oro\Bundle\SecurityBundle\Authentication\Token\UsernamePasswordOrganizationToken;
use Oro\Bundle\UserBundle\Entity\User;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Loads new Opportunity entities.
 */
class LoadOpportunitiesData extends AbstractDemoFixture implements DependentFixtureInterface
{
    /** @var Contact[] */
    protected $contacts;

    /** @var  B2bCustomer[] */
    protected $b2bCustomers;

    /** @var Organization */
    protected $organization;

    /** @var AccountCustomerManager */
    protected $accountCustomerManager;

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        parent::setContainer($container);
        $this->accountCustomerManager = $container->get('oro_sales.manager.account_customer');
    }

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            'Oro\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadContactData',
            'Oro\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadLeadsData',
            'Oro\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadB2bCustomerData',
            'Oro\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadChannelData'
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->initSupportingEntities();
        $this->loadOpportunities();

        $tokenStorage = $this->container->get('security.token_storage');
        $tokenStorage->setToken(null);
    }

    protected function initSupportingEntities()
    {
        $this->organization = $this->getReference('default_organization');
        $this->contacts     = $this->em->getRepository('OroContactBundle:Contact')->findAll();
        $this->b2bCustomers = $this->em->getRepository('OroSalesBundle:B2bCustomer')->findAll();
    }

    public function loadOpportunities()
    {
        for ($i = 0; $i < 50; $i++) {
            $user = $this->getRandomUserReference();

            $this->setSecurityContext($user);
            $contact     = $this->contacts[array_rand($this->contacts)];
            $customer    = $this->b2bCustomers[array_rand($this->b2bCustomers)];
            $opportunity = $this->createOpportunity($contact, $customer, $user);
            $this->em->persist($opportunity);
        }

        $this->em->flush();
    }

    /**
     * @param User $user
     */
    protected function setSecurityContext($user)
    {
        $tokenStorage = $this->container->get('security.token_storage');
        $token = new UsernamePasswordOrganizationToken(
            $user,
            $user->getUsername(),
            'main',
            $this->organization,
            $user->getUserRoles()
        );
        $tokenStorage->setToken($token);
    }

    /**
     * @param Contact     $contact
     * @param B2bCustomer $customer
     * @param User        $user
     *
     * @return Opportunity
     */
    protected function createOpportunity($contact, $customer, $user)
    {
        $opportunity = new Opportunity();
        $opportunity->setName($contact->getFirstName() . ' ' . $contact->getLastName());
        $opportunity->setContact($contact);
        $opportunity->setOwner($user);
        $opportunity->setOrganization($this->organization);
        $opportunity->setCustomerAssociation($this->accountCustomerManager->getAccountCustomerByTarget($customer));
        $budgetAmountVal = mt_rand(10, 10000);
        $opportunity->setBudgetAmount(MultiCurrency::create($budgetAmountVal, 'USD'));

        $opportunityStatuses = ['in_progress', 'lost', 'needs_analysis', 'won'];
        $statusName = $opportunityStatuses[array_rand($opportunityStatuses)];
        $enumClass = ExtendHelper::buildEnumValueClassName(Opportunity::INTERNAL_STATUS_CODE);
        $opportunity->setStatus($this->em->getReference($enumClass, $statusName));
        if ($statusName == Opportunity::STATUS_WON) {
            $closeRevenueVal = mt_rand(10, 10000);
            $opportunity->setCloseRevenue(MultiCurrency::create($closeRevenueVal, 'USD'));
            $opportunity->setBaseCloseRevenueValue($closeRevenueVal);
        }

        return $opportunity;
    }
}
