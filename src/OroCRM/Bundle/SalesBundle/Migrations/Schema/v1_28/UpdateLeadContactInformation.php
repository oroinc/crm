<?php

namespace OroCRM\Bundle\SalesBundle\Migrations\Schema\v1_28;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\EntityConfigBundle\Migration\UpdateEntityConfigEntityValueQuery;

class UpdateLeadContactInformation implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $queries->addQuery(
            new UpdateEntityConfigEntityValueQuery(
                'OroCRM\Bundle\SalesBundle\Entity\Lead',
                'entity',
                'contact_information',
                ['email' => [['fieldName' => 'primaryEmail']], 'phone' => [['fieldName' => 'primaryPhone']]]
            )
        );
    }
}
