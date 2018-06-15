<?php

namespace Oro\Bundle\SalesBundle\Migrations\Schema\v1_39;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\EntityConfigBundle\Migration\UpdateEntityConfigEntityValueQuery;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\SalesBundle\Entity\B2bCustomer;
use Oro\Bundle\SalesBundle\Form\Type\B2bCustomerSelectType;

class UpdateB2bCustomerFormType implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $queries->addQuery(
            new UpdateEntityConfigEntityValueQuery(
                B2bCustomer::class,
                'form',
                'form_type',
                B2bCustomerSelectType::class,
                'oro_sales_b2bcustomer_select'
            )
        );
    }
}
