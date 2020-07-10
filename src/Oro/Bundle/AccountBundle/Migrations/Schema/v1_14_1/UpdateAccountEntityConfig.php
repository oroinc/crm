<?php

namespace Oro\Bundle\AccountBundle\Migrations\Schema\v1_14_1;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\EntityConfigBundle\Migration\UpdateEntityConfigEntityValueQuery;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class UpdateAccountEntityConfig implements Migration
{
    /**
     * {@inheritDoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        /**
         * This configuration currently could not be changed from the UI, so we could
         * replace it without a merge with already defined configuration
         * (At the current moment only field level configuration is changeable)
         */
        $query = new UpdateEntityConfigEntityValueQuery(
            'Oro\Bundle\AccountBundle\Entity\Account',
            'entity',
            'contact_information',
            [
                'email' => [
                    [
                        'fieldName' => 'contactInformation'
                    ]
                ]
            ]
        );

        $queries->addPostQuery($query);
    }
}
