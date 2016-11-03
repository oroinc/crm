<?php

namespace Oro\Bundle\SalesBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\EntityBundle\Tools\EntityRoutingHelper;
use Oro\Bundle\SalesBundle\Provider\CustomerConfigProvider;

/**
 * @Route("/customer")
 */
class CustomerController extends Controller
{
    /**
     * @Route("/customer/grid-dialog/{entityClass}", name="oro_sales_customer_grid_dialog")
     * @Template("OroSalesBundle:Customer/dialog:grid.html.twig")
     */
    public function gridDialogAction($entityClass)
    {
        $resolvedClass = $this->getRoutingHelper()->resolveEntityClass($entityClass);
        $entityClassAlias = $this->get('oro_entity.entity_alias_resolver')
            ->getPluralAlias($resolvedClass);
        $entityTargets = $this->getCustomerConfigProvider()->getCustomersData($resolvedClass);

        return [
            'sourceEntityClassAlias' => $entityClassAlias,
            'entityTargets'          => $entityTargets,
            'params'                 => [
                'grid_path' => $this->generateUrl(
                    'oro_sales_customer_grid',
                    [
                        'entityClass' => $entityTargets[0]['className'],
                    ],
                    UrlGeneratorInterface::ABSOLUTE_URL
                )
            ]
        ];
    }

    /**
     * @Route("/customer/grid/{entityClass}", name="oro_sales_customer_grid")
     * @Template("OroDataGridBundle:Grid:dialog/widget.html.twig")
     */
    public function customerGridAction($entityClass)
    {
        $resolvedClass = $this->getRoutingHelper()->resolveEntityClass($entityClass);

        return [
            'gridName'     => $this->getCustomerConfigProvider()->getCustomerGridByEntity($resolvedClass),
            'multiselect'  => false,
            'params'       => [
                'class_name' => $resolvedClass,
            ],
            'renderParams' => []
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
     * @return CustomerConfigProvider
     */
    protected function getCustomerConfigProvider()
    {
        return $this->get('oro_sales.customer_config_provider');
    }
}
