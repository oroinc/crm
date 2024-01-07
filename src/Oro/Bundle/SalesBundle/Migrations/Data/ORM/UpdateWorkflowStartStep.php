<?php

namespace Oro\Bundle\SalesBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\WorkflowBundle\Entity\Repository\WorkflowItemRepository;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowDefinition;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;

/**
 * Update workflow start step for default lead and opportunity workflows
 */
class UpdateWorkflowStartStep extends AbstractFixture
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        /** @var WorkflowItemRepository $workflowItemRepository */
        $workflowItemRepository = $manager->getRepository(WorkflowItem::class);
        $workflowDefinitionRepository = $manager->getRepository(WorkflowDefinition::class);

        // update start step for default lead workflow
        /** @var WorkflowDefinition $leadWorkflowDefinition */
        $leadWorkflowDefinition = $workflowDefinitionRepository->find('b2b_flow_lead');
        if ($leadWorkflowDefinition && $leadWorkflowDefinition->getStartStep()) {
            $workflowItemRepository->getEntityWorkflowStepUpgradeQueryBuilder($leadWorkflowDefinition)
                ->getQuery()
                ->execute();
        }

        // update start step for default opportunity workflow
        $opportunityWorkflowDefinition = $workflowDefinitionRepository->find('b2b_flow_sales');
        if ($opportunityWorkflowDefinition && $opportunityWorkflowDefinition->getStartStep()) {
            /** @var WorkflowDefinition $opportunityWorkflowDefinition */
            $workflowItemRepository->getEntityWorkflowStepUpgradeQueryBuilder($opportunityWorkflowDefinition)
                ->getQuery()
                ->execute();
        }
    }
}
