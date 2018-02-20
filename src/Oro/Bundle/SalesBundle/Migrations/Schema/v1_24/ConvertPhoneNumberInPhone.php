<?php

namespace Oro\Bundle\SalesBundle\Migrations\Schema\v1_24;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\OrderedMigrationInterface;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
        $query  = 'INSERT INTO orocrm_sales_lead_phone (owner_id, phone, is_primary)
                       SELECT orocrm_sales_lead.id, orocrm_sales_lead.phone_number, \'1\' FROM orocrm_sales_lead
                       WHERE orocrm_sales_lead.phone_number IS NOT NULL';

        $queries->addPostQuery($query);
    }
}
