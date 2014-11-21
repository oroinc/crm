<?php

namespace OroCRM\Bundle\MagentoBundle\Migrations\Schema\v1_21;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;

use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\MigrationBundle\Migration\OrderedMigrationInterface;

class OroCRMMagentoBundle implements Migration, OrderedMigrationInterface
{
    /**
     * @inheritdoc
     */
    public function getOrder()
    {
        return 20;
    }

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        // Copy values from VAT to temp auxiliary column with correction
        $query = 'UPDATE orocrm_magento_customer customer ' .
            'SET customer.vat_temp = ROUND(customer.vat*100.0) ' .
            'WHERE customer.vat IS NOT NULL';
        $queries->addPreQuery($query);

        // Change data type of the VAT column
        $schema
            ->getTable('orocrm_magento_customer')
            ->getColumn('vat')
            ->setType(Type::getType(Type::STRING));

        // Copy values back to VAT column
        $query = 'UPDATE orocrm_magento_customer customer ' .
            'SET customer.vat = customer.vat_temp ' .
            'WHERE customer.vat IS NOT NULL';
        $queries->addPostQuery($query);
    }
}
