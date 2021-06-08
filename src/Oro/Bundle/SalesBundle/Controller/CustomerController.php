<?php

namespace Oro\Bundle\SalesBundle\Controller;

use Oro\Bundle\DataGridBundle\Provider\MultiGridProvider;
use Oro\Bundle\EntityBundle\ORM\EntityAliasResolver;
use Oro\Bundle\EntityBundle\Tools\EntityRoutingHelper;
use Oro\Bundle\SalesBundle\Provider\Customer\AccountConfigProvider;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Provides grid dialog action
 * @Route("/customer")
 */
class CustomerController extends AbstractController
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedServices()
    {
        return array_merge(parent::getSubscribedServices(), [
            EntityRoutingHelper::class,
            'oro_sales.customer.account_config_provider' => AccountConfigProvider::class,
            MultiGridProvider::class,
            EntityAliasResolver::class
        ]);
    }

    /**
     * @Route("/customer/grid-dialog/{entityClass}", name="oro_sales_customer_grid_dialog")
     * @Template("@OroDataGrid/Grid/dialog/multi.html.twig")
     *
     * @param string $entityClass
     *
     * @return array
     */
    public function gridDialogAction($entityClass)
    {
        $resolvedClass = $this->get(EntityRoutingHelper::class)->resolveEntityClass($entityClass);
        $entityClassAlias = $this->get(EntityAliasResolver::class)
            ->getPluralAlias($resolvedClass);
        $entityTargets = $this->get(MultiGridProvider::class)->getEntitiesData(
            $this->get('oro_sales.customer.account_config_provider')->getCustomerClasses()
        );

        $request = $this->get('request_stack')->getCurrentRequest();
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
}
