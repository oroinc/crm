<?php

namespace OroCRM\Bundle\SalesBundle\Migrations\Schema\v1_16;

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
                'entityName' => 'OroCRM\Bundle\SalesBundle\Entity\Lead',
                'field' => 'createdAt',
                'value' => 'oro.ui.created_at',
                'replace' => 'orocrm.sales.lead.created_at.label'
            ],
            [
                'entityName' => 'OroCRM\Bundle\SalesBundle\Entity\Lead',
                'field' => 'updatedAt',
                'value' => 'oro.ui.updated_at',
                'replace' => 'orocrm.sales.lead.updated_at.label'
            ],
            [
                'entityName' => 'OroCRM\Bundle\SalesBundle\Entity\Opportunity',
                'field' => 'createdAt',
                'value' => 'oro.ui.created_at',
                'replace' => 'orocrm.sales.opportunity.created_at.label'
            ],
            [
                'entityName' => 'OroCRM\Bundle\SalesBundle\Entity\Opportunity',
                'field' => 'updatedAt',
                'value' => 'oro.ui.updated_at',
                'replace' => 'orocrm.sales.opportunity.updated_at.label'
            ],
            [
                'entityName' => 'OroCRM\Bundle\SalesBundle\Entity\SalesFunnel',
                'field' => 'createdAt',
                'value' => 'oro.ui.created_at',
                'replace' => 'orocrm.sales.salesfunnel.created_at.label'
            ],
            [
                'entityName' => 'OroCRM\Bundle\SalesBundle\Entity\SalesFunnel',
                'field' => 'updatedAt',
                'value' => 'oro.ui.updated_at',
                'replace' => 'orocrm.sales.salesfunnel.updated_at.label'
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
