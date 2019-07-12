<?php

namespace Oro\Bundle\SalesBundle\Tests\Behat;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityRepository;
use Oro\Bundle\EntityExtendBundle\Entity\AbstractEnumValue;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\SalesBundle\Entity\Lead;
use Oro\Bundle\SalesBundle\Entity\Opportunity;
use Oro\Bundle\SalesBundle\Entity\OpportunityCloseReason;
use Oro\Bundle\TestFrameworkBundle\Behat\Isolation\ReferenceRepositoryInitializerInterface;
use Oro\Bundle\TestFrameworkBundle\Test\DataFixtures\Collection;

class ReferenceRepositoryInitializer implements ReferenceRepositoryInitializerInterface
{
    /**
     * {@inheritdoc}
     */
    public function init(Registry $doctrine, Collection $referenceRepository)
    {
        $className = ExtendHelper::buildEnumValueClassName(Lead::INTERNAL_STATUS_CODE);

        /** @var EntityRepository $repository */
        $repository = $doctrine->getManager()->getRepository($className);

        /** @var AbstractEnumValue $status */
        foreach ($repository->findAll() as $status) {
            $referenceRepository->set('lead_status_' . $status->getId(), $status);
        }

        $className = ExtendHelper::buildEnumValueClassName(Opportunity::INTERNAL_STATUS_CODE);

        $repository = $doctrine->getManager()->getRepository($className);

        foreach ($repository->findAll() as $status) {
            $referenceRepository->set('opportunity_status_' . $status->getId(), $status);
        }

        $closeReasonRepo = $doctrine->getManager()->getRepository(OpportunityCloseReason::class);
        /** @var OpportunityCloseReason $closeReason */
        foreach ($closeReasonRepo->findAll() as $closeReason) {
            $referenceRepository->set('opportunity_close_reason_' . $closeReason->getName(), $closeReason);
        }
    }
}
