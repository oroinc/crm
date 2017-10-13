<?php

namespace Oro\Bundle\MagentoBundle\Migrations\Schema\v1_50_2;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class AddCountryTextToAddresses implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->addCountryTextField($schema, 'orocrm_magento_customer_addr');
        $this->addCountryTextField($schema, 'orocrm_magento_order_address');
        $this->addCountryTextField($schema, 'orocrm_magento_cart_address');
    }

    /**
     * @param Schema $schema
     * @param string $tableName
     */
    private function addCountryTextField(Schema $schema, $tableName)
    {
        $table = $schema->getTable($tableName);
        if (!$table->hasColumn('country_text')) {
            $table->addColumn('country_text', 'string', ['notnull' => false, 'length' => 255]);
        }
    }
}
