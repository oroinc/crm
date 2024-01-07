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

/**
 * Loads new Opportunity entities.
 */
class LoadOpportunitiesData extends AbstractDemoFixture implements DependentFixtureInterface
{
    /**
     * {@inheritDoc}
     */
    public function getDependencies(): array
    {
        return [
            LoadContactData::class,
            LoadLeadsData::class,
            LoadB2bCustomerData::class,
            LoadChannelData::class
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager): void
    {
        $tokenStorage = $this->container->get('security.token_storage');
        $accountCustomerManager = $this->container->get('oro_sales.manager.account_customer');
        $organization = $this->getReference('default_organization');
        $contacts = $manager->getRepository(Contact::class)->findAll();
        $customers = $manager->getRepository(B2bCustomer::class)->findAll();

        for ($i = 0; $i < 50; $i++) {
            $user = $this->getRandomUserReference();

            $tokenStorage->setToken(new UsernamePasswordOrganizationToken(
                $user,
                'main',
                $organization,
                $user->getUserRoles()
            ));
            $contact = $contacts[array_rand($contacts)];
            $customer = $customers[array_rand($customers)];
            $opportunity = $this->createOpportunity(
                $manager,
                $accountCustomerManager,
                $contact,
                $customer,
                $user,
                $organization
            );
            $manager->persist($opportunity);
        }
        $manager->flush();
        $tokenStorage->setToken(null);
    }

    private function createOpportunity(
        ObjectManager $manager,
        AccountCustomerManager $accountCustomerManager,
        Contact $contact,
        B2bCustomer $customer,
        User $user,
        Organization $organization
    ): Opportunity {
        $opportunity = new Opportunity();
        $opportunity->setName($contact->getFirstName() . ' ' . $contact->getLastName());
        $opportunity->setContact($contact);
        $opportunity->setOwner($user);
        $opportunity->setOrganization($organization);
        $opportunity->setCustomerAssociation($accountCustomerManager->getAccountCustomerByTarget($customer));
        $budgetAmountVal = mt_rand(10, 10000);
        $opportunity->setBudgetAmount(MultiCurrency::create($budgetAmountVal, 'USD'));

        $opportunityStatuses = ['in_progress', 'lost', 'needs_analysis', 'won'];
        $statusName = $opportunityStatuses[array_rand($opportunityStatuses)];
        $enumClass = ExtendHelper::buildEnumValueClassName(Opportunity::INTERNAL_STATUS_CODE);
        $opportunity->setStatus($manager->getReference($enumClass, $statusName));
        if (Opportunity::STATUS_WON === $statusName) {
            $closeRevenueVal = mt_rand(10, 10000);
            $opportunity->setCloseRevenue(MultiCurrency::create($closeRevenueVal, 'USD'));
            $opportunity->setBaseCloseRevenueValue($closeRevenueVal);
        }

        return $opportunity;
    }
}
