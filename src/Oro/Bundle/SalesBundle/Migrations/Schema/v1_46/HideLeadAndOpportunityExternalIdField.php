<?php

namespace Oro\Bundle\SalesBundle\Migrations\Schema\v1_46;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\EntityBundle\EntityConfig\DatagridScope;
use Oro\Bundle\EntityConfigBundle\Migration\UpdateEntityConfigFieldValueQuery;
use Oro\Bundle\EntityExtendBundle\Migration\ExtendOptionsManagerAwareInterface;
use Oro\Bundle\EntityExtendBundle\Migration\ExtendOptionsManagerAwareTrait;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\SalesBundle\Entity\Lead;
use Oro\Bundle\SalesBundle\Entity\Opportunity;

/**
 * Fully hides externalId field of Lead and Opportunity entities from grid, filters, view and edit form.
 */
class HideLeadAndOpportunityExternalIdField implements Migration, ExtendOptionsManagerAwareInterface
{
    use ExtendOptionsManagerAwareTrait;

    #[\Override]
    public function up(Schema $schema, QueryBag $queries): void
    {
        $this->hideLeadExternalId($queries);
        $this->hideOpportunityExternalId($queries);
    }

    private function hideLeadExternalId(QueryBag $queries): void
    {
        // Works in case when the affected field does not yet exist.
        $this->extendOptionsManager->mergeColumnOptions(
            'orocrm_sales_lead',
            'external_id',
            [
                'datagrid' => ['is_visible' => DatagridScope::IS_VISIBLE_FALSE],
                'form' => ['is_enabled' => false],
                'view' => ['is_displayable' => false],
            ]
        );

        // Works in case when the affected field already exists.
        $queries->addPostQuery(new UpdateEntityConfigFieldValueQuery(
            Lead::class,
            'external_id',
            'datagrid',
            'is_visible',
            DatagridScope::IS_VISIBLE_FALSE
        ));
        $queries->addPostQuery(new UpdateEntityConfigFieldValueQuery(
            Lead::class,
            'external_id',
            'form',
            'is_enabled',
            false
        ));
        $queries->addPostQuery(new UpdateEntityConfigFieldValueQuery(
            Lead::class,
            'external_id',
            'view',
            'is_displayable',
            false
        ));
    }

    private function hideOpportunityExternalId(QueryBag $queries): void
    {
        // Works in case when the affected field does not yet exist.
        $this->extendOptionsManager->mergeColumnOptions(
            'orocrm_sales_opportunity',
            'external_id',
            [
                'datagrid' => ['is_visible' => DatagridScope::IS_VISIBLE_FALSE],
                'form' => ['is_enabled' => false],
                'view' => ['is_displayable' => false],
            ]
        );

        // Works in case when the affected field already exists.
        $queries->addPostQuery(new UpdateEntityConfigFieldValueQuery(
            Opportunity::class,
            'external_id',
            'datagrid',
            'is_visible',
            DatagridScope::IS_VISIBLE_FALSE
        ));
        $queries->addPostQuery(new UpdateEntityConfigFieldValueQuery(
            Opportunity::class,
            'external_id',
            'form',
            'is_enabled',
            false
        ));
        $queries->addPostQuery(new UpdateEntityConfigFieldValueQuery(
            Opportunity::class,
            'external_id',
            'view',
            'is_displayable',
            false
        ));
    }
}
