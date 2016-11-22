<?php

namespace Oro\Bundle\SalesBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\EntityBundle\Tools\EntityRoutingHelper;
use Oro\Bundle\SalesBundle\Provider\Customer\ConfigProvider;

/**
 * @Route("/customer")
 */
class CustomerController extends Controller
{
    /**
     * @Route("/customer/grid-dialog/{entityClass}", name="oro_sales_customer_grid_dialog")
     * @Template("OroSalesBundle:Customer/dialog:grid.html.twig")
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
        $entityTargets = $this->getCustomerConfigProvider()->getCustomersData();

        return [
            'sourceEntityClassAlias' => $entityClassAlias,
            'entityTargets'          => $entityTargets,
            'params'                 => [
                'grid_path' => $this->generateUrl(
                    'oro_sales_customer_grid',
                    [],
                    UrlGeneratorInterface::ABSOLUTE_URL
                )
            ]
        ];
    }

    /**
     * @Route("/customer/grid/{entityClass}", name="oro_sales_customer_grid")
     * @Template("OroDataGridBundle:Grid:dialog/widget.html.twig")
     *
     * @param string $entityClass
     *
     * @return array
     */
    public function customerGridAction($entityClass = null)
    {
        $resolvedClass = $this->getRoutingHelper()->resolveEntityClass($entityClass);

        return [
            'gridName'     => $this->getCustomerConfigProvider()->getDefaultGrid($resolvedClass),
            'multiselect'  => false,
            'params'       => [
                'class_name' => $resolvedClass,
            ],
            'renderParams' => [],
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
        return $this->get('oro_sales.customer.config_provider');
    }
}
