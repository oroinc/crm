<?php

namespace Oro\Bundle\SalesBundle\Migrations\Schema\v1_39;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\ChannelBundle\Form\Type\ChannelSelectType;
use Oro\Bundle\EntityExtendBundle\Migration\ExtendOptionsManagerAwareInterface;
use Oro\Bundle\EntityExtendBundle\Migration\ExtendOptionsManagerAwareTrait;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\SalesBundle\Form\Type\B2bCustomerSelectType;

/**
 * This migration updates data_channel and customer fields which could remain after
 * upgrade process.
 */
class UpdateFormTypeForRemainingFields implements Migration, ExtendOptionsManagerAwareInterface
{
    use ExtendOptionsManagerAwareTrait;

    #[\Override]
    public function up(Schema $schema, QueryBag $queries): void
    {
        $this->updateFieldFormType(
            'orocrm_sales_lead',
            'data_channel',
            ChannelSelectType::class,
            'oro_channel_select_type'
        );
        $this->updateFieldFormType(
            'orocrm_sales_opportunity',
            'data_channel',
            ChannelSelectType::class,
            'oro_channel_select_type'
        );
        $this->updateFieldFormType(
            'orocrm_sales_opportunity',
            'customer',
            B2bCustomerSelectType::class,
            'oro_sales_b2bcustomer_select'
        );
    }

    private function updateFieldFormType(string $table, string $field, string $formType, string $replaceFormType): void
    {
        if (!$this->extendOptionsManager->hasColumnOptions($table, $field)) {
            return;
        }

        $options = $this->extendOptionsManager->getColumnOptions($table, $field);
        if ($options['form']['form_type'] !== $replaceFormType) {
            return;
        }

        $this->extendOptionsManager->mergeColumnOptions($table, $field, ['form' => ['form_type' => $formType]]);
    }
}
