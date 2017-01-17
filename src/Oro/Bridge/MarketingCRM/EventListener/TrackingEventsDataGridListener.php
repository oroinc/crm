<?php

namespace Oro\Bridge\MarketingCRM\EventListener;

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
        $config->getOrmQuery()
            ->resetFrom()
            ->addFrom($config->getExtendedEntityClassName(), 've')
            ->addInnerJoin('ve.webEvent', 'e');
    }

    /**
     * @param DatagridConfiguration $config
     */
    protected function addCustomersFilter(DatagridConfiguration $config)
    {
        $config->getOrmQuery()->addAndWhere(
            sprintf(
                'IDENTITY(ve.%s) IN (:customerIds)',
                ExtendHelper::buildAssociationName(self::CUSTOMER_CLASS_NAME, self::ASSOCIATION_KIND)
            )
        );

        $config->offsetAddToArrayByPath(
            DatagridConfiguration::DATASOURCE_BIND_PARAMETERS_PATH,
            [['name' => 'customerIds']]
        );
    }
}
