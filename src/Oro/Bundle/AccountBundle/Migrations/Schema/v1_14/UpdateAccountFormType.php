<?php

namespace Oro\Bundle\AccountBundle\Migrations\Schema\v1_14;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\AccountBundle\Form\Type\AccountSelectType;
use Oro\Bundle\EntityConfigBundle\Migration\UpdateEntityConfigFieldValueQuery;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class UpdateAccountFormType implements Migration
{
    /**
     * {@inheritDoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $queries->addQuery(
            new UpdateEntityConfigFieldValueQuery(
                Account::class,
                'channel',
                'form',
                'form_type',
                AccountSelectType::class
            )
        );
    }
}
