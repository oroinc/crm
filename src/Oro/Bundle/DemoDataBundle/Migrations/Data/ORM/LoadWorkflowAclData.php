<?php

namespace Oro\Bundle\DemoDataBundle\Migrations\Data\ORM;

use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\WorkflowBundle\Model\Filter\WorkflowDefinitionFilters;

/**
 * Loads CRM workflows ACL data
 */
class LoadWorkflowAclData extends LoadAclRolesData
{
    #[\Override]
    public function load(ObjectManager $manager)
    {
        /* @var $filters WorkflowDefinitionFilters */
        $filters = $this->container->get('oro_workflow.registry.definition_filters');
        $filters->setEnabled(false); // disable filters, because some workflows disabled by `features` by default

        parent::load($manager);

        $filters->setEnabled(true);
    }

    #[\Override]
    protected function getDataPath()
    {
        return '@OroDemoDataBundle/Migrations/Data/ORM/CrmRoles/workflows.yml';
    }
}
