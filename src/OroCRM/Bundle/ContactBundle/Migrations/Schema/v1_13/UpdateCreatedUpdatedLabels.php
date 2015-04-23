<?php

namespace OroCRM\Bundle\ContactBundle\Migrations\Schema\v1_13;

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
                'entityName' => 'OroCRM\Bundle\ContactBundle\Entity\Contact',
                'field' => 'createdAt',
                'value' => 'oro.ui.created_at',
                'replace' => 'orocrm.contact.created_at.label'
            ],
            [
                'entityName' => 'OroCRM\Bundle\ContactBundle\Entity\Contact',
                'field' => 'updatedAt',
                'value' => 'oro.ui.updated_at',
                'replace' => 'orocrm.contact.updated_at.label'
            ],
            [
                'entityName' => 'OroCRM\Bundle\ContactBundle\Entity\ContactAddress',
                'field' => 'created',
                'value' => 'oro.ui.created_at',
                'replace' => 'orocrm.contact.contactaddress.created.label'
            ],
            [
                'entityName' => 'OroCRM\Bundle\ContactBundle\Entity\ContactAddress',
                'field' => 'updated',
                'value' => 'oro.ui.updated_at',
                'replace' => 'orocrm.contact.contactaddress.updated.label'
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
