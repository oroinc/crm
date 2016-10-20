<?php

namespace Oro\Bundle\SalesBundle\Migrations\Schema\v1_24;

use Doctrine\DBAL\Schema\Schema;

use Symfony\Component\DependencyInjection\ContainerInterface;

use Oro\Bundle\MigrationBundle\Migration\OrderedMigrationInterface;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class ConvertPhoneNumberInPhone implements
    Migration,
    OrderedMigrationInterface
{
    /** @var ContainerInterface */
    protected $container;

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 5;
    }

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $query  = 'INSERT INTO oro_sales_lead_phone (owner_id, phone, is_primary)
                       SELECT oro_sales_lead.id, oro_sales_lead.phone_number, \'1\' FROM oro_sales_lead
                       WHERE oro_sales_lead.phone_number IS NOT NULL';

        $queries->addPostQuery($query);
    }
}
