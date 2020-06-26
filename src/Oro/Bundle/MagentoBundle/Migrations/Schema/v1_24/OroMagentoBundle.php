<?php

namespace Oro\Bundle\MagentoBundle\Migrations\Schema\v1_24;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\OrderedMigrationInterface;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class OroMagentoBundle implements Migration, OrderedMigrationInterface
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
        $query = 'UPDATE orocrm_magento_customer ' .
            'SET vat_temp = ROUND(vat * 100.0) ' .
            'WHERE vat IS NOT NULL';
        $queries->addPreQuery($query);

        // Change data type of the VAT column
        $schema
            ->getTable('orocrm_magento_customer')
            ->getColumn('vat')
            ->setType(Type::getType(Types::STRING));

        // Copy values back to VAT column
        $query = 'UPDATE orocrm_magento_customer ' .
            'SET vat = vat_temp ' .
            'WHERE vat IS NOT NULL';
        $queries->addPostQuery($query);
    }
}
