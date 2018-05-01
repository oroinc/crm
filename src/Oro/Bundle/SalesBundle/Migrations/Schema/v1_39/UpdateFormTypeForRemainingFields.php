<?php

namespace Oro\Bundle\SalesBundle\Migrations\Schema\v1_39;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\ChannelBundle\Form\Type\ChannelSelectType;
use Oro\Bundle\EntityExtendBundle\Migration\ExtendOptionsManager;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\SalesBundle\Form\Type\B2bCustomerSelectType;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * This migration updates data_channel and customer fields which could remain after
 * upgrade process.
 */
class UpdateFormTypeForRemainingFields implements Migration, ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->updateFieldFormType('orocrm_sales_lead', 'data_channel', ChannelSelectType::class);
        $this->updateFieldFormType('orocrm_sales_opportunity', 'data_channel', ChannelSelectType::class);
        $this->updateFieldFormType('orocrm_sales_funnel', 'data_channel', ChannelSelectType::class);
        $this->updateFieldFormType('orocrm_sales_opportunity', 'customer', B2bCustomerSelectType::class);
    }

    /**
     * @param string $table
     * @param string $field
     * @param string $formType
     */
    private function updateFieldFormType(string $table, string $field, string $formType)
    {
        /** @var ExtendOptionsManager $extendOptionsManager */
        $extendOptionsManager = $this->container->get('oro_entity_extend.migration.options_manager');

        if ($extendOptionsManager->hasColumnOptions($table, $field)) {
            $extendOptionsManager->mergeColumnOptions($table, $field, [
                'form' => [
                    'form_type' => $formType
                ]
            ]);
        }
    }
}
