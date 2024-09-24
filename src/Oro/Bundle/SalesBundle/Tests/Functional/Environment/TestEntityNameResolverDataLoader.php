<?php

namespace Oro\Bundle\SalesBundle\Tests\Functional\Environment;

use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Oro\Bundle\EntityBundle\Provider\EntityNameProviderInterface;
use Oro\Bundle\EntityBundle\Tests\Functional\Environment\TestEntityNameResolverDataLoaderInterface;
use Oro\Bundle\SalesBundle\Entity\B2bCustomer;
use Oro\Bundle\SalesBundle\Entity\Lead;
use Oro\Bundle\SalesBundle\Entity\LeadAddress;
use Oro\Bundle\SalesBundle\Entity\Opportunity;

class TestEntityNameResolverDataLoader implements TestEntityNameResolverDataLoaderInterface
{
    private TestEntityNameResolverDataLoaderInterface $innerDataLoader;

    public function __construct(TestEntityNameResolverDataLoaderInterface $innerDataLoader)
    {
        $this->innerDataLoader = $innerDataLoader;
    }

    #[\Override]
    public function loadEntity(
        EntityManagerInterface $em,
        ReferenceRepository $repository,
        string $entityClass
    ): array {
        if (Lead::class === $entityClass) {
            $lead = new Lead();
            $lead->setOrganization($repository->getReference('organization'));
            $lead->setOwner($repository->getReference('user'));
            $lead->setName('Test Lead');
            $repository->setReference('lead', $lead);
            $em->persist($lead);
            $em->flush();

            return ['lead'];
        }

        if (LeadAddress::class === $entityClass) {
            $lead = new Lead();
            $lead->setOrganization($repository->getReference('organization'));
            $lead->setOwner($repository->getReference('user'));
            $lead->setName('Test Lead');
            $em->persist($lead);
            $leadAddress = new LeadAddress();
            $leadAddress->setOrganization($repository->getReference('organization'));
            $leadAddress->setOwner($lead);
            $leadAddress->setFirstName('Jane');
            $leadAddress->setMiddleName('M');
            $leadAddress->setLastName('Doo');
            $repository->setReference('leadAddress', $leadAddress);
            $em->persist($leadAddress);
            $em->flush();

            return ['leadAddress'];
        }

        if (Opportunity::class === $entityClass) {
            $opportunity = new Opportunity();
            $opportunity->setOrganization($repository->getReference('organization'));
            $opportunity->setOwner($repository->getReference('user'));
            $opportunity->setName('Test Opportunity');
            $repository->setReference('opportunity', $opportunity);
            $em->persist($opportunity);
            $em->flush();

            return ['opportunity'];
        }

        if (B2bCustomer::class === $entityClass) {
            $b2bCustomer = new B2bCustomer();
            $b2bCustomer->setOrganization($repository->getReference('organization'));
            $b2bCustomer->setOwner($repository->getReference('user'));
            $b2bCustomer->setName('Test B2B Customer');
            $repository->setReference('b2bCustomer', $b2bCustomer);
            $em->persist($b2bCustomer);
            $em->flush();

            return ['b2bCustomer'];
        }

        return $this->innerDataLoader->loadEntity($em, $repository, $entityClass);
    }

    #[\Override]
    public function getExpectedEntityName(
        ReferenceRepository $repository,
        string $entityClass,
        string $entityReference,
        ?string $format,
        ?string $locale
    ): string {
        if (Lead::class === $entityClass) {
            return 'Test Lead';
        }
        if (LeadAddress::class === $entityClass) {
            return EntityNameProviderInterface::SHORT === $format
                ? 'Jane'
                : 'Jane M Doo';
        }
        if (Opportunity::class === $entityClass) {
            return 'Test Opportunity';
        }
        if (B2bCustomer::class === $entityClass) {
            return 'Test B2B Customer';
        }

        return $this->innerDataLoader->getExpectedEntityName(
            $repository,
            $entityClass,
            $entityReference,
            $format,
            $locale
        );
    }
}
