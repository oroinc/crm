<?php

namespace Oro\Bundle\ContactUsBundle\Migrations\Schema\v1_19;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Extension\RenameExtensionAwareInterface;
use Oro\Bundle\MigrationBundle\Migration\Extension\RenameExtensionAwareTrait;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\OrderedMigrationInterface;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class OroContactUsBundle implements Migration, OrderedMigrationInterface, RenameExtensionAwareInterface
{
    use RenameExtensionAwareTrait;

    /**
     * {@inheritDoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $table = $schema->getTable('orocrm_contactus_request');

        if ($table->hasColumn('organization_name')) {
            $this->renameExtension->renameColumn(
                $schema,
                $queries,
                $table,
                'organization_name',
                'customer_name'
            );
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 100;
    }
}
