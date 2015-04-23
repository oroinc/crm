<?php

namespace OroCRM\Bundle\ContactUsBundle\Migrations\Schema\v1_9;

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
        $fields = [
            [
                'entityName' => 'OroCRM\Bundle\ContactUsBundle\Entity\ContactRequest',
                'field' => 'createdAt',
                'value' => 'oro.ui.created_at',
                'replace' => 'orocrm.contactus.contactrequest.created_at.label'
            ],
            [
                'entityName' => 'OroCRM\Bundle\ContactUsBundle\Entity\ContactRequest',
                'field' => 'updatedAt',
                'value' => 'oro.ui.updated_at',
                'replace' => 'orocrm.contactus.contactrequest.updated_at.label'
            ],
        ];

        foreach ($fields as $field) {
            $queries->addQuery(
                new UpdateEntityConfigFieldValueQuery(
                    $field['entityName'],
                    $field['field'],
                    'entity',
                    'label',
                    $field['value'],
                    $field['replace']
                )
            );
            $queries->addQuery(
                new UpdateEntityConfigIndexFieldValueQuery(
                    $field['entityName'],
                    $field['field'],
                    'entity',
                    'label',
                    $field['value'],
                    $field['replace']
                )
            );
        }
    }
}
