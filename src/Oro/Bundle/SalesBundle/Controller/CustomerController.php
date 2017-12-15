<?php

namespace Oro\Bundle\SalesBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\DataGridBundle\Provider\MultiGridProvider;
use Oro\Bundle\EntityBundle\Tools\EntityRoutingHelper;
use Oro\Bundle\SalesBundle\Provider\Customer\ConfigProvider;

/**
 * @Route("/customer")
 */
class CustomerController extends Controller
{
    /**
     * @Route("/customer/grid-dialog/{entityClass}", name="oro_sales_customer_grid_dialog")
     * @Template("OroDataGridBundle:Grid/dialog:multi.html.twig")
     *
     * @param string $entityClass
     *
     * @return array
     */
    public function gridDialogAction($entityClass)
    {
        $resolvedClass = $this->getRoutingHelper()->resolveEntityClass($entityClass);
        $entityClassAlias = $this->get('oro_entity.entity_alias_resolver')
            ->getPluralAlias($resolvedClass);
        $entityTargets = $this->getMultiGridProvider()->getEntitiesData(
            $this->getCustomerConfigProvider()->getCustomerClasses()
        );

        $request = $this->container->get('request_stack')->getCurrentRequest();
        $params = [
            'params' => $request->get('params', [])
        ];
        if (isset($entityTargets[0]['gridName'], $entityTargets[0]['className'])) {
            $params = array_merge_recursive(
                $params,
                [
                    'gridName' => $entityTargets[0]['gridName'],
                    'params' => [
                        'entity_class' => $entityTargets[0]['className']
                    ]
                ]
            );
        }

        return [
            'gridWidgetName'         => 'customer-multi-grid-widget',
            'dialogWidgetName'       => 'customer-dialog',
            'params'                 => $params,
            'sourceEntityClassAlias' => $entityClassAlias,
            'entityTargets'          => $entityTargets
        ];
    }

    /**
     * @return EntityRoutingHelper
     */
    protected function getRoutingHelper()
    {
        return $this->get('oro_entity.routing_helper');
    }

    /**
     * @return ConfigProvider
     */
    protected function getCustomerConfigProvider()
    {
        return $this->get('oro_sales.customer.account_config_provider');
    }

    /**
     * @return MultiGridProvider
     */
    protected function getMultiGridProvider()
    {
        return $this->get('oro_datagrid.multi_grid_provider');
    }
}
