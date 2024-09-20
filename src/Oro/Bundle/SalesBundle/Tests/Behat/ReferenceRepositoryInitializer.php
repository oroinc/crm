<?php

namespace Oro\Bundle\SalesBundle\Tests\Behat;

use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\EntityExtendBundle\Entity\EnumOption;
use Oro\Bundle\EntityExtendBundle\Entity\EnumOptionInterface;
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
    public function init(ManagerRegistry $doctrine, Collection $referenceRepository): void
    {
        $repository = $doctrine->getManager()->getRepository(EnumOption::class);
        /** @var EnumOptionInterface $status */
        foreach ($repository->findBy(['enumCode' => Lead::INTERNAL_STATUS_CODE]) as $status) {
            $referenceRepository->set($status->getId(), $status);
        }

        foreach ($repository->findBy(['enumCode' => Opportunity::INTERNAL_STATUS_CODE]) as $status) {
            $referenceRepository->set($status->getId(), $status);
        }

        $closeReasonRepo = $doctrine->getManager()->getRepository(OpportunityCloseReason::class);
        /** @var OpportunityCloseReason $closeReason */
        foreach ($closeReasonRepo->findAll() as $closeReason) {
            $referenceRepository->set('opportunity_close_reason_' . $closeReason->getName(), $closeReason);
        }
    }
}
