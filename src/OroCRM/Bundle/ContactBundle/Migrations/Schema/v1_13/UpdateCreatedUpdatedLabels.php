<?php

namespace Oro\Bundle\ContactBundle\Migrations\Schema\v1_13;

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
                'entityName' => 'Oro\Bundle\ContactBundle\Entity\Contact',
                'field' => 'createdAt',
                'value' => 'oro.ui.created_at',
                'replace' => 'oro.contact.created_at.label'
            ],
            [
                'entityName' => 'Oro\Bundle\ContactBundle\Entity\Contact',
                'field' => 'updatedAt',
                'value' => 'oro.ui.updated_at',
                'replace' => 'oro.contact.updated_at.label'
            ],
            [
                'entityName' => 'Oro\Bundle\ContactBundle\Entity\ContactAddress',
                'field' => 'created',
                'value' => 'oro.ui.created_at',
                'replace' => 'oro.contact.contactaddress.created.label'
            ],
            [
                'entityName' => 'Oro\Bundle\ContactBundle\Entity\ContactAddress',
                'field' => 'updated',
                'value' => 'oro.ui.updated_at',
                'replace' => 'oro.contact.contactaddress.updated.label'
            ]
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
