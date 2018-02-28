<?php

namespace Oro\Bundle\MagentoBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\WorkflowBundle\Entity\Repository\WorkflowItemRepository;

class UpdateWorkflowStartStep extends AbstractFixture
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        /** @var WorkflowItemRepository $workflowItemRepository */
        $workflowItemRepository = $manager->getRepository('OroWorkflowBundle:WorkflowItem');
        $workflowDefinitionRepository = $manager->getRepository('OroWorkflowBundle:WorkflowDefinition');

        // update start step for default shopping cart workflow
        $shoppingCartWorkflowDefinition = $workflowDefinitionRepository->find('b2c_flow_abandoned_shopping_cart');
        if ($shoppingCartWorkflowDefinition && $shoppingCartWorkflowDefinition->getStartStep()) {
            $workflowItemRepository->getEntityWorkflowStepUpgradeQueryBuilder($shoppingCartWorkflowDefinition)
                ->getQuery()
                ->execute();
        }

        // update start step for default order workflow
        $orderWorkflowDefinition = $workflowDefinitionRepository->find('b2c_flow_order_follow_up');
        if ($orderWorkflowDefinition && $orderWorkflowDefinition->getStartStep()) {
            $workflowItemRepository->getEntityWorkflowStepUpgradeQueryBuilder($orderWorkflowDefinition)
                ->getQuery()
                ->execute();
        }
    }
}
