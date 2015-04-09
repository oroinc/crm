<?php

namespace OroCRM\Bundle\ContactUsBundle\Migrations\Schema\v1_8;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\EntityConfigBundle\Migration\UpdateEntityConfigFieldValueQuery;
use Oro\Bundle\EntityConfigBundle\Migration\UpdateEntityConfigIndexFieldValueQuery;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class UpdateCreatedUpdatedLabels implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $entityName = 'OroCRM\Bundle\ContactUsBundle\Entity\ContactRequest';
        $scope = 'entity';
        $code = 'label';
        $fieldCreated = 'createdAt';
        $valueCreated = 'oro.ui.created_at';

        $fieldUpdated = 'updatedAt';
        $valueUpdated = 'oro.ui.updated_at';

        $queries->addQuery(
            new UpdateEntityConfigFieldValueQuery($entityName, $fieldCreated, $scope, $code, $valueCreated)
        );
        $queries->addQuery(
            new UpdateEntityConfigIndexFieldValueQuery($entityName, $fieldCreated, $scope, $code, $valueCreated)
        );

        $queries->addQuery(
            new UpdateEntityConfigFieldValueQuery($entityName, $fieldUpdated, $scope, $code, $valueUpdated)
        );
        $queries->addQuery(
            new UpdateEntityConfigIndexFieldValueQuery($entityName, $fieldUpdated, $scope, $code, $valueUpdated)
        );
    }
}
