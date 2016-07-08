<?php

namespace OroCRM\Bundle\SalesBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

use Oro\Bundle\WorkflowBundle\Configuration\WorkflowConfigurationProvider;
use Oro\Bundle\WorkflowBundle\Configuration\WorkflowDefinitionConfigurationBuilder;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowDefinition;
use Oro\Bundle\WorkflowBundle\Handler\WorkflowDefinitionHandler;

class UpdateOpportunityWorkflows extends AbstractFixture implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $workflowName = 'opportunity_flow';

        /* @var $definition WorkflowDefinition */
        $definition = $manager->getRepository(WorkflowDefinition::class)->findOneBy([
            'name' => $workflowName,
        ]);

        $system = $definition->isSystem();

        /* @var $configurationProvider WorkflowConfigurationProvider */
        $configurationProvider = $this->container->get('oro_workflow.configuration.provider.workflow_config');
        $configuration = $configurationProvider->getWorkflowDefinitionConfiguration(
            null,
            [$workflowName]
        );

        /* @var $definitionHandler WorkflowDefinitionHandler */
        $definitionHandler = $this->container->get('oro_workflow.handler.workflow_definition');

        /* @var $configurationBuilder WorkflowDefinitionConfigurationBuilder */
        $configurationBuilder = $this->container->get('oro_workflow.configuration.builder.workflow_definition');
        list($definition) = $configurationBuilder->buildFromConfiguration($configuration);

        $definition->setSystem($system);

        $definitionHandler->updateWorkflowDefinition($definition);
    }
}
