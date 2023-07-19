<?php

namespace Oro\Bundle\ContactUsBundle\Migrations\Schema\v1_19;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Extension\RenameExtension;
use Oro\Bundle\MigrationBundle\Migration\Extension\RenameExtensionAwareInterface;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\OrderedMigrationInterface;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class OroContactUsBundle implements Migration, OrderedMigrationInterface, RenameExtensionAwareInterface
{
    private RenameExtension $renameExtension;

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

    public function getOrder()
    {
        return 100;
    }

    public function setRenameExtension(RenameExtension $renameExtension)
    {
        $this->renameExtension = $renameExtension;
    }
}
