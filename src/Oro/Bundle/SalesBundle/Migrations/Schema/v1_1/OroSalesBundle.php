<?php

namespace Oro\Bundle\SalesBundle\Migrations\Schema\v1_1;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Extension\RenameExtensionAwareInterface;
use Oro\Bundle\MigrationBundle\Migration\Extension\RenameExtensionAwareTrait;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class OroSalesBundle implements Migration, RenameExtensionAwareInterface
{
    use RenameExtensionAwareTrait;

    /**
     * {@inheritDoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->renameExtension->renameTable(
            $schema,
            $queries,
            'orocrm_sales_opportunity_close_reason',
            'orocrm_sales_opport_close_rsn'
        );
        $this->renameExtension->renameTable(
            $schema,
            $queries,
            'orocrm_sales_opportunity_status',
            'orocrm_sales_opport_status'
        );
    }
}
