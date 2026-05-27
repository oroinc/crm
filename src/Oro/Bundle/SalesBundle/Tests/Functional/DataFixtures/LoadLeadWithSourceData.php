<?php

namespace Oro\Bundle\SalesBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\SalesBundle\Entity\Lead;
use Oro\Bundle\SalesBundle\Entity\LeadEmail;
use Oro\Bundle\TestFrameworkBundle\Tests\Functional\DataFixtures\LoadOrganization;
use Oro\Bundle\TestFrameworkBundle\Tests\Functional\DataFixtures\LoadUser;

/**
 * Creates a Lead with extended enum fields (source, status) set for duplication tests.
 */
class LoadLeadWithSourceData extends AbstractFixture implements DependentFixtureInterface
{
    public const LEAD = 'lead_with_source';

    #[\Override]
    public function getDependencies(): array
    {
        return [LoadOrganization::class, LoadUser::class];
    }

    #[\Override]
    public function load(ObjectManager $manager): void
    {
        $lead = new Lead();
        $lead->setName('Test Lead');
        $lead->setFirstName('John');
        $lead->setLastName('Doe');
        $lead->setCompanyName('Test Co');
        $lead->setNotes('Test notes');
        $lead->setOrganization($this->getReference(LoadOrganization::ORGANIZATION));
        $lead->setOwner($this->getReference(LoadUser::USER));

        $lead->setStatus(
            $manager->find(
                ExtendHelper::buildEnumValueClassName(Lead::INTERNAL_STATUS_CODE),
                'new'
            )
        );

        $source = $manager->getRepository(ExtendHelper::buildEnumValueClassName('lead_source'))
            ->findOneBy([]);

        $lead->setSource($source);

        $email = new LeadEmail('test@example.com');
        $email->setPrimary(true);
        $lead->addEmail($email);

        $manager->persist($lead);
        $manager->flush();

        $this->setReference(self::LEAD, $lead);
    }
}
