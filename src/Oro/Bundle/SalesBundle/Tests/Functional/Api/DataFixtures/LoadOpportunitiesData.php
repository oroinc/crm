<?php

namespace Oro\Bundle\SalesBundle\Tests\Functional\Api\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\CurrencyBundle\Entity\MultiCurrency;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\SalesBundle\Entity\Opportunity;
use Oro\Bundle\SalesBundle\Entity\OpportunityCloseReason;
use Oro\Bundle\TestFrameworkBundle\Tests\Functional\DataFixtures\LoadOrganization;
use Oro\Bundle\TestFrameworkBundle\Tests\Functional\DataFixtures\LoadUser;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class LoadOpportunitiesData extends AbstractFixture implements ContainerAwareInterface, DependentFixtureInterface
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
            LoadUser::class,
            LoadLeadsData::class
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->em = $manager;
        try {
            $this->createOpportunity(1, 'lost', 'cancelled');
            $this->createOpportunity(2, 'won');
        } finally {
            $this->em = null;
        }
    }

    /**
     * @param int         $number
     * @param string      $status
     * @param string|null $closeReason
     */
    protected function createOpportunity($number, $status, $closeReason = null)
    {
        $opportunity = new Opportunity();
        $opportunity->setName(sprintf('Opportunity %d', $number));
        $opportunity->setOrganization($this->getReference('organization'));
        $opportunity->setOwner($this->getReference('user'));
        $opportunity->setCustomerAssociation($this->getReference('customer_association'));
        $opportunity->setBudgetAmount(MultiCurrency::create(50, 'USD'));
        $opportunity->setCloseRevenue(MultiCurrency::create(100, 'USD'));
        $opportunity->setProbability(1);
        $opportunity->setCloseDate(new \DateTime('2017-01-01', new \DateTimeZone('UTC')));
        $opportunity->setStatus(
            $this->em->getReference(
                ExtendHelper::buildEnumValueClassName(Opportunity::INTERNAL_STATUS_CODE),
                $status
            )
        );
        if ($closeReason) {
            $opportunity->setCloseReason(
                $this->em->getReference(OpportunityCloseReason::class, $closeReason)
            );
        }
        $opportunity->setLead($this->getReference(sprintf('lead%d', $number)));

        $this->em->persist($opportunity);
        $this->em->flush();
        $this->setReference(sprintf('opportunity%d', $number), $opportunity);
    }
}
