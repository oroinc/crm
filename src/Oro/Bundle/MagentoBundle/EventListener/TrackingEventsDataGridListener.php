<?php

namespace Oro\Bundle\MagentoBundle\EventListener;

use Symfony\Component\HttpFoundation\RequestStack;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Event\PreBuild;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;

/**
 * Change source table and filter by magento customers.
 *
 * When extending a grid source table cannot be replaced in the config,
 * thus using a listener.
 *
 * Adds filter by customer identifiers, because it is extended association
 * with dynamically calculated  name.
 */
class TrackingEventsDataGridListener
{
    const ASSOCIATION_KIND = 'association';
    const CUSTOMER_CLASS_NAME = 'Oro\Bundle\MagentoBundle\Entity\Customer';

    /**
     * @param PreBuild $event
     */
    public function onPreBuild(PreBuild $event)
    {
        $config = $event->getConfig();

        $this->replaceSourceTable($config);
        $this->addCustomersFilter($config);
    }

    /**
     * @param DatagridConfiguration $config
     */
    protected function replaceSourceTable(DatagridConfiguration $config)
    {
        $sourceEntityName = $config['extended_entity_name'];

        $config->offsetSetByPath('[source][query][from]', [
            ['table' => $sourceEntityName, 'alias' => 've'],
        ]);

        $config->joinTable('inner', ['join' => 've.webEvent', 'alias' => 'e']);
    }

    /**
     * @param DatagridConfiguration $config
     */
    protected function addCustomersFilter(DatagridConfiguration $config)
    {
        $customerAssociationName = ExtendHelper::buildAssociationName(
            self::CUSTOMER_CLASS_NAME,
            self::ASSOCIATION_KIND
        );

        $config->offsetAddToArrayByPath('[source][query][where][and]', [
            sprintf('IDENTITY(ve.%s) IN (:customerIds)', $customerAssociationName)
        ]);

        $config->offsetAddToArrayByPath('[source][bind_parameters]', [
            ['name' => 'customerIds']
        ]);
    }
}
