<?php

namespace OroCRM\Bundle\MagentoBundle\Migrations\Schema\v1_41_1;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class RemoveWorkflowFields implements Migration
{
    public function up(Schema $schema, QueryBag $queries)
    {
        //workflow now has no direct relations

        $orderTable = $schema->getTable('orocrm_magento_order');
        $orderTable->dropColumn('workflow_step_id');
        $orderTable->dropColumn('workflow_item_id');

        $cartTable = $schema->getTable('orocrm_magento_cart');
        $cartTable->dropColumn('workflow_step_id');
        $cartTable->dropColumn('workflow_item_id');
    }
}
