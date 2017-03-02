<?php

namespace Oro\Bundle\CRMBundle\Migrations\Schema;

use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\Installation;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\CRMBundle\Migrations\Schema\v1_1\MigrateRelations;
use Oro\Bundle\CRMBundle\Migrations\Schema\v1_2\MigrateGridViews;
use Oro\Bundle\CRMBundle\Migrations\Schema\v1_3\EmbededFormType;
use Oro\Bundle\CRMBundle\Migrations\Schema\v1_3\TaggingEntityName;
use Oro\Bundle\CRMBundle\Migrations\Schema\v1_3_1\WorkflowItemEntityClass;

class OroCRMBundleInstaller implements Installation, ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * {@inheritdoc}
     */
    public function getMigrationVersion()
    {
        return 'v1_3_1';
    }

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        if ($this->container->hasParameter('installed') && $this->container->getParameter('installed')) {
            MigrateRelations::updateWorkFlow($schema, $queries);
            MigrateGridViews::updateGridViews($queries);
            EmbededFormType::updateEmbededFormType($queries);
            TaggingEntityName::updateTaggingEntityName($queries);
            WorkflowItemEntityClass::updateWorkflowItemEntityClass($queries);
        }
    }
}
