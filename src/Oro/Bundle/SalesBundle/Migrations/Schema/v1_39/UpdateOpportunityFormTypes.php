<?php

namespace Oro\Bundle\SalesBundle\Migrations\Schema\v1_39;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\ContactBundle\Form\Type\ContactSelectType;
use Oro\Bundle\EntityConfigBundle\Migration\UpdateEntityConfigEntityValueQuery;
use Oro\Bundle\EntityConfigBundle\Migration\UpdateEntityConfigFieldValueQuery;
use Oro\Bundle\FormBundle\Form\Type\OroMoneyType;
use Oro\Bundle\FormBundle\Form\Type\OroPercentType;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\SalesBundle\Entity\Opportunity;
use Oro\Bundle\SalesBundle\Form\Type\OpportunitySelectType;

class UpdateOpportunityFormTypes implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $queries->addQuery(
            new UpdateEntityConfigEntityValueQuery(
                Opportunity::class,
                'form',
                'form_type',
                OpportunitySelectType::class,
                'oro_sales_opportunity_select'
            )
        );

        $queries->addQuery($this->getFieldUpdateQuery('contact', ContactSelectType::class, 'oro_contact_select'));
        $queries->addQuery($this->getFieldUpdateQuery('probability', OroPercentType::class, 'oro_percent'));
        $queries->addQuery($this->getFieldUpdateQuery('budgetAmountValue', OroMoneyType::class, 'oro_money'));
        $queries->addQuery($this->getFieldUpdateQuery('closeRevenueValue', OroMoneyType::class, 'oro_money'));
    }

    private function getFieldUpdateQuery(
        string $column,
        string $formType,
        string $replaceValue
    ): UpdateEntityConfigFieldValueQuery {
        return new UpdateEntityConfigFieldValueQuery(
            Opportunity::class,
            $column,
            'form',
            'form_type',
            $formType,
            $replaceValue
        );
    }
}
