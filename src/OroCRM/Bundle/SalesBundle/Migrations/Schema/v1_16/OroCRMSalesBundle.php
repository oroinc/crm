<?php

namespace OroCRM\Bundle\SalesBundle\Migrations\Schema\v1_16;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\EntityExtendBundle\Migration\ExtendOptionsManager;
use Oro\Bundle\EntityExtendBundle\Migration\OroOptions;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class OroCRMSalesBundle implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $addressColumn = $schema->getTable('orocrm_sales_opportunity')->getColumn('customer_id');
        $addressColumn->setOptions(
            [
                OroOptions::KEY => [
                    ExtendOptionsManager::FIELD_NAME_OPTION => 'customer',
                    'form'                                  => ['form_type' => 'orocrm_sales_b2bcustomer_select']
                ],
            ]
        );
    }
}
