<?php

namespace OroCRM\Bundle\SalesBundle\Migrations\Data\ORM;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\WorkflowBundle\Model\WorkflowManager;

class DeactivateSalesWorkflows extends AbstractFixture implements ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        /** @var WorkflowManager $workflowManager */
        $workflowManager = $this->container->get('oro_workflow.manager');
        //TODO: change to usage of WorkflowDefinition or workflow_name or Workflow or WorkflowItem in BAP-10979
        $workflowManager->deactivateWorkflow('OroCRM\Bundle\SalesBundle\Entity\Lead');
        $workflowManager->deactivateWorkflow('OroCRM\Bundle\SalesBundle\Entity\Opportunity');
    }
}
